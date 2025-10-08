# Repository Structure - v3.0.0

This document describes the simplified repository structure for v3.0.0.

## Directory Tree

```
8800SX/
├── .github/
│   └── workflows/
│       └── docker-build.yml          # CI/CD workflow for Docker builds
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
├── docker-compose.yml                # Traefik deployment example
├── Dockerfile                        # Unified container definition
├── LICENSE                           # MIT License
├── README.md                         # Main documentation
├── RELEASE.md                        # Release creation guide
├── SECURITY.md                       # Security policy
└── test.sh                           # Automated test script
```

## Key Files

### Docker & Deployment

| File | Purpose |
|------|---------|
| `Dockerfile` | Single unified container with Nginx, PHP-FPM, and MariaDB |
| `docker-compose.yml` | Example Traefik deployment configuration |
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
| `RELEASE.md` | Guide for creating releases |
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
DB_PASSWORD=your_secure_password
```

### Docker Volumes

Persistent data is stored in volumes:

- `viavi_data` - MySQL database files
- `viavi_uploads` - Uploaded test files
- `viavi_logs` - MySQL logs (optional)

## Workflows

### GitHub Actions

**docker-build.yml** - Automated build and push workflow:

1. **Test Job** (runs on all pushes and PRs):
   - Build Docker image
   - Test image components
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

## Changes from v2.x

### Removed

- Multi-container Docker setup
- `secrets/` directory
- PHPUnit test infrastructure
- Old documentation (6 files)
- Old workflows (3 files)
- Composer dependencies

### Simplified

- Single Dockerfile (was `Dockerfile.unified`)
- Single workflow (was 4 workflows)
- Configuration via `.env` (was secrets files)
- Documentation (was 10+ files, now 5 files)

### Added

- CHANGELOG.md
- RELEASE.md
- Enhanced README.md

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
