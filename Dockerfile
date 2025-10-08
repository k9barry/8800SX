# Multi-container Docker image for Nginx + PHP-FPM
# Service name: viavi-web
FROM php:8.3-fpm

# Install system dependencies (without MariaDB)
RUN apt-get update && apt-get install -y \
    nginx \
    mariadb-client \
    supervisor \
    curl \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

# Configure PHP
COPY --from=php:8.3-fpm /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
RUN sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 128M/g' /usr/local/etc/php/php.ini \
    && sed -i 's/max_file_uploads = 20/max_file_uploads = 100/g' /usr/local/etc/php/php.ini \
    && sed -i 's/post_max_size = 8M/post_max_size = 128M/g' /usr/local/etc/php/php.ini \
    && sed -i 's/;max_execution_time = 30/max_execution_time = 300/g' /usr/local/etc/php/php.ini \
    && sed -i 's/;max_input_time = 60/max_input_time = 300/g' /usr/local/etc/php/php.ini \
    && sed -i 's/memory_limit = 128M/memory_limit = 256M/g' /usr/local/etc/php/php.ini \
    && echo "mysqli.default_socket = /run/mysqld/mysqld.sock" >> /usr/local/etc/php/php.ini \
    && echo "pdo_mysql.default_socket = /run/mysqld/mysqld.sock" >> /usr/local/etc/php/php.ini

# Configure PHP-FPM to listen on localhost
RUN sed -i 's/listen = 9000/listen = 127.0.0.1:9000/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;clear_env = no/clear_env = no/g' /usr/local/etc/php-fpm.d/www.conf

# Create necessary directories
RUN mkdir -p /var/www/html/uploads \
    && mkdir -p /run/nginx \
    && mkdir -p /var/log/supervisor \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Copy application files
COPY data/web /var/www/html/

# Copy Nginx configuration
COPY data/web/config/nginx.conf /etc/nginx/sites-available/default

# Create supervisord configuration
RUN mkdir -p /etc/supervisor/conf.d
COPY <<EOF /etc/supervisord.conf
[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:php-fpm]
command=/usr/local/sbin/php-fpm -F
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
priority=1

[program:nginx]
command=/usr/sbin/nginx -g 'daemon off;'
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
priority=2
EOF

# Create entrypoint script
COPY <<'EOF' /entrypoint.sh
#!/bin/sh
set -e

# Get database configuration from environment variables
DB_HOST="${DB_HOST:-viavi-db}"
DB_NAME="${DB_NAME:-viavi}"
DB_USER="${DB_USER:-viavi}"
DB_PASSWORD="${DB_PASSWORD:-ChangeMe}"

# Wait for database to be ready
echo "Waiting for database to be ready..."
for i in {60..0}; do
    if mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASSWORD}" -e "SELECT 1" > /dev/null 2>&1; then
        echo "Database is ready!"
        break
    fi
    echo "Database is unavailable - sleeping"
    sleep 1
done

if [ "$i" = 0 ]; then
    echo "Database failed to become ready"
    exit 1
fi

# Create password file for PHP application (backward compatibility)
echo -n "${DB_PASSWORD}" > /tmp/db_password.txt
chmod 644 /tmp/db_password.txt

# Set environment variables for PHP
export DB_HOST="${DB_HOST}"
export DB_NAME="${DB_NAME}"
export DB_USER="${DB_USER}"
export DB_PASSWORD="${DB_PASSWORD}"
export DB_PASSWORD_FILE=/tmp/db_password.txt

# Start supervisord to manage services
exec /usr/bin/supervisord -c /etc/supervisord.conf
EOF

RUN chmod +x /entrypoint.sh

# Update both config.php and connection.php to use environment variable for database host
RUN sed -i "s/\$db_server.*=.*'localhost';/\$db_server = getenv('DB_HOST') ?: 'viavi-db';/g" /var/www/html/app/config.php || true \
    && sed -i "s/\$db_server.*=.*'db';/\$db_server = getenv('DB_HOST') ?: 'viavi-db';/g" /var/www/html/app/config.php || true \
    && sed -i 's/\$host = "localhost";/\$host = getenv("DB_HOST") ?: "viavi-db";/g' /var/www/html/connection.php || true \
    && sed -i 's/\$host = "db";/\$host = getenv("DB_HOST") ?: "viavi-db";/g' /var/www/html/connection.php || true

# Expose HTTP port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Set working directory
WORKDIR /var/www/html

ENTRYPOINT ["/entrypoint.sh"]
