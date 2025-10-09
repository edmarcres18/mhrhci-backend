# Docker Setup Guide

## Prerequisites
- Docker Desktop installed
- Docker Compose installed

## Quick Start

1. **Build and start the containers:**
   ```bash
   docker-compose up -d --build
   ```

2. **Access the application:**
   - Application: http://localhost:4001
   - PHPMyAdmin: http://localhost:4002
   - MySQL: localhost:3307
   - Redis: localhost:6380

## Services

The Docker setup includes the following services:

- **app**: Laravel application (Nginx + PHP-FPM + Queue Worker)
- **mysql**: MySQL 8.0 database
- **redis**: Redis 7 for caching, sessions, and queues
- **phpmyadmin**: Database management interface

### Scheduler (Supervisor)

The `scheduler` service runs Laravel's scheduler in daemon mode using Supervisor.

- Command: `php artisan schedule:work`
- Process manager: `supervisord`
- Healthcheck: verifies `laravel-scheduler` is RUNNING via `supervisorctl`

Verify scheduler status:

```bash
docker-compose ps scheduler
docker-compose logs -f scheduler
docker-compose exec scheduler supervisorctl -c /etc/supervisor/supervisord.conf status
```

## Environment Configuration

All services are configured via the `.env` file. Key variables:

```env
# External ports (host machine)
NGINX_PORT=4001
DB_EXTERNAL_PORT=3307
REDIS_EXTERNAL_PORT=6380
PHPMYADMIN_PORT=4002

# Internal ports (container network)
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=mhrweb_db
DB_USERNAME=mhrhci_admin
DB_PASSWORD=mhrhci-admin@2025

REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=mhrhci-admin@2025
```

## Common Commands

### Start containers
```bash
docker-compose up -d
```

### Stop containers
```bash
docker-compose down
```

### View logs
```bash
docker-compose logs -f
docker-compose logs -f app
```

### Execute artisan commands
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan cache:clear
```

### Access container shell
```bash
docker-compose exec app bash
```

### Rebuild containers
```bash
docker-compose down
docker-compose up -d --build
```

If only the scheduler changed, you can rebuild it specifically:

```bash
docker-compose build scheduler && docker-compose up -d scheduler
```

### Remove all data (including volumes)
```bash
docker-compose down -v
```

## Troubleshooting

### Database connection issues
Ensure MySQL is fully started:
```bash
docker-compose logs mysql
```

### Permission errors
Fix storage permissions:
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 755 /var/www/html/storage
```

### Cache issues
Clear all caches:
```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan route:clear
```

## Data Persistence

Data is persisted in Docker volumes:
- `mysql-data`: MySQL database files
- `redis-data`: Redis data files

To backup the database:
```bash
docker-compose exec mysql mysqldump -u mhrhci_admin -p mhrweb_db > backup.sql
```

## Production Deployment

For production, modify the following:

1. Update `.env`:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

2. Use production-ready MySQL password
3. Configure proper domain in `APP_URL`
4. Enable HTTPS with reverse proxy (nginx/traefik)
