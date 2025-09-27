# Use PHP-FPM 8.3 LTS base image for security and stability  
FROM php:8.3-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    curl \
    && docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

# Copy and configure PHP settings
COPY --from=php:8.3-fpm /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

# Configure PHP for file uploads and security
RUN sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 128M/g' /usr/local/etc/php/php.ini \
    && sed -i 's/max_file_uploads = 20/max_file_uploads = 100/g' /usr/local/etc/php/php.ini \
    && sed -i 's/post_max_size = 8M/post_max_size = 128M/g' /usr/local/etc/php/php.ini \
    && sed -i 's/;max_execution_time = 30/max_execution_time = 300/g' /usr/local/etc/php/php.ini \
    && sed -i 's/;max_input_time = 60/max_input_time = 300/g' /usr/local/etc/php/php.ini \
    && sed -i 's/memory_limit = 128M/memory_limit = 256M/g' /usr/local/etc/php/php.ini

# Set working directory
WORKDIR /var/www/html

# Create uploads directory with proper permissions
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Switch to non-root user for security
USER www-data

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
    CMD php-fpm -t

EXPOSE 9000
