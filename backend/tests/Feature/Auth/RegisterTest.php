<?php

namespace Tests\Feature\Auth;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data(): void
    {
        Event::fake([Registered::class]);

        $payload = [
            'name' => 'Alice Doe',
            'username' => 'alice_doe',
            'email' => 'alice@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'username',
                    'email',
                    'role',
                    'is_private',
                    'is_active',
                    'profile',
                    'followers_count',
                    'following_count',
                    'token',
                    'token_type',
                ],
                'token',
                'token_type',
            ])
            ->assertJsonPath('data.username', 'alice_doe')
            ->assertJsonPath('data.email', 'alice@example.com')
            ->assertJsonPath('token_type', 'Bearer');

        $this->assertDatabaseHas('users', [
            'username' => 'alice_doe',
            'email' => 'alice@example.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        $user = User::where('email', 'alice@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $this->assertNotEquals('Password123!', $user->password);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
        ]);

        Event::assertDispatched(Registered::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    public function test_registration_fails_when_username_already_exists(): void
    {
        User::factory()->create([
            'username' => 'existing_user',
        ]);

        $payload = [
            'name' => 'Another User',
            'username' => 'existing_user',
            'email' => 'another@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'The given data was invalid.',
            ])
            ->assertJsonValidationErrors(['username']);
    }

    public function test_registration_fails_when_email_already_exists(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $payload = [
            'name' => 'Another User',
            'username' => 'unique_user',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'The given data was invalid.',
            ])
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_missing_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'The given data was invalid.',
            ])
            ->assertJsonValidationErrors(['name', 'username', 'email', 'password']);
    }

    public function test_registration_fails_with_unconfirmed_password(): void
    {
        $payload = [
            'name' => 'Alice Doe',
            'username' => 'alice_doe',
            'email' => 'alice@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_with_weak_password(): void
    {
        $payload = [
            'name' => 'Alice Doe',
            'username' => 'alice_doe',
            'email' => 'alice@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_with_invalid_username_format(): void
    {
        $payload = [
            'name' => 'Alice Doe',
            'username' => 'invalid username with spaces!',
            'email' => 'alice@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }
}
