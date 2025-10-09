# Deployment Guide: Build vs Pre-built Images

## Quick Answer to Your Question

**Yes, you are creating packaged containers for viavi-web!** The GitHub Actions workflow automatically builds and publishes Docker images to GitHub Container Registry (ghcr.io) on every push to main and every release.

**You have two options:**

### ✅ Option 1: Use Pre-built Images (Recommended for Production)

**No build needed!** Just pull the image from GitHub Container Registry.

Use `docker-compose.prod.yml`:
```bash
docker compose -f docker-compose.prod.yml up -d
```

This configuration uses:
```yaml
image: ghcr.io/k9barry/8800sx:main-web
```

**Benefits:**
- ✅ Faster deployment (no build time, ~2-5 minutes faster)
- ✅ Lower CPU/memory usage on deployment server
- ✅ Consistent, tested images (built by CI/CD)
- ✅ Easy version pinning for stability

**Available images:**
- `ghcr.io/k9barry/8800sx:main-web` - Latest from main branch
- `ghcr.io/k9barry/8800sx:3.0.1-web` - Specific version (recommended)
- `ghcr.io/k9barry/8800sx:3.0-web` - Latest 3.0.x version
- `ghcr.io/k9barry/8800sx:3-web` - Latest 3.x version

### 🔧 Option 2: Build from Source (For Development)

**Build locally** from the Dockerfile.

Use `docker-compose.yml`:
```bash
docker compose up -d
```

This configuration uses:
```yaml
build:
  context: .
  dockerfile: Dockerfile
```

**Benefits:**
- ✅ Test code changes immediately
- ✅ Customize the application
- ✅ Work offline (no registry dependency)

## Migration Path

### Currently (Your Question)

Your current `docker-compose.yml` has the `build:` section, which means it builds locally from the Dockerfile. You're asking if you should switch to using the pre-built images.

### Recommended Approach

**For production deployments:**
1. Use `docker-compose.prod.yml` which pulls from ghcr.io
2. Pin to a specific version tag (e.g., `3.0.1-web`) for stability

**For development:**
1. Use `docker-compose.yml` which builds from source
2. This lets you test changes immediately

### Switching Methods

**Method 1: Use separate compose files (Recommended)**
```bash
# Development
docker compose up -d

# Production
docker compose -f docker-compose.prod.yml up -d
```

**Method 2: Edit docker-compose.yml**

Comment/uncomment lines in `docker-compose.yml`:
```yaml
  viavi-web:
    # For local development - build from source:
    # build:
    #   context: .
    #   dockerfile: Dockerfile
    # For production - use pre-built image:
    image: ghcr.io/k9barry/8800sx:3.0.1-web
```

## Summary

| Question | Answer |
|----------|--------|
| Are you creating packaged containers? | **Yes!** GitHub Actions builds and publishes to ghcr.io |
| Do you need the build Dockerfile in docker-compose? | **Optional** - Use for development, not needed for production |
| Should you pull from ghcr.io? | **Yes for production** - Faster, consistent, tested images |
| Which file to use? | `docker-compose.prod.yml` for production, `docker-compose.yml` for dev |

## Image Registry Information

- **Registry:** GitHub Container Registry (ghcr.io)
- **Repository:** ghcr.io/k9barry/8800sx
- **Access:** Public (no authentication needed)
- **Build Trigger:** Automatic on push to main and version tags
- **Architectures:** amd64, arm64 (multi-platform support coming soon)

## Example Production Deployment

```bash
# 1. Clone repository (only need docker-compose.prod.yml and .env)
git clone https://github.com/k9barry/8800SX.git
cd 8800SX

# 2. Configure environment
cp .env.example .env
nano .env  # Set DB_PASSWORD

# 3. Create Traefik network (if using Traefik)
docker network create traefik

# 4. Deploy with pre-built image
docker compose -f docker-compose.prod.yml up -d

# 5. Images are pulled automatically from ghcr.io
# No build step needed!
```

## Updating Production

To update to a new version:

```bash
# Pull latest image
docker compose -f docker-compose.prod.yml pull viavi-web

# Recreate container with new image
docker compose -f docker-compose.prod.yml up -d

# Or in one command:
docker compose -f docker-compose.prod.yml pull && \
docker compose -f docker-compose.prod.yml up -d
```

## Cost/Resource Comparison

| Metric | Build from Source | Pre-built Image |
|--------|-------------------|-----------------|
| First deployment time | ~5-10 minutes | ~2-3 minutes |
| CPU usage during deploy | High (build process) | Low (just pull) |
| Disk space during build | ~2GB (build layers) | ~500MB (final image) |
| Network usage | Base images only | Full image (~500MB) |
| Consistency | May vary by environment | Always identical |

## Conclusion

**For your production deployment, you should:**
1. ✅ Use `docker-compose.prod.yml`
2. ✅ Pull from `ghcr.io/k9barry/8800sx:3.0.1-web` (or desired version)
3. ✅ Remove the need to have Dockerfile on production server
4. ✅ Faster deployments and consistent images

**You do NOT need the Dockerfile on production servers** - just docker-compose.prod.yml, .env, and the data/init-db.sql file for initialization.
