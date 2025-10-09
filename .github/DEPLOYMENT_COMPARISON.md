# Deployment Method Comparison

## Quick Reference

| Feature | docker-compose.yml (Build) | docker-compose.prod.yml (Pre-built) |
|---------|---------------------------|-------------------------------------|
| **Command** | `docker compose up -d` | `docker compose -f docker-compose.prod.yml up -d` |
| **Image Source** | Built locally from Dockerfile | Pulled from ghcr.io/k9barry/8800sx |
| **Build Time** | 5-10 minutes | No build (just pull, 1-2 min) |
| **CPU Usage** | High during build | Low (download only) |
| **Disk Space** | ~2GB during build | ~500MB (image only) |
| **Consistency** | May vary by environment | Always identical |
| **Use Case** | Development, testing changes | Production, staging |
| **Network Needed** | Base images only | Full image download |
| **Customization** | Easy (edit code, rebuild) | Need to fork and rebuild |

## When to Use Each

### Use docker-compose.yml (Build from Source) When:

✅ You're developing or modifying the application  
✅ You need to test code changes immediately  
✅ You want full control over the build process  
✅ You're working offline or in air-gapped environment  
✅ You need custom modifications  

### Use docker-compose.prod.yml (Pre-built Images) When:

✅ You're deploying to production  
✅ You want faster deployment times  
✅ You need consistent, tested images  
✅ You want to minimize server resource usage  
✅ You're deploying to multiple servers  
✅ You want easy rollback to specific versions  

## Configuration Files

### docker-compose.yml
```yaml
services:
  viavi-web:
    # Builds from local Dockerfile
    build:
      context: .
      dockerfile: Dockerfile
```

### docker-compose.prod.yml
```yaml
services:
  viavi-web:
    # Pulls from GitHub Container Registry
    image: ghcr.io/k9barry/8800sx:main-web
```

## Version Pinning

For production, you can pin to specific versions:

```yaml
# Latest from main branch (may change)
image: ghcr.io/k9barry/8800sx:main-web

# Specific version (recommended for production)
image: ghcr.io/k9barry/8800sx:3.0.1-web

# Latest patch of minor version
image: ghcr.io/k9barry/8800sx:3.0-web

# Latest minor of major version
image: ghcr.io/k9barry/8800sx:3-web
```

## Switching Between Methods

### Method 1: Use Different Compose Files (Recommended)

```bash
# Development
docker compose up -d

# Production
docker compose -f docker-compose.prod.yml up -d
```

### Method 2: Edit docker-compose.yml

Modify the `viavi-web` service in `docker-compose.yml`:

```yaml
  viavi-web:
    # Comment out for production
    # build:
    #   context: .
    #   dockerfile: Dockerfile
    
    # Uncomment for production
    image: ghcr.io/k9barry/8800sx:3.0.1-web
```

## CI/CD Integration

Images are automatically built and pushed by GitHub Actions:

1. **On Push to Main**: Creates `main-web` tag
2. **On Version Tag**: Creates versioned tags (e.g., `3.0.1-web`)
3. **Multi-platform**: Builds for amd64 (and arm64 coming soon)

See `.github/workflows/docker-build.yml` for details.

## Updating Deployments

### With Pre-built Images (docker-compose.prod.yml)

```bash
# Pull latest image
docker compose -f docker-compose.prod.yml pull viavi-web

# Recreate with new image
docker compose -f docker-compose.prod.yml up -d

# One-liner
docker compose -f docker-compose.prod.yml pull && \
  docker compose -f docker-compose.prod.yml up -d
```

### With Local Build (docker-compose.yml)

```bash
# Rebuild and restart
docker compose up -d --build

# Or rebuild specific service
docker compose build viavi-web
docker compose up -d
```

## Minimal Files Needed

### For Production (Pre-built Images)
- `docker-compose.prod.yml`
- `.env` (with DB_PASSWORD)
- `data/init-db.sql`

❌ **Not needed:** Dockerfile, source code, build tools

### For Development (Local Build)
- `docker-compose.yml`
- `.env` (with DB_PASSWORD)
- `Dockerfile`
- `data/` directory (full source)

✅ **Needed:** All source files for building

## Summary

**Production Best Practice:**
- Use `docker-compose.prod.yml` with pinned version tag
- Example: `image: ghcr.io/k9barry/8800sx:3.0.1-web`
- Faster, more reliable, easier to manage

**Development Best Practice:**
- Use `docker-compose.yml` with local build
- Makes testing changes quick and easy
- Full control over the build process
