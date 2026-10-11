<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\Save;
use App\Models\User;
use Illuminate\Database\Seeder;

class EngagementSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $posts = Post::all();
        $comments = Comment::all();

        if ($users->isEmpty() || $posts->isEmpty()) {
            return;
        }

        // 1. Likes on posts
        foreach ($posts as $post) {
            $likers = $users->random(rand(2, min(8, $users->count())));
            foreach ($likers as $liker) {
                Like::firstOrCreate([
                    'user_id' => $liker->id,
                    'likeable_type' => 'post',
                    'likeable_id' => $post->id,
                ]);
            }
            $post->update([
                'likes_count' => Like::where('likeable_type', 'post')->where('likeable_id', $post->id)->count(),
            ]);
        }

        // 2. Likes on comments
        foreach ($comments as $comment) {
            if (fake()->boolean(70)) {
                $likers = $users->random(rand(1, min(4, $users->count())));
                foreach ($likers as $liker) {
                    Like::firstOrCreate([
                        'user_id' => $liker->id,
                        'likeable_type' => 'comment',
                        'likeable_id' => $comment->id,
                    ]);
                }
                $comment->update([
                    'likes_count' => Like::where('likeable_type', 'comment')->where('likeable_id', $comment->id)->count(),
                ]);
            }
        }

        // 3. Post Saves (bookmarks)
        foreach ($posts as $post) {
            $savers = $users->random(rand(1, min(5, $users->count())));
            foreach ($savers as $saver) {
                Save::firstOrCreate([
                    'user_id' => $saver->id,
                    'post_id' => $post->id,
                ]);
            }
            $post->update([
                'saves_count' => Save::where('post_id', $post->id)->count(),
            ]);
        }
    }
}
