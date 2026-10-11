<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->count() < 2) {
            return;
        }

        $demoUser = User::where('email', 'user@novasocial.test')->first() ?? $users->first();
        $otherUsers = $users->where('id', '!=', $demoUser->id);

        if ($otherUsers->isEmpty()) {
            return;
        }

        $sender1 = $otherUsers->first();
        $sender2 = $otherUsers->skip(1)->first() ?? $sender1;

        // 1. Follow notification (unread)
        Notification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\UserFollowedNotification',
            'notifiable_type' => 'user',
            'notifiable_id' => $demoUser->id,
            'data' => json_encode([
                'actor_id' => $sender1->id,
                'actor_name' => $sender1->name,
                'actor_username' => $sender1->username,
                'message' => "{$sender1->name} started following you.",
                'action_url' => "/profiles/{$sender1->username}",
            ]),
            'read_at' => null,
            'created_at' => now()->subMinutes(30),
            'updated_at' => now()->subMinutes(30),
        ]);

        // 2. Like notification (read)
        Notification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\PostLikedNotification',
            'notifiable_type' => 'user',
            'notifiable_id' => $demoUser->id,
            'data' => json_encode([
                'actor_id' => $sender2->id,
                'actor_name' => $sender2->name,
                'actor_username' => $sender2->username,
                'message' => "{$sender2->name} liked your post.",
                'action_url' => '/posts/1',
            ]),
            'read_at' => now()->subHours(1),
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(1),
        ]);

        // 3. Comment notification (unread)
        Notification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\PostCommentedNotification',
            'notifiable_type' => 'user',
            'notifiable_id' => $demoUser->id,
            'data' => json_encode([
                'actor_id' => $sender1->id,
                'actor_name' => $sender1->name,
                'actor_username' => $sender1->username,
                'message' => "{$sender1->name} commented on your photo.",
                'action_url' => '/posts/1#comments',
            ]),
            'read_at' => null,
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);
    }
}
