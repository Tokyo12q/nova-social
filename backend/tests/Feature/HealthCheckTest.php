<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'status',
                    'timestamp',
                    'database',
                    'environment',
                ],
            ])
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.database', 'connected');
    }

    public function test_non_existent_api_endpoint_returns_consistent_404_json_error(): void
    {
        $response = $this->getJson('/api/v1/non-existent-route');

        $response->assertStatus(404)
            ->assertExactJson([
                'status' => 'error',
                'message' => 'Resource not found.',
            ]);
    }

    public function test_method_not_allowed_returns_consistent_405_json_error(): void
    {
        $response = $this->postJson('/api/v1/health');

        $response->assertStatus(405)
            ->assertExactJson([
                'status' => 'error',
                'message' => 'Method not allowed.',
            ]);
    }

    public function test_unauthenticated_api_request_returns_consistent_401_json_error(): void
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401)
            ->assertExactJson([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_authenticated_user_can_access_sanctum_user_endpoint(): void
    {
        $user = User::factory()->create([
            'name' => 'Nova User',
            'email' => 'user@novasocial.test',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(200)
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', 'user@novasocial.test');
    }
}
