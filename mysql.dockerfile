# Add PHP-FPM 8.2 base image
FROM php:8.2-fpm
# Install your extensions to connect to MySQL and add mysqli
RUN docker-php-ext-install mysqli