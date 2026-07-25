# syntax=docker/dockerfile:1
# Local PHP-FPM image. Build with context ./docker/php (see docker-compose.yml).

FROM php:8.2-fpm-bookworm

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
        $PHPIZE_DEPS \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
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
        libonig-dev; \
    rm -rf /tmp/* /tmp/pear ~/.pearrc

COPY --link --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --link local.ini /usr/local/etc/php/conf.d/99-local.ini
COPY --link --chmod=755 entrypoint.sh /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
