<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Seeder;

class ModerationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $posts = Post::all();
        $comments = Comment::all();
        $stories = Story::all();

        if ($users->count() < 3) {
            return;
        }

        $moderator = User::where('role', 'moderator')->first() ?? $users[1];
        $reporter = $users->last();

        // 1. Report on a Post (Resolved)
        if ($posts->isNotEmpty()) {
            $reportedPost = $posts->first();
            Report::create([
                'reporter_id' => $reporter->id,
                'reportable_type' => 'post',
                'reportable_id' => $reportedPost->id,
                'reason' => 'spam',
                'details' => 'Post contains repetitive promotional links.',
                'status' => 'resolved',
                'reviewed_by' => $moderator->id,
                'resolution_notes' => 'Reviewed content; determined links were within community guidelines. Marked resolved.',
            ]);
        }

        // 2. Report on a Comment (Pending)
        if ($comments->isNotEmpty()) {
            $reportedComment = $comments->first();
            Report::create([
                'reporter_id' => $reporter->id,
                'reportable_type' => 'comment',
                'reportable_id' => $reportedComment->id,
                'reason' => 'harassment',
                'details' => 'Aggressive phrasing targeted at author.',
                'status' => 'pending',
                'reviewed_by' => null,
                'resolution_notes' => null,
            ]);
        }

        // 3. Report on a User (In Review)
        if ($users->count() >= 4) {
            $reportedUser = $users[3];
            Report::create([
                'reporter_id' => $reporter->id,
                'reportable_type' => 'user',
                'reportable_id' => $reportedUser->id,
                'reason' => 'misinformation',
                'details' => 'Suspected automated spam account.',
                'status' => 'in_review',
                'reviewed_by' => $moderator->id,
                'resolution_notes' => 'Investigating account behavioral patterns.',
            ]);
        }

        // 4. Report on a Story (Dismissed)
        if ($stories->isNotEmpty()) {
            $reportedStory = $stories->first();
            Report::create([
                'reporter_id' => $reporter->id,
                'reportable_type' => 'story',
                'reportable_id' => $reportedStory->id,
                'reason' => 'nudity',
                'details' => 'Beach photo flagged erroneously.',
                'status' => 'dismissed',
                'reviewed_by' => $moderator->id,
                'resolution_notes' => 'False positive flag. Content conforms to safety policy.',
            ]);
        }

        // 5. User Blocks
        if ($users->count() >= 6) {
            $userA = $users[4];
            $userB = $users[5];
            Block::firstOrCreate([
                'blocker_id' => $userA->id,
                'blocked_id' => $userB->id,
            ]);
        }
    }
}
