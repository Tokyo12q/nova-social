<?php

namespace Database\Seeders;

use App\Models\Hashtag;
use App\Models\Post;
use App\Models\PostHashtag;
use App\Models\PostMedia;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        // 1. Predefined set of realistic hashtags
        $tags = [
            'laravel', 'webdev', 'technology', 'design', 'architecture',
            'nature', 'photography', 'travel', 'minimalism', 'coffee',
            'art', 'lifestyle', 'community', 'coding', 'innovation',
        ];

        $hashtagModels = [];
        foreach ($tags as $tag) {
            $hashtagModels[$tag] = Hashtag::firstOrCreate(
                ['name' => strtolower($tag)],
                ['posts_count' => 0]
            );
        }

        // Realistic seed posts templates
        $postsData = [
            [
                'caption' => 'Refactoring legacy code into clean modern architecture. There is nothing like the feeling of deleting 500 lines of messy code! #laravel #webdev #coding',
                'location' => 'San Francisco, CA',
                'tags' => ['laravel', 'webdev', 'coding'],
                'media' => [
                    ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1080&fit=crop'],
                ],
            ],
            [
                'caption' => 'Golden hour mist rising over the redwood forest. Nature always provides the best color palette. #nature #photography #travel',
                'location' => 'Muir Woods, CA',
                'tags' => ['nature', 'photography', 'travel'],
                'media' => [
                    ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?w=1080&fit=crop'],
                    ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1511497584788-87676104235f?w=1080&fit=crop'],
                ],
            ],
            [
                'caption' => 'Minimalist workspace setup for deep focus work today. Pour-over coffee is ready. #minimalism #design #coffee',
                'location' => 'Seattle, WA',
                'tags' => ['minimalism', 'design', 'coffee'],
                'media' => [
                    ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?w=1080&fit=crop'],
                ],
            ],
            [
                'caption' => 'Exploring the brutalist concrete structures downtown. Concrete geometric beauty. #architecture #design #art',
                'location' => 'Berlin, Germany',
                'tags' => ['architecture', 'design', 'art'],
                'media' => [
                    ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1080&fit=crop'],
                    ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1080&fit=crop'],
                    ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=1080&fit=crop'],
                ],
            ],
            [
                'caption' => 'Quick preview walkthrough of our upcoming design system tokens and responsive components. #design #technology #innovation',
                'location' => 'Vancouver, Canada',
                'tags' => ['design', 'technology', 'innovation'],
                'media' => [
                    [
                        'type' => 'video',
                        'url' => 'https://example.com/videos/design-system-preview.mp4',
                        'thumbnail_url' => 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=1080&fit=crop',
                    ],
                ],
            ],
        ];

        // Seed template posts across active users
        foreach ($postsData as $index => $item) {
            $user = $users[$index % $users->count()];

            $post = Post::create([
                'user_id' => $user->id,
                'caption' => $item['caption'],
                'location' => $item['location'],
                'comments_disabled' => false,
                'likes_count' => 0,
                'comments_count' => 0,
                'saves_count' => 0,
            ]);

            // Add media
            foreach ($item['media'] as $order => $media) {
                PostMedia::create([
                    'post_id' => $post->id,
                    'media_type' => $media['type'],
                    'url' => $media['url'],
                    'thumbnail_url' => $media['thumbnail_url'] ?? null,
                    'width' => 1080,
                    'height' => 1080,
                    'order' => $order,
                ]);
            }

            // Link hashtags
            foreach ($item['tags'] as $tagName) {
                if (isset($hashtagModels[$tagName])) {
                    $hashtag = $hashtagModels[$tagName];
                    PostHashtag::firstOrCreate([
                        'post_id' => $post->id,
                        'hashtag_id' => $hashtag->id,
                    ], [
                        'created_at' => now(),
                    ]);
                }
            }
        }

        // Additional generated posts
        foreach ($users->take(10) as $user) {
            $post = Post::create([
                'user_id' => $user->id,
                'caption' => fake()->paragraph(2) . ' #lifestyle #community',
                'location' => fake()->optional(0.7)->city(),
                'comments_disabled' => fake()->boolean(10),
                'likes_count' => 0,
                'comments_count' => 0,
                'saves_count' => 0,
            ]);

            PostMedia::create([
                'post_id' => $post->id,
                'media_type' => 'image',
                'url' => 'https://images.unsplash.com/photo-' . fake()->numberBetween(1500000000, 1600000000) . '?w=1080&fit=crop',
                'thumbnail_url' => null,
                'width' => 1080,
                'height' => 1080,
                'order' => 0,
            ]);

            $randomTag = $hashtagModels['lifestyle'] ?? null;
            if ($randomTag) {
                PostHashtag::firstOrCreate([
                    'post_id' => $post->id,
                    'hashtag_id' => $randomTag->id,
                ], [
                    'created_at' => now(),
                ]);
            }
        }

        // Recalculate posts_count on hashtags
        foreach (Hashtag::all() as $tag) {
            $tag->update([
                'posts_count' => PostHashtag::where('hashtag_id', $tag->id)->count(),
            ]);
        }
    }
}
