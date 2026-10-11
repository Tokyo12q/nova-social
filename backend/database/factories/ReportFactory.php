<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'reporter_id' => User::factory(),
            'reportable_type' => 'post',
            'reportable_id' => Post::factory(),
            'reason' => fake()->randomElement([
                'spam',
                'harassment',
                'hate_speech',
                'violence',
                'nudity',
                'misinformation',
                'other',
            ]),
            'details' => fake()->optional(0.7)->sentence(),
            'status' => 'pending',
            'reviewed_by' => null,
            'resolution_notes' => null,
        ];
    }

    public function forPost(?Post $post = null): static
    {
        return $this->state(fn (array $attributes) => [
            'reportable_type' => 'post',
            'reportable_id' => $post ? $post->id : Post::factory(),
        ]);
    }

    public function forComment(?Comment $comment = null): static
    {
        return $this->state(fn (array $attributes) => [
            'reportable_type' => 'comment',
            'reportable_id' => $comment ? $comment->id : Comment::factory(),
        ]);
    }

    public function forUser(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'reportable_type' => 'user',
            'reportable_id' => $user ? $user->id : User::factory(),
        ]);
    }

    public function forStory(?Story $story = null): static
    {
        return $this->state(fn (array $attributes) => [
            'reportable_type' => 'story',
            'reportable_id' => $story ? $story->id : Story::factory(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'reviewed_by' => null,
            'resolution_notes' => null,
        ]);
    }

    public function inReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_review',
        ]);
    }

    public function resolved(?User $reviewer = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'reviewed_by' => $reviewer ? $reviewer->id : User::factory()->moderator(),
            'resolution_notes' => fake()->sentence(),
        ]);
    }

    public function dismissed(?User $reviewer = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dismissed',
            'reviewed_by' => $reviewer ? $reviewer->id : User::factory()->moderator(),
            'resolution_notes' => fake()->sentence(),
        ]);
    }
}
