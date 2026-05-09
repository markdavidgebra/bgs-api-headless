<?php

namespace Database\Factories;

use App\Models\Slide;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slide>
 */
class SlideFactory extends Factory
{
    protected $model = Slide::class;

    public function definition(): array
    {
        return [
            'sort_order' => fake()->numberBetween(0, 20),
            'is_active' => true,
            'subtitle' => fake()->optional()->sentence(3),
            'title' => fake()->sentence(4),
            'title_span' => fake()->optional()->words(2, true),
            'description' => fake()->optional()->paragraph(),
            'button_text' => fake()->optional()->randomElement(['Book now', 'Learn more', 'Shop']),
            'button_url' => fake()->optional()->url(),
            'show_video' => fake()->boolean(40),
            'video_url' => fake()->optional()->url(),
            'video_label' => fake()->optional()->words(2, true),
            'image' => null,
            'image_alt' => fake()->optional()->words(4, true),
        ];
    }
}
