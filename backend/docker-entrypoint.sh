#!/bin/bash
set -e

# Create required directories if they don't exist
mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/www/html/resources/views

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Install composer dependencies if vendor folder doesn't exist
if [ ! -d "/var/www/html/vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader
fi

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    if [ -f ".env" ] && grep -q "^APP_KEY=$" .env; then
        echo "Generating application key..."
        php artisan key:generate --force
    fi
fi

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); echo 'OK'; } catch (Exception \$e) { exit(1); }" 2>/dev/null; do
    echo "MySQL is unavailable - sleeping"
    sleep 2
done
echo "MySQL is up!"

# Run migrations
echo "Running migrations..."
php artisan migrate:fresh --seed

# Create storage link if it doesn't exist
if [ ! -L "/var/www/html/public/storage" ]; then
    echo "Creating storage link..."
    php artisan storage:link || true
fi

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
