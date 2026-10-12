<?php

namespace Tests\Feature\Profile;

use App\Models\Block;
use App\Models\Follow;
use App\Models\Post;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ViewProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_public_profile_by_username_unauthenticated(): void
    {
        $user = User::factory()->create([
            'name' => 'Sarah Connor',
            'username' => 'sarahc',
            'is_private' => false,
        ]);

        Profile::factory()->create([
            'user_id' => $user->id,
            'bio' => 'Saving humanity one day at a time.',
            'website' => 'https://sarahconnor.org',
            'location' => 'Los Angeles, CA',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'cover_url' => 'https://example.com/cover.jpg',
        ]);

        Post::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/profiles/sarahc');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'username',
                    'avatar_url',
                    'cover_url',
                    'bio',
                    'website',
                    'location',
                    'is_private',
                    'can_view_full_profile',
                    'posts_count',
                    'followers_count',
                    'following_count',
                    'joined_at',
                ],
            ])
            ->assertJsonPath('data.username', 'sarahc')
            ->assertJsonPath('data.name', 'Sarah Connor')
            ->assertJsonPath('data.bio', 'Saving humanity one day at a time.')
            ->assertJsonPath('data.website', 'https://sarahconnor.org')
            ->assertJsonPath('data.location', 'Los Angeles, CA')
            ->assertJsonPath('data.is_private', false)
            ->assertJsonPath('data.can_view_full_profile', true)
            ->assertJsonPath('data.posts_count', 3);
    }

    public function test_viewing_nonexistent_username_returns_404(): void
    {
        $response = $this->getJson('/api/v1/profiles/nonexistent_user_xyz');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Resource not found.',
            ]);
    }

    public function test_private_profile_shows_only_basic_info_to_unauthenticated_viewer(): void
    {
        $user = User::factory()->create([
            'name' => 'John Connor',
            'username' => 'john_secret',
            'is_private' => true,
        ]);

        Profile::factory()->create([
            'user_id' => $user->id,
            'bio' => 'Confidential resistance transmissions.',
            'website' => 'https://resistance.classified',
            'location' => 'Undisclosed Bunker',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'cover_url' => 'https://example.com/cover.jpg',
        ]);

        $response = $this->getJson('/api/v1/profiles/john_secret');

        $response->assertStatus(200)
            ->assertJsonPath('data.username', 'john_secret')
            ->assertJsonPath('data.name', 'John Connor')
            ->assertJsonPath('data.avatar_url', 'https://example.com/avatar.jpg')
            ->assertJsonPath('data.is_private', true)
            ->assertJsonPath('data.can_view_full_profile', false)
            ->assertJsonPath('data.bio', null)
            ->assertJsonPath('data.website', null)
            ->assertJsonPath('data.location', null)
            ->assertJsonPath('data.cover_url', null);
    }

    public function test_private_profile_shows_only_basic_info_to_non_follower(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Kyle Reese',
            'username' => 'kylereese',
            'is_private' => true,
        ]);

        Profile::factory()->create([
            'user_id' => $targetUser->id,
            'bio' => 'Came through time for you.',
            'website' => 'https://future.war',
            'location' => '2029 Los Angeles',
        ]);

        $viewer = User::factory()->create();
        Sanctum::actingAs($viewer);

        $response = $this->getJson('/api/v1/profiles/kylereese');

        $response->assertStatus(200)
            ->assertJsonPath('data.username', 'kylereese')
            ->assertJsonPath('data.can_view_full_profile', false)
            ->assertJsonPath('data.bio', null)
            ->assertJsonPath('data.website', null);
    }

    public function test_private_profile_shows_full_info_to_owner(): void
    {
        $user = User::factory()->create([
            'username' => 'privateme',
            'is_private' => true,
        ]);

        Profile::factory()->create([
            'user_id' => $user->id,
            'bio' => 'My secret personal diary',
            'website' => 'https://private.me',
            'location' => 'Home',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/profiles/privateme');

        $response->assertStatus(200)
            ->assertJsonPath('data.username', 'privateme')
            ->assertJsonPath('data.can_view_full_profile', true)
            ->assertJsonPath('data.bio', 'My secret personal diary')
            ->assertJsonPath('data.website', 'https://private.me');
    }

    public function test_private_profile_shows_full_info_to_accepted_follower(): void
    {
        $targetUser = User::factory()->create([
            'username' => 'private_friend',
            'is_private' => true,
        ]);

        Profile::factory()->create([
            'user_id' => $targetUser->id,
            'bio' => 'Exclusive friend updates',
            'website' => 'https://friends.only',
        ]);

        $follower = User::factory()->create();

        Follow::create([
            'follower_id' => $follower->id,
            'following_id' => $targetUser->id,
            'status' => 'accepted',
        ]);

        Sanctum::actingAs($follower);

        $response = $this->getJson('/api/v1/profiles/private_friend');

        $response->assertStatus(200)
            ->assertJsonPath('data.can_view_full_profile', true)
            ->assertJsonPath('data.bio', 'Exclusive friend updates')
            ->assertJsonPath('data.website', 'https://friends.only');
    }

    public function test_private_profile_shows_only_basic_info_to_pending_follower(): void
    {
        $targetUser = User::factory()->create([
            'username' => 'private_pending',
            'is_private' => true,
        ]);

        Profile::factory()->create([
            'user_id' => $targetUser->id,
            'bio' => 'Pending approval only',
        ]);

        $pendingViewer = User::factory()->create();

        Follow::create([
            'follower_id' => $pendingViewer->id,
            'following_id' => $targetUser->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($pendingViewer);

        $response = $this->getJson('/api/v1/profiles/private_pending');

        $response->assertStatus(200)
            ->assertJsonPath('data.can_view_full_profile', false)
            ->assertJsonPath('data.bio', null);
    }

    public function test_viewing_profile_fails_with_404_when_blocked_by_target_user(): void
    {
        $targetUser = User::factory()->create(['username' => 'blocker_user']);
        Profile::factory()->create(['user_id' => $targetUser->id]);

        $viewer = User::factory()->create();

        Block::create([
            'blocker_id' => $targetUser->id,
            'blocked_id' => $viewer->id,
        ]);

        Sanctum::actingAs($viewer);

        $response = $this->getJson('/api/v1/profiles/blocker_user');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Resource not found.',
            ]);
    }

    public function test_viewing_profile_fails_with_404_when_viewer_has_blocked_target_user(): void
    {
        $targetUser = User::factory()->create(['username' => 'blocked_target']);
        Profile::factory()->create(['user_id' => $targetUser->id]);

        $viewer = User::factory()->create();

        Block::create([
            'blocker_id' => $viewer->id,
            'blocked_id' => $targetUser->id,
        ]);

        Sanctum::actingAs($viewer);

        $response = $this->getJson('/api/v1/profiles/blocked_target');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Resource not found.',
            ]);
    }
}
