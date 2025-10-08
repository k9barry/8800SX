# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - 2025-10-08

### Changed - BREAKING CHANGES

- **Multi-container architecture**: Reverted to multi-container Docker Compose setup
- **Three services**: Separated into `viavi-web` (Nginx + PHP-FPM), `viavi-db` (MariaDB), and `viavi` (unified, optional)
- **Traefik integration**: Built-in Traefik labels on viavi-web service for host `viavi.example.com`
- **Environment-based configuration**: Expanded `.env` file with `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_ROOT_PASSWORD`
- **Database connection**: Updated PHP files to support environment variables (DB_HOST, DB_NAME, DB_USER, DB_PASSWORD)
- **Backward compatibility**: Unified container available via Docker profile for migration

### Added

- Separate Dockerfile for viavi-web service (Nginx + PHP-FPM without MariaDB)
- Internal Docker network `viavi-internal` for service communication
- Traefik external network support
- Health checks for all services
- Database initialization via docker-entrypoint-initdb.d
- Dockerfile.unified for backward compatibility with unified container approach

### Removed

- None in this release; added multi-container support alongside existing unified container

### Migration Guide

#### From Unified Container (v2.x)

If migrating from unified container deployment:

1. Backup your data:
   ```bash
   docker exec viavi mysqldump -u viavi -pChangeMe viavi > backup.sql
   ```

2. Stop old container:
   ```bash
   docker stop viavi
   docker rm viavi
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
   docker compose exec -i viavi-db mysql -u viavi -pChangeMe viavi < backup.sql
   ```

#### Using Unified Container (Backward Compatibility)

To continue using the unified container:

```bash
docker compose --profile unified up -d
```

## [2.0.0] - Previous Release

See Git history for changes in v2.0.0 and earlier.
