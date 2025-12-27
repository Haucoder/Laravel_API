# ============================================
# GIAI ĐOẠN 1: Dùng Node.js để build Vue (Thêm mới)
# ============================================
FROM node:18-alpine as node_builder

WORKDIR /app

# Copy file config để cài thư viện trước (tận dụng cache)
COPY package.json package-lock.json vite.config.js ./
RUN npm install

# Copy toàn bộ code vào để build
COPY . .

# Chạy lệnh build (Nó sẽ đẻ ra thư mục /app/public/build)
RUN npm run build


# ============================================
# GIAI ĐOẠN 2: Chạy Laravel (Code cũ của ông + chỉnh sửa)
# ============================================
FROM php:8.2

# Cài đặt các thư viện hệ thống
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

# Copy code Laravel vào container
COPY . .

# 🔥 QUAN TRỌNG: Copy thư mục 'build' từ Giai đoạn 1 sang Giai đoạn 2
# Đây chính là bước giúp code Vue mới được cập nhật
COPY --from=node_builder /app/public/build /var/www/public/build

# Cài thư viện Laravel
RUN composer install --no-dev --optimize-autoloader

# Phân quyền lại cho thư mục storage (để tránh lỗi permission denied)
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Mở cổng
EXPOSE 10000

# Lệnh chạy
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000
