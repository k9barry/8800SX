# Direct Answers to Traefik Questions

## Question 1: Is the web service needed to publish this repo to a server using Traefik?

**Answer: YES, the web service (nginx) is absolutely needed.**

### Why?

1. **Traefik is a reverse proxy**, not a web server. It routes traffic but doesn't serve your application directly.

2. **Nginx serves your PHP application**:
   - Serves static files (CSS, JavaScript, images)
   - Communicates with PHP-FPM to execute PHP code
   - Handles file uploads
   - Manages application routing

3. **The architecture looks like this**:
   ```
   Internet → Traefik (routing/SSL) → Nginx (web server) → PHP-FPM (application)
                                                          ↓
                                                       MySQL (database)
   ```

### What Changes?

With Traefik, you:
- **Keep nginx** - it still serves the application
- **Remove the port binding** (8080:80) from docker-compose.yml
- **Add Traefik labels** to the nginx container
- **Connect to Traefik's network** instead of exposing ports directly

## Question 2: What would the docker-compose file look like to work with Traefik?

**Answer: See the example files provided:**

1. **`docker-compose.traefik.example.yml`** - Your Viavi application configured for Traefik
2. **`docker-compose.traefik-standalone.yml`** - The Traefik service itself

### Key Changes to Your Current docker-compose.yml:

```yaml
services:
  web:
    image: nginx
    # REMOVE: ports: - "8080:80"  (No direct port binding needed)
    networks:
      - traefik-public  # ADD: Connect to Traefik network
      - viavi-backend
    labels:
      # ADD: Traefik configuration labels
      traefik.enable: "true"
      traefik.http.routers.viavi-https.rule: "Host(`your-domain.com`)"
      traefik.http.routers.viavi-https.entrypoints: "websecure"
      traefik.http.routers.viavi-https.tls: "true"
      traefik.http.routers.viavi-https.tls.certresolver: "letsencrypt"
      traefik.http.services.viavi-service.loadbalancer.server.port: "80"

  # php-fpm and db stay the same, just use viavi-backend network

networks:
  traefik-public:
    external: true  # ADD: External network managed by Traefik
  viavi-backend:
    driver: bridge
```

### Complete Working Examples

The repository now contains three new files with complete examples:

1. **TRAEFIK-QUICKSTART.md** - 10-minute setup guide
2. **TRAEFIK.md** - Complete reference with all options
3. **docker-compose.traefik.example.yml** - Ready-to-use configuration

## Quick Setup Summary

### Step 1: Start Traefik (One Time)
```bash
docker network create traefik-public
mkdir -p traefik && touch traefik/acme.json && chmod 600 traefik/acme.json
docker compose -f docker-compose.traefik-standalone.yml up -d
```

### Step 2: Configure Viavi
```bash
# Use the example file or add the labels to your existing docker-compose.yml
cp docker-compose.traefik.example.yml docker-compose.yml

# Edit and replace 'viavi.example.com' with your actual domain
nano docker-compose.yml
```

### Step 3: Start Viavi
```bash
docker compose up -d
```

## What You Get

| Without Traefik | With Traefik |
|-----------------|--------------|
| http://yourserver:8080 | https://your-domain.com |
| HTTP only | HTTPS with automatic SSL |
| Manual port management | Automatic routing |
| One service per port | Multiple services on one server |
| Manual SSL certificates | Auto-renewing Let's Encrypt |

## Benefits of Using Traefik

1. **Automatic HTTPS** - Free SSL certificates from Let's Encrypt
2. **Multi-domain support** - Host multiple apps on one server
3. **Service discovery** - Automatic configuration via Docker labels
4. **Load balancing** - Built-in load balancing capabilities
5. **Monitoring** - Web dashboard for monitoring services

## Important Notes

- **Domain required**: You need a domain name pointing to your server for SSL to work
- **Ports 80/443**: Must be open on your firewall
- **Email required**: For Let's Encrypt certificate notifications
- **DNS propagation**: Wait for DNS to propagate before requesting certificates

## Files Created

All documentation and examples are now in the repository:

- `TRAEFIK.md` - Complete integration guide (10,000+ words)
- `TRAEFIK-QUICKSTART.md` - Quick start guide (< 10 minutes)
- `docker-compose.traefik.example.yml` - Viavi configured for Traefik
- `docker-compose.traefik-standalone.yml` - Traefik service definition
- `README.md` - Updated with Traefik references

## No Changes Made to Core Files

As requested, **no changes were made** to:
- ✅ Your existing `docker-compose.yml`
- ✅ The Dockerfile
- ✅ Application code
- ✅ Nginx configuration

Only **documentation and example files** were added.
