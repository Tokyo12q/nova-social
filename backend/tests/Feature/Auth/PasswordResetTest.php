<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'reset_me@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'reset_me@example.com',
        ]);

        $response->assertStatus(200)
            ->assertExactJson([
                'message' => 'Password reset link sent to your email.',
            ]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_fails_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'The given data was invalid.',
            ])
            ->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_fails_when_user_does_not_exist(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'does_not_exist@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'The given data was invalid.',
            ])
            ->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_is_rate_limited_after_repeated_attempts(): void
    {
        RateLimiter::clear('rate_forgot@example.com|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/forgot-password', [
                'email' => 'rate_forgot@example.com',
            ]);
        }

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'rate_forgot@example.com',
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'reset_pass@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $oldToken = $user->createToken('old_token')->plainTextToken;

        $resetToken = Password::broker()->createToken($user);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $resetToken,
            'email' => 'reset_pass@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(200)
            ->assertExactJson([
                'message' => 'Password reset successfully.',
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));

        // Old tokens are revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);

        // User can login with new password
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'login' => 'reset_pass@example.com',
            'password' => 'NewPassword123!',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonPath('data.email', 'reset_pass@example.com');
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'bad_token@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'invalid-token',
            'email' => 'bad_token@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_reset_password_fails_with_mismatched_password_confirmation(): void
    {
        $user = User::factory()->create([
            'email' => 'mismatch@example.com',
        ]);

        $resetToken = Password::broker()->createToken($user);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $resetToken,
            'email' => 'mismatch@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'DifferentPassword123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_reset_password_fails_with_weak_password(): void
    {
        $user = User::factory()->create([
            'email' => 'weak_pass@example.com',
        ]);

        $resetToken = Password::broker()->createToken($user);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $resetToken,
            'email' => 'weak_pass@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
