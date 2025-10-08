# Unified Docker image combining Nginx, PHP-FPM, and MySQL
# Service name: viavi
FROM php:8.3-fpm

# Install system dependencies and MySQL
RUN apt-get update && apt-get install -y \
    nginx \
    mariadb-server \
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
    && sed -i 's/memory_limit = 128M/memory_limit = 256M/g' /usr/local/etc/php/php.ini

# Configure PHP-FPM to listen on localhost
RUN sed -i 's/listen = 9000/listen = 127.0.0.1:9000/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;clear_env = no/clear_env = no/g' /usr/local/etc/php-fpm.d/www.conf

# Create necessary directories
RUN mkdir -p /var/www/html/uploads \
    && mkdir -p /run/nginx \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /var/lib/mysql \
    && mkdir -p /run/mysqld \
    && mkdir -p /var/log/mysql \
    && chown -R www-data:www-data /var/www/html \
    && chown -R mysql:mysql /var/lib/mysql \
    && chown -R mysql:mysql /run/mysqld \
    && chown -R mysql:mysql /var/log/mysql \
    && chmod -R 755 /var/www/html

# Copy application files
COPY data/web /var/www/html/
COPY data/init-db.sql /docker-entrypoint-initdb.d/init-db.sql

# Copy Nginx configuration (updated for localhost)
COPY data/web/config/nginx.conf /etc/nginx/sites-available/default

# Create supervisord configuration
RUN mkdir -p /etc/supervisor/conf.d
COPY <<EOF /etc/supervisord.conf
[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:mariadb]
command=/usr/sbin/mariadbd --user=mysql --datadir=/var/lib/mysql --log-error=/var/log/mysql/error.log
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
priority=1

[program:php-fpm]
command=/usr/local/sbin/php-fpm -F
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
priority=2

[program:nginx]
command=/usr/sbin/nginx -g 'daemon off;'
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
priority=3
EOF

# Create entrypoint script
COPY <<'EOF' /entrypoint.sh
#!/bin/sh
set -e

# Set database password from environment or use default
DB_PASSWORD="${DB_PASSWORD:-ChangeMe}"

# Initialize MySQL if not already initialized
if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "Initializing MySQL database..."
    mariadb-install-db --user=mysql --datadir=/var/lib/mysql > /dev/null
    
    # Start MySQL temporarily to create database and user
    /usr/sbin/mariadbd --user=mysql --datadir=/var/lib/mysql --skip-networking &
    MYSQL_PID=$!
    
    # Wait for MySQL to be ready
    echo "Waiting for MySQL to start..."
    for i in {30..0}; do
        if mysqladmin ping -h localhost --silent; then
            break
        fi
        echo "MySQL is unavailable - sleeping"
        sleep 1
    done
    
    if [ "$i" = 0 ]; then
        echo "MySQL failed to start"
        exit 1
    fi
    
    echo "MySQL started successfully"
    
    # Create database and user
    mysql -u root <<-EOSQL
        CREATE DATABASE IF NOT EXISTS viavi;
        CREATE USER IF NOT EXISTS 'viavi'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
        GRANT ALL PRIVILEGES ON viavi.* TO 'viavi'@'localhost';
        FLUSH PRIVILEGES;
EOSQL
    
    # Import initial database schema
    if [ -f "/docker-entrypoint-initdb.d/init-db.sql" ]; then
        echo "Importing database schema..."
        mysql -u viavi -p"${DB_PASSWORD}" viavi < /docker-entrypoint-initdb.d/init-db.sql
    fi
    
    # Stop temporary MySQL
    kill $MYSQL_PID
    wait $MYSQL_PID
    echo "MySQL initialization complete"
fi

# Create password file for PHP application
echo -n "${DB_PASSWORD}" > /tmp/db_password.txt
chmod 644 /tmp/db_password.txt

# Set environment variable for PHP to find password file
export DB_PASSWORD_FILE=/tmp/db_password.txt

# Start supervisord to manage all services
exec /usr/bin/supervisord -c /etc/supervisord.conf
EOF

RUN chmod +x /entrypoint.sh

# Update config.php to use localhost for database
RUN sed -i "s/\$db_server.*=.*'db';/\$db_server = 'localhost';/g" /var/www/html/app/config.php

# Expose HTTP port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Set working directory
WORKDIR /var/www/html

ENTRYPOINT ["/entrypoint.sh"]
