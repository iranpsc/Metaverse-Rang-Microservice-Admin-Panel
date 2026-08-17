# syntax=docker.arvancloud.ir/docker/dockerfile:1
# Production PHP image with Laravel app baked in.
# Build with context at the project root (see docker-compose.yml).

ARG DOCKER_REGISTRY=docker.arvancloud.ir

# -----------------------------------------------------------------------------
# Composer dependencies
# -----------------------------------------------------------------------------
FROM ${DOCKER_REGISTRY}/composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs

# -----------------------------------------------------------------------------
# Frontend assets (Vite)
# -----------------------------------------------------------------------------
FROM ${DOCKER_REGISTRY}/node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci --prefer-offline --no-audit --no-fund

# Full tree needed so laravel-vite-plugin resolves the Laravel root correctly.
COPY . .
RUN npm run build

# -----------------------------------------------------------------------------
# Runtime: PHP-FPM + application
# -----------------------------------------------------------------------------
FROM ${DOCKER_REGISTRY}/php:8.4-fpm-bookworm AS app

ARG DOCKER_REGISTRY=docker.arvancloud.ir

# Runtime libs stay; *-dev and $PHPIZE_DEPS are purged after compile.
# Apt cache mounts speed rebuilds without bloating layers (Docker BuildKit).
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    set -eux; \
    rm -f /etc/apt/apt.conf.d/docker-clean; \
    echo 'Binary::apt::APT::Keep-Downloaded-Packages "true";' > /etc/apt/apt.conf.d/keep-cache; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        unzip \
        libfreetype6 \
        libjpeg62-turbo \
        libpng16-16 \
        libzip4 \
        libicu72 \
        libonig5 \
        libsqlite3-0 \
        $PHPIZE_DEPS \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libsqlite3-dev; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        ftp \
        opcache; \
    docker-php-source extract; \
    mkdir -p /usr/src/php/ext/redis; \
    curl -fsSL https://github.com/phpredis/phpredis/archive/refs/tags/6.1.0.tar.gz \
        | tar xz -C /usr/src/php/ext/redis --strip-components=1; \
    docker-php-ext-install -j"$(nproc)" redis; \
    docker-php-source delete; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
        $PHPIZE_DEPS \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libsqlite3-dev; \
    rm -rf /tmp/* /tmp/pear ~/.pearrc

COPY --link --from=vendor /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --link . .
COPY --link --from=vendor /app/vendor ./vendor
COPY --link --from=frontend /app/public/build ./public/build

COPY --link docker/php/local.ini /usr/local/etc/php/conf.d/99-local.ini
COPY --link --chmod=755 docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN set -eux; \
    mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
        database; \
    touch database/database.sqlite; \
    composer dump-autoload --optimize --no-interaction; \
    cp .env.example .env; \
    php artisan package:discover --ansi; \
    rm -f .env; \
    chown -R www-data:www-data storage bootstrap/cache database; \
    chmod -R ug+rwx storage bootstrap/cache; \
    chmod ug+rw database/database.sqlite

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
