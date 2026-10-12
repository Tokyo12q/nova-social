<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_username_and_password(): void
    {
        $user = User::factory()->create([
            'username' => 'alice_login',
            'email' => 'alice_login@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'alice_login',
            'password' => 'Password123!',
        ]);

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
                    'token',
                    'token_type',
                ],
                'token',
                'token_type',
            ])
            ->assertJsonPath('data.username', 'alice_login')
            ->assertJsonPath('token_type', 'Bearer');
    }

    public function test_user_can_login_with_valid_email_and_password(): void
    {
        $user = User::factory()->create([
            'username' => 'bob_login',
            'email' => 'bob_login@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'bob_login@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'bob_login@example.com')
            ->assertJsonPath('token_type', 'Bearer');
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'username' => 'alice_wrong_pw',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'alice_wrong_pw',
            'password' => 'WrongPassword!',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid credentials.',
            ]);
    }

    public function test_login_fails_with_non_existent_user(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'non_existent_user',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid credentials.',
            ]);
    }

    public function test_login_fails_when_user_account_is_inactive(): void
    {
        $user = User::factory()->inactive()->create([
            'username' => 'inactive_user',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'inactive_user',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Your account is deactivated.',
            ]);
    }

    public function test_login_fails_when_missing_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'The given data was invalid.',
            ])
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_is_rate_limited_after_repeated_failed_attempts(): void
    {
        RateLimiter::clear('rate_test_user|127.0.0.1');

        $credentials = [
            'login' => 'rate_test_user',
            'password' => 'WrongPassword!',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', $credentials);
        }

        $response = $this->postJson('/api/v1/auth/login', $credentials);

        $response->assertStatus(429)
            ->assertJson([
                'status' => 'error',
            ]);
    }
}
