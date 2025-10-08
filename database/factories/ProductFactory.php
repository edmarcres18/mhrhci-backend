<?php

namespace Database\Factories;

use App\Models\Product;
use App\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $faker = \Faker\Factory::create();
        
        return [
            'name' => $faker->words(3, true),
            'product_type' => $faker->randomElement(ProductType::cases()),
            'description' => $faker->paragraph(),
            // Keep images empty by default to avoid broken /storage paths in dev
            'images' => [],
            // Random list of features
            'features' => $faker->boolean(70) ? $faker->sentences($faker->numberBetween(2, 5)) : [],
        ];
    }
}
