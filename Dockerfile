FROM php:8.3.2-fpm
# Install your extensions to connect to MySQL and add mysqli
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini && \
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 128M/g' /usr/local/etc/php/php.ini && \
    sed -i 's/max_file_uploads = 20/max_file_uploads = 1000/g' /usr/local/etc/php/php.ini && \
    sed -i 's/post_max_size = 8M/post_max_size = 128M/g' /usr/local/etc/php/php.ini && \
    docker-php-ext-install mysqli && \
    apt-get update && apt-get install -y --no-install-recommends \
        unzip=6.0-28 \
        curl=7.88.1-10+deb12u14 \
        ca-certificates=20230311+deb12u1 && \
    rm -rf /var/lib/apt/lists/* && \
    update-ca-certificates
WORKDIR /var/www/html/app
# Download TCPDF to a persistent location outside the volume mount
RUN curl -L -k https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.7.7.zip -o /tmp/tcpdf.zip && \
    unzip /tmp/tcpdf.zip -d /usr/local/lib && \
    mv /usr/local/lib/TCPDF-6.7.7 /usr/local/lib/tcpdf && \
    rm /tmp/tcpdf.zip
