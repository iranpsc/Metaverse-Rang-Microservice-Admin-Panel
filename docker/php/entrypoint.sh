#!/bin/sh
set -e

cd /var/www/html

# Host bind-mounts (/opt/metarang/...) or named volumes may start empty — recreate dirs.
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    database

# Translation models use the sqlite connection at database/database.sqlite.
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

# Install Composer deps only when missing (dev bind-mount case).
# Skip timestamp-based reinstalls so startup is not blocked on Packagist.
if [ "${COMPOSER_SKIP_INSTALL:-0}" != "1" ] && [ ! -f vendor/autoload.php ] && [ -f composer.json ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Named volumes / host bind-mounts can retain stale package caches from --dev installs.
# Always rediscover so providers match the vendor tree in this container.
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php bootstrap/cache/routes*.php
php artisan package:discover --ansi --no-interaction

# Ensure APP_KEY is available. Prefer writing into a mounted .env; otherwise export
# for this process (image builds omit .env via .dockerignore).
if [ -z "${APP_KEY:-}" ]; then
    if [ -f .env ]; then
        if ! grep -q '^APP_KEY=base64:' .env; then
            php artisan key:generate --force
        fi
    else
        APP_KEY="$(php artisan key:generate --show)"
        export APP_KEY
    fi
fi

# config/filesystems.php links public/uploads → storage/app/public
if [ ! -L public/uploads ]; then
    php artisan storage:link --force 2>/dev/null || true
fi

chown -R www-data:www-data storage bootstrap/cache database 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true
chmod ug+rw database/database.sqlite 2>/dev/null || true

exec docker-php-entrypoint "$@"
