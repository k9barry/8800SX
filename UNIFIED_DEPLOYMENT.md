# Unified Docker Image Deployment Guide

This guide explains how to deploy the 8800SX Viavi Service Monitor Database as a single Docker container that combines Nginx, PHP-FPM, and MySQL (MariaDB) services.

## Overview

The unified image packages all three services (web server, application runtime, and database) into a single container named `viavi`. This simplifies deployment, especially when using Traefik as a reverse proxy.

### What's Inside

- **Nginx**: Web server serving on port 80
- **PHP-FPM 8.3**: Application runtime with mysqli extension
- **MariaDB**: MySQL-compatible database server
- **Supervisord**: Process manager to run all services

## Quick Start

### Method 1: Using Pre-built Image (Recommended)

```bash
# Pull the image from GitHub Container Registry
docker pull ghcr.io/k9barry/8800sx:unified

# Run the container
docker run -d \
  --name viavi \
  -p 8080:80 \
  -e DB_PASSWORD=your_secure_password \
  -v viavi_data:/var/lib/mysql \
  -v viavi_uploads:/var/www/html/uploads \
  ghcr.io/k9barry/8800sx:unified
```

### Method 2: Build Locally

```bash
# Clone the repository
git clone https://github.com/k9barry/8800SX.git
cd 8800SX

# Build the unified image
docker build -f Dockerfile.unified -t viavi:latest .

# Run the container
docker run -d \
  --name viavi \
  -p 8080:80 \
  -e DB_PASSWORD=your_secure_password \
  -v viavi_data:/var/lib/mysql \
  -v viavi_uploads:/var/www/html/uploads \
  viavi:latest
```

### Access the Application

Once the container is running, access the application at:
- http://localhost:8080

## Deployment with Traefik

### Prerequisites

1. Traefik must be running in your Docker environment
2. Create the Traefik network (if not already created):
   ```bash
   docker network create traefik
   ```

### Using Docker Compose with Traefik

1. **Copy the example compose file:**
   ```bash
   cp docker-compose.traefik.yml docker-compose.yml
   ```

2. **Edit the configuration:**
   - Update the `Host` rule to match your domain: `Host(\`viavi.example.com\`)`
   - Set your database password in `.env` file or environment variable
   - Configure TLS certificate resolver if using Let's Encrypt

3. **Start the service:**
   ```bash
   docker-compose up -d
   ```

### Traefik Configuration Example

Here's a minimal `docker-compose.traefik.yml` configuration:

```yaml
version: "3.8"

services:
  viavi:
    image: ghcr.io/k9barry/8800sx:unified
    container_name: viavi
    restart: unless-stopped
    environment:
      - DB_PASSWORD=your_secure_password
    volumes:
      - viavi_data:/var/lib/mysql
      - viavi_uploads:/var/www/html/uploads
    networks:
      - traefik
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.viavi.rule=Host(`viavi.example.com`)"
      - "traefik.http.routers.viavi.entrypoints=websecure"
      - "traefik.http.routers.viavi.tls.certresolver=letsencrypt"
      - "traefik.http.services.viavi.loadbalancer.server.port=80"

volumes:
  viavi_data:
  viavi_uploads:

networks:
  traefik:
    external: true
```

## Configuration

### Environment Variables

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `DB_PASSWORD` | MySQL database password | `ChangeMe` | Yes (for production) |

**⚠️ Security Warning**: Always change the default password in production!

### Volumes

| Volume | Purpose | Recommended |
|--------|---------|-------------|
| `/var/lib/mysql` | Database data persistence | ✅ Required |
| `/var/www/html/uploads` | Uploaded files storage | ✅ Required |
| `/var/log/mysql` | MySQL error logs | Optional |

### Ports

| Port | Service | Purpose |
|------|---------|---------|
| 80 | Nginx | HTTP web interface |

## Health Checks

The unified image includes a health check that verifies:
- Nginx is responding on port 80
- Application is accessible

Health check configuration:
- **Interval**: 30 seconds
- **Timeout**: 10 seconds
- **Start Period**: 60 seconds (allows time for MySQL initialization)
- **Retries**: 3

Check container health:
```bash
docker ps
docker inspect viavi | grep -A 10 Health
```

## Monitoring and Logs

### View All Logs
```bash
docker logs viavi
```

### Follow Logs
```bash
docker logs -f viavi
```

### View Specific Service Logs
The unified image uses supervisord, which outputs logs for all services to stdout/stderr.

### Access Container Shell
```bash
docker exec -it viavi /bin/sh
```

### Check Service Status
```bash
docker exec viavi supervisorctl status
```

## Database Management

### Connect to MySQL
```bash
# From host
docker exec -it viavi mysql -u viavi -p viavi

# Or with password in command (less secure)
docker exec -it viavi mysql -u viavi -p${DB_PASSWORD} viavi
```

### Backup Database
```bash
# Export database
docker exec viavi mysqldump -u viavi -p${DB_PASSWORD} viavi > backup.sql

# Import database
docker exec -i viavi mysql -u viavi -p${DB_PASSWORD} viavi < backup.sql
```

## Troubleshooting

### Container Won't Start

**Check logs:**
```bash
docker logs viavi
```

**Common issues:**
- Database initialization taking too long (wait 60 seconds)
- Port 80 already in use (change port mapping)
- Volume permission issues

### Database Connection Errors

**Verify database is running:**
```bash
docker exec viavi supervisorctl status mariadb
```

**Check database password:**
```bash
docker exec viavi cat /tmp/db_password.txt
```

**Restart database service:**
```bash
docker exec viavi supervisorctl restart mariadb
```

### Application Not Accessible

**Check if services are running:**
```bash
docker exec viavi supervisorctl status
```

**Expected output:**
```
mariadb                          RUNNING   pid 123, uptime 0:05:00
php-fpm                          RUNNING   pid 124, uptime 0:05:00
nginx                            RUNNING   pid 125, uptime 0:05:00
```

**Test from inside container:**
```bash
docker exec viavi curl -I http://localhost/
```

### File Upload Errors

**Check uploads directory permissions:**
```bash
docker exec viavi ls -la /var/www/html/uploads
```

**Fix permissions if needed:**
```bash
docker exec viavi chown -R nginx:nginx /var/www/html/uploads
docker exec viavi chmod -R 755 /var/www/html/uploads
```

## Production Deployment Best Practices

### Security Checklist

- [x] Change default database password
- [ ] Deploy behind HTTPS (use Traefik with Let's Encrypt)
- [ ] Configure firewall rules
- [ ] Set up regular database backups
- [ ] Enable container logging to external system
- [ ] Monitor resource usage
- [ ] Keep image updated
- [ ] Consider adding authentication middleware in Traefik
- [ ] Restrict file upload types (configured in app)
- [ ] Implement rate limiting in Traefik

### Recommended Production Setup

```yaml
services:
  viavi:
    image: ghcr.io/k9barry/8800sx:unified
    container_name: viavi
    restart: unless-stopped
    environment:
      - DB_PASSWORD=${DB_PASSWORD}  # Use .env file or secrets
    volumes:
      - /path/to/persistent/storage/mysql:/var/lib/mysql
      - /path/to/persistent/storage/uploads:/var/www/html/uploads
    networks:
      - traefik
    labels:
      # HTTPS only with Let's Encrypt
      - "traefik.enable=true"
      - "traefik.http.routers.viavi.rule=Host(`viavi.yourdomain.com`)"
      - "traefik.http.routers.viavi.entrypoints=websecure"
      - "traefik.http.routers.viavi.tls=true"
      - "traefik.http.routers.viavi.tls.certresolver=letsencrypt"
      - "traefik.http.services.viavi.loadbalancer.server.port=80"
      
      # Add basic authentication
      - "traefik.http.middlewares.viavi-auth.basicauth.users=${BASIC_AUTH_USERS}"
      - "traefik.http.routers.viavi.middlewares=viavi-auth"
      
      # Add rate limiting
      - "traefik.http.middlewares.viavi-ratelimit.ratelimit.average=100"
      - "traefik.http.middlewares.viavi-ratelimit.ratelimit.burst=50"
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
        reservations:
          cpus: '0.5'
          memory: 512M
```

### Backup Strategy

**Automated daily backups:**
```bash
#!/bin/bash
# backup-viavi.sh
DATE=$(date +%Y%m%d_%H%M%S)
DB_PASSWORD="your_password"

# Backup database
docker exec viavi mysqldump -u viavi -p${DB_PASSWORD} viavi | gzip > /backups/viavi_db_${DATE}.sql.gz

# Backup uploads
docker run --rm -v viavi_uploads:/data -v /backups:/backup alpine tar czf /backup/viavi_uploads_${DATE}.tar.gz -C /data .

# Keep only last 30 days
find /backups -name "viavi_*" -mtime +30 -delete
```

Add to crontab:
```bash
0 2 * * * /path/to/backup-viavi.sh
```

## Comparison: Unified vs Multi-Container

### Unified Image (This Guide)

**Pros:**
- ✅ Single image to manage
- ✅ Simpler deployment
- ✅ Perfect for Traefik integration
- ✅ Easier to distribute
- ✅ Lower resource overhead
- ✅ Faster startup

**Cons:**
- ❌ Less flexible scaling
- ❌ All services restart together
- ❌ Harder to debug individual services

**Best for:**
- Single-server deployments
- Development environments
- Traefik-based infrastructure
- Simple production setups

### Multi-Container (Original)

**Pros:**
- ✅ Services can scale independently
- ✅ Can restart individual services
- ✅ Better separation of concerns
- ✅ Easier to upgrade individual components

**Cons:**
- ❌ More complex to manage
- ❌ Requires orchestration
- ❌ Higher resource usage

**Best for:**
- Kubernetes deployments
- High-availability setups
- Large-scale deployments

## Migration from Multi-Container Setup

If you're migrating from the original multi-container setup:

1. **Backup your data:**
   ```bash
   docker-compose exec db mysqldump -u viavi -p viavi > backup.sql
   ```

2. **Stop old containers:**
   ```bash
   docker-compose down
   ```

3. **Start unified container:**
   ```bash
   docker run -d \
     --name viavi \
     -p 8080:80 \
     -e DB_PASSWORD=your_password \
     -v viavi_data:/var/lib/mysql \
     -v viavi_uploads:/var/www/html/uploads \
     ghcr.io/k9barry/8800sx:unified
   ```

4. **Restore data if needed:**
   ```bash
   docker exec -i viavi mysql -u viavi -pyour_password viavi < backup.sql
   ```

## Support

For issues, questions, or contributions:
- GitHub Issues: https://github.com/k9barry/8800SX/issues
- Documentation: https://github.com/k9barry/8800SX
- Security Issues: See [SECURITY.md](SECURITY.md)

## License

This project is licensed under the same terms as the main 8800SX project.
