<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Principal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'logo',
        'description',
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
