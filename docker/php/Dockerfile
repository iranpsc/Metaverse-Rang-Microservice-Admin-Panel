# syntax=docker/dockerfile:1
# Production PHP image with Laravel app baked in.
# Build with context at the project root (see docker-compose.yml).
# Follows Docker multi-stage + BuildKit cache-mount best practices:
# https://docs.docker.com/build/building/best-practices/
# https://docs.docker.com/build/building/multi-stage/

# -----------------------------------------------------------------------------
# Composer dependencies (no app source — maximizes layer cache)
# -----------------------------------------------------------------------------
FROM composer:2 AS vendor

ARG GITHUB_TOKEN=

WORKDIR /app

COPY composer.json composer.lock ./

# Prefer a regional Packagist mirror and retry: GitHub dist URLs often 429 on production hosts.
# Cache mount keeps Composer downloads across rebuilds without bloating image layers.
RUN --mount=type=cache,target=/tmp/composer-cache \
    set -eux; \
    export COMPOSER_CACHE_DIR=/tmp/composer-cache; \
    export COMPOSER_PROCESS_TIMEOUT=600; \
    if [ -n "$GITHUB_TOKEN" ]; then \
        composer config -g github-oauth.github.com "$GITHUB_TOKEN"; \
    fi; \
    composer config -g repos.packagist composer "https://package-mirror.liara.ir/repository/composer/"; \
    composer config -g github-protocols https; \
    i=1; \
    until composer install \
        --no-dev \
        --no-interaction \
        --prefer-install=source \
        --optimize-autoloader \
        --no-scripts \
        --ignore-platform-reqs; do \
        if [ "$i" -ge 5 ]; then exit 1; fi; \
        echo "composer install failed (attempt $i/5), retrying..."; \
        i=$((i + 1)); \
        sleep $((i * 10)); \
    done

# -----------------------------------------------------------------------------
# Frontend assets (Vite)
# -----------------------------------------------------------------------------
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./

# --ignore-scripts: simple-line-icons postinstall needs `less`, which is not a
# runtime dependency. Dist CSS is already shipped; Vite only imports that file.
RUN --mount=type=cache,target=/root/.npm \
    npm ci --prefer-offline --no-audit --no-fund --ignore-scripts

# Full tree needed so laravel-vite-plugin resolves the Laravel root correctly.
COPY . .
RUN npm run build

# -----------------------------------------------------------------------------
# Runtime: PHP-FPM + application
# -----------------------------------------------------------------------------
FROM php:8.4-fpm-bookworm AS app

LABEL org.opencontainers.image.title="admin-hamgit" \
      org.opencontainers.image.description="Laravel admin panel (PHP-FPM)" \
      org.opencontainers.image.source="https://github.com/irpsc/admin-hamgit"

# Which PHP conf to bake in (production.ini for prod; local.ini for docker-compose.dev).
ARG PHP_INI_FILE=production.ini

# Runtime libs stay; *-dev and $PHPIZE_DEPS are purged after compile.
# Apt cache mounts speed rebuilds without bloating layers (Docker BuildKit).
# Packages sorted alphabetically per Docker best practices.
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    set -eux; \
    rm -f /etc/apt/apt.conf.d/docker-clean; \
    echo 'Binary::apt::APT::Keep-Downloaded-Packages "true";' > /etc/apt/apt.conf.d/keep-cache; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        libfreetype6 \
        libfreetype6-dev \
        libicu-dev \
        libicu72 \
        libjpeg62-turbo \
        libjpeg62-turbo-dev \
        libonig-dev \
        libonig5 \
        libpng-dev \
        libpng16-16 \
        libsqlite3-0 \
        libsqlite3-dev \
        libzip-dev \
        libzip4 \
        unzip \
        $PHPIZE_DEPS; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        ftp \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_sqlite \
        zip; \
    curl -fsSL -o /tmp/redis.tgz https://github.com/phpredis/phpredis/archive/refs/tags/6.2.0.tar.gz; \
    tar xzf /tmp/redis.tgz -C /tmp; \
    cd /tmp/phpredis-6.2.0; \
    phpize; \
    ./configure; \
    make -j"$(nproc)"; \
    make install; \
    docker-php-ext-enable redis; \
    rm -rf /tmp/redis.tgz /tmp/phpredis-6.2.0; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
        $PHPIZE_DEPS \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libsqlite3-dev \
        libzip-dev; \
    rm -rf /tmp/* /tmp/pear ~/.pearrc

COPY --link --from=vendor /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --link . .
COPY --link --from=vendor /app/vendor ./vendor
COPY --link --from=frontend /app/public/build ./public/build

COPY --link docker/php/${PHP_INI_FILE} /usr/local/etc/php/conf.d/99-app.ini
COPY --link --chmod=755 docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN set -eux; \
    mkdir -p \
        bootstrap/cache \
        database \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs; \
    touch database/database.sqlite; \
    composer dump-autoload --optimize --no-interaction; \
    cp .env.example .env; \
    php artisan package:discover --ansi; \
    rm -f .env; \
    chown -R www-data:www-data storage bootstrap/cache database; \
    chmod -R ug+rwx storage bootstrap/cache; \
    chmod ug+rw database/database.sqlite

# Bind mounts (Dokploy ../files or local paths) obscure these dirs at runtime;
# entrypoint recreates the required subdirs when mounts start empty.
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
