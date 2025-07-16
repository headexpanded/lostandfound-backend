# lostandfound/backend/Dockerfile
# Base image for PHP-FPM (Laravel requires PHP 8.2+, using 8.3 as requested)
FROM php:8.3-fpm-alpine

# Set working directory inside the container
WORKDIR /var/www/html

# Update apk repositories
RUN set -ex; \
    apk update

# Install system dependencies and PHP extensions required for compilation
# We use apk add --no-cache for Alpine Linux to keep image size small
# build-base: Provides essential compilation tools (gcc, make, etc.)
# g++: C++ compiler, sometimes needed for PHP extensions
# libc-dev: Standard C library development headers
# libxml2-dev: For 'xml' extension
# libpng-dev, libjpeg-turbo-dev, freetype-dev: For 'gd' extension
# oniguruma-dev: For 'mbstring' extension
# libexif-dev: For 'exif' extension
# icu-dev: For 'intl' extension
# mariadb-dev: For 'pdo_mysql' extension (Alpine uses MariaDB client libraries)
# libzip-dev: For 'zip' extension
# linux-headers: Provides kernel headers for extensions like 'sockets'
RUN set -ex; \
    apk add --no-cache \
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
    linux-headers

# Install PHP extensions required by Laravel and common applications
# Removed 'iconv' from this list as it's often built-in or causes conflicts when explicitly installed.
RUN set -ex; \
    docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath intl gd xml ctype opcache sockets

# Clean up build dependencies to reduce image size
# These packages are only needed for compilation, not for runtime
RUN set -ex; \
    apk del build-base g++ libc-dev mariadb-dev libzip-dev linux-headers; \
    rm -rf /var/cache/apk/*

# Install Composer globally
# Composer is a dependency manager for PHP
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Expose port 9000 for PHP-FPM (default port)
EXPOSE 9000

# Start PHP-FPM
# This is the default command for php-fpm-alpine images, so it's often implicit.
# Explicitly stating it ensures clarity.
CMD ["php-fpm"]
