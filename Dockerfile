FROM php:8.1
RUN apt-get update
RUN docker-php-ext-install -j$(nproc) mysqli opcache
RUN apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql
RUN apt-get install -y python3.10
RUN apt-get install -y pip
RUN pip install --upgrade pip
RUN pip install matplotlib==3.6.2
RUN pip install mtcnn==0.1.1
RUN pip install numpy==1.23.5
RUN pip install opencv-python
RUN pip install tensorflow --user
RUN pip install opencv-python-headless==4.6.0.66
RUN pip install Pillow==9.3.0
RUN pip install tqdm==4.64.1
WORKDIR /refape
ADD . /refape
EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080"]
