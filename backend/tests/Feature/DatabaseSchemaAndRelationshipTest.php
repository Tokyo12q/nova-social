<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Comment;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Follow;
use App\Models\Hashtag;
use App\Models\Like;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Post;
use App\Models\PostHashtag;
use App\Models\PostMedia;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Save;
use App\Models\Story;
use App\Models\StoryView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaAndRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_and_profile_one_to_one_relationship(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Profile::class, $user->fresh()->profile);
        $this->assertEquals($profile->id, $user->profile->id);
        $this->assertEquals($user->id, $profile->user->id);
    }

    public function test_user_posts_and_post_media_relationship(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $media1 = PostMedia::factory()->create([
            'post_id' => $post->id,
            'order' => 0,
        ]);
        $media2 = PostMedia::factory()->create([
            'post_id' => $post->id,
            'order' => 1,
        ]);

        $this->assertCount(1, $user->posts);
        $this->assertCount(2, $post->media);
        $this->assertEquals($media1->id, $post->media->first()->id);
        $this->assertEquals($media2->id, $post->media->last()->id);
        $this->assertEquals($user->id, $post->user->id);
    }

    public function test_comments_and_threaded_replies_relationship(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $parentComment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => null,
            'content' => 'Top level comment',
        ]);

        $replyComment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
            'content' => 'Nested reply comment',
        ]);

        $this->assertCount(2, $post->comments);
        $this->assertCount(1, $parentComment->replies);
        $this->assertEquals($replyComment->id, $parentComment->replies->first()->id);
        $this->assertEquals($parentComment->id, $replyComment->parent->id);
    }

    public function test_polymorphic_likes_on_posts_and_comments(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id]);

        $postLike = Like::factory()->create([
            'user_id' => $user->id,
            'likeable_type' => 'post',
            'likeable_id' => $post->id,
        ]);

        $commentLike = Like::factory()->create([
            'user_id' => $user->id,
            'likeable_type' => 'comment',
            'likeable_id' => $comment->id,
        ]);

        $this->assertInstanceOf(Post::class, $postLike->likeable);
        $this->assertInstanceOf(Comment::class, $commentLike->likeable);
        $this->assertCount(1, $post->likes);
        $this->assertCount(1, $comment->likes);
        $this->assertCount(2, $user->likes);
    }

    public function test_saves_and_saved_posts_relationship(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Save::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $this->assertCount(1, $user->saves);
        $this->assertCount(1, $user->savedPosts);
        $this->assertEquals($post->id, $user->savedPosts->first()->id);
        $this->assertCount(1, $post->savedByUsers);
    }

    public function test_follows_graph_and_statuses(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();

        Follow::factory()->create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
            'status' => 'accepted',
        ]);

        $this->assertCount(1, $follower->following);
        $this->assertCount(1, $following->followers);
        $this->assertEquals('accepted', $follower->following->first()->pivot->status);
    }

    public function test_stories_and_story_views(): void
    {
        $creator = User::factory()->create();
        $viewer = User::factory()->create();

        $story = Story::factory()->create([
            'user_id' => $creator->id,
            'expires_at' => now()->addHours(24),
        ]);

        StoryView::factory()->create([
            'story_id' => $story->id,
            'viewer_id' => $viewer->id,
            'viewed_at' => now(),
        ]);

        $this->assertCount(1, $creator->stories);
        $this->assertCount(1, $story->views);
        $this->assertCount(1, $story->viewers);
        $this->assertEquals($viewer->id, $story->viewers->first()->id);
    }

    public function test_conversations_participants_and_messages(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = Conversation::factory()->create([
            'type' => 'direct',
            'created_by' => $user1->id,
        ]);

        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user1->id,
            'role' => 'admin',
        ]);

        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user2->id,
            'role' => 'member',
        ]);

        $msg = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user1->id,
            'body' => 'Hello there!',
        ]);

        $this->assertCount(2, $conversation->participants);
        $this->assertCount(2, $conversation->users);
        $this->assertCount(1, $conversation->messages);
        $this->assertEquals($user1->id, $msg->sender->id);
    }

    public function test_hashtags_and_post_hashtags(): void
    {
        $post = Post::factory()->create();
        $hashtag = Hashtag::factory()->create(['name' => 'laravel']);

        PostHashtag::factory()->create([
            'post_id' => $post->id,
            'hashtag_id' => $hashtag->id,
        ]);

        $this->assertCount(1, $post->hashtags);
        $this->assertCount(1, $hashtag->posts);
        $this->assertEquals('laravel', $post->hashtags->first()->name);
    }

    public function test_polymorphic_reports(): void
    {
        $reporter = User::factory()->create();
        $moderator = User::factory()->moderator()->create();
        $post = Post::factory()->create();

        $report = Report::factory()->create([
            'reporter_id' => $reporter->id,
            'reportable_type' => 'post',
            'reportable_id' => $post->id,
            'reason' => 'spam',
            'status' => 'resolved',
            'reviewed_by' => $moderator->id,
            'resolution_notes' => 'Spam confirmed and handled.',
        ]);

        $this->assertInstanceOf(Post::class, $report->reportable);
        $this->assertEquals($reporter->id, $report->reporter->id);
        $this->assertEquals($moderator->id, $report->reviewer->id);
        $this->assertCount(1, $post->reports);
    }

    public function test_blocks_relationship(): void
    {
        $blocker = User::factory()->create();
        $blocked = User::factory()->create();

        Block::factory()->create([
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked->id,
        ]);

        $this->assertCount(1, $blocker->blockedUsers);
        $this->assertCount(1, $blocked->blockedByUsers);
    }

    public function test_notifications_relationship(): void
    {
        $user = User::factory()->create();

        Notification::factory()->create([
            'notifiable_type' => 'user',
            'notifiable_id' => $user->id,
        ]);

        $this->assertCount(1, $user->notifications);
    }

    public function test_soft_deletes_on_supported_entities(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id]);
        $convo = Conversation::factory()->create();
        $message = Message::factory()->create(['conversation_id' => $convo->id, 'sender_id' => $user->id]);

        $post->delete();
        $comment->delete();
        $message->delete();
        $user->delete();

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
        $this->assertSoftDeleted('messages', ['id' => $message->id]);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_cascade_delete_on_foreign_keys(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $post = Post::factory()->create(['user_id' => $user->id]);
        $media = PostMedia::factory()->create(['post_id' => $post->id]);

        // Force delete user to trigger database foreign key cascades
        $user->forceDelete();

        $this->assertDatabaseMissing('profiles', ['id' => $profile->id]);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('post_media', ['id' => $media->id]);
    }
}
