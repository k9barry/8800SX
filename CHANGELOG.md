# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- **init-db.sql location**: Moved init-db.sql from `./data/` to `./viavi/data/` directory to keep database initialization script alongside database files
- **docker-compose.yml**: Updated volume mount to use `./viavi/data/init-db.sql` instead of `./data/init-db.sql`
- **.gitignore**: Updated to track `viavi/data/init-db.sql` and `viavi/uploads/.gitkeep` while ignoring other files in these directories

### Added

- **viavi directory structure**: Added `viavi/data/` and `viavi/uploads/` directories to repository with appropriate .gitkeep files

## [3.0.1] - 2025-10-09

### Removed

- **Unified container**: Removed Dockerfile.unified and all unified container support
- **Unified container documentation**: Removed all references from README.md and REPOSITORY_STRUCTURE.md
- **Build scripts**: Removed build.sh and test.sh (unified container-specific)
- **CI/CD**: Removed unified container build job from GitHub Actions workflow

### Changed

- **docker-compose.yml**: Removed unified container service and volumes
- **CI/CD**: Updated test workflow to use multi-container setup
- **Documentation**: Simplified all documentation to focus on multi-container architecture only

### Fixed

- **Docker build issue**: Fixed viavi-web service not starting due to missing Dockerfile reference

## [3.0.0] - 2025-10-08

### Changed - BREAKING CHANGES

- **Multi-container architecture**: Multi-container Docker Compose setup
- **Two services**: Separated into `viavi-web` (Nginx + PHP-FPM) and `viavi-db` (MariaDB)
- **Traefik integration**: Built-in Traefik labels on viavi-web service for host `viavi.example.com`
- **Environment-based configuration**: Expanded `.env` file with `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_ROOT_PASSWORD`
- **Database connection**: Updated PHP files to support environment variables (DB_HOST, DB_NAME, DB_USER, DB_PASSWORD)

### Added

- Dockerfile for viavi-web service (Nginx + PHP-FPM without MariaDB)
- Internal Docker network `viavi-internal` for service communication
- Traefik external network support
- Health checks for all services
- Database initialization via docker-entrypoint-initdb.d

### Migration Guide

#### From Previous Versions

1. Backup your data:
   ```bash
   docker compose exec viavi-db mysqldump -u viavi -p viavi > backup.sql
   ```

2. Stop old containers:
   ```bash
   docker compose down
   ```

3. Update configuration:
   ```bash
   cp .env.example .env
   # Edit .env and set DB_PASSWORD, DB_ROOT_PASSWORD, etc.
   ```

4. Create Traefik network (if using Traefik):
   ```bash
   docker network create traefik
   ```

5. Start multi-container setup:
   ```bash
   docker compose up -d
   ```

6. Restore data if needed:
   ```bash
   docker compose exec -i viavi-db mysql -u viavi -p viavi < backup.sql
   ```

## [2.0.0] - Previous Release

See Git history for changes in v2.0.0 and earlier.
