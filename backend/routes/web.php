<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    try {
        $stats = [
            'users' => \App\Models\User::count(),
            'posts' => \App\Models\Post::count(),
            'comments' => \App\Models\Comment::count(),
            'stories' => \App\Models\Story::count(),
            'conversations' => \App\Models\Conversation::count(),
        ];
    } catch (\Throwable $e) {
        $stats = [
            'users' => 0,
            'posts' => 0,
            'comments' => 0,
            'stories' => 0,
            'conversations' => 0,
        ];
    }

    return view('welcome', compact('stats'));
});

