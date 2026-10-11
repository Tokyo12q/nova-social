<?php

namespace Database\Seeders;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Database\Seeder;

class FollowSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->count() < 2) {
            return;
        }

        foreach ($users as $user) {
            // Pick a random subset of other users to follow
            $targetUsers = $users->where('id', '!=', $user->id)->random(min(5, $users->count() - 1));

            foreach ($targetUsers as $target) {
                $status = $target->is_private ? (fake()->boolean(60) ? 'pending' : 'accepted') : 'accepted';

                Follow::firstOrCreate(
                    [
                        'follower_id' => $user->id,
                        'following_id' => $target->id,
                    ],
                    [
                        'status' => $status,
                    ]
                );
            }
        }
    }
}
