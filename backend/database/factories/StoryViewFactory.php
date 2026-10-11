<?php

namespace Database\Factories;

use App\Models\Story;
use App\Models\StoryView;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoryView>
 */
class StoryViewFactory extends Factory
{
    protected $model = StoryView::class;

    public function definition(): array
    {
        return [
            'story_id' => Story::factory(),
            'viewer_id' => User::factory(),
            'viewed_at' => now(),
        ];
    }
}
