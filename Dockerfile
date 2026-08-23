# 1. Base Image: PHP 8.3 CLI Alpine (Sesuai requirement composer.json php: ^8.3)
FROM php:8.3-cli-alpine

# Set working directory
WORKDIR /app

# 2. Install system dependencies & build tools
RUN apk add --no-cache \
    curl \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    nodejs \
    npm

# 3. Configure and install PHP extensions needed by Laravel & Image manipulation
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        bcmath \
        xml \
        ctype \
        fileinfo \
        intl \
        zip \
        gd \
        opcache

# 4. Install Composer directly from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Copy project source files
COPY . /app

# 6. Install PHP dependencies via Composer (production mode)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 7. Install NPM dependencies & Build Frontend Assets (Vite)
RUN if [ -f package.json ]; then npm install && npm run build && rm -rf node_modules; fi

# 8. Set permissions for Laravel storage, bootstrap cache, and entrypoint
RUN chmod -R 775 /app/storage /app/bootstrap/cache \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod +x /app/entrypoint.sh

# 9. Expose port 8000
EXPOSE 8000

# 10. Startup entrypoint script
ENTRYPOINT ["/app/entrypoint.sh"]