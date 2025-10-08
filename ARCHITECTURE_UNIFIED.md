# Unified Container Architecture

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                      Traefik (Optional)                      │
│                    Reverse Proxy / Load Balancer             │
│                    HTTPS / Let's Encrypt                     │
└──────────────────────────┬──────────────────────────────────┘
                           │ HTTP/HTTPS
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                    Docker Container: viavi                   │
│                                                               │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              Supervisord (Process Manager)           │   │
│  └───────────┬────────────┬───────────┬─────────────────┘   │
│              │            │           │                      │
│              ▼            ▼           ▼                      │
│  ┌───────────────┐  ┌──────────┐  ┌──────────────┐        │
│  │    Nginx      │  │ PHP-FPM  │  │   MariaDB    │        │
│  │  Web Server   │  │   8.3    │  │   Database   │        │
│  │  Port: 80     │  │localhost │  │  localhost   │        │
│  └───────┬───────┘  └────┬─────┘  └──────┬───────┘        │
│          │               │                │                 │
│          │  FastCGI      │                │                 │
│          └───────────────┤                │                 │
│                          │   MySQL        │                 │
│                          └────────────────┘                 │
│                                                               │
│  Volumes:                                                    │
│  • /var/lib/mysql        ─── Database persistence           │
│  • /var/www/html/uploads ─── File uploads                   │
└─────────────────────────────────────────────────────────────┘
```

## Process Flow

### 1. Startup Sequence

```
Container Start
      │
      ▼
/entrypoint.sh
      │
      ├─► Check if DB initialized
      │   │
      │   └─► If NO:
      │       ├─► Initialize MariaDB
      │       ├─► Start temporary MySQL
      │       ├─► Create 'viavi' database
      │       ├─► Create 'viavi' user
      │       ├─► Import schema (init-db.sql)
      │       └─► Stop temporary MySQL
      │
      ├─► Create password file
      │
      └─► Start Supervisord
              │
              ├─► Start MariaDB (priority 1)
              ├─► Start PHP-FPM (priority 2)
              └─► Start Nginx (priority 3)
```

### 2. Request Flow

```
User Browser
      │
      ▼
HTTP Request :8080
      │
      ▼
┌──────────────┐
│   Traefik    │ (Optional)
│   Routing    │
└──────┬───────┘
       │
       ▼
Container Port 80
       │
       ▼
┌─────────────────┐
│  Nginx (Port 80)│
└────────┬────────┘
         │
         ├─► Static files (.css, .js)
         │   └─► Return directly
         │
         └─► PHP files (.php)
             │
             ▼
      ┌──────────────┐
      │   PHP-FPM    │
      │ 127.0.0.1:9000│
      └──────┬───────┘
             │
             ├─► Execute PHP
             │
             └─► Database query?
                 │
                 ▼
          ┌─────────────┐
          │   MariaDB   │
          │  localhost  │
          └─────┬───────┘
                │
                ▼
          Query Result
                │
                ▼
          Process in PHP
                │
                ▼
          HTML Response
                │
                ▼
          Back to Nginx
                │
                ▼
          To User Browser
```

## Service Communication

### Internal Network (within container)

```
┌────────────┐
│   Nginx    │ ──FastCGI──► PHP-FPM (127.0.0.1:9000)
└────────────┘
                     │
                     │
                     ▼
              ┌─────────────┐
              │   MariaDB   │ (localhost:3306)
              └─────────────┘
```

All services communicate via localhost (127.0.0.1), making the container self-contained.

## File System Structure

```
/
├── var/
│   ├── www/html/              # Web application
│   │   ├── upload.php
│   │   ├── result.php
│   │   ├── app/
│   │   │   ├── config.php
│   │   │   └── ...
│   │   └── uploads/           # Volume mount
│   │
│   ├── lib/mysql/             # Volume mount (database)
│   │   ├── mysql/
│   │   ├── viavi/
│   │   └── ...
│   │
│   └── log/
│       ├── nginx/
│       └── mysql/
│
├── etc/
│   ├── nginx/
│   │   └── sites-available/
│   │       └── default        # Nginx config
│   ├── supervisor/
│   │   └── supervisord.conf   # Process manager config
│   └── php/
│       └── php-fpm.d/
│           └── www.conf       # PHP-FPM config
│
├── docker-entrypoint-initdb.d/
│   └── init-db.sql            # Database schema
│
├── tmp/
│   └── db_password.txt        # Password for PHP app
│
└── entrypoint.sh              # Initialization script
```

## Multi-Container vs Unified Architecture

### Traditional Multi-Container (docker-compose.yml)

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Container  │────▶│  Container   │────▶│  Container   │
│    Nginx     │     │   PHP-FPM    │     │    MySQL     │
│              │     │              │     │              │
│   Port 80    │     │  Port 9000   │     │  Port 3306   │
└──────────────┘     └──────────────┘     └──────────────┘
       │                     │                     │
       └─────────────────────┴─────────────────────┘
                     Docker Network
```

**Pros:**
- Independent scaling
- Individual service management
- Easier debugging

**Cons:**
- More complex orchestration
- Higher resource overhead
- More containers to manage

### Unified Single Container (Dockerfile.unified)

```
┌────────────────────────────────────────┐
│          Single Container              │
│                                        │
│  ┌────────┐  ┌─────────┐  ┌────────┐ │
│  │ Nginx  │  │PHP-FPM  │  │MariaDB │ │
│  └───┬────┘  └────┬────┘  └────┬───┘ │
│      └────────────┴─────────────┘     │
│           Localhost Network           │
│                                        │
│         Port 80 ────────────►         │
└────────────────────────────────────────┘
```

**Pros:**
- Simple deployment
- Lower overhead
- Single image to manage
- Perfect for Traefik
- Faster startup

**Cons:**
- Less flexible scaling
- Services restart together

## Deployment Scenarios

### Scenario 1: Standalone Server

```
┌─────────────────────────────────────┐
│        Server (Docker Host)         │
│                                     │
│  ┌──────────────────────────────┐  │
│  │   Container: viavi           │  │
│  │   Port: 8080:80              │  │
│  └──────────────────────────────┘  │
│                                     │
└─────────────────┬───────────────────┘
                  │
                  ▼
           User Access via
        http://server:8080
```

### Scenario 2: Traefik Integration

```
┌─────────────────────────────────────────────────────┐
│              Server (Docker Host)                    │
│                                                      │
│  ┌─────────────┐          ┌──────────────────────┐ │
│  │   Traefik   │◄────────►│  Container: viavi    │ │
│  │  Port: 443  │   Docker │  Port: 80 (internal) │ │
│  └──────┬──────┘   Network└──────────────────────┘ │
│         │                                           │
└─────────┼───────────────────────────────────────────┘
          │
          ▼
    HTTPS (Let's Encrypt)
          │
          ▼
   viavi.example.com
          │
          ▼
      User Access
```

### Scenario 3: Docker Swarm / Kubernetes

```
┌─────────────────────────────────────────────┐
│          Orchestration Layer                │
│                                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐ │
│  │  viavi   │  │  viavi   │  │  viavi   │ │
│  │ replica1 │  │ replica2 │  │ replica3 │ │
│  └──────────┘  └──────────┘  └──────────┘ │
│                                             │
│  Note: Use persistent volumes for data     │
└─────────────────────────────────────────────┘
```

## Resource Usage

### Typical Resource Consumption

```
┌─────────────────────────────────────────┐
│          Container: viavi               │
│                                         │
│  Memory Usage:                          │
│  ├─ MariaDB:     ~200-400 MB           │
│  ├─ PHP-FPM:     ~50-100 MB            │
│  ├─ Nginx:       ~10-20 MB             │
│  └─ Total:       ~300-600 MB           │
│                                         │
│  CPU Usage:                             │
│  ├─ Idle:        <5%                    │
│  └─ Active:      10-50% (variable)      │
│                                         │
│  Disk Space:                            │
│  ├─ Image:       ~600 MB                │
│  ├─ Database:    ~500 MB+ (grows)       │
│  └─ Uploads:     Variable               │
└─────────────────────────────────────────┘
```

## Health Monitoring

### Health Check Flow

```
Docker Engine
      │
      ▼
Every 30 seconds
      │
      ▼
Execute: curl -f http://localhost/
      │
      ├─► Success (HTTP 200)
      │   └─► Container: healthy
      │
      └─► Failure
          ├─► Retry (up to 3 times)
          └─► After 3 failures: unhealthy
```

### Service Status Monitoring

```
supervisorctl status
      │
      ▼
┌─────────────────────────────────────┐
│ mariadb    RUNNING   pid 8          │
│ php-fpm    RUNNING   pid 9          │
│ nginx      RUNNING   pid 10         │
└─────────────────────────────────────┘
```

## Data Persistence

### Volume Strategy

```
Container Lifecycle
┌────────────────────────────────────┐
│  Container: viavi (ephemeral)      │
│                                    │
│  ┌──────────────────────────────┐ │
│  │  /var/lib/mysql              │─┼─► Volume: viavi_data
│  └──────────────────────────────┘ │   (persistent)
│                                    │
│  ┌──────────────────────────────┐ │
│  │  /var/www/html/uploads       │─┼─► Volume: viavi_uploads
│  └──────────────────────────────┘ │   (persistent)
└────────────────────────────────────┘

Even if container is removed, volumes persist!
```

## Build Process

```
Dockerfile.unified
      │
      ▼
Base Image: php:8.3-fpm
      │
      ├─► Install: nginx, mariadb, supervisor
      ├─► Configure: PHP settings
      ├─► Configure: PHP-FPM (localhost:9000)
      ├─► Create: directories
      ├─► Copy: application files
      ├─► Copy: nginx config
      ├─► Configure: nginx (localhost PHP-FPM)
      ├─► Create: supervisord.conf
      ├─► Create: entrypoint.sh
      └─► Set: entrypoint
              │
              ▼
         Docker Image
              │
              ▼
      Push to Registry
   (ghcr.io/k9barry/8800sx:unified)
```

## Security Layers

```
┌─────────────────────────────────────────┐
│         Security Measures               │
│                                         │
│  Network Layer:                         │
│  └─► Internal communication only        │
│                                         │
│  Application Layer:                     │
│  ├─► Input validation                   │
│  ├─► SQL injection prevention           │
│  ├─► File upload validation             │
│  └─► XSS protection                     │
│                                         │
│  Container Layer:                       │
│  ├─► Non-root user (www-data, mysql)   │
│  ├─► Read-only mounts where possible    │
│  └─► Health checks                      │
│                                         │
│  Infrastructure Layer (Traefik):        │
│  ├─► HTTPS/TLS                          │
│  ├─► Let's Encrypt certificates         │
│  ├─► Rate limiting (optional)           │
│  └─► Basic auth (optional)              │
└─────────────────────────────────────────┘
```

## Summary

The unified container architecture provides a complete, self-contained deployment solution that:

1. ✅ Combines all services into one manageable container
2. ✅ Uses standard Linux process management (supervisord)
3. ✅ Maintains data persistence through volumes
4. ✅ Integrates seamlessly with Traefik
5. ✅ Provides built-in health monitoring
6. ✅ Follows security best practices
7. ✅ Is production-ready and tested

**Container Name**: `viavi`  
**Image**: `ghcr.io/k9barry/8800sx:unified`  
**Port**: 80 (HTTP)  
**Status**: Production Ready ✅
