#!/usr/bin/env bash
set -Eeuo pipefail

PORT="${PORT:-8080}"
RUNTIME_DB_URL="${DB_URL:-${DATABASE_URL:-}}"

# Render/Neon may provide DATABASE_URL. Laravel's pgsql connection reads DB_URL first.
if [[ -n "$RUNTIME_DB_URL" ]]; then
    export DB_URL="$RUNTIME_DB_URL"
    export DATABASE_URL="$RUNTIME_DB_URL"
fi

# Render exposes the web service port through PORT. Keep Nginx bound to that port.
cat > /etc/nginx/sites-available/default <<NGINX
server {
    listen ${PORT} default_server;
    listen [::]:${PORT} default_server;
    server_name _;
    root /var/www/html/public;
    index index.php;
    charset utf-8;
    client_max_body_size 10M;

    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 5;
    gzip_types text/plain text/css text/javascript application/javascript application/json image/svg+xml font/woff font/woff2;

    location ~* ^/(build|images)/.*\\.(css|js|jpg|jpeg|jfif|png|gif|webp|svg|woff|woff2|ttf|eot|ico)$ {
        expires 1y;
        add_header Cache-Control "public, max-age=31536000, immutable";
        access_log off;
        try_files \$uri =404;
    }

    location = /favicon.ico { access_log off; log_not_found off; expires 30d; }
    location = /robots.txt { access_log off; log_not_found off; }
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }

    location ~ \\.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_read_timeout 60s;
    }

    location ~ /\\.(?!well-known).* { deny all; }
}
NGINX

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/framework/cache/locks storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Run migrations with the direct Neon endpoint when the configured URL is pooled.
# Clear any old cached config first so this CLI process reads the temporary endpoint.
if [[ -n "$RUNTIME_DB_URL" ]]; then
    MIGRATION_DB_URL="${RUNTIME_DB_URL/-pooler./.}"
    export DB_URL="$MIGRATION_DB_URL"
    export DATABASE_URL="$MIGRATION_DB_URL"
fi

php artisan config:clear
php artisan migrate --force

# Restore the pooled URL for web requests, then create production caches.
if [[ -n "$RUNTIME_DB_URL" ]]; then
    export DB_URL="$RUNTIME_DB_URL"
    export DATABASE_URL="$RUNTIME_DB_URL"
fi
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Demo data is never seeded automatically in production. Run `php artisan db:seed`
# intentionally from a controlled local/maintenance session only when needed.
if [[ "${RUN_SEEDER:-false}" == "true" ]]; then
    if [[ "${APP_ENV:-production}" == "production" ]]; then
        echo "Refusing to run the demo seeder automatically in production." >&2
        exit 1
    fi
    php artisan db:seed --force
fi

php-fpm -D
exec nginx -g 'daemon off;'
