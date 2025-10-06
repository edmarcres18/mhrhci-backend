# NGINX SSL/HTTPS Setup Guide

This document explains how to configure NGINX to handle both HTTP and HTTPS requests in production.

## 🔒 Configuration Overview

The NGINX configuration now supports:
- ✅ **HTTP (Port 80)** - For local development and redirects
- ✅ **HTTPS (Port 443)** - For secure production traffic
- ✅ **HTTP to HTTPS Redirect** - Automatic for production domains
- ✅ **Security Headers** - HSTS, XSS Protection, Content Security
- ✅ **Cloudflare Support** - Proxy headers and Real IP detection
- ✅ **TLS 1.2/1.3** - Modern SSL/TLS protocols
- ✅ **OCSP Stapling** - Improved SSL performance

## 📋 Prerequisites

You need SSL certificates to enable HTTPS. There are several options:

### Option 1: Cloudflare Origin Certificate (Recommended for Cloudflare users)

1. **Generate Origin Certificate in Cloudflare Dashboard:**
   - Go to SSL/TLS → Origin Server
   - Click "Create Certificate"
   - Select key type: RSA (2048)
   - Set validity: 15 years
   - Download both files: `cert.pem` and `key.pem`

2. **Upload to Server:**
   ```bash
   # Create directory
   docker-compose exec app mkdir -p /etc/ssl/cloudflare
   
   # Copy certificates (from your local machine)
   docker cp cert.pem <container_id>:/etc/ssl/cloudflare/cert.pem
   docker cp key.pem <container_id>:/etc/ssl/cloudflare/key.pem
   
   # Set permissions
   docker-compose exec app chmod 600 /etc/ssl/cloudflare/key.pem
   docker-compose exec app chmod 644 /etc/ssl/cloudflare/cert.pem
   ```

3. **Update nginx config (lines 73-75):**
   ```nginx
   ssl_certificate /etc/ssl/cloudflare/cert.pem;
   ssl_certificate_key /etc/ssl/cloudflare/key.pem;
   ```

### Option 2: Let's Encrypt (Free, Auto-Renewal)

1. **Install Certbot:**
   ```bash
   docker-compose exec app apt-get update
   docker-compose exec app apt-get install -y certbot python3-certbot-nginx
   ```

2. **Generate Certificate:**
   ```bash
   docker-compose exec app certbot --nginx -d mhrhci.mhrpci.site -d www.mhrhci.mhrpci.site
   ```

3. **Auto-Renewal (add to crontab):**
   ```bash
   0 0 * * * certbot renew --quiet
   ```

### Option 3: Self-Signed Certificate (Development Only)

```bash
# Generate self-signed certificate
docker-compose exec app openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/nginx/ssl/key.pem \
  -out /etc/nginx/ssl/cert.pem \
  -subj "/C=PH/ST=Metro Manila/L=Manila/O=MHRPCI/CN=mhrhci.mhrpci.site"

# Set permissions
docker-compose exec app chmod 600 /etc/nginx/ssl/key.pem
docker-compose exec app chmod 644 /etc/nginx/ssl/cert.pem
```

**⚠️ Note:** Self-signed certificates will show security warnings in browsers. Only use for development.

## 🚀 Production Setup

### 1. Update SSL Certificate Paths

Edit `docker/nginx/default.conf` lines 70-71:

```nginx
ssl_certificate /etc/nginx/ssl/cert.pem;
ssl_certificate_key /etc/nginx/ssl/key.pem;
```

Replace with your actual certificate paths.

### 2. Enable Cloudflare Real IP (If using Cloudflare)

Uncomment lines 101-123 in `docker/nginx/default.conf`:

```nginx
set_real_ip_from 103.21.244.0/22;
set_real_ip_from 103.22.200.0/22;
# ... (all Cloudflare IP ranges)
real_ip_header CF-Connecting-IP;
```

### 3. Configure Laravel Trusted Proxies

Edit `app/Http/Middleware/TrustProxies.php`:

```php
protected $proxies = '*'; // For Cloudflare

protected $headers =
    Request::HEADER_X_FORWARDED_FOR |
    Request::HEADER_X_FORWARDED_HOST |
    Request::HEADER_X_FORWARDED_PORT |
    Request::HEADER_X_FORWARDED_PROTO |
    Request::HEADER_X_FORWARDED_AWS_ELB;
```

### 4. Update Environment Variables

Edit `.env`:

```env
APP_URL=https://mhrhci.mhrpci.site
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

### 5. Restart Services

```bash
# Reload NGINX configuration
docker-compose exec app nginx -t  # Test config
docker-compose exec app nginx -s reload  # Reload

# Or restart all services
docker-compose restart
```

## 🔐 Security Headers Explained

### HSTS (HTTP Strict Transport Security)
```nginx
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
```
- Forces browsers to only use HTTPS for 1 year
- Applies to all subdomains
- Eligible for HSTS preload list

### X-Frame-Options
```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
```
- Prevents clickjacking attacks
- Only allows iframe from same origin

### X-Content-Type-Options
```nginx
add_header X-Content-Type-Options "nosniff" always;
```
- Prevents MIME type sniffing

### X-XSS-Protection
```nginx
add_header X-XSS-Protection "1; mode=block" always;
```
- Enables browser XSS filter

### Referrer-Policy
```nginx
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```
- Controls referrer information sent

### Permissions-Policy
```nginx
add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;
```
- Restricts browser features

## 🧪 Testing

### 1. Test NGINX Configuration
```bash
docker-compose exec app nginx -t
```

### 2. Test HTTP to HTTPS Redirect
```bash
curl -I http://mhrhci.mhrpci.site
# Should return: HTTP/1.1 301 Moved Permanently
# Location: https://mhrhci.mhrpci.site/
```

### 3. Test HTTPS
```bash
curl -I https://mhrhci.mhrpci.site
# Should return: HTTP/2 200
```

### 4. Test SSL Security
Use online tools:
- [SSL Labs SSL Test](https://www.ssllabs.com/ssltest/)
- [Mozilla Observatory](https://observatory.mozilla.org/)
- [Security Headers](https://securityheaders.com/)

**Target Grade:** A or A+

## 🔄 Local Development

For local development (localhost), the HTTP server will work without redirecting to HTTPS.

To completely disable HTTPS redirect for local:

```nginx
# Comment out lines 14-17
# if ($redirect_to_https = 1) {
#     return 301 https://$host$request_uri;
# }
```

## 📊 Performance Optimization

### Enable HTTP/2
Already enabled on line 60:
```nginx
listen 443 ssl http2;
```

### Enable Gzip Compression
Add to `http` block:
```nginx
gzip on;
gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss;
```

### Session Cache
Already configured (line 81):
```nginx
ssl_session_cache shared:SSL:10m;
```

## 🐛 Troubleshooting

### 1. "SSL certificate error"
- Check certificate paths are correct
- Verify certificate files exist
- Check file permissions (key: 600, cert: 644)

### 2. "Too many redirects"
- Check Laravel `TrustProxies` middleware
- Verify Cloudflare SSL mode is "Full" or "Full (strict)"

### 3. "Connection refused on port 443"
- Ensure Docker exposes port 443: `docker-compose.yml`
  ```yaml
  ports:
    - "80:80"
    - "443:443"
  ```

### 4. "Mixed content warnings"
- Update all internal URLs to use HTTPS
- Check `APP_URL` in `.env`
- Use relative URLs or `asset()` helper

## 📚 Additional Resources

- [Mozilla SSL Configuration Generator](https://ssl-config.mozilla.org/)
- [Cloudflare SSL Modes](https://developers.cloudflare.com/ssl/origin-configuration/ssl-modes/)
- [Laravel HTTPS Configuration](https://laravel.com/docs/master/requests#configuring-trusted-proxies)
- [NGINX SSL Module Documentation](https://nginx.org/en/docs/http/ngx_http_ssl_module.html)

## 🎯 Summary Checklist

- [ ] SSL certificates generated/obtained
- [ ] Certificates uploaded to server
- [ ] NGINX config updated with certificate paths
- [ ] Cloudflare Real IP enabled (if applicable)
- [ ] Laravel TrustProxies configured
- [ ] Environment variables updated
- [ ] NGINX configuration tested
- [ ] Services restarted
- [ ] HTTP to HTTPS redirect working
- [ ] HTTPS serving correctly
- [ ] SSL security score tested (A or A+)
- [ ] Security headers verified
- [ ] Application functioning properly

---

**Last Updated:** 2025-10-06
**Version:** 1.0.0
