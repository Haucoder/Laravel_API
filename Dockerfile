# Sử dụng PHP 8.2
FROM php:8.2

# Cài đặt các thư viện hệ thống (Đã thêm ca-certificates)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client \
    ca-certificates

# Cài đặt Extensions PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Cập nhật chứng chỉ
RUN update-ca-certificates

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục
WORKDIR /var/www

# Copy code
COPY . .

# Cài thư viện Laravel
RUN composer install --no-dev --optimize-autoloader

# Mở cổng
EXPOSE 10000

# Lệnh chạy (Tạo bảng trước rồi mới chạy web)
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000
