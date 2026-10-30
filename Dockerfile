FROM php:8.5-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx curl git unzip libpq-dev libsqlite3-dev libpng-dev libjpeg62-turbo-dev \
    libwebp-dev libfreetype6-dev libzip-dev libonig-dev libxml2-dev libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
    bcmath curl exif gd mbstring pdo_pgsql pdo_sqlite pgsql xml zip \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'realpath_cache_size=4096K'; \
    echo 'realpath_cache_ttl=600'; \
    echo 'upload_max_filesize=8M'; \
    echo 'post_max_size=10M'; \
    echo 'memory_limit=256M'; \
    } > /usr/local/etc/php/conf.d/99-production-performance.ini

RUN { \
    echo '[www]'; \
    echo 'pm = dynamic'; \
    echo 'pm.max_children = 8'; \
    echo 'pm.start_servers = 2'; \
    echo 'pm.min_spare_servers = 1'; \
    echo 'pm.max_spare_servers = 4'; \
    echo 'pm.max_requests = 400'; \
    echo 'clear_env = no'; \
    echo 'catch_workers_output = yes'; \
    } > /usr/local/etc/php-fpm.d/zz-render.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN composer dump-autoload --optimize --classmap-authoritative \
    && npm run build \
    && rm -rf node_modules \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data \
    storage/framework/cache/locks storage/logs bootstrap/cache \
    && sed -i 's/\r$//' docker/start.sh \
    && chown -R www-data:www-data storage bootstrap/cache public \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x docker/start.sh

EXPOSE 8080
CMD ["./docker/start.sh"]
