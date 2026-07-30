#!/bin/sh
set -e

cd /var/www/html

if [ ! -f vendor/autoload.php ] || { [ -f composer.lock ] && [ composer.lock -nt vendor/composer/installed.json ]; }; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if [ -f .env ] && ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

if [ ! -L public/storage ]; then
    php artisan storage:link --force 2>/dev/null || true
fi

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

exec docker-php-entrypoint "$@"
