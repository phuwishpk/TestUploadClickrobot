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

# Install PHP extensions with WebP support
# Note: On Alpine, webp extension is separate from gd
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
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

# Fix storage permissions (in case COPY overwrote them)
RUN mkdir -p storage/app/private \
    storage/app/public \
    storage/app/temp \
    storage/app/uploads \
    && chown -R www-data:www-data storage/app/private storage/app/public storage/app/temp storage/app/uploads \
    && chmod -R 775 storage/app/private storage/app/public storage/app/temp storage/app/uploads \
    && ln -sfn /var/www/html/storage/app/uploads /var/www/html/public/uploads

# Fix PHP-FPM to run as www-data
RUN sed -i 's/^user = application/user = www-data/' /usr/local/etc/php-fpm.d/application.conf && \
    sed -i 's/^group = application/group = www-data/' /usr/local/etc/php-fpm.d/application.conf && \
    sed -i 's/^user = application/user = www-data/' /usr/local/etc/php-fpm.d/docker.conf && \
    sed -i 's/^group = application/group = www-data/' /usr/local/etc/php-fpm.d/docker.conf && \
    mv /usr/local/etc/php-fpm.d/docker.conf /usr/local/etc/php-fpm.d/zzz-docker.conf && \
    mkdir -p /opt/docker/bin/service.d/php-fpm.d/ && \
    echo '#!/bin/sh' > /opt/docker/bin/service.d/php-fpm.d/fix-config.sh && \
    echo 'sed -i "s/^user = application\$/user = www-data/" /usr/local/etc/php-fpm.d/application.conf' >> /opt/docker/bin/service.d/php-fpm.d/fix-config.sh && \
    echo 'sed -i "s/^group = application\$/group = www-data/" /usr/local/etc/php-fpm.d/application.conf' >> /opt/docker/bin/service.d/php-fpm.d/fix-config.sh && \
    chmod +x /opt/docker/bin/service.d/php-fpm.d/fix-config.sh

# Configure PHP
RUN echo "upload_max_filesize = 200M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 200M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/uploads.ini

# Configure Nginx
ENV WEB_DOCUMENT_ROOT=/var/www/html/public

# Expose port
EXPOSE 80

# Start services
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
