############################################################
# Stage 1: resolve WordPress core + third-party plugins/themes
# via Composer, pinned in composer.json/composer.lock. Bumping
# a version is then a one-line composer.json change instead of
# committing vendored plugin source to this repo.
############################################################
FROM composer:2 AS wp_vendor

WORKDIR /build

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

############################################################
# Stage 2: runtime image
############################################################
FROM php:8.3-fpm

############################################################
# Install system dependencies and PHP extensions required
# for WordPress with NGINX + MariaDB:
#
# - System packages:
#     • libzip-dev      → enables PHP ZIP archive support
#     • libpng-dev      → required for PNG image processing
#     • libjpeg-dev     → required for JPEG image processing
#     • libfreetype6-dev → used for font rendering in GD
#
# - PHP extensions:
#     • gd             → image manipulation
#     • mysqli         → MySQL improved driver
#     • pdo, pdo_mysql → database abstraction for MySQL
#     • zip            → handle ZIP archives
#     • opcache        → boosts performance via bytecode caching
#
# Finally, apt cache is cleaned up to minimize image size.
############################################################
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql gd zip opcache \
    && rm -rf /var/lib/apt/lists/*

# Set the working directory for subsequent instructions
WORKDIR /var/www/html

# WordPress core + WPackagist-managed plugins/themes (versions pinned in composer.json)
COPY --from=wp_vendor --chown=www-data:www-data /build/wordpress .

# Our own plugins (tracked as git submodules) and site config, layered on top
# so they're never clobbered by the composer-managed tree above.
COPY --chown=www-data:www-data wordpress/wp-content/plugins/Mpi* wp-content/plugins/
COPY --chown=www-data:www-data wordpress/wp-content/index.php wp-content/index.php
COPY --chown=www-data:www-data wordpress/wp-config.php wp-config.php

# Fix permissions: WordPress directory writable by www-data user
RUN chown -R www-data:www-data . && chmod -R 755 .

# Configure OPcache for better performance
RUN docker-php-ext-enable opcache && \
    echo 'opcache.enable=1' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini && \
    echo 'opcache.interned_strings_buffer=16' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini && \
    echo 'opcache.max_accelerated_files=10000' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini && \
    echo 'opcache.memory_consumption=256' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini && \
    echo 'opcache.save_comments=1' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini && \
    echo 'opcache.revalidate_freq=0' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini && \
    echo 'opcache.validate_timestamps=1' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Configure PHP for WordPress
RUN echo 'upload_max_filesize = 100M' >> /usr/local/etc/php/conf.d/wordpress.ini && \
    echo 'post_max_size = 100M' >> /usr/local/etc/php/conf.d/wordpress.ini && \
    echo 'max_execution_time = 300' >> /usr/local/etc/php/conf.d/wordpress.ini && \
    echo 'memory_limit = 256M' >> /usr/local/etc/php/conf.d/wordpress.ini

# Define the volume in the Dockerfile
# This tells Docker that /var/www/html should be a volume
VOLUME /var/www/html
