<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'caption' => fake()->paragraph(2),
            'location' => fake()->optional(0.6)->city(),
            'comments_disabled' => false,
            'likes_count' => 0,
            'comments_count' => 0,
            'saves_count' => 0,
        ];
    }

    public function commentsDisabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'comments_disabled' => true,
        ]);
    }
}
