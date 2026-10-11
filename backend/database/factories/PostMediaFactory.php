<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostMedia>
 */
class PostMediaFactory extends Factory
{
    protected $model = PostMedia::class;

    public function definition(): array
    {
        $isImage = fake()->boolean(80);

        return [
            'post_id' => Post::factory(),
            'media_type' => $isImage ? 'image' : 'video',
            'url' => $isImage
                ? 'https://images.unsplash.com/photo-' . fake()->numberBetween(1500000000, 1600000000) . '?w=1080&fit=crop'
                : 'https://example.com/videos/' . fake()->uuid() . '.mp4',
            'thumbnail_url' => $isImage
                ? null
                : 'https://images.unsplash.com/photo-' . fake()->numberBetween(1500000000, 1600000000) . '?w=400&fit=crop',
            'width' => 1080,
            'height' => 1080,
            'order' => 0,
        ];
    }

    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'media_type' => 'image',
            'url' => 'https://images.unsplash.com/photo-' . fake()->numberBetween(1500000000, 1600000000) . '?w=1080&fit=crop',
            'thumbnail_url' => null,
        ]);
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'media_type' => 'video',
            'url' => 'https://example.com/videos/' . fake()->uuid() . '.mp4',
            'thumbnail_url' => 'https://images.unsplash.com/photo-' . fake()->numberBetween(1500000000, 1600000000) . '?w=400&fit=crop',
        ]);
    }
}
