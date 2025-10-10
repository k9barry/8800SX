# Repository Structure - v3.0.1

This document describes the multi-container repository structure for v3.0.1.

## Overview

Version 3.0.1 features a multi-container Docker Compose architecture with separate services for the web application and database, along with Traefik integration for production deployments.

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
├── viavi/                            # Persistent data (ignored by git)
│   ├── data/                         # MySQL database files
│   └── uploads/                      # Uploaded test files
├── .dockerignore                     # Files to exclude from Docker build
├── .editorconfig                     # Editor configuration
├── .env.example                      # Environment variables template
├── .gitignore                        # Git ignore patterns
├── CHANGELOG.md                      # Version history and changes
├── docker-compose.yml                # Multi-container deployment with Traefik
├── Dockerfile                        # Web service (Nginx + PHP-FPM)
├── LICENSE                           # MIT License
├── README.md                         # Main documentation
├── REPOSITORY_STRUCTURE.md           # This file
└── SECURITY.md                       # Security policy
```

## Key Files

### Docker & Deployment

| File | Purpose |
|------|---------|
| `Dockerfile` | Web service with Nginx and PHP-FPM |
| `docker-compose.yml` | Multi-container deployment with Traefik integration |
| `.env.example` | Environment variables template (copy to `.env`) |
| `.dockerignore` | Optimizes Docker build by excluding unnecessary files |

### CI/CD

| File | Purpose |
|------|---------|
| `.github/workflows/docker-build.yml` | GitHub Actions workflow for building and publishing |

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

### Persistent Data Directories

Persistent data is stored in local bind mounts:

- `./viavi/data` - MySQL database files (viavi-db)
- `./viavi/uploads` - Uploaded test files (viavi-web)

### Docker Networks

- `viavi-internal` - Internal communication between services
- `traefik` - External Traefik reverse proxy network

## Workflows

### GitHub Actions

**version-bump.yml** - Automated release management:

Triggers when a PR is merged to main:
- Detects version label on PR (`major`, `minor`, `patch`)
- Defaults to `patch` if no label is found
- Calculates next semantic version
- Creates and pushes git tag (e.g., `v3.0.1`)
- Creates GitHub Release with:
  - Version information
  - Docker image tags
  - PR details and changelog link

**docker-build.yml** - Automated build and push workflow:

1. **Test Job** (runs on all pushes and PRs):
   - Build Docker image
   - Run automated test suite

2. **Build-and-Push Job** (runs on push to main and tags):
   - Build multi-platform image (amd64, arm64)
   - Push to GitHub Container Registry
   - Tag with semantic versioning

### Semantic Versioning

Tags follow semantic versioning and are created automatically when PRs are merged:

- `v3.0.1` → `ghcr.io/k9barry/8800sx:3.0.1`, `latest`

**Version Labels:**
- Add `major` label to PR for breaking changes (e.g., 2.0.0 → 3.0.0)
- Add `minor` label to PR for new features (e.g., 3.0.0 → 3.1.0)
- Add `patch` label to PR for bug fixes (e.g., 3.0.0 → 3.0.1)
- No label defaults to `patch` bump

## Architecture

### Multi-Container Setup

The application uses a multi-container architecture:

- **Separate database service**: MariaDB runs in its own container (`viavi-db`)
- **Web service**: Nginx + PHP-FPM in a dedicated container (`viavi-web`)
- **Traefik integration**: Built-in labels for reverse proxy and SSL
- **Internal networking**: Services communicate via Docker network

### Features

- Environment variable-based database configuration
- Health checks for all services
- Traefik labels for automatic HTTPS
- Local bind mounts for persistent data and uploads

## Getting Started

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
