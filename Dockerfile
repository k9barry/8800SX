# Add PHP-FPM 8.2 base image
FROM php:8.2-fpm
# Install your extensions to connect to MySQL and add mysqli
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini && \
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 128M/g' /usr/local/etc/php/php.ini && \
    sed -i 's/max_file_uploads = 20/max_file_uploads = 1000/g' /usr/local/etc/php/php.ini && \
    sed -i 's/post_max_size = 8M/post_max_size = 128M/g' /usr/local/etc/php/php.ini && \
    docker-php-ext-install mysqli
