# ── Mugi Jaya Warehouse — Railway deployment image ───────────────────────────
FROM php:8.3-cli

# System libraries + PHP extensions Laravel/MySQL need
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libonig-dev curl ca-certificates \
    && docker-php-ext-install pdo_mysql mbstring bcmath zip \
    && rm -rf /var/lib/apt/lists/*

# Composer (from the official composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node 22 for the Vite asset build
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . .

# PHP deps (no dev) + build front-end assets into public/build
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm ci \
    && npm run build \
    && php artisan storage:link || true

# Railway provides $PORT at runtime
EXPOSE 8080
CMD ["sh", "-c", "php artisan migrate --force && php artisan config:cache && php artisan view:cache && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
