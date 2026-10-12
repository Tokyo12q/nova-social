<?php

namespace Tests\Feature\Profile;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_toggle_privacy_to_true_and_false(): void
    {
        $user = User::factory()->create([
            'is_private' => false,
        ]);
        Profile::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        // Toggle to private
        $response = $this->patchJson('/api/v1/settings/account', [
            'is_private' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_private', true);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_private' => true,
        ]);

        // Toggle back to public
        $response = $this->patchJson('/api/v1/settings/account', [
            'is_private' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_private', false);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_private' => false,
        ]);
    }

    public function test_authenticated_user_can_toggle_privacy_via_privacy_endpoint(): void
    {
        $user = User::factory()->create([
            'is_private' => false,
        ]);
        Profile::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/profiles/me/privacy', [
            'is_private' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_private', true);

        $this->assertTrue($user->fresh()->is_private);
    }

    public function test_unauthenticated_request_to_update_settings_returns_401(): void
    {
        $response = $this->patchJson('/api/v1/settings/account', [
            'is_private' => true,
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_invalid_privacy_value_fails_validation(): void
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/settings/account', [
            'is_private' => 'not-a-boolean-value',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_private']);
    }
}
