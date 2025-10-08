# Creating Release v3.0.0

This document describes how to create the v3.0.0 release after merging this PR.

## Steps to Create Release

1. **Merge the PR** to main branch

2. **Create and push the tag**:
   ```bash
   git checkout main
   git pull origin main
   git tag -a v3.0.0 -m "Release v3.0.0 - Unified Docker workflow"
   git push origin v3.0.0
   ```

3. **GitHub Actions will automatically**:
   - Build the Docker image
   - Push to GitHub Container Registry with tags:
     - `ghcr.io/k9barry/8800sx:3.0.0`
     - `ghcr.io/k9barry/8800sx:3.0`
     - `ghcr.io/k9barry/8800sx:3`
     - `ghcr.io/k9barry/8800sx:latest`
     - `ghcr.io/k9barry/8800sx:main`

4. **Create GitHub Release**:
   - Go to https://github.com/k9barry/8800SX/releases/new
   - Select tag: `v3.0.0`
   - Release title: `v3.0.0 - Unified Docker Workflow`
   - Description: Copy from CHANGELOG.md
   - Click "Publish release"

## Release Notes Template

```markdown
# Release v3.0.0 - Unified Docker Workflow

## 🚀 Major Changes - BREAKING RELEASE

This is a complete rewrite of the project focusing on a simplified, unified Docker container approach.

### What's Changed

- **Single Docker container**: All services (Nginx, PHP-FPM, MariaDB) in one image
- **Simplified configuration**: Use `.env` file with `DB_PASSWORD` environment variable
- **Semantic versioning**: Starting from v3.0.0
- **Multi-platform support**: Both amd64 and arm64 architectures

### Breaking Changes

- Removed multi-container setup (separate Dockerfile and docker-compose.yml)
- Removed `secrets/` directory - use environment variables instead
- Changed image tags - use `latest` instead of `unified`
- Removed PHPUnit test infrastructure

### Migration from v2.x

See [CHANGELOG.md](CHANGELOG.md) for detailed migration guide.

### Quick Start

```bash
docker pull ghcr.io/k9barry/8800sx:3.0.0

docker run -d \
  --name viavi \
  -p 8080:80 \
  -e DB_PASSWORD=your_secure_password \
  -v viavi_data:/var/lib/mysql \
  -v viavi_uploads:/var/www/html/uploads \
  ghcr.io/k9barry/8800sx:3.0.0
```

Access at http://localhost:8080

### Full Documentation

See [README.md](README.md) for complete documentation.

## 📦 Container Images

- `ghcr.io/k9barry/8800sx:3.0.0` - This specific version
- `ghcr.io/k9barry/8800sx:3.0` - Latest 3.0.x version
- `ghcr.io/k9barry/8800sx:3` - Latest 3.x version
- `ghcr.io/k9barry/8800sx:latest` - Latest stable version

**Full Changelog**: https://github.com/k9barry/8800SX/compare/2.0...v3.0.0
```

## Verification

After release, verify the images are available and working:

```bash
# Pull the images
docker pull ghcr.io/k9barry/8800sx:3.0.0
docker pull ghcr.io/k9barry/8800sx:latest

# Run a quick test with the test script
./test.sh ghcr.io/k9barry/8800sx:3.0.0 true
```

The test script will verify all components and services are functioning correctly.
