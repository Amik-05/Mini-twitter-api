<?php

namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;


class PostController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
           'content' =>['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'exists:posts,id'],
        ]);

        $user = $request->user();

        $post = $user->posts()->create([
           'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return response()->json([
            'message' =>'Пост создан',
            'post' =>$post,
        ], 201);
    }

    public function index()
    {
        $posts = Post::with(['user', 'replies.user'])
            ->whereNull('parent_id')
            ->latest()
            ->get();


        return response()->json([
            'post' => $posts,
        ]);
    }

    public function reply(Request $request, Post $post)
    {
        $validated = $request->validate([
           'content' => ['required', 'string', 'max:1000'],
        ]);
        $reply = $request->user()->posts()->create([
            'content' => $validated['content'],
            'parent_id' => $post->id,
        ]);
        return response()->json([
           'message' => 'Ответ опубликован',
            'reply' => $reply,
        ], 201);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000']
        ]);

        $post->update([
            'content' => $validated['content']
        ]);

        return response()->json([
            'message' => 'Контент обновлен',
            'post' => [
                'id' => $post->id,
                'content' => $post->content,
                'updated_at' => $post->updated_at
            ]
        ]);
    }

    public function like(Post $post, Request $request)
    {
        $user = $request->user();

        $user->likedPosts()->syncWithoutDetaching([$post->id]);

        return response()->json([
            'message' => 'Лайк поставлен',
        ]);
    }

    public function unlike(Post $post, Request $request)
    {
        $user = $request->user();

        $user->likedPosts()->detach($post->id);

        return response()->json([
           'message' => 'Лайк снят',
        ]);
    }

    public function feed(Request $request)
    {
        $authUser = $request->user();

        $posts = Post::with([
                'user',
                'replies.user',
                'likedByUsers',
                'replies.likedByUsers'
            ])
            ->whereNull('parent_id')
            ->latest()
            ->paginate(10);

        $posts->getCollection()->transform(function (Post $post) use ($authUser){
            return $this->transformPost($post, $authUser);
        });

        return  response()->json([
            'data' => $posts->items(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total()
            ]
        ]);

    }

    public function transformPost(Post $post, ?User $authUser):array
    {
        return [
            'id' => $post->id,
            'content' => $post->content,
            'author' => $post->user()
            ?[
                'id' => $post->user->id,
                'name' => $post->user->name,
            ]
            :[
                'id' => null,
                'name' => 'Deleted user'
            ],
            'likes_count' => $post->likedByUsers->count(),
            'liked_by_me' => $authUser
                ? $post->likedByUsers->contains($authUser->id)
                : false,
            'can_delete' => $authUser && $authUser->can('delete', $post),
            'can_edit' => $authUser && $authUser->can('update', $post),
            'created_at' => $post->created_at,
            'replies' => $post->replies->map(function ($reply) use ($authUser) {
                return [
                    'id' => $reply->id,
                    'content' => $reply->content,
                    'created_at' => $reply->created_at,
                    'can_delete' => $authUser && $authUser->can('delete', $reply),
                ];
            }),


        ];
    }

    public function destroy(Request $request, Post $post)
    {

        $this->authorize('delete', $post);

        $post->delete();

        return response()->json([
            'message' => 'Пост удален'
        ]);
    }

    public function toggleLike(Post $post)
    {
        $user = auth()->user();

        $alreadyLiked = $post->likedByUsers()
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyLiked)
        {
            $post->likedByUsers()->detach($user->id);

            return response()->json([
                'liked' => false,
                'likes_count' =>$post->likedByUsers()->count(),
            ]);
        }

        $post->likedByUsers()->attach($user->id);

        return response()->json([
            'liked' => true,
            'likes_count' =>$post->likedByUsers()->count(),
        ]);

    }

}
