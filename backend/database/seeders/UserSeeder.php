<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@novasocial.test'],
            [
                'name' => 'Nova Administrator',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'is_private' => false,
                'email_verified_at' => now(),
            ]
        );
        Profile::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'bio' => 'Nova Social Chief Administrator & System Guardian.',
                'website' => 'https://novasocial.test',
                'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&fit=crop&crop=faces',
                'cover_url' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=1200&h=400&fit=crop',
                'location' => 'San Francisco, CA',
            ]
        );

        // 2. Moderator user
        $moderator = User::firstOrCreate(
            ['email' => 'moderator@novasocial.test'],
            [
                'name' => 'Nova Moderator',
                'username' => 'moderator',
                'password' => Hash::make('password'),
                'role' => 'moderator',
                'is_active' => true,
                'is_private' => false,
                'email_verified_at' => now(),
            ]
        );
        Profile::firstOrCreate(
            ['user_id' => $moderator->id],
            [
                'bio' => 'Ensuring a safe, vibrant, and welcoming community for everyone.',
                'website' => 'https://novasocial.test/community',
                'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&fit=crop&crop=faces',
                'cover_url' => 'https://images.unsplash.com/photo-1557683316-973673baf926?w=1200&h=400&fit=crop',
                'location' => 'Austin, TX',
            ]
        );

        // 3. Demo normal user
        $demoUser = User::firstOrCreate(
            ['email' => 'user@novasocial.test'],
            [
                'name' => 'Nova User',
                'username' => 'novauser',
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
                'is_private' => false,
                'email_verified_at' => now(),
            ]
        );
        Profile::firstOrCreate(
            ['user_id' => $demoUser->id],
            [
                'bio' => 'Passionate software craftsman, open-source enthusiast, and coffee lover.',
                'website' => 'https://github.com/novauser',
                'avatar_url' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=400&fit=crop&crop=faces',
                'cover_url' => 'https://images.unsplash.com/photo-1579546929518-9e396f3cc809?w=1200&h=400&fit=crop',
                'location' => 'Seattle, WA',
            ]
        );

        // 4. Specific seeded community members with realistic personas
        $communityUsers = [
            [
                'name' => 'Elena Rostova',
                'username' => 'elena_design',
                'email' => 'elena@novasocial.test',
                'role' => 'user',
                'is_private' => false,
                'bio' => 'Digital product designer & design systems advocate. Exploring the intersection of typography and interfaces.',
                'website' => 'https://elenarostova.design',
                'location' => 'Berlin, Germany',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&fit=crop&crop=faces',
            ],
            [
                'name' => 'Marcus Vance',
                'username' => 'marcusv',
                'email' => 'marcus@novasocial.test',
                'role' => 'user',
                'is_private' => false,
                'bio' => 'Landscape & architectural photographer. Chasing light across the Pacific Northwest.',
                'website' => 'https://marcusvance.photo',
                'location' => 'Portland, OR',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&fit=crop&crop=faces',
            ],
            [
                'name' => 'Sophia Chen',
                'username' => 'sophia_code',
                'email' => 'sophia@novasocial.test',
                'role' => 'user',
                'is_private' => false,
                'bio' => 'Full-stack engineer building high-throughput distributed systems with Laravel & Go.',
                'website' => 'https://sophiachen.dev',
                'location' => 'Vancouver, Canada',
                'avatar' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=400&fit=crop&crop=faces',
            ],
            [
                'name' => 'Liam O\'Connor',
                'username' => 'liam_travels',
                'email' => 'liam@novasocial.test',
                'role' => 'user',
                'is_private' => false,
                'bio' => 'Nomadic travel writer and culinary explorer. Visiting 50 countries before 30.',
                'website' => 'https://liamroutes.com',
                'location' => 'Dublin, Ireland',
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&fit=crop&crop=faces',
            ],
            [
                'name' => 'Aria Thorne',
                'username' => 'ariathorne',
                'email' => 'aria@novasocial.test',
                'role' => 'user',
                'is_private' => true, // Private account example
                'bio' => 'Private journal of creative thoughts and studio explorations. Follow request required.',
                'website' => null,
                'location' => 'London, UK',
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=400&fit=crop&crop=faces',
            ],
        ];

        foreach ($communityUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'username' => $userData['username'],
                    'password' => Hash::make('password'),
                    'role' => $userData['role'],
                    'is_active' => true,
                    'is_private' => $userData['is_private'],
                    'email_verified_at' => now(),
                ]
            );

            Profile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'bio' => $userData['bio'],
                    'website' => $userData['website'],
                    'avatar_url' => $userData['avatar'],
                    'cover_url' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=1200&h=400&fit=crop',
                    'location' => $userData['location'],
                ]
            );
        }

        // 5. Additional realistic users via factory
        User::factory(12)->create()->each(function (User $user) {
            Profile::factory()->create(['user_id' => $user->id]);
        });
    }
}
