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

# Copy WordPress files into container and set ownership in a single step
COPY --chown=www-data:www-data wordpress .

# Fix permissions
RUN chmod -R 755 .

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
