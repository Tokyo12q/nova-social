<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $posts = Post::where('comments_disabled', false)->get();
        $users = User::all();

        if ($posts->isEmpty() || $users->isEmpty()) {
            return;
        }

        $realisticComments = [
            'Absolutely stunning capture! What camera setup did you use?',
            'Totally agree on this. Clean code and proper abstraction save weeks of headache down the road.',
            'The aesthetic and color grading here is completely on point.',
            'Love this perspective! Reminds me of my trip to the coast last summer.',
            'Such a great insight. Bookmarking this for my upcoming architecture review.',
            'Incredible work! Keep pushing the boundaries.',
            'The attention to detail in this is remarkable.',
        ];

        $realisticReplies = [
            'Thanks so much! Really appreciate the feedback.',
            'Exactly! Glad you found it useful.',
            'Shot this on a Sony A7 IV with a 24-70mm GM lens!',
            'Appreciate the kind words, definitely looking forward to sharing more soon.',
        ];

        foreach ($posts as $post) {
            // Seed 2 to 4 top-level comments
            $numComments = rand(2, 4);
            for ($i = 0; $i < $numComments; $i++) {
                $commentAuthor = $users->random();
                $topComment = Comment::create([
                    'post_id' => $post->id,
                    'user_id' => $commentAuthor->id,
                    'parent_id' => null,
                    'content' => $realisticComments[array_rand($realisticComments)],
                    'likes_count' => 0,
                ]);

                // 50% chance of a reply
                if (fake()->boolean(50)) {
                    $replyAuthor = $users->where('id', '!=', $commentAuthor->id)->random();
                    Comment::create([
                        'post_id' => $post->id,
                        'user_id' => $replyAuthor->id,
                        'parent_id' => $topComment->id,
                        'content' => $realisticReplies[array_rand($realisticReplies)],
                        'likes_count' => 0,
                    ]);
                }
            }

            // Sync post comments_count
            $post->update([
                'comments_count' => Comment::where('post_id', $post->id)->count(),
            ]);
        }
    }
}
