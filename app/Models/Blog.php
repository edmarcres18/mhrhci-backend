<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Blog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'content',
        'images',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'images' => 'array',
    ];

    /**
     * The "booted" method of the model.
     * Clear API cache when blog is created, updated, or deleted.
     */
    protected static function booted(): void
    {
        static::created(function () {
            self::clearApiCache();
        });

        static::updated(function ($blog) {
            self::clearApiCache($blog->id);
        });

        static::deleted(function ($blog) {
            self::clearApiCache($blog->id);
        });
    }

    /**
     * Clear all blog API related caches.
     */
    protected static function clearApiCache(?int $blogId = null): void
    {
        // Clear specific blog cache if ID provided
        if ($blogId) {
            Cache::forget("blog_show_api_{$blogId}");
            Cache::forget("blog_related_{$blogId}");
        }

        // Clear latest blogs cache for different limits
        for ($i = 1; $i <= 50; $i++) {
            Cache::forget("blogs_latest_{$i}");
        }

        // Clear all related blogs caches (since content changed, relations may change)
        // In production with Redis, use cache tags for more efficient invalidation
        $allBlogs = self::pluck('id');
        foreach ($allBlogs as $id) {
            Cache::forget("blog_related_{$id}");
        }

        // Clear paginated blogs cache (pattern matching)
        // Note: This clears common cache keys. For production with Redis,
        // consider using cache tags for more efficient cache invalidation.
        Cache::flush(); // Use with caution in production - better to use tags
    }
}
