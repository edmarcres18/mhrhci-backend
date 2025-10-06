# Blog API Documentation

## Overview
Production-ready REST API for fetching blog posts with advanced features including pagination, filtering, caching, and rate limiting.

## Base URL
```
https://your-domain.com/api/v1
```

## Security Features
- ✅ **Rate Limiting**: Prevents abuse and ensures fair usage
- ✅ **Input Validation**: All parameters are validated
- ✅ **Error Handling**: Comprehensive error responses
- ✅ **Caching**: Redis/database caching for performance
- ✅ **HTTPS Ready**: Secure transport layer

## Endpoints

### 1. Get All Blogs
Retrieve a paginated list of all blog posts with optional filtering and sorting.

#### Endpoint
```
GET /api/v1/blogs
```

#### Rate Limit
- **60 requests per minute** per IP address

#### Query Parameters
| Parameter   | Type    | Required | Default      | Description                                    |
|-------------|---------|----------|--------------|------------------------------------------------|
| `search`    | string  | No       | -            | Search in title and content (max 255 chars)   |
| `perPage`   | integer | No       | 10           | Items per page (min: 1, max: 100)             |
| `page`      | integer | No       | 1            | Page number (min: 1)                          |
| `sortBy`    | string  | No       | created_at   | Sort field: `created_at`, `updated_at`, `title` |
| `sortOrder` | string  | No       | desc         | Sort order: `asc`, `desc`                     |

#### Example Request
```bash
# Get all blogs with default pagination
curl -X GET "https://your-domain.com/api/v1/blogs"

# Search with custom pagination
curl -X GET "https://your-domain.com/api/v1/blogs?search=technology&perPage=25&page=1"

# Sort by title ascending
curl -X GET "https://your-domain.com/api/v1/blogs?sortBy=title&sortOrder=asc"
```

#### Success Response (200 OK)
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Understanding Laravel",
            "content": "Full content of the blog post...",
            "excerpt": "Understanding Laravel is essential for modern...",
            "images": [
                "https://your-domain.com/storage/blogs/image1.jpg",
                "https://your-domain.com/storage/blogs/image2.jpg"
            ],
            "created_at": "2025-10-01T02:15:30+00:00",
            "updated_at": "2025-10-01T02:15:30+00:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "to": 10,
        "per_page": 10,
        "total": 45,
        "last_page": 5
    },
    "links": {
        "first": "https://your-domain.com/api/v1/blogs?page=1",
        "last": "https://your-domain.com/api/v1/blogs?page=5",
        "prev": null,
        "next": "https://your-domain.com/api/v1/blogs?page=2"
    }
}
```

#### Error Responses

**Validation Error (422 Unprocessable Entity)**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "perPage": ["The per page must not be greater than 100."]
    }
}
```

**Rate Limit Exceeded (429 Too Many Requests)**
```json
{
    "message": "Too Many Requests"
}
```

**Server Error (500 Internal Server Error)**
```json
{
    "success": false,
    "message": "An error occurred while fetching blogs"
}
```

---

### 2. Get Latest Blogs
Retrieve the N most recent blog posts (default: 3).

#### Endpoint
```
GET /api/v1/blogs/latest
```

#### Rate Limit
- **100 requests per minute** per IP address

#### Query Parameters
| Parameter | Type    | Required | Default | Description                    |
|-----------|---------|----------|---------|--------------------------------|
| `limit`   | integer | No       | 3       | Number of blogs (min: 1, max: 50) |

#### Example Request
```bash
# Get 3 latest blogs (default)
curl -X GET "https://your-domain.com/api/v1/blogs/latest"

# Get 10 latest blogs
curl -X GET "https://your-domain.com/api/v1/blogs/latest?limit=10"
```

#### Success Response (200 OK)
```json
{
    "success": true,
    "data": [
        {
            "id": 45,
            "title": "Latest Blog Post",
            "content": "Full content here...",
            "excerpt": "Latest Blog Post excerpt...",
            "images": [
                "https://your-domain.com/storage/blogs/latest.jpg"
            ],
            "created_at": "2025-10-01T02:15:30+00:00",
            "updated_at": "2025-10-01T02:15:30+00:00"
        },
        {
            "id": 44,
            "title": "Second Latest Post",
            "content": "Content...",
            "excerpt": "Second Latest Post excerpt...",
            "images": [],
            "created_at": "2025-09-30T18:20:15+00:00",
            "updated_at": "2025-09-30T18:20:15+00:00"
        }
    ],
    "meta": {
        "count": 2,
        "limit": 3
    }
}
```

#### Error Responses

**Validation Error (422 Unprocessable Entity)**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "limit": ["The limit must not be greater than 50."]
    }
}
```

---

## Response Fields

### Blog Object
| Field       | Type     | Description                                    |
|-------------|----------|------------------------------------------------|
| `id`        | integer  | Unique blog identifier                         |
| `title`     | string   | Blog title                                     |
| `content`   | string   | Full blog content                              |
| `excerpt`   | string   | Auto-generated excerpt (150 chars max)         |
| `images`    | array    | Array of full image URLs                       |
| `created_at`| string   | ISO 8601 formatted creation timestamp          |
| `updated_at`| string   | ISO 8601 formatted last update timestamp       |

---

## Performance & Scalability

### Caching
- **All Blogs endpoint**: 5-minute cache
- **Latest Blogs endpoint**: 10-minute cache
- Cache keys based on request parameters
- Automatic cache invalidation on blog updates

### Rate Limiting
Protects against abuse and ensures API availability:
- All blogs: 60 requests/minute
- Latest blogs: 100 requests/minute

### Best Practices for Clients
1. **Implement caching** on client-side
2. **Use pagination** efficiently (avoid fetching all pages at once)
3. **Respect rate limits** (implement exponential backoff)
4. **Handle errors gracefully** (retry on 5xx, don't retry on 4xx)
5. **Use conditional requests** (check for new data before fetching)

---

## Code Examples

### JavaScript (Fetch API)
```javascript
// Fetch all blogs with search
async function fetchBlogs(search = '', page = 1, perPage = 10) {
    const url = new URL('https://your-domain.com/api/v1/blogs');
    url.searchParams.append('search', search);
    url.searchParams.append('page', page);
    url.searchParams.append('perPage', perPage);
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            return data;
        } else {
            console.error('Error:', data.message);
        }
    } catch (error) {
        console.error('Network error:', error);
    }
}

// Fetch latest blogs
async function fetchLatestBlogs(limit = 3) {
    const url = `https://your-domain.com/api/v1/blogs/latest?limit=${limit}`;
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error:', error);
    }
}
```

### PHP (cURL)
```php
<?php
// Fetch all blogs
function fetchBlogs($search = '', $page = 1, $perPage = 10) {
    $url = 'https://your-domain.com/api/v1/blogs?' . http_build_query([
        'search' => $search,
        'page' => $page,
        'perPage' => $perPage,
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return null;
}

// Fetch latest blogs
function fetchLatestBlogs($limit = 3) {
    $url = "https://your-domain.com/api/v1/blogs/latest?limit={$limit}";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}
?>
```

### Python (Requests)
```python
import requests

def fetch_blogs(search='', page=1, per_page=10):
    """Fetch all blogs with pagination and search"""
    url = 'https://your-domain.com/api/v1/blogs'
    params = {
        'search': search,
        'page': page,
        'perPage': per_page
    }
    
    try:
        response = requests.get(url, params=params, timeout=10)
        response.raise_for_status()
        return response.json()
    except requests.exceptions.RequestException as e:
        print(f"Error: {e}")
        return None

def fetch_latest_blogs(limit=3):
    """Fetch latest N blogs"""
    url = f'https://your-domain.com/api/v1/blogs/latest'
    params = {'limit': limit}
    
    try:
        response = requests.get(url, params=params, timeout=10)
        response.raise_for_status()
        return response.json()
    except requests.exceptions.RequestException as e:
        print(f"Error: {e}")
        return None
```

---

## Security Considerations

### HTTPS Required
Always use HTTPS in production to protect data in transit.

### CORS Configuration
Configure CORS headers in Laravel to allow specific domains:
```php
// In bootstrap/app.php or config/cors.php
'allowed_origins' => ['https://your-frontend-domain.com'],
```

### Input Sanitization
All inputs are automatically sanitized and validated by Laravel.

### SQL Injection Protection
Laravel's Eloquent ORM protects against SQL injection by using parameterized queries.

### Rate Limiting
Implemented per IP address to prevent abuse. Consider implementing API keys for authenticated access if needed.

---

## Monitoring & Logging

All API errors are automatically logged to Laravel's log files:
- Location: `storage/logs/laravel.log`
- Format: Includes error message and stack trace
- Monitoring: Set up alerts for 500 errors in production

---

## Support & Contact

For issues or questions about the API, please contact your development team or open an issue in your project repository.

---

## Changelog

### Version 1.0.0 (2025-10-01)
- ✅ Initial release
- ✅ GET /api/v1/blogs endpoint
- ✅ GET /api/v1/blogs/latest endpoint
- ✅ Rate limiting implementation
- ✅ Caching support
- ✅ Comprehensive error handling
