# lostandfound/backend/Dockerfile
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    build-base \
    g++ \
    libc-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libexif-dev \
    icu-dev \
    mariadb-dev \
    libzip-dev \
    linux-headers \
    wget

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        intl \
        gd \
        xml \
        ctype \
        opcache \
        sockets

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN addgroup -g 1000 www && \
    adduser -u 1000 -G www -s /bin/sh -D www

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Copy existing application directory permissions
COPY --chown=www:www . /var/www/html

# Change current user to www
USER www

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Generate application key
RUN php artisan key:generate --no-interaction

# Clear and cache config
RUN php artisan config:clear && \
    php artisan config:cache

# Clear and cache routes
RUN php artisan route:clear && \
    php artisan route:cache

# Clear and cache views
RUN php artisan view:clear && \
    php artisan view:cache

# Set proper permissions
RUN chmod -R 755 storage bootstrap/cache

# Switch back to root for final setup
USER root

# Create .env file if it doesn't exist
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Set proper permissions for Laravel
RUN chown -R www:www /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/storage && \
    chmod -R 775 /var/www/html/bootstrap/cache

# Switch back to www user
USER www

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
