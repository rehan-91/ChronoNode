# Stage 1: Build Frontend Assets
FROM node:20-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: Build Server Runtime Environment
FROM php:8.3-fpm-alpine

# Install system dependencies, Nginx, and PHP extensions
RUN apk add --no-cache nginx wget \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_mysql bcmath opcache \
    && apk del .build-deps

# Configure Nginx for Render's routing proxy
RUN echo 'server { \
    listen 10000; \
    server_name _; \
    root /var/www/html/public; \
    index index.php index.html; \
    \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    \
    location ~ \.php$ { \
        include fastcgi_params; \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name; \
    } \
}' > /etc/nginx/http.d/default.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy frontend assets from Stage 1
COPY --from=frontend-builder /app/public/build ./public/build

# Install PHP production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Set secure folder permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Expose the standard internal port contract utilized by Render's proxies
EXPOSE 10000

# Start Nginx in background and PHP-FPM in foreground
CMD ["sh", "-c", "nginx && php-fpm"]
