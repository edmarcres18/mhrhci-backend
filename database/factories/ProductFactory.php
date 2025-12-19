<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Principal;
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
        $principal = Principal::query()->inRandomOrder()->first() ?? Principal::factory()->create();

        return [
            'name' => $this->faker->words(3, true),
            'product_type' => $this->faker->randomElement(ProductType::cases()),
            'principal_id' => $principal->id,
            'description' => $this->faker->paragraph(),
            // Keep images empty by default to avoid broken /storage paths in dev
            'images' => [],
            // Random list of features
            'features' => $this->faker->boolean(70) ? $this->faker->sentences($this->faker->numberBetween(2, 5)) : [],
        ];
    }
}
