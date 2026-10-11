<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'type' => 'App\Notifications\GenericNotification',
            'notifiable_type' => 'user',
            'notifiable_id' => User::factory(),
            'data' => json_encode([
                'title' => fake()->sentence(3),
                'message' => fake()->sentence(),
                'action_url' => fake()->url(),
            ]),
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now(),
        ]);
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }
}
