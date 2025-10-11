FROM php:8.3.2-fpm
# Install your extensions to connect to MySQL and add mysqli
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini && \
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 128M/g' /usr/local/etc/php/php.ini && \
    sed -i 's/max_file_uploads = 20/max_file_uploads = 1000/g' /usr/local/etc/php/php.ini && \
    sed -i 's/post_max_size = 8M/post_max_size = 128M/g' /usr/local/etc/php/php.ini && \
    docker-php-ext-install mysqli && \
    apt-get update && apt-get install -y --no-install-recommends unzip curl ca-certificates && \
    rm -rf /var/lib/apt/lists/* && \
    update-ca-certificates
WORKDIR /var/www/html/app
RUN curl -L -k https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.7.7.zip -o tcpdf.zip && \
    unzip tcpdf.zip && \
    mv TCPDF-6.7.7 tcpdf && \
    rm tcpdf.zip
