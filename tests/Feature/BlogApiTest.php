<?php

namespace Tests\Feature;

use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BlogApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test fetching all blogs with default parameters
     */
    public function test_api_index_returns_paginated_blogs(): void
    {
        // Create test blogs
        Blog::factory()->count(15)->create();

        $response = $this->getJson('/api/v1/blogs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'content',
                        'excerpt',
                        'images',
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
     * Test fetching blogs with custom pagination
     */
    public function test_api_index_with_custom_pagination(): void
    {
        Blog::factory()->count(30)->create();

        $response = $this->getJson('/api/v1/blogs?perPage=25&page=1');

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
     * Test fetching blogs with search parameter
     */
    public function test_api_index_with_search(): void
    {
        Blog::factory()->create(['title' => 'Laravel Tutorial']);
        Blog::factory()->create(['title' => 'PHP Best Practices']);
        Blog::factory()->create(['title' => 'JavaScript Guide']);

        $response = $this->getJson('/api/v1/blogs?search=Laravel');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('Laravel', $data[0]['title']);
    }

    /**
     * Test fetching blogs with sorting
     */
    public function test_api_index_with_sorting(): void
    {
        Blog::factory()->create(['title' => 'Zebra', 'created_at' => now()->subDays(1)]);
        Blog::factory()->create(['title' => 'Apple', 'created_at' => now()->subDays(2)]);
        Blog::factory()->create(['title' => 'Mango', 'created_at' => now()->subDays(3)]);

        $response = $this->getJson('/api/v1/blogs?sortBy=title&sortOrder=asc');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals('Apple', $data[0]['title']);
    }

    /**
     * Test validation for invalid parameters
     */
    public function test_api_index_validates_parameters(): void
    {
        $response = $this->getJson('/api/v1/blogs?perPage=200'); // Max is 100

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors(['perPage']);
    }

    /**
     * Test fetching latest blogs with default limit
     */
    public function test_api_latest_returns_three_blogs_by_default(): void
    {
        Blog::factory()->count(10)->create();

        $response = $this->getJson('/api/v1/blogs/latest');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'content',
                        'excerpt',
                        'images',
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
     * Test fetching latest blogs with custom limit
     */
    public function test_api_latest_with_custom_limit(): void
    {
        Blog::factory()->count(20)->create();

        $response = $this->getJson('/api/v1/blogs/latest?limit=10');

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
     * Test latest blogs returns newest first
     */
    public function test_api_latest_returns_newest_first(): void
    {
        $oldest = Blog::factory()->create(['created_at' => now()->subDays(5)]);
        $middle = Blog::factory()->create(['created_at' => now()->subDays(2)]);
        $newest = Blog::factory()->create(['created_at' => now()]);

        $response = $this->getJson('/api/v1/blogs/latest?limit=3');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals($newest->id, $data[0]['id']);
        $this->assertEquals($middle->id, $data[1]['id']);
        $this->assertEquals($oldest->id, $data[2]['id']);
    }

    /**
     * Test validation for latest blogs limit
     */
    public function test_api_latest_validates_limit(): void
    {
        $response = $this->getJson('/api/v1/blogs/latest?limit=100'); // Max is 50

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

        Blog::factory()->count(5)->create();

        // First request - should cache
        $response1 = $this->getJson('/api/v1/blogs');
        $response1->assertStatus(200);

        // Check cache exists
        $cacheKey = 'blogs_api_'.md5(json_encode([
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

        Blog::factory()->count(5)->create();

        // First request - should cache
        $response = $this->getJson('/api/v1/blogs/latest');
        $response->assertStatus(200);

        // Check cache exists
        $this->assertTrue(Cache::has('blogs_latest_3'));
    }

    /**
     * Test rate limiting is configured
     */
    public function test_api_endpoints_have_rate_limiting(): void
    {
        Blog::factory()->count(5)->create();

        // Make multiple requests
        for ($i = 0; $i < 65; $i++) {
            $response = $this->getJson('/api/v1/blogs');

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
        Blog::factory()->create([
            'images' => ['blogs/test-image.jpg'],
        ]);

        $response = $this->getJson('/api/v1/blogs');

        $response->assertStatus(200);

        $images = $response->json('data.0.images');
        $this->assertNotEmpty($images);
        $this->assertStringContainsString('storage/blogs/test-image.jpg', $images[0]);
    }

    /**
     * Test excerpt is generated correctly
     */
    public function test_api_generates_excerpt(): void
    {
        $longContent = str_repeat('Lorem ipsum dolor sit amet. ', 50);

        Blog::factory()->create([
            'content' => $longContent,
        ]);

        $response = $this->getJson('/api/v1/blogs');

        $response->assertStatus(200);

        $excerpt = $response->json('data.0.excerpt');
        $this->assertNotNull($excerpt);
        $this->assertLessThanOrEqual(153, strlen($excerpt)); // 150 + '...'
        $this->assertStringEndsWith('...', $excerpt);
    }
}
