<?php

namespace Database\Factories;

use App\Models\Blog;
use Faker\Generator as Faker;
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
            'title' => fake()->sentence(6),
            'content' => fake()->paragraphs(5, true),
            // Keep images empty by default to avoid broken /storage paths in dev
            'images' => [],
        ];
    }
}
