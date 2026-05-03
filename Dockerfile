FROM webdevops/php-nginx:8.2-alpine

# Install additional packages
RUN apk add --no-cache \
    bash \
    ffmpeg \
    imagemagick \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    exif \
    pcntl \
    zip \
    bcmath \
    gd \
    mbstring

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Create storage directories
RUN mkdir -p storage/app/public \
    storage/app/temp \
    storage/app/uploads \
    storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Configure PHP
RUN echo "upload_max_filesize = 200M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 200M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/uploads.ini

# Configure Nginx
ENV WEB_DOCUMENT_ROOT=/var/www/html/public

# Expose port
EXPOSE 80

# Start services
CMD ["supervisord", "-c", "/opt/docker/supervisord.conf"]
