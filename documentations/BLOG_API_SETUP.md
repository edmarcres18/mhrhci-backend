# Blog API Setup Guide

## 🚀 Quick Start

The Blog API is now ready to use! Follow these steps to test and deploy.

## 📋 What's Included

### API Endpoints
1. **GET /api/v1/blogs** - Fetch all blogs with pagination, search, and filtering
2. **GET /api/v1/blogs/latest** - Fetch the N latest blogs (default: 3)

### Security Features
- ✅ Rate limiting (60-100 requests/minute)
- ✅ Input validation
- ✅ Error handling & logging
- ✅ SQL injection protection
- ✅ XSS protection

### Performance Features
- ✅ Redis/Database caching (5-10 minutes)
- ✅ Efficient database queries
- ✅ Optimized pagination
- ✅ Image URL optimization

## 🔧 Configuration

### 1. Cache Configuration (Optional but Recommended)

For production, configure Redis for better performance:

**Install Redis PHP Extension:**
```bash
# Windows (via Laragon)
# Redis should be available in Laragon's menu

# Or install Redis manually
composer require predis/predis
```

**Update `.env`:**
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

If Redis is not available, Laravel will use the file cache driver automatically.

### 2. CORS Configuration (For External API Access)

If you need to access the API from external domains (e.g., a separate frontend):

**Edit `bootstrap/app.php`:**
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);
    
    // Add CORS middleware
    $middleware->api(prepend: [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);

    $middleware->web(append: [
        HandleAppearance::class,
        HandleInertiaRequests::class,
        AddLinkHeadersForPreloadedAssets::class,
    ]);
})
```

**Create `config/cors.php` if it doesn't exist:**
```php
<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // Change to specific domains in production
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

### 3. Rate Limiting Customization (Optional)

To adjust rate limits, edit `routes/api.php`:

```php
// Change from 60 to your desired limit
->middleware('throttle:120,1') // 120 requests per minute
```

## 🧪 Testing

### Run All Tests
```bash
php artisan test --filter=BlogApiTest
```

### Run Specific Test
```bash
# Test pagination
php artisan test --filter=test_api_index_returns_paginated_blogs

# Test latest blogs
php artisan test --filter=test_api_latest_returns_three_blogs_by_default

# Test caching
php artisan test --filter=test_api_index_uses_cache
```

### Manual Testing with cURL

**Test All Blogs:**
```bash
curl -X GET "http://localhost/api/v1/blogs"
```

**Test with Search:**
```bash
curl -X GET "http://localhost/api/v1/blogs?search=test&perPage=5"
```

**Test Latest Blogs:**
```bash
curl -X GET "http://localhost/api/v1/blogs/latest?limit=5"
```

**Test Rate Limiting:**
```bash
# Run this in a loop to trigger rate limit
for i in {1..65}; do curl -X GET "http://localhost/api/v1/blogs"; done
```

## 📊 Performance Optimization

### 1. Enable OPcache (Production)

**In `php.ini`:**
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

### 2. Optimize Laravel

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### 3. Database Indexing

Ensure your blogs table has proper indexes:

```php
// In your migration file
Schema::table('blogs', function (Blueprint $table) {
    $table->index('created_at');
    $table->index('updated_at');
    $table->fulltext(['title', 'content']); // For better search performance
});
```

Run the migration:
```bash
php artisan migrate
```

### 4. Clear Cache When Needed

```bash
# Clear application cache
php artisan cache:clear

# Clear specific cache keys
php artisan tinker
>>> Cache::forget('blogs_latest_3');
>>> Cache::flush(); // Clear all cache
```

## 🔒 Security Best Practices

### 1. HTTPS in Production

**Force HTTPS in `AppServiceProvider.php`:**
```php
use Illuminate\Support\Facades\URL;

public function boot()
{
    if (app()->environment('production')) {
        URL::forceScheme('https');
    }
}
```

### 2. Environment Variables

Ensure `.env` is not committed:
```bash
# .gitignore should include:
.env
.env.backup
```

### 3. API Keys (Optional Enhancement)

For private API access, implement Laravel Sanctum:

```bash
php artisan install:api
```

Then protect routes:
```php
Route::middleware('auth:sanctum')->group(function () {
    // Protected routes
});
```

### 4. Additional Rate Limiting

For stricter control, create custom rate limiters in `AppServiceProvider.php`:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot()
{
    RateLimiter::for('blog-api', function (Request $request) {
        return Limit::perMinute(60)->by($request->ip());
    });
}
```

## 📱 Frontend Integration Examples

### React/Next.js
```javascript
// lib/blogApi.js
export async function getAllBlogs(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const response = await fetch(
        `${process.env.NEXT_PUBLIC_API_URL}/api/v1/blogs?${queryString}`
    );
    return response.json();
}

export async function getLatestBlogs(limit = 3) {
    const response = await fetch(
        `${process.env.NEXT_PUBLIC_API_URL}/api/v1/blogs/latest?limit=${limit}`
    );
    return response.json();
}
```

### Vue.js
```javascript
// composables/useBlogApi.js
export const useBlogApi = () => {
    const fetchBlogs = async (params) => {
        const query = new URLSearchParams(params).toString();
        const response = await fetch(`/api/v1/blogs?${query}`);
        return response.json();
    };
    
    const fetchLatest = async (limit = 3) => {
        const response = await fetch(`/api/v1/blogs/latest?limit=${limit}`);
        return response.json();
    };
    
    return { fetchBlogs, fetchLatest };
};
```

## 🐛 Troubleshooting

### Issue: "Class 'Redis' not found"
**Solution:** Install Redis PHP extension or change `CACHE_DRIVER=file` in `.env`

### Issue: "Too Many Requests" (429 Error)
**Solution:** Wait 1 minute or adjust rate limits in `routes/api.php`

### Issue: Images not loading
**Solution:** Run `php artisan storage:link` to create symbolic link

### Issue: Cache not clearing
**Solution:** 
```bash
php artisan cache:clear
php artisan config:clear
```

### Issue: API returns empty data
**Solution:** Check if blogs exist in database:
```bash
php artisan tinker
>>> App\Models\Blog::count();
```

## 📈 Monitoring

### Log Files
Monitor API errors in:
```
storage/logs/laravel.log
```

### Set Up Log Monitoring (Production)

**Install log monitoring service:**
- Sentry (error tracking)
- New Relic (performance monitoring)
- DataDog (full-stack monitoring)

**Or use Laravel Telescope for development:**
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Access at: `http://your-domain.com/telescope`

## 🚀 Deployment Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Configure Redis for caching
- [ ] Enable HTTPS/SSL
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Configure CORS for allowed domains
- [ ] Set up monitoring (Sentry, New Relic, etc.)
- [ ] Set up automated backups
- [ ] Configure proper error logging
- [ ] Test all endpoints in production
- [ ] Load test with expected traffic

## 📚 Additional Resources

- [Full API Documentation](./BLOG_API_DOCUMENTATION.md)
- [Laravel Rate Limiting Docs](https://laravel.com/docs/routing#rate-limiting)
- [Laravel Caching Docs](https://laravel.com/docs/cache)
- [Laravel Testing Docs](https://laravel.com/docs/testing)

## 🆘 Support

For issues or questions:
1. Check `storage/logs/laravel.log` for errors
2. Run tests: `php artisan test --filter=BlogApiTest`
3. Review API documentation: `BLOG_API_DOCUMENTATION.md`
4. Contact your development team

---

**Happy Coding! 🎉**
