<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function destroy(Request $request)
    {
        $user = $request->user();

        $this->authorize('delete', $user);

        $user->tokens()->delete();

        $user->delete();

        return response()->json([
            'message'=>'Аккаунт удален'
        ]);
    }
}
