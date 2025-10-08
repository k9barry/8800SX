# Quick Start Guide: Unified Container

This is a streamlined guide for deploying the Viavi 8800SX application as a single Docker container.

## 🚀 Quick Deploy

### Option 1: Docker Run (Simplest)

```bash
docker run -d \
  --name viavi \
  -p 8080:80 \
  -e DB_PASSWORD=your_secure_password \
  -v viavi_data:/var/lib/mysql \
  -v viavi_uploads:/var/www/html/uploads \
  ghcr.io/k9barry/8800sx:unified
```

Access at: http://localhost:8080

### Option 2: Docker Compose (Standalone)

Create `docker-compose.yml`:

```yaml
version: "3.8"
services:
  viavi:
    image: ghcr.io/k9barry/8800sx:unified
    container_name: viavi
    restart: unless-stopped
    ports:
      - "8080:80"
    environment:
      - DB_PASSWORD=your_secure_password
    volumes:
      - viavi_data:/var/lib/mysql
      - viavi_uploads:/var/www/html/uploads

volumes:
  viavi_data:
  viavi_uploads:
```

Deploy:
```bash
docker-compose up -d
```

### Option 3: Docker Compose with Traefik

**Setup:**
```bash
# Copy example files
cp docker-compose.traefik.yml docker-compose.yml
cp .env.example .env

# Edit .env and set your DB_PASSWORD
nano .env

# Create Traefik network if not exists
docker network create traefik

# Deploy
docker-compose up -d
```

See `docker-compose.traefik.yml` for full Traefik integration example.

Quick version:
```yaml
version: "3.8"
services:
  viavi:
    image: ghcr.io/k9barry/8800sx:unified
    container_name: viavi
    restart: unless-stopped
    environment:
      - DB_PASSWORD=your_secure_password
    volumes:
      - viavi_data:/var/lib/mysql
      - viavi_uploads:/var/www/html/uploads
    networks:
      - traefik
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.viavi.rule=Host(`viavi.yourdomain.com`)"
      - "traefik.http.routers.viavi.entrypoints=websecure"
      - "traefik.http.routers.viavi.tls.certresolver=letsencrypt"
      - "traefik.http.services.viavi.loadbalancer.server.port=80"

volumes:
  viavi_data:
  viavi_uploads:

networks:
  traefik:
    external: true
```

## 🔧 Configuration

### Environment Variables

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `DB_PASSWORD` | MySQL database password | `ChangeMe` | Recommended |

### Volumes

| Volume | Purpose | Size |
|--------|---------|------|
| `/var/lib/mysql` | Database data | ~500MB+ |
| `/var/www/html/uploads` | Uploaded files | Variable |

### Ports

| Port | Service |
|------|---------|
| 80 | HTTP (Nginx) |

## 📊 Management Commands

### View Logs
```bash
docker logs -f viavi
```

### Check Service Status
```bash
docker exec viavi ps aux | grep -E "nginx|php-fpm|mariadbd"
```

### Access Container Shell
```bash
docker exec -it viavi /bin/bash
```

### Database Access
```bash
# MySQL shell
docker exec -it viavi mariadb -u viavi -p

# Run SQL commands
docker exec viavi mariadb -u viavi -p${DB_PASSWORD} -e "SHOW DATABASES;"
```

### Restart Services
```bash
# Restart container
docker restart viavi

# Or individual service (from inside container)
docker exec viavi supervisorctl restart nginx
docker exec viavi supervisorctl restart php-fpm
docker exec viavi supervisorctl restart mariadb
```

## 🛠️ Build Locally

```bash
# Clone repository
git clone https://github.com/k9barry/8800SX.git
cd 8800SX

# Build image
docker build -f Dockerfile.unified -t viavi:local .

# Or use the build script
./build-unified.sh

# Test the build
./test-unified.sh
```

## 🔍 Troubleshooting

### Container won't start
```bash
# Check logs
docker logs viavi

# Common issues:
# - Port 80 already in use: Change port mapping -p 8081:80
# - Volume permission issues: Check volume permissions
```

### Web interface not accessible
```bash
# Test from inside container
docker exec viavi curl -I http://localhost/

# Check nginx is running
docker exec viavi pgrep nginx

# Check nginx error logs
docker logs viavi 2>&1 | grep nginx
```

### Database connection errors
```bash
# Check if database is running
docker exec viavi pgrep mariadbd

# Verify password
docker exec viavi cat /tmp/db_password.txt

# Check database initialization
docker exec viavi ls -la /var/lib/mysql/
```

### Reset database
```bash
# Stop and remove container
docker stop viavi && docker rm viavi

# Remove volumes (⚠️ DELETES ALL DATA)
docker volume rm viavi_data viavi_uploads

# Start fresh
docker run -d --name viavi -p 8080:80 \
  -e DB_PASSWORD=new_password \
  -v viavi_data:/var/lib/mysql \
  -v viavi_uploads:/var/www/html/uploads \
  ghcr.io/k9barry/8800sx:unified
```

## 📚 Additional Resources

- **Full Documentation**: [UNIFIED_DEPLOYMENT.md](UNIFIED_DEPLOYMENT.md)
- **Traefik Setup**: [docker-compose.traefik.yml](docker-compose.traefik.yml)
- **Main README**: [README.md](README.md)
- **Security**: [SECURITY.md](SECURITY.md)

## ⚡ Performance Tips

### For Production

1. **Use named volumes** (better performance):
   ```bash
   -v viavi_data:/var/lib/mysql
   ```

2. **Set resource limits**:
   ```bash
   docker run -d --name viavi \
     --memory="2g" \
     --cpus="2" \
     ...
   ```

3. **Enable health checks** (already included in image):
   - Interval: 30s
   - Timeout: 10s
   - Start period: 60s

4. **Regular backups**:
   ```bash
   # Backup database
   docker exec viavi mysqldump -u viavi -p${DB_PASSWORD} viavi > backup.sql
   
   # Backup uploads
   docker run --rm \
     -v viavi_uploads:/data \
     -v $(pwd):/backup \
     alpine tar czf /backup/uploads-backup.tar.gz -C /data .
   ```

## 🔐 Security Checklist

Before deploying to production:

- [ ] Change default DB_PASSWORD
- [ ] Deploy behind HTTPS (use Traefik with Let's Encrypt)
- [ ] Configure firewall rules
- [ ] Set up regular backups
- [ ] Monitor logs
- [ ] Keep image updated: `docker pull ghcr.io/k9barry/8800sx:unified`
- [ ] Consider adding Traefik authentication middleware

## 📦 Image Information

- **Registry**: ghcr.io/k9barry/8800sx
- **Tags**: 
  - `unified` - Latest stable unified build
  - `latest` - Latest stable unified build
  - `main` - Latest from main branch
- **Size**: ~600MB (includes Nginx + PHP + MariaDB)
- **Base**: Debian Bookworm (via php:8.3-fpm)
- **Architecture**: amd64, arm64 (multi-platform)

---

**Need help?** Open an issue at https://github.com/k9barry/8800SX/issues
