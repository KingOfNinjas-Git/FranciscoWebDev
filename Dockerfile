# Use the official PHP image with extensions
FROM php:8.2-apache

# Enable Apache mod_rewrite for Laravel routes
RUN a2enmod rewrite

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libonig-dev libxml2-dev zip unzip git curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Copy existing application code to /var/www/html
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Copy the default Laravel .env if needed
RUN cp .env.example .env || true

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set Laravel permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose the port Apache runs on
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
