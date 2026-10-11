<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Like>
 */
class LikeFactory extends Factory
{
    protected $model = Like::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'likeable_type' => 'post',
            'likeable_id' => Post::factory(),
        ];
    }

    public function forPost(?Post $post = null): static
    {
        return $this->state(fn (array $attributes) => [
            'likeable_type' => 'post',
            'likeable_id' => $post ? $post->id : Post::factory(),
        ]);
    }

    public function forComment(?Comment $comment = null): static
    {
        return $this->state(fn (array $attributes) => [
            'likeable_type' => 'comment',
            'likeable_id' => $comment ? $comment->id : Comment::factory(),
        ]);
    }
}
