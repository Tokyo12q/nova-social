<?php

namespace Database\Factories;

use App\Models\Hashtag;
use App\Models\Post;
use App\Models\PostHashtag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostHashtag>
 */
class PostHashtagFactory extends Factory
{
    protected $model = PostHashtag::class;

    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'hashtag_id' => Hashtag::factory(),
            'created_at' => now(),
        ];
    }
}
