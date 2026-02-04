#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); echo 'OK'; } catch (Exception \$e) { exit(1); }" 2>/dev/null; do
    echo "MySQL is unavailable - sleeping"
    sleep 2
done
echo "MySQL is up!"

# Run migrations
echo "Running migrations..."
php artisan migrate:fresh --force

# Clear and cache config for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
