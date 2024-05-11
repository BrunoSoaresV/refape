# Stage 1: Use the PHP image as base
FROM php:8.1

# Install necessary PHP extensions and configure uploads
RUN touch /usr/local/etc/php/conf.d/uploads.ini \
    && echo "upload_max_filesize = 20000M;" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 20000M;" >> /usr/local/etc/php/conf.d/uploads.ini \
    && docker-php-ext-install -j$(nproc) mysqli opcache \
    && apt-get update \
    && apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Stage 2: Install Python and necessary Python packages
FROM python:3.10-slim

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    python3-pip \
    python3-setuptools \
    python3-wheel \
    libxml2 \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

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

# Copy PHP files from stage 1
COPY --from=0 /usr/local /usr/local

# Set the working directory and expose the port
WORKDIR /refape
ADD . /refape
EXPOSE 8080

# Command to run the PHP server
CMD ["php", "-S", "0.0.0.0:8080"]
