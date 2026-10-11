<?php

namespace Database\Seeders;

use App\Models\Story;
use App\Models\StoryView;
use App\Models\User;
use Illuminate\Database\Seeder;

class StorySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        $storySamples = [
            [
                'caption' => 'Morning studio session vibes ☕',
                'media_type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=1080&h=1920&fit=crop',
            ],
            [
                'caption' => 'Sunset over the bay today is mesmerizing 🌅',
                'media_type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1080&h=1920&fit=crop',
            ],
            [
                'caption' => 'Working on secret updates! Stay tuned 🚀',
                'media_type' => 'image',
                'url' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1080&h=1920&fit=crop',
            ],
            [
                'caption' => 'Live coding demo snippet',
                'media_type' => 'video',
                'url' => 'https://example.com/stories/demo-code.mp4',
            ],
        ];

        foreach ($users->take(6) as $index => $user) {
            $sample = $storySamples[$index % count($storySamples)];

            // Create active story
            $story = Story::create([
                'user_id' => $user->id,
                'media_type' => $sample['media_type'],
                'media_url' => $sample['url'],
                'caption' => $sample['caption'],
                'views_count' => 0,
                'expires_at' => now()->addHours(rand(8, 24)),
            ]);

            // Add story views
            $viewers = $users->where('id', '!=', $user->id)->random(rand(2, min(6, $users->count() - 1)));
            foreach ($viewers as $viewer) {
                StoryView::firstOrCreate([
                    'story_id' => $story->id,
                    'viewer_id' => $viewer->id,
                ], [
                    'viewed_at' => now()->subMinutes(rand(5, 120)),
                ]);
            }

            $story->update([
                'views_count' => StoryView::where('story_id', $story->id)->count(),
            ]);

            // Create one expired story for realistic archive testing
            if ($index === 0) {
                Story::create([
                    'user_id' => $user->id,
                    'media_type' => 'image',
                    'media_url' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1080&h=1920&fit=crop',
                    'caption' => 'Yesterday evening event highlights 🎉',
                    'views_count' => 5,
                    'expires_at' => now()->subHours(2),
                    'created_at' => now()->subHours(26),
                ]);
            }
        }
    }
}
