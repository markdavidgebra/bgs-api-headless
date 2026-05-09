<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    protected $model = Blog::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('##'),
            'excerpt' => fake()->optional()->text(200),
            'content' => '<p>'.implode('</p><p>', fake()->paragraphs(5)).'</p>',
            'image' => null,
            'author_name' => fake()->name(),
            'category' => fake()->optional()->word(),
            'status' => fake()->randomElement(['draft', 'published']),
            'published_at' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => $attributes['published_at'] ?? now()->subDay(),
        ]);
    }
}
