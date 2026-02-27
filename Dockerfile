FROM php:8.4-fpm

# Install dependencies sistem (ditambah dukungan untuk WebP, JPEG, dan Freetype)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libfreetype6-dev

# Bersihkan cache apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Konfigurasi ekstensi GD agar mendukung WebP dan JPEG
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp

# Install ekstensi PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Dapatkan Composer versi terbaru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory ke /var/www
WORKDIR /var/www

# Copy seluruh file project
COPY . .

# Set permission agar web server bisa menulis ke folder storage dan cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache