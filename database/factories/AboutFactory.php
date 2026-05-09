<?php

namespace Database\Factories;

use App\Models\About;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<About>
 */
class AboutFactory extends Factory
{
    protected $model = About::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'subtitle' => fake()->optional()->sentence(6),
            'content' => fake()->paragraphs(4, true),
            'image' => null,
            'meta' => fake()->boolean(40) ? ['highlight' => fake()->word()] : null,
            'sort_order' => fake()->numberBetween(0, 20),
            'status' => fake()->randomElement(['draft', 'published']),
        ];
    }
}
