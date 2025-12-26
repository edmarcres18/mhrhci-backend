<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Principal extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'logo',
        'description',
        'is_featured',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_featured' => 'boolean',
    ];

    /**
     * Clear product-related caches when principals change.
     */
    protected static function booted(): void
    {
        static::created(fn () => self::clearProductCaches());
        static::updated(fn () => self::clearProductCaches());
        static::deleted(fn () => self::clearProductCaches());
    }

    /**
     * Relation: a principal has many products.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Determine if the principal is featured.
     */
    public function isFeatured(): bool
    {
        return (bool) $this->is_featured;
    }

    /**
     * Scope for featured principals.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Clear product caches impacted by principal changes.
     */
    protected static function clearProductCaches(): void
    {
        // Clear latest/featured products cache for different limits
        for ($i = 1; $i <= 50; $i++) {
            Cache::forget("products_latest_{$i}");
            Cache::forget("products_featured_{$i}");
        }

        // Clear paginated API caches
        Cache::flush();
    }
}
