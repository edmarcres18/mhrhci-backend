#!/bin/bash

set -e

echo "Waiting for MySQL to be ready..."
until nc -z -v -w30 mysql 3306
do
  echo "Waiting for database connection..."
  sleep 5
done

echo "MySQL is up - executing command"

echo "Waiting for Redis to be ready..."
until nc -z -v -w30 redis 6379
do
  echo "Waiting for Redis connection..."
  sleep 2
done

echo "Redis is up - executing command"

# Create storage directories if they don't exist
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/logs

# Set permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Cache configuration for better performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
