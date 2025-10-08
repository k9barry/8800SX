# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - 2024-10-08

### Changed - BREAKING CHANGES

- **Complete rewrite**: Project now uses only unified Docker container approach
- **Simplified architecture**: Single Dockerfile instead of multi-container setup
- **Configuration via .env**: All configuration through environment variables
- **Removed multi-container setup**: Removed old `Dockerfile` for PHP-FPM only and multi-service `docker-compose.yml`
- **Renamed files**: `Dockerfile.unified` → `Dockerfile`, `docker-compose.traefik.yml` → `docker-compose.yml`
- **Unified workflow**: Single GitHub Actions workflow for build and semantic versioning
- **Removed old workflows**: Eliminated separate workflows for tests, code quality, and legacy Docker builds
- **Simplified scripts**: `build-unified.sh` → `build.sh`, `test-unified.sh` → `test.sh`

### Removed

- Multi-container Docker setup files (`docker-compose.yml` with separate services)
- Old `Dockerfile` for PHP-FPM container
- `secrets/` directory (replaced with environment variables)
- PHPUnit test infrastructure (`tests/`, `phpunit.xml`, `phpstan.neon`)
- Composer dependencies (`composer.json`, `composer.lock`)
- Old documentation files:
  - `PACKAGING_STEPS.md`
  - `QUICKSTART_UNIFIED.md`
  - `UNIFIED_DEPLOYMENT.md`
  - `ARCHITECTURE_UNIFIED.md`
  - `RELEASE_2.0.md`
  - `RELEASE_NOTES_2.0.md`
  - `create_release_2.0.sh`
- GitHub Actions workflows:
  - `docker-image.yml`
  - `tests.yml`
  - `code-quality.yml`

### Added

- New simplified `README.md` focused on unified container deployment
- `CHANGELOG.md` for tracking version history
- Enhanced GitHub Actions workflow (`docker-build.yml`) with:
  - Semantic versioning support
  - Multi-platform builds (amd64, arm64)
  - Automated testing
  - Push to GitHub Container Registry

### Migration Guide

If upgrading from v2.x:

1. Backup your data: `docker compose exec db mysqldump -u viavi -p viavi > backup.sql`
2. Stop old containers: `docker compose down`
3. Update to v3.0.0 image
4. Use environment variables instead of secrets file
5. Start new unified container with `DB_PASSWORD` environment variable
6. Restore data if needed

## [2.0.0] - Previous Release

See Git history for changes in v2.0.0 and earlier.
