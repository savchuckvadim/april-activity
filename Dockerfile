FROM php:8.2-fpm

# Copy composer.lock and composer.json into the working directory
# COPY composer.lock composer.json /var/www/html/

# Set working directory
WORKDIR /var/www/html/
COPY . /var/www/html/
COPY composer.lock composer.json /var/www/html/


# Install dependencies for the operating system software
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    libzip-dev \
    unzip \
    git \
    libonig-dev \
    curl \
    libicu-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    libreoffice \
    libreoffice-writer \
    fonts-dejavu \
    fonts-liberation \
    default-jre \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis


# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions for php
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Install composer (php package manager)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy existing application directory contents to the working directory
# COPY . .

# Устанавливаем зависимости через Composer
# RUN composer install --no-dev --optimize-autoloader
RUN composer install
RUN composer dump-autoload


RUN mkdir -p /var/www/.config/libreoffice
# Assign permissions of the working directory to the www-data user
RUN chown -R www-data:www-data /var/www/html/bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage
RUN chmod -R 775 /var/www/html/storage /var/www/.config/libreoffice

ENV HOME=/tmp

# Expose port 9000 and start php-fpm server (for FastCGI Process Manager)
EXPOSE 9000
CMD ["php-fpm"]

