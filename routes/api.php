<?php

use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostLikeController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
Route::middleware('auth:sanctum')
    ->post('/posts', [PostController::class, 'store']);

Route::get('/posts', [PostController::class, 'index']);

Route::middleware('auth:sanctum')
    ->post('/posts/{post}/reply', [PostController::class, 'reply']);

Route::middleware('auth:sanctum')->group(function (){
    Route::post('/posts/{post}/like', [PostLikeController::class, 'toggle']);
});

Route::middleware('auth:sanctum')
    ->post('/posts/{post}/unlike', [PostController::class, 'unlike']);

Route::middleware('auth:sanctum')->get('/feed', [PostController::class, 'feed']);

Route::delete('/posts/{post}', [PostController::class, 'destroy'])
    ->middleware('auth:sanctum');

Route::middleware('auth:sanctum')
    ->post('/posts/{post}/like', [PostController::class, 'toggleLike']);

Route::middleware('auth:sanctum')->group(function (){
    Route::put('/posts/{post}', [PostController::class, 'update']);
});

Route::middleware('auth:sanctum')->group(function (){
    Route::delete('/me', [AccountController::class, 'destroy']);
});


