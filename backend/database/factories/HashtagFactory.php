<?php

namespace Database\Factories;

use App\Models\Hashtag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hashtag>
 */
class HashtagFactory extends Factory
{
    protected $model = Hashtag::class;

    public function definition(): array
    {
        return [
            'name' => Str::lower(fake()->unique()->lexify('?????????')),
            'posts_count' => 0,
        ];
    }
}
