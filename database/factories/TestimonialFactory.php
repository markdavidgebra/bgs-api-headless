<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'designation' => fake()->optional()->jobTitle(),
            'quote' => fake()->paragraph(2),
            'image' => null,
            'sort_order' => fake()->numberBetween(0, 50),
            'status' => fake()->randomElement(['draft', 'published']),
        ];
    }
}
