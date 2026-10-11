<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Conversation;
use App\Models\Hashtag;
use App\Models\Like;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Save;
use App\Models\Story;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_all_tables_with_realistic_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Core accounts verification
        $this->assertDatabaseHas('users', [
            'email' => 'admin@novasocial.test',
            'role' => 'admin',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'moderator@novasocial.test',
            'role' => 'moderator',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'user@novasocial.test',
            'role' => 'user',
        ]);

        // Entity counts verification
        $this->assertGreaterThan(10, User::count());
        $this->assertGreaterThan(10, Profile::count());
        $this->assertGreaterThan(5, Post::count());
        $this->assertGreaterThan(5, Hashtag::count());
        $this->assertGreaterThan(0, Comment::count());
        $this->assertGreaterThan(0, Like::count());
        $this->assertGreaterThan(0, Save::count());
        $this->assertGreaterThan(0, Story::count());
        $this->assertGreaterThan(0, Conversation::count());
        $this->assertGreaterThan(0, Message::count());
        $this->assertGreaterThan(0, Report::count());
        $this->assertGreaterThan(0, Notification::count());
    }
}
