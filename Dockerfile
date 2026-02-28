FROM php:8.4-fpm

# Install dependencies sistem, termasuk dukungan WebP dan JPEG
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

# Bersihkan cache apt agar ukuran container lebih ringan
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Konfigurasi ekstensi GD agar mendukung WebP dan JPEG
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp

# Install ekstensi PHP yang dibutuhkan Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Dapatkan Composer versi terbaru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory ke /var/www
WORKDIR /var/www

# Copy seluruh file project ke dalam container
COPY . .

# Set hak akses aman untuk folder aplikasi
RUN chown -R www-data:www-data /var/www