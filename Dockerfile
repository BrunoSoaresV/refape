# Stage 1: Use the PHP image as base
FROM php:8.1 AS php_base

# Install necessary PHP extensions and configure uploads
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        libcurl4 \
    && docker-php-ext-install -j$(nproc) mysqli opcache \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Configure uploads
RUN { \
    echo 'upload_max_filesize = 20000M'; \
    echo 'post_max_size = 20000M'; \
} > /usr/local/etc/php/conf.d/uploads.ini

# Stage 2: Install Python and necessary Python packages
FROM python:3.10-slim AS python_base

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libxml2 \
    libxml2-dev

# Upgrade pip and install Python packages
RUN pip install --upgrade pip \
    && pip install matplotlib==3.6.2 \
    && pip install mtcnn==0.1.1 \
    && pip install numpy==1.23.5 \
    && pip install opencv-python \
    && pip install tensorflow \
    && pip install opencv-python-headless==4.6.0.66 \
    && pip install Pillow==9.3.0 \
    && pip install tqdm==4.64.1

# Stage 3: Final image
FROM php:8.1

# Copy PHP extensions from stage 1
COPY --from=php_base /usr/local /usr/local

# Copy Python installations from stage 2
COPY --from=python_base /usr/local/bin/python /usr/local/bin/python
COPY --from=python_base /usr/local/lib/python3.10 /usr/local/lib/python3.10

# Set the working directory and add the PHP files
WORKDIR /refape
COPY . /refape

# Expose the port
EXPOSE 8080

# Command to run the PHP server
CMD ["php", "-S", "0.0.0.0:8080"]
