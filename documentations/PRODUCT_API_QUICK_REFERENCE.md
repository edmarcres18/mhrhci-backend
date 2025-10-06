# Product API - Quick Reference Card

## 🔗 Endpoints

### 1. Get All Products
```
GET /api/v1/products
```
**Rate Limit:** 60 req/min | **Cache:** 5 minutes

**Parameters:**
```
?search=keyword         # Search in name/description
?perPage=25            # Items per page (1-100)
?page=2                # Page number
?sortBy=name           # created_at|updated_at|name
?sortOrder=asc         # asc|desc
```

**Example:**
```bash
curl "http://localhost/api/v1/products?search=laptop&perPage=20&page=1"
```

---

### 2. Get Latest Products
```
GET /api/v1/products/latest
```
**Rate Limit:** 100 req/min | **Cache:** 10 minutes

**Parameters:**
```
?limit=5               # Number of products (1-50, default: 3)
```

**Example:**
```bash
curl "http://localhost/api/v1/products/latest?limit=10"
```

---

## 📦 Response Format

### Success Response (200)
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Product Name",
            "description": "Full description...",
            "excerpt": "Auto-generated excerpt...",
            "images": ["https://domain.com/storage/products/image.jpg"],
            "features": ["Feature 1", "Feature 2"],
            "created_at": "2025-10-01T02:15:30+00:00",
            "updated_at": "2025-10-01T02:15:30+00:00"
        }
    ],
    "meta": {...},
    "links": {...}
}
```

### Error Response (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "perPage": ["The per page must not be greater than 100."]
    }
}
```

### Rate Limit Response (429)
```json
{
    "message": "Too Many Requests"
}
```

---

## 🧪 Quick Test Commands

### Test Basic Fetch
```bash
curl -X GET "http://localhost/api/v1/products"
```

### Test with Search
```bash
curl -X GET "http://localhost/api/v1/products?search=laptop&perPage=5"
```

### Test Latest
```bash
curl -X GET "http://localhost/api/v1/products/latest?limit=3"
```

### Test Validation
```bash
curl -X GET "http://localhost/api/v1/products?perPage=200"  # Should return 422
```

---

## 🔧 Maintenance Commands

### Clear API Cache
```bash
php artisan cache:clear
```

### Run API Tests
```bash
php artisan test --filter=ProductApiTest
```

### Check Routes
```bash
php artisan route:list --path=api/v1/products
```

### Monitor Logs
```bash
tail -f storage/logs/laravel.log
```

---

## 📊 Files Modified/Created

### Modified Files
- `app/Http/Controllers/ProductController.php` - Added `apiIndex()` and `apiLatest()` methods
- `app/Models/Product.php` - Added cache invalidation
- `routes/api.php` - Added API routes with rate limiting

### Created Files
- `PRODUCT_API_DOCUMENTATION.md` - Full API documentation
- `PRODUCT_API_QUICK_REFERENCE.md` - This file
- `Product_API.postman_collection.json` - Postman collection for testing
- `tests/Feature/ProductApiTest.php` - Comprehensive test suite

---

## ⚡ Performance Tips

1. **Enable Redis** for better caching:
   ```env
   CACHE_DRIVER=redis
   ```

2. **Add Database Indexes**:
   ```sql
   CREATE INDEX idx_created_at ON products(created_at);
   CREATE INDEX idx_updated_at ON products(updated_at);
   CREATE FULLTEXT INDEX idx_search ON products(name, description);
   ```

3. **Optimize for Production**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   composer install --optimize-autoloader --no-dev
   ```

---

## 🔒 Security Checklist

- ✅ Rate limiting enabled (60-100 req/min)
- ✅ Input validation on all parameters
- ✅ SQL injection protection (Eloquent ORM)
- ✅ XSS protection (auto-escaping)
- ✅ Error logging enabled
- ⚠️ Enable HTTPS in production
- ⚠️ Configure CORS for allowed domains
- ⚠️ Set `APP_DEBUG=false` in production

---

## 📱 Integration Examples

### JavaScript
```javascript
const response = await fetch('/api/v1/products/latest?limit=5');
const data = await response.json();
console.log(data.data); // Array of products
```

### PHP
```php
$products = file_get_contents('http://localhost/api/v1/products');
$data = json_decode($products, true);
```

### Python
```python
import requests
response = requests.get('http://localhost/api/v1/products/latest?limit=3')
data = response.json()
```

---

## 🆘 Troubleshooting

| Issue | Solution |
|-------|----------|
| 429 Error | Wait 1 minute or adjust rate limits |
| Empty data | Check if products exist: `Product::count()` |
| Images not loading | Run: `php artisan storage:link` |
| Cache issues | Run: `php artisan cache:clear` |

---

## 📚 Related Documentation

- Full Documentation: `PRODUCT_API_DOCUMENTATION.md`
- Setup Guide: `BLOG_API_SETUP.md` (applies to products too)
- Postman Collection: `Product_API.postman_collection.json`
- Test Suite: `tests/Feature/ProductApiTest.php`

---

**Version:** 1.0.0 | **Last Updated:** 2025-10-01
