#!/usr/bin/env bash

echo "========================================"
echo "Starting Laravel deployment"
echo "========================================"

echo "Running Composer..."
composer install --no-dev --working-dir=/var/www/html --optimize-autoloader --no-interaction

echo "Clearing old Laravel caches..."
php /var/www/html/artisan optimize:clear

echo "Caching Laravel configuration..."
php /var/www/html/artisan config:cache

echo "Caching Laravel routes..."
php /var/www/html/artisan route:cache

echo "Running database migrations..."
php /var/www/html/artisan migrate --force

echo "========================================"
echo "Laravel deployment completed"
echo "========================================"