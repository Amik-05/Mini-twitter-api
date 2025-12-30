<?php

namespace App\Http\Controllers;
use App\Models\Post;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    public function toggle(Request $request, Post $post)
    {
        $user = $request->user();

        $alreadyLiked = $post->likedByUsers()
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyLiked)
        {
            $post->likedByUsers()->detach($user->id);
        }
        else
        {
            $post->likedByUsers()->attach($user->id);
        }

        return response()->json([
            'post_id' =>$post->id,
            'liked_by_me' => !$alreadyLiked,
            'likes_count' => $post->likedByUsers()->count(),
        ]);
    }
}
