<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_verify_email_via_signed_url(): void
    {
        Event::fake([Verified::class]);

        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->getJson($verificationUrl);

        $response->assertStatus(200)
            ->assertExactJson([
                'message' => 'Email verified successfully.',
            ]);

        $this->assertNotNull($user->fresh()->email_verified_at);

        Event::assertDispatched(Verified::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    public function test_verifying_already_verified_email_returns_already_verified_message(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->getJson($verificationUrl);

        $response->assertStatus(200)
            ->assertExactJson([
                'message' => 'Email already verified.',
            ]);
    }

    public function test_email_verification_fails_with_invalid_signature(): void
    {
        $user = User::factory()->unverified()->create();

        $invalidUrl = "/api/v1/auth/email/verify/{$user->id}/" . sha1($user->getEmailForVerification()) . '?signature=tampered';

        $response = $this->getJson($invalidUrl);

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_resend_verification_notification(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/email/verification-notification');

        $response->assertStatus(200)
            ->assertExactJson([
                'message' => 'Verification link sent.',
            ]);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_resending_verification_notification_when_already_verified(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/email/verification-notification');

        $response->assertStatus(200)
            ->assertExactJson([
                'message' => 'Email already verified.',
            ]);

        Notification::assertNotSentTo($user, VerifyEmail::class);
    }

    public function test_unauthenticated_request_to_resend_verification_returns_401(): void
    {
        $response = $this->postJson('/api/v1/auth/email/verification-notification');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ]);
    }
}
