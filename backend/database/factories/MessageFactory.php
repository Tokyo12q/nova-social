<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_id' => User::factory(),
            'body' => fake()->sentence(),
            'media_url' => null,
            'media_type' => null,
            'is_read' => false,
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
        ]);
    }

    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'media_url' => 'https://images.unsplash.com/photo-' . fake()->numberBetween(1500000000, 1600000000) . '?w=800&fit=crop',
            'media_type' => 'image',
        ]);
    }

    public function withVideo(): static
    {
        return $this->state(fn (array $attributes) => [
            'media_url' => 'https://example.com/messages/' . fake()->uuid() . '.mp4',
            'media_type' => 'video',
        ]);
    }

    public function withFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'media_url' => 'https://example.com/files/' . fake()->uuid() . '.pdf',
            'media_type' => 'file',
        ]);
    }
}
