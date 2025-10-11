# Traefik Integration Guide

This guide explains how to integrate the Viavi 8800SX application with Traefik reverse proxy for production deployment.

## Overview

**Yes, the web service (nginx) is needed** when using Traefik. Traefik acts as a reverse proxy and load balancer that routes traffic to your nginx web service, but it doesn't replace nginx itself.

### Architecture with Traefik

```
Internet → Traefik (Port 80/443) → Nginx (Port 80) → PHP-FPM (Port 9000)
                                                    ↓
                                              MySQL (Port 3306)
```

## Why You Need the Web Service

1. **Nginx serves static files**: CSS, JavaScript, images
2. **Nginx handles PHP FastCGI**: Communicates with PHP-FPM container
3. **Traefik routes traffic**: Handles SSL/TLS, domain routing, and load balancing
4. **Separation of concerns**: Traefik for routing, Nginx for application serving

## Docker Compose with Traefik

Here's an example `docker-compose.yml` configured to work with Traefik:

```yaml
version: '3.8'

services:
  web:
    image: nginx
    container_name: viavi-web
    restart: always
    depends_on:
      - php-fpm
    volumes:
      - ./data/web:/var/www/html
      - ./data/web/uploads:/var/www/html/uploads
      - ./data/web/nginx.conf:/etc/nginx/conf.d/default.conf
    networks:
      - traefik-public
      - viavi-backend
    labels:
      # Enable Traefik for this service
      - "traefik.enable=true"
      
      # Specify the network Traefik should use
      - "traefik.docker.network=traefik-public"
      
      # HTTP Router
      - "traefik.http.routers.viavi.rule=Host(`viavi.example.com`)"
      - "traefik.http.routers.viavi.entrypoints=web"
      
      # HTTPS Router (with automatic redirect)
      - "traefik.http.routers.viavi-secure.rule=Host(`viavi.example.com`)"
      - "traefik.http.routers.viavi-secure.entrypoints=websecure"
      - "traefik.http.routers.viavi-secure.tls=true"
      - "traefik.http.routers.viavi-secure.tls.certresolver=letsencrypt"
      
      # Middleware for HTTP to HTTPS redirect
      - "traefik.http.middlewares.viavi-redirect.redirectscheme.scheme=https"
      - "traefik.http.middlewares.viavi-redirect.redirectscheme.permanent=true"
      - "traefik.http.routers.viavi.middlewares=viavi-redirect"
      
      # Service (tell Traefik which port to use)
      - "traefik.http.services.viavi.loadbalancer.server.port=80"

  php-fpm:
    container_name: viavi-app
    build:
      context: .
      dockerfile: Dockerfile
    volumes:
      - ./data/web:/var/www/html
      - ./data/web/uploads:/var/www/html/uploads
    environment:
      DB_PASSWORD_FILE: /run/secrets/db_password
    secrets:
      - db_password
    networks:
      - viavi-backend

  db:
    image: mysql:8.4.2
    container_name: viavi-db
    restart: always
    volumes: 
      - ./data/db/data:/var/lib/mysql
      - ./data/db/logs:/var/log/mysql
      - ./data/db/init/init-db.sql:/docker-entrypoint-initdb.d/init-db.sql
    environment:
      MYSQL_RANDOM_ROOT_PASSWORD: true
      MYSQL_DATABASE: viavi
      MYSQL_USER: viavi
      MYSQL_PASSWORD_FILE: /run/secrets/db_password
    secrets:
      - db_password
    networks:
      - viavi-backend

secrets:
   db_password:
     file: ./secrets/db_password.txt

networks:
  # External network that Traefik uses
  traefik-public:
    external: true
  # Internal network for app communication
  viavi-backend:
    driver: bridge
```

## Complete Traefik Setup

If you don't already have Traefik running, here's a complete setup:

### 1. Create Traefik Docker Compose

Create a separate `docker-compose.traefik.yml`:

```yaml
version: '3.8'

services:
  traefik:
    image: traefik:v2.10
    container_name: traefik
    restart: always
    ports:
      - "80:80"     # HTTP
      - "443:443"   # HTTPS
      - "8081:8080" # Traefik Dashboard (optional)
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - ./traefik/traefik.yml:/traefik.yml:ro
      - ./traefik/acme.json:/acme.json
    networks:
      - traefik-public
    labels:
      # Dashboard configuration (optional)
      - "traefik.enable=true"
      - "traefik.http.routers.traefik.rule=Host(`traefik.example.com`)"
      - "traefik.http.routers.traefik.service=api@internal"
      - "traefik.http.routers.traefik.entrypoints=websecure"
      - "traefik.http.routers.traefik.tls.certresolver=letsencrypt"

networks:
  traefik-public:
    name: traefik-public
    driver: bridge
```

### 2. Create Traefik Configuration

Create `traefik/traefik.yml`:

```yaml
api:
  dashboard: true
  insecure: false

entryPoints:
  web:
    address: ":80"
    http:
      redirections:
        entryPoint:
          to: websecure
          scheme: https
  websecure:
    address: ":443"

providers:
  docker:
    endpoint: "unix:///var/run/docker.sock"
    exposedByDefault: false
    network: traefik-public

certificatesResolvers:
  letsencrypt:
    acme:
      email: your-email@example.com
      storage: /acme.json
      httpChallenge:
        entryPoint: web

log:
  level: INFO
  format: common

accessLog:
  format: common
```

### 3. Setup Steps

```bash
# 1. Create Traefik network
docker network create traefik-public

# 2. Create acme.json for SSL certificates
mkdir -p traefik
touch traefik/acme.json
chmod 600 traefik/acme.json

# 3. Start Traefik
docker compose -f docker-compose.traefik.yml up -d

# 4. Update your Viavi docker-compose.yml with Traefik labels (see above)

# 5. Start Viavi application
docker compose up -d
```

## Configuration Changes Required

### 1. Update Domain

Replace `viavi.example.com` in the labels with your actual domain name:

```yaml
- "traefik.http.routers.viavi.rule=Host(`your-domain.com`)"
- "traefik.http.routers.viavi-secure.rule=Host(`your-domain.com`)"
```

### 2. Update Email for Let's Encrypt

In `traefik/traefik.yml`:

```yaml
certificatesResolvers:
  letsencrypt:
    acme:
      email: your-email@example.com  # Change this
```

### 3. Remove Port Binding (Optional)

Since Traefik handles external access, you can optionally remove the port binding from the web service:

```yaml
web:
  # Remove or comment out:
  # ports:
  #   - 8080:80
```

## Key Differences from Current Setup

| Current Setup | With Traefik |
|---------------|--------------|
| Direct port exposure (8080:80) | No direct port exposure |
| HTTP only | HTTPS with automatic certificates |
| Single domain | Multiple domains possible |
| No automatic SSL | Let's Encrypt integration |
| Manual routing | Automatic service discovery |

## Advantages of Using Traefik

1. **Automatic SSL/TLS**: Let's Encrypt integration with automatic certificate renewal
2. **Multiple services**: Host multiple applications on the same server
3. **Load balancing**: Distribute traffic across multiple instances
4. **Service discovery**: Automatic configuration via Docker labels
5. **Middleware**: Add authentication, rate limiting, headers, etc.
6. **Dashboard**: Web UI for monitoring and management

## Additional Middleware Examples

### Basic Authentication

Add password protection to your application:

```yaml
labels:
  # ... existing labels ...
  - "traefik.http.middlewares.viavi-auth.basicauth.users=admin:$$apr1$$hash$$here"
  - "traefik.http.routers.viavi-secure.middlewares=viavi-auth"
```

Generate password hash:
```bash
htpasswd -nb admin yourpassword
```

### Rate Limiting

Prevent abuse:

```yaml
labels:
  # ... existing labels ...
  - "traefik.http.middlewares.viavi-ratelimit.ratelimit.average=100"
  - "traefik.http.middlewares.viavi-ratelimit.ratelimit.burst=50"
  - "traefik.http.routers.viavi-secure.middlewares=viavi-ratelimit"
```

### IP Whitelist

Restrict access to specific IPs:

```yaml
labels:
  # ... existing labels ...
  - "traefik.http.middlewares.viavi-ipwhitelist.ipwhitelist.sourcerange=192.168.1.0/24,10.0.0.0/8"
  - "traefik.http.routers.viavi-secure.middlewares=viavi-ipwhitelist"
```

## Testing

1. **Check Traefik is running**:
   ```bash
   docker ps | grep traefik
   ```

2. **View Traefik logs**:
   ```bash
   docker logs traefik -f
   ```

3. **Check Viavi service registration**:
   ```bash
   docker logs viavi-web
   ```

4. **Test HTTP to HTTPS redirect**:
   ```bash
   curl -I http://your-domain.com
   # Should see 301/302 redirect to https
   ```

5. **Test HTTPS**:
   ```bash
   curl -I https://your-domain.com
   # Should see 200 OK with SSL certificate
   ```

## Troubleshooting

### Service not accessible

1. Check Traefik can see the service:
   ```bash
   docker exec traefik traefik healthcheck
   ```

2. Verify networks:
   ```bash
   docker network inspect traefik-public
   ```

3. Check labels:
   ```bash
   docker inspect viavi-web | grep -A 20 Labels
   ```

### SSL certificate issues

1. Check acme.json permissions:
   ```bash
   ls -la traefik/acme.json
   # Should be -rw------- (600)
   ```

2. View certificate resolver logs:
   ```bash
   docker logs traefik | grep acme
   ```

3. Ensure DNS is pointing to your server before requesting certificates

### 502 Bad Gateway

1. Verify nginx is responding:
   ```bash
   docker exec viavi-web nginx -t
   docker logs viavi-web
   ```

2. Check PHP-FPM is running:
   ```bash
   docker exec viavi-app ps aux | grep php-fpm
   ```

## Production Checklist

- [ ] Domain DNS configured and propagated
- [ ] Firewall allows ports 80 and 443
- [ ] Traefik network created
- [ ] acme.json file created with correct permissions
- [ ] Email configured in Traefik for Let's Encrypt
- [ ] Database password set securely
- [ ] All labels updated with correct domain
- [ ] Testing completed successfully
- [ ] Monitoring and logging configured
- [ ] Backup strategy in place

## References

- [Traefik Documentation](https://doc.traefik.io/traefik/)
- [Traefik Docker Provider](https://doc.traefik.io/traefik/providers/docker/)
- [Let's Encrypt with Traefik](https://doc.traefik.io/traefik/https/acme/)
- [Traefik Middlewares](https://doc.traefik.io/traefik/middlewares/overview/)

## Support

For issues specific to:
- **Viavi application**: Open an issue on [GitHub](https://github.com/k9barry/viavi/issues)
- **Traefik**: Check [Traefik Community Forum](https://community.traefik.io/)
