# Sử dụng PHP 8.2
FROM php:8.2

# Cài đặt các thư viện hệ thống cần thiết cho Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client

# Cài đặt các Extensions PHP (để kết nối Database, xử lý ảnh...)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www

# Copy toàn bộ code từ GitHub vào Server
COPY . .

# Chạy lệnh cài đặt thư viện PHP
RUN composer install --no-dev --optimize-autoloader

# Mở cổng 10000 (Cổng mặc định của Render)
EXPOSE 10000

# Chạy lệnh khởi động Web
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000
