# Use official PHP 7.2.34 Apache image
FROM php:7.2.34-apache

# Install system dependencies required for Composer and PDF generation
# Install only essential packages to keep image size small
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better Docker layer caching
COPY composer.json composer.lock* ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application source code
COPY src/ ./
COPY pdf/ ./pdf/

# Set proper permissions for Apache
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80
