<?php

namespace Database\Factories;

use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Story>
 */
class StoryFactory extends Factory
{
    protected $model = Story::class;

    public function definition(): array
    {
        $isImage = fake()->boolean(80);

        return [
            'user_id' => User::factory(),
            'media_type' => $isImage ? 'image' : 'video',
            'media_url' => $isImage
                ? 'https://images.unsplash.com/photo-' . fake()->numberBetween(1500000000, 1600000000) . '?w=1080&h=1920&fit=crop'
                : 'https://example.com/stories/' . fake()->uuid() . '.mp4',
            'caption' => fake()->optional(0.6)->sentence(),
            'views_count' => 0,
            'expires_at' => now()->addHours(24),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subHours(2),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addHours(12),
        ]);
    }
}
