<?php

namespace Tests\Feature\Auth;

use App\Models\Follow;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_retrieve_their_profile_and_counts(): void
    {
        $user = User::factory()->create([
            'username' => 'alice_me',
            'email' => 'alice_me@example.com',
        ]);

        Profile::factory()->create([
            'user_id' => $user->id,
            'bio' => 'Building Nova Social',
            'location' => 'San Francisco, CA',
        ]);

        $follower = User::factory()->create();
        Follow::create([
            'follower_id' => $follower->id,
            'following_id' => $user->id,
            'status' => 'accepted',
        ]);

        $following = User::factory()->create();
        Follow::create([
            'follower_id' => $user->id,
            'following_id' => $following->id,
            'status' => 'accepted',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'username',
                    'email',
                    'role',
                    'is_private',
                    'is_active',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                    'profile' => [
                        'id',
                        'bio',
                        'website',
                        'avatar_url',
                        'cover_url',
                        'location',
                    ],
                    'followers_count',
                    'following_count',
                ],
            ])
            ->assertJsonPath('data.username', 'alice_me')
            ->assertJsonPath('data.profile.bio', 'Building Nova Social')
            ->assertJsonPath('data.followers_count', 1)
            ->assertJsonPath('data.following_count', 1)
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('password')
            ->assertJsonMissingPath('data.token')
            ->assertJsonMissingPath('token');
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_request_with_invalid_token_returns_401(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ]);
    }
}
