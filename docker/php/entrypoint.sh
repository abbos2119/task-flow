#!/bin/sh
set -e
cd /var/www/application
if [ -f "composer.json" ]; then
  if [ -f "composer.lock" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
  else
    composer update --no-interaction --prefer-dist --optimize-autoloader
  fi
  if [ ! -f ".env" ] && [ -f ".env.example" ]; then
    cp .env.example .env
    php artisan key:generate --no-interaction 2>/dev/null || true
  fi
  mkdir -p storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views
  chmod -R 775 storage bootstrap/cache
  chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
fi
exec "$@"
