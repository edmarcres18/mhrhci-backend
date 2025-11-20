<?php

namespace App\Models;

use App\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'product_type',
        'description',
        'images',
        'features',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'product_type' => ProductType::class,
        'images' => 'array',
        'features' => 'array',
    ];

    /**
     * The "booted" method of the model.
     * Clear API cache when product is created, updated, or deleted.
     */
    protected static function booted(): void
    {
        static::created(function () {
            self::clearApiCache();
        });

        static::updated(function () {
            self::clearApiCache();
        });

        static::deleted(function () {
            self::clearApiCache();
        });
    }

    /**
     * Clear all product API related caches.
     */
    protected static function clearApiCache(): void
    {
        // Clear latest products cache for different limits
        for ($i = 1; $i <= 50; $i++) {
            Cache::forget("products_latest_{$i}");
        }

        // Clear paginated products cache (pattern matching)
        // Note: This clears common cache keys. For production with Redis,
        // consider using cache tags for more efficient cache invalidation.
        Cache::flush(); // Use with caution in production - better to use tags
    }
}
