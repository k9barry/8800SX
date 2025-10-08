# Repository Structure - v3.0.0

This document describes the multi-container repository structure for v3.0.0.

## Overview

Version 3.0.0 features a multi-container Docker Compose architecture with separate services for the web application and database, along with Traefik integration for production deployments.

## Directory Tree

```
8800SX/
├── .github/
│   └── workflows/
│       ├── docker-build.yml          # CI/CD workflow for Docker builds
│       └── version-bump.yml          # Version management workflow
├── data/
│   ├── web/                          # PHP application files
│   │   ├── app/                      # Main application code
│   │   ├── config/                   # Nginx configuration
│   │   └── uploads/                  # Upload directory (runtime)
│   └── init-db.sql                   # Database initialization script
├── .dockerignore                     # Files to exclude from Docker build
├── .editorconfig                     # Editor configuration
├── .env.example                      # Environment variables template
├── .gitignore                        # Git ignore patterns
├── build.sh                          # Build script for local development
├── CHANGELOG.md                      # Version history and changes
├── docker-compose.yml                # Multi-container deployment with Traefik
├── Dockerfile                        # Web service (Nginx + PHP-FPM)
├── Dockerfile.unified                # Unified container (backward compatibility)
├── LICENSE                           # MIT License
├── README.md                         # Main documentation
├── REPOSITORY_STRUCTURE.md           # This file
├── SECURITY.md                       # Security policy
└── test.sh                           # Automated test script
```

## Key Files

### Docker & Deployment

| File | Purpose |
|------|---------|
| `Dockerfile` | Multi-container web service with Nginx and PHP-FPM |
| `Dockerfile.unified` | Unified container with Nginx, PHP-FPM, and MariaDB (backward compatibility) |
| `docker-compose.yml` | Multi-container deployment with Traefik integration |
| `.env.example` | Environment variables template (copy to `.env`) |
| `.dockerignore` | Optimizes Docker build by excluding unnecessary files |

### CI/CD

| File | Purpose |
|------|---------|
| `.github/workflows/docker-build.yml` | GitHub Actions workflow for building and publishing |

### Scripts

| File | Purpose |
|------|---------|
| `build.sh` | Local build script with options |
| `test.sh` | Automated test suite |

### Documentation

| File | Purpose |
|------|---------|
| `README.md` | Main project documentation and quick start |
| `CHANGELOG.md` | Version history and migration guides |
| `REPOSITORY_STRUCTURE.md` | Repository organization (this file) |
| `SECURITY.md` | Security policy and reporting |
| `LICENSE` | MIT License |

### Application

| Directory | Purpose |
|-----------|---------|
| `data/web/` | PHP application files |
| `data/web/app/` | Core application code |
| `data/web/config/` | Nginx configuration |
| `data/init-db.sql` | Database schema initialization |

## Configuration

### Environment Variables

Configuration is done via `.env` file:

```env
DB_ROOT_PASSWORD=RootChangeMe
DB_NAME=viavi
DB_USER=viavi
DB_PASSWORD=your_secure_password
```

### Docker Services

The multi-container setup includes:

- **viavi-web**: Nginx + PHP-FPM web application
- **viavi-db**: MariaDB database server
- **viavi**: Unified container (optional, profile-based)

### Docker Volumes

Persistent data is stored in volumes:

- `viavi_data` - MySQL database files (viavi-db)
- `viavi_uploads` - Uploaded test files (viavi-web)
- `viavi_unified_data` - MySQL database files (unified container)
- `viavi_unified_uploads` - Uploaded test files (unified container)

### Docker Networks

- `viavi-internal` - Internal communication between services
- `traefik` - External Traefik reverse proxy network

## Workflows

### GitHub Actions

**docker-build.yml** - Automated build and push workflow:

1. **Test Job** (runs on all pushes and PRs):
   - Build Docker image
   - Run automated test suite

2. **Build-and-Push Job** (runs on push to main and tags):
   - Build multi-platform image (amd64, arm64)
   - Push to GitHub Container Registry
   - Tag with semantic versioning

### Semantic Versioning

Tags follow semantic versioning starting from v3.0.0:

- `v3.0.0` → `ghcr.io/k9barry/8800sx:3.0.0`, `3.0`, `3`, `latest`
- `main` branch → `ghcr.io/k9barry/8800sx:main`
- Commit SHA → `ghcr.io/k9barry/8800sx:<sha>`

## Architecture Changes in v3.0.0

### Multi-Container Setup

Version 3.0.0 introduces a multi-container architecture:

- **Separate database service**: MariaDB runs in its own container (`viavi-db`)
- **Web service**: Nginx + PHP-FPM in a dedicated container (`viavi-web`)
- **Traefik integration**: Built-in labels for reverse proxy and SSL
- **Internal networking**: Services communicate via Docker network

### Backward Compatibility

The unified container remains available:

- Use Docker profile: `docker compose --profile unified up -d`
- Or use standalone: `docker run ghcr.io/k9barry/8800sx:latest`
- Preserved in `Dockerfile.unified`

### New Features

- Environment variable-based database configuration
- Health checks for all services
- Traefik labels for automatic HTTPS
- Separate volumes for each deployment type

## Getting Started

### Multi-Container Deployment

1. **Clone repository**:
   ```bash
   git clone https://github.com/k9barry/8800SX.git
   cd 8800SX
   ```

2. **Configure**:
   ```bash
   cp .env.example .env
   nano .env  # Set DB_PASSWORD
   ```

3. **Deploy**:
   ```bash
   docker compose up -d
   ```

4. **Access**: http://localhost:8080

For more details, see [README.md](README.md).
