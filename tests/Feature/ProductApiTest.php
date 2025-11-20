<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test fetching all products with default parameters
     */
    public function test_api_index_returns_paginated_products(): void
    {
        // Create test products
        Product::factory()->count(15)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'excerpt',
                        'images',
                        'features',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'to',
                    'per_page',
                    'total',
                    'last_page',
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
            ])
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(10, $response->json('data')); // Default per page is 10
    }

    /**
     * Test fetching products with custom pagination
     */
    public function test_api_index_with_custom_pagination(): void
    {
        Product::factory()->count(30)->create();

        $response = $this->getJson('/api/v1/products?perPage=25&page=1');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta' => [
                    'per_page' => 25,
                    'current_page' => 1,
                ],
            ]);

        $this->assertCount(25, $response->json('data'));
    }

    /**
     * Test fetching products with search parameter
     */
    public function test_api_index_with_search(): void
    {
        Product::factory()->create(['name' => 'Laravel Framework']);
        Product::factory()->create(['name' => 'PHP Development Kit']);
        Product::factory()->create(['name' => 'JavaScript Library']);

        $response = $this->getJson('/api/v1/products?search=Laravel');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('Laravel', $data[0]['name']);
    }

    /**
     * Test fetching products with sorting
     */
    public function test_api_index_with_sorting(): void
    {
        Product::factory()->create(['name' => 'Zebra Product', 'created_at' => now()->subDays(1)]);
        Product::factory()->create(['name' => 'Apple Product', 'created_at' => now()->subDays(2)]);
        Product::factory()->create(['name' => 'Mango Product', 'created_at' => now()->subDays(3)]);

        $response = $this->getJson('/api/v1/products?sortBy=name&sortOrder=asc');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertEquals('Apple Product', $data[0]['name']);
    }

    /**
     * Test validation for invalid parameters
     */
    public function test_api_index_validates_parameters(): void
    {
        $response = $this->getJson('/api/v1/products?perPage=200'); // Max is 100

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors(['perPage']);
    }

    /**
     * Test fetching latest products with default limit
     */
    public function test_api_latest_returns_three_products_by_default(): void
    {
        Product::factory()->count(10)->create();

        $response = $this->getJson('/api/v1/products/latest');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'excerpt',
                        'images',
                        'features',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => [
                    'count',
                    'limit',
                ],
            ])
            ->assertJson([
                'success' => true,
                'meta' => [
                    'limit' => 3,
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test fetching latest products with custom limit
     */
    public function test_api_latest_with_custom_limit(): void
    {
        Product::factory()->count(20)->create();

        $response = $this->getJson('/api/v1/products/latest?limit=10');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta' => [
                    'count' => 10,
                    'limit' => 10,
                ],
            ]);

        $this->assertCount(10, $response->json('data'));
    }

    /**
     * Test latest products returns newest first
     */
    public function test_api_latest_returns_newest_first(): void
    {
        $oldest = Product::factory()->create(['created_at' => now()->subDays(5)]);
        $middle = Product::factory()->create(['created_at' => now()->subDays(2)]);
        $newest = Product::factory()->create(['created_at' => now()]);

        $response = $this->getJson('/api/v1/products/latest?limit=3');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertEquals($newest->id, $data[0]['id']);
        $this->assertEquals($middle->id, $data[1]['id']);
        $this->assertEquals($oldest->id, $data[2]['id']);
    }

    /**
     * Test validation for latest products limit
     */
    public function test_api_latest_validates_limit(): void
    {
        $response = $this->getJson('/api/v1/products/latest?limit=100'); // Max is 50

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors(['limit']);
    }

    /**
     * Test caching is working for index endpoint
     */
    public function test_api_index_uses_cache(): void
    {
        Cache::flush(); // Clear cache first
        
        Product::factory()->count(5)->create();

        // First request - should cache
        $response1 = $this->getJson('/api/v1/products');
        $response1->assertStatus(200);

        // Check cache exists
        $cacheKey = 'products_api_' . md5(json_encode([
            'search' => '',
            'perPage' => 10,
            'page' => 1,
            'sortBy' => 'created_at',
            'sortOrder' => 'desc',
        ]));
        
        $this->assertTrue(Cache::has($cacheKey));
    }

    /**
     * Test caching is working for latest endpoint
     */
    public function test_api_latest_uses_cache(): void
    {
        Cache::flush();
        
        Product::factory()->count(5)->create();

        // First request - should cache
        $response = $this->getJson('/api/v1/products/latest');
        $response->assertStatus(200);

        // Check cache exists
        $this->assertTrue(Cache::has('products_latest_3'));
    }

    /**
     * Test rate limiting is configured
     */
    public function test_api_endpoints_have_rate_limiting(): void
    {
        Product::factory()->count(5)->create();

        // Make multiple requests
        for ($i = 0; $i < 65; $i++) {
            $response = $this->getJson('/api/v1/products');
            
            if ($i < 60) {
                $response->assertStatus(200);
            } else {
                // After 60 requests, should be rate limited
                $response->assertStatus(429);
                break;
            }
        }
    }

    /**
     * Test image URLs are properly formatted
     */
    public function test_api_returns_full_image_urls(): void
    {
        Product::factory()->create([
            'images' => ['products/test-product.jpg'],
        ]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
        
        $images = $response->json('data.0.images');
        $this->assertNotEmpty($images);
        $this->assertStringContainsString('storage/products/test-product.jpg', $images[0]);
    }

    /**
     * Test excerpt is generated correctly
     */
    public function test_api_generates_excerpt(): void
    {
        $longDescription = str_repeat('Lorem ipsum dolor sit amet. ', 50);
        
        Product::factory()->create([
            'description' => $longDescription,
        ]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
        
        $excerpt = $response->json('data.0.excerpt');
        $this->assertNotNull($excerpt);
        $this->assertLessThanOrEqual(153, strlen($excerpt)); // 150 + '...'
        $this->assertStringEndsWith('...', $excerpt);
    }

    /**
     * Test features array is returned
     */
    public function test_api_returns_features_array(): void
    {
        Product::factory()->create([
            'features' => ['Feature 1', 'Feature 2', 'Feature 3'],
        ]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
        
        $features = $response->json('data.0.features');
        $this->assertIsArray($features);
        $this->assertCount(3, $features);
        $this->assertEquals('Feature 1', $features[0]);
    }

    /**
     * Test empty features returns empty array
     */
    public function test_api_handles_null_features(): void
    {
        Product::factory()->create([
            'features' => null,
        ]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
        
        $features = $response->json('data.0.features');
        $this->assertIsArray($features);
        $this->assertEmpty($features);
    }
}
