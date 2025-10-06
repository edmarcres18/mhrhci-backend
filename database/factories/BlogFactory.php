<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    protected $model = Blog::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'content' => $this->faker->paragraphs(5, true),
            // Keep images empty by default to avoid broken /storage paths in dev
            'images' => [],
        ];
    }
}
