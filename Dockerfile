# Multi-stage build for smaller final image
FROM php:7.2.34-apache AS builder

# Configure archived repositories and install minimal build dependencies
RUN echo "deb http://archive.debian.org/debian stretch main" > /etc/apt/sources.list && \
    echo "deb http://archive.debian.org/debian-security stretch/updates main" >> /etc/apt/sources.list && \
    echo "Acquire::Check-Valid-Until \"false\";" > /etc/apt/apt.conf.d/10no--check-valid-until && \
    apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock* ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-cache

# Final stage - minimal runtime image
FROM php:7.2.34-apache

# Copy only the vendor directory from builder
COPY --from=builder /app/vendor ./vendor

# Copy application source code
COPY src/ ./
COPY pdf/ ./pdf/

# Set proper permissions and clean up in single layer
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    rm -rf /tmp/* /var/tmp/*

# Expose port 80
EXPOSE 80
