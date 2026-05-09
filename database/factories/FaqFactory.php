<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        return [
            'question' => fake()->sentence(8).'?',
            'answer' => fake()->paragraph(3),
            'sort_order' => fake()->numberBetween(0, 100),
            'status' => fake()->randomElement(['draft', 'published']),
        ];
    }
}
