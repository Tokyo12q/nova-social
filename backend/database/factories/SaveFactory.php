<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Save;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Save>
 */
class SaveFactory extends Factory
{
    protected $model = Save::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
        ];
    }
}
