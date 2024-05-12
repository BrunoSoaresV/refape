# Stage 1: Use the PHP image as base
FROM php:8.1 AS php_base

# Install system dependencies for PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    libcurl4 \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    libxml2-dev \
    libzip-dev \
    libonig-dev \
    zlib1g-dev \
    make \
    gcc \
    libmemcached-dev \
    libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Install GD extension for PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install other PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    mysqli \
    opcache \
    pdo \
    pdo_pgsql \
    pgsql \
    zip \
    mbstring \
    xml \
    ctype \
    json \
    tokenizer \
    bcmath

# Configure PHP uploads
RUN { \
    echo 'upload_max_filesize = 20000M'; \
    echo 'post_max_size = 20000M'; \
} > /usr/local/etc/php/conf.d/uploads.ini

# Stage 2: Final image
FROM php:8.1

# Copy PHP extensions from stage 1
COPY --from=php_base /usr/local /usr/local

# Set the working directory and add the PHP files
WORKDIR /refape
COPY . /refape

# Expose the port
EXPOSE 8080

# Command to run the PHP server
CMD ["php", "-S", "0.0.0.0:8080"]
