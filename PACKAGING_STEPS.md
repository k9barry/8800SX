# Steps to Package Docker-Compose Services into One Docker Image

This document outlines the steps taken to package the 8800SX application's docker-compose services (Nginx, PHP-FPM, MySQL) into a single Docker image that can be pulled and run in an existing docker-compose instance using Traefik.

## Overview

The unified Docker image combines three services into one container:
- **Nginx** - Web server (port 80)
- **PHP-FPM 8.3** - Application runtime
- **MariaDB** - MySQL-compatible database

The container is named **`viavi`** and is fully compatible with Traefik reverse proxy.

## Steps Completed

### 1. Created Unified Dockerfile

**File**: `Dockerfile.unified`

Key features:
- Based on `php:8.3-fpm` (Debian)
- Installs Nginx, MariaDB, and Supervisor
- Configures PHP for file uploads (128MB max)
- Uses Supervisord to manage all three services
- Includes health checks
- Sets up proper permissions and directories

### 2. Process Management with Supervisord

Supervisord configuration manages three programs:
1. **mariadb** (priority 1) - Database service
2. **php-fpm** (priority 2) - PHP processing
3. **nginx** (priority 3) - Web server

All services log to stdout/stderr for easy monitoring with `docker logs`.

### 3. Database Initialization Script

**Entrypoint**: `/entrypoint.sh`

The entrypoint script:
- Checks if database is already initialized
- Initializes MariaDB on first run
- Creates `viavi` database and user
- Imports schema from `init-db.sql`
- Starts supervisord to manage services

### 4. Network Configuration

Updated configurations:
- Nginx configured to connect to PHP-FPM on `127.0.0.1:9000` (localhost)
- PHP app configured to connect to MySQL on `localhost` (not `db` service)
- All communication happens within the single container

### 5. Traefik Integration

**File**: `docker-compose.traefik.yml`

Example configuration includes:
- Traefik labels for routing
- HTTPS/TLS support with Let's Encrypt
- HTTP to HTTPS redirect
- Service port configuration (80)
- Volume mappings for persistence
- Network configuration for Traefik

### 6. Build Automation

**File**: `build-unified.sh`

Features:
- Builds the unified image
- Supports various options (--build-only, --no-cache, --tag, --password, --port)
- Runs and configures the container
- Waits for health checks
- Provides useful information and commands

Usage:
```bash
./build-unified.sh --password MySecurePass --port 8080
```

### 7. Automated Testing

**File**: `test-unified.sh`

Test suite validates:
- Image builds successfully
- Container starts correctly
- All three services are running (nginx, php-fpm, mariadbd)
- Web server responds with HTTP 200
- PHP-FPM is functional
- Database is accessible
- Required files exist in container

### 8. CI/CD Pipeline

**File**: `.github/workflows/docker-unified-image.yml`

GitHub Actions workflow:
- Builds image on push to main
- Publishes to GitHub Container Registry (ghcr.io)
- Supports multi-platform (amd64, arm64)
- Tags appropriately (unified, latest, sha)
- Runs basic tests on PRs

### 9. Comprehensive Documentation

Created three documentation files:

#### UNIFIED_DEPLOYMENT.md (10KB+)
Complete deployment guide covering:
- Multiple deployment methods
- Configuration options
- Traefik integration
- Health checks
- Monitoring and logs
- Database management
- Troubleshooting
- Production best practices
- Security checklist
- Migration guide
- Comparison with multi-container setup

#### QUICKSTART_UNIFIED.md (6KB)
Quick reference guide with:
- Three deployment options
- Configuration tables
- Management commands
- Build instructions
- Troubleshooting
- Performance tips
- Security checklist

#### Updated README.md
- Added deployment options section
- Traefik integration examples
- Links to detailed guides
- Updated Docker configuration section

### 10. Build Optimization

**File**: `.dockerignore`

Excludes unnecessary files:
- Git repository
- Documentation (except needed files)
- Development files
- IDE configurations
- Test files
- CI/CD files (not needed in container)
- Secrets (handled via environment)

## How to Use

### Method 1: Pull Pre-built Image

```bash
docker pull ghcr.io/k9barry/8800sx:unified

docker run -d \
  --name viavi \
  -p 8080:80 \
  -e DB_PASSWORD=your_password \
  -v viavi_data:/var/lib/mysql \
  -v viavi_uploads:/var/www/html/uploads \
  ghcr.io/k9barry/8800sx:unified
```

### Method 2: Build Locally

```bash
# Clone repository
git clone https://github.com/k9barry/8800SX.git
cd 8800SX

# Build
docker build -f Dockerfile.unified -t viavi:latest .

# Or use build script
./build-unified.sh
```

### Method 3: Docker Compose with Traefik

**Setup:**
```bash
cp .env.example .env
nano .env  # Set your DB_PASSWORD
```

**docker-compose.yml:**
```yaml
version: "3.8"
services:
  viavi:
    image: ghcr.io/k9barry/8800sx:unified
    container_name: viavi
    restart: unless-stopped
    environment:
      - DB_PASSWORD=${DB_PASSWORD}
    volumes:
      - viavi_data:/var/lib/mysql
      - viavi_uploads:/var/www/html/uploads
    networks:
      - traefik
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.viavi.rule=Host(`viavi.example.com`)"
      - "traefik.http.routers.viavi.entrypoints=websecure"
      - "traefik.http.routers.viavi.tls=true"
      - "traefik.http.routers.viavi.tls.certresolver=letsencrypt"
      - "traefik.http.services.viavi.loadbalancer.server.port=80"

volumes:
  viavi_data:
  viavi_uploads:

networks:
  traefik:
    external: true
```

## Key Files

| File | Purpose | Size |
|------|---------|------|
| `Dockerfile.unified` | Multi-service container definition | 5.3KB |
| `docker-compose.traefik.yml` | Traefik deployment example | 2.2KB |
| `.env.example` | Environment variables template | 0.3KB |
| `UNIFIED_DEPLOYMENT.md` | Complete deployment guide | 10.4KB |
| `QUICKSTART_UNIFIED.md` | Quick reference | 6.0KB |
| `build-unified.sh` | Build automation script | 4.2KB |
| `test-unified.sh` | Automated test suite | 5.2KB |
| `.dockerignore` | Build optimization | 0.6KB |
| `.github/workflows/docker-unified-image.yml` | CI/CD pipeline | 2.2KB |

## Environment Variables

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `DB_PASSWORD` | MySQL database password | `ChangeMe` | Yes (production) |

**Setup**: Copy `.env.example` to `.env` and set your secure password.

## Volumes

| Volume | Purpose | Recommended |
|--------|---------|-------------|
| `/var/lib/mysql` | Database data persistence | ✅ Required |
| `/var/www/html/uploads` | Uploaded files storage | ✅ Required |
| `/var/log/mysql` | MySQL error logs | Optional |

## Ports

| Port | Service | Purpose |
|------|---------|---------|
| 80 | Nginx | HTTP web interface |

## Advantages of Unified Image

### ✅ Pros
- Single image to pull and deploy
- Simpler deployment process
- Perfect for Traefik integration
- Easier distribution and updates
- Lower resource overhead
- Faster startup time
- Ideal for single-server deployments

### ⚠️ Considerations
- Less flexible for scaling individual services
- All services restart together
- Larger image size (~600MB vs ~200MB for PHP-FPM alone)

## Testing

Run the automated test suite:

```bash
./test-unified.sh
```

Expected output:
- ✓ Image built successfully
- ✓ Container started
- ✓ All services running
- ✓ Web server responding (HTTP 200)
- ✓ PHP-FPM working
- ✓ Database accessible
- ✓ File structure verified

## Monitoring

Check container health:
```bash
# View logs
docker logs -f viavi

# Check services
docker exec viavi ps aux | grep -E "nginx|php-fpm|mariadbd"

# Test web interface
curl http://localhost:8080/
```

## Troubleshooting

### Container won't start
- Check port 80 is available
- Verify volume permissions
- Check logs: `docker logs viavi`

### Services not running
- Wait 60 seconds for initialization
- Check individual services: `docker exec viavi pgrep nginx`
- Review supervisord logs in `docker logs`

### Database connection issues
- Verify DB_PASSWORD is set correctly
- Check database initialization: `docker exec viavi ls /var/lib/mysql/`
- Test connection: `docker exec viavi mariadb -u root -e "SHOW DATABASES;"`

## Security Recommendations

1. **Always change the default password**
   ```bash
   -e DB_PASSWORD=use_a_strong_password_here
   ```

2. **Deploy behind HTTPS**
   - Use Traefik with Let's Encrypt
   - See `docker-compose.traefik.yml` example

3. **Regular updates**
   ```bash
   docker pull ghcr.io/k9barry/8800sx:unified
   docker stop viavi && docker rm viavi
   docker run -d --name viavi ... ghcr.io/k9barry/8800sx:unified
   ```

4. **Backups**
   ```bash
   # Database
   docker exec viavi mysqldump -u viavi -p${DB_PASSWORD} viavi > backup.sql
   
   # Files
   docker run --rm -v viavi_uploads:/data -v $(pwd):/backup alpine \
     tar czf /backup/uploads-backup.tar.gz -C /data .
   ```

5. **Monitoring**
   - Set up log aggregation
   - Monitor resource usage: `docker stats viavi`
   - Set up alerts for container health

## Support

For issues, questions, or contributions:
- **Documentation**: See [UNIFIED_DEPLOYMENT.md](UNIFIED_DEPLOYMENT.md)
- **Quick Start**: See [QUICKSTART_UNIFIED.md](QUICKSTART_UNIFIED.md)
- **GitHub Issues**: https://github.com/k9barry/8800SX/issues
- **Security**: See [SECURITY.md](SECURITY.md)

## Summary

The unified Docker image successfully packages all docker-compose services into a single container named `viavi` that:
- ✅ Combines Nginx + PHP-FPM + MariaDB
- ✅ Uses supervisord for process management
- ✅ Initializes database automatically
- ✅ Works seamlessly with Traefik
- ✅ Includes comprehensive documentation
- ✅ Has automated build and test pipelines
- ✅ Is production-ready with security best practices

The image is available at: `ghcr.io/k9barry/8800sx:unified`
