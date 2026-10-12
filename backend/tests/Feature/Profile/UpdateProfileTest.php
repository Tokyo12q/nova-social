<?php

namespace Tests\Feature\Profile;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_their_own_profile_text_fields(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'username' => 'original_user',
        ]);

        Profile::factory()->create([
            'user_id' => $user->id,
            'bio' => 'Old bio',
            'website' => 'https://old.example.com',
            'location' => 'Old City',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/profiles/me', [
            'name' => 'Updated Name',
            'bio' => 'Brand new bio crafting digital masterpieces.',
            'website' => 'https://updated.example.com',
            'location' => 'New York, NY',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.bio', 'Brand new bio crafting digital masterpieces.')
            ->assertJsonPath('data.website', 'https://updated.example.com')
            ->assertJsonPath('data.location', 'New York, NY');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'bio' => 'Brand new bio crafting digital masterpieces.',
            'website' => 'https://updated.example.com',
            'location' => 'New York, NY',
        ]);
    }

    public function test_authenticated_user_can_upload_avatar_and_cover_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $avatar = UploadedFile::fake()->image('avatar.jpg', 300, 300);
        $cover = UploadedFile::fake()->image('cover.png', 1200, 400);

        $response = $this->postJson('/api/v1/profiles/me', [
            'name' => 'Photographer',
            'avatar' => $avatar,
            'cover' => $cover,
        ]);

        $response->assertStatus(200);

        $updatedProfile = $user->profile()->first();

        $this->assertNotNull($updatedProfile->avatar_url);
        $this->assertNotNull($updatedProfile->cover_url);

        $avatarPath = Str::after($updatedProfile->avatar_url, '/storage/');
        $coverPath = Str::after($updatedProfile->cover_url, '/storage/');

        Storage::disk('public')->assertExists($avatarPath);
        Storage::disk('public')->assertExists($coverPath);
    }

    public function test_updating_avatar_replaces_and_deletes_old_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        // First upload
        $firstAvatar = UploadedFile::fake()->image('first_avatar.jpg', 300, 300);
        $this->postJson('/api/v1/profiles/me', ['avatar' => $firstAvatar]);

        $firstUrl = $user->profile()->first()->avatar_url;
        $firstPath = Str::after($firstUrl, '/storage/');
        Storage::disk('public')->assertExists($firstPath);

        // Second upload to replace
        $secondAvatar = UploadedFile::fake()->image('second_avatar.png', 300, 300);
        $this->postJson('/api/v1/profiles/me', ['avatar' => $secondAvatar]);

        $secondUrl = $user->profile()->first()->avatar_url;
        $secondPath = Str::after($secondUrl, '/storage/');

        // Assert second file exists and first file was deleted
        Storage::disk('public')->assertExists($secondPath);
        Storage::disk('public')->assertMissing($firstPath);
    }

    public function test_updating_cover_replaces_and_deletes_old_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        // First upload
        $firstCover = UploadedFile::fake()->image('first_cover.jpg', 1200, 400);
        $this->postJson('/api/v1/profiles/me', ['cover' => $firstCover]);

        $firstUrl = $user->profile()->first()->cover_url;
        $firstPath = Str::after($firstUrl, '/storage/');
        Storage::disk('public')->assertExists($firstPath);

        // Second upload to replace
        $secondCover = UploadedFile::fake()->image('second_cover.jpg', 1200, 400);
        $this->postJson('/api/v1/profiles/me', ['cover' => $secondCover]);

        $secondUrl = $user->profile()->first()->cover_url;
        $secondPath = Str::after($secondUrl, '/storage/');

        // Assert second file exists and first file was deleted
        Storage::disk('public')->assertExists($secondPath);
        Storage::disk('public')->assertMissing($firstPath);
    }

    public function test_upload_fails_when_file_exceeds_size_limit(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        // Exceeds 2048 KB (3000 KB)
        $largeAvatar = UploadedFile::fake()->create('large.jpg', 3000, 'image/jpeg');

        $response = $this->postJson('/api/v1/profiles/me', [
            'avatar' => $largeAvatar,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_upload_fails_when_file_is_invalid_mime_type(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $pdfFile = UploadedFile::fake()->create('resume.pdf', 500, 'application/pdf');

        $response = $this->postJson('/api/v1/profiles/me', [
            'avatar' => $pdfFile,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_website_fails_when_invalid_url_format(): void
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/profiles/me', [
            'website' => 'not-a-valid-url',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['website']);
    }

    public function test_unauthenticated_user_cannot_update_profile(): void
    {
        $response = $this->putJson('/api/v1/profiles/me', [
            'name' => 'Hacker',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ]);
    }
}
