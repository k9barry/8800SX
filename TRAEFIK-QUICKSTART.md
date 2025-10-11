# Traefik Quick Start Guide

This is a simplified guide to get Viavi running with Traefik in under 10 minutes.

## Prerequisites

- Docker and Docker Compose installed
- A domain name pointing to your server's IP address
- Ports 80 and 443 open on your firewall

## Step-by-Step Setup

### 1. Create Traefik Network

```bash
docker network create traefik-public
```

### 2. Prepare Traefik Certificate Storage

```bash
mkdir -p traefik
touch traefik/acme.json
chmod 600 traefik/acme.json
```

### 3. Start Traefik

Edit the email in `docker-compose.traefik-standalone.yml`:

```yaml
- "--certificatesresolvers.letsencrypt.acme.email=your-email@example.com"
```

Then start Traefik:

```bash
docker compose -f docker-compose.traefik-standalone.yml up -d
```

### 4. Configure Viavi for Traefik

Copy the example configuration:

```bash
cp docker-compose.traefik.example.yml docker-compose.yml
```

Edit `docker-compose.yml` and replace `viavi.example.com` with your domain:

```yaml
traefik.http.routers.viavi-http.rule: "Host(`your-domain.com`)"
traefik.http.routers.viavi-https.rule: "Host(`your-domain.com`)"
```

### 5. Set Database Password

```bash
mkdir -p secrets
echo "YourSecurePassword123!" > secrets/db_password.txt
```

### 6. Start Viavi Application

```bash
docker compose up -d
```

### 7. Verify

Wait 1-2 minutes for SSL certificates to be issued, then visit:

```
https://your-domain.com
```

## Verification Commands

```bash
# Check all containers are running
docker ps

# View Traefik logs
docker logs traefik

# View Viavi web logs  
docker logs viavi-web

# Check SSL certificate
curl -I https://your-domain.com
```

## Troubleshooting

### "502 Bad Gateway"

Wait 30 seconds and try again. The application may still be starting.

### "No SSL Certificate"

1. Check DNS is pointing to your server: `nslookup your-domain.com`
2. Check Traefik logs: `docker logs traefik | grep acme`
3. Verify acme.json permissions: `ls -la traefik/acme.json` (should be `-rw-------`)

### "Cannot reach application"

1. Verify Traefik is running: `docker ps | grep traefik`
2. Check if containers are in the same network:
   ```bash
   docker network inspect traefik-public
   ```
3. Verify labels are correct:
   ```bash
   docker inspect viavi-web | grep -A 30 Labels
   ```

## What's Different from Standard Setup?

| Without Traefik | With Traefik |
|-----------------|--------------|
| Access via IP:8080 | Access via your-domain.com |
| HTTP only | HTTPS with valid SSL certificate |
| Manual port management | Automatic routing |
| No certificate renewal | Automatic Let's Encrypt renewal |

## Adding More Applications

Once Traefik is running, you can easily add more applications:

1. Add Traefik labels to the application's docker-compose.yml
2. Connect the application to the `traefik-public` network
3. Start the application

Example for a second application:

```yaml
services:
  myapp:
    image: myapp:latest
    networks:
      - traefik-public
    labels:
      traefik.enable: "true"
      traefik.http.routers.myapp.rule: "Host(`myapp.example.com`)"
      traefik.http.routers.myapp.entrypoints: "websecure"
      traefik.http.routers.myapp.tls.certresolver: "letsencrypt"
      traefik.http.services.myapp.loadbalancer.server.port: "80"

networks:
  traefik-public:
    external: true
```

## Next Steps

- Review [TRAEFIK.md](TRAEFIK.md) for detailed configuration options
- Add basic authentication for extra security
- Configure rate limiting to prevent abuse
- Set up monitoring and alerts
- Configure backup strategies

## Common Customizations

### Change Traefik Dashboard Port

Edit `docker-compose.traefik-standalone.yml`:

```yaml
ports:
  - "8081:8080"  # Change 8081 to your preferred port
```

### Add Basic Authentication

Generate password hash:
```bash
htpasswd -nb admin yourpassword
```

Add to `docker-compose.yml`:
```yaml
traefik.http.routers.viavi-https.middlewares: "viavi-auth"
traefik.http.middlewares.viavi-auth.basicauth.users: "admin:$$apr1$$..."
```

### Disable HTTP (Force HTTPS Only)

In `docker-compose.traefik-standalone.yml`, uncomment these lines:

```yaml
- "--entrypoints.web.http.redirections.entryPoint.to=websecure"
- "--entrypoints.web.http.redirections.entryPoint.scheme=https"
```

Or use per-service middleware (already configured in the example).

## Support

For detailed information, see:
- [TRAEFIK.md](TRAEFIK.md) - Complete integration guide
- [Traefik Documentation](https://doc.traefik.io/traefik/)
- [GitHub Issues](https://github.com/k9barry/viavi/issues)
