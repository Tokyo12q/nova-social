<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'type' => 'direct',
            'title' => null,
            'avatar_url' => null,
            'created_by' => User::factory(),
            'last_message_at' => now(),
        ];
    }

    public function direct(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'direct',
            'title' => null,
            'avatar_url' => null,
        ]);
    }

    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'group',
            'title' => fake()->words(3, true),
            'avatar_url' => 'https://images.unsplash.com/photo-' . fake()->numberBetween(1500000000, 1600000000) . '?w=400&fit=crop',
        ]);
    }
}
