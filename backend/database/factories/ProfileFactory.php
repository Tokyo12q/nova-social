<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bio' => fake()->paragraph(2),
            'website' => fake()->optional(0.7)->url(),
            'avatar_url' => 'https://images.unsplash.com/photo-' . fake()->numberBetween(1500000000, 1600000000) . '?w=400&fit=crop&crop=faces',
            'cover_url' => 'https://images.unsplash.com/photo-' . fake()->numberBetween(1500000000, 1600000000) . '?w=1200&h=400&fit=crop',
            'location' => fake()->city() . ', ' . fake()->countryCode(),
        ];
    }
}
