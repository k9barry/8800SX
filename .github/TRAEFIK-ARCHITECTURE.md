# Traefik Architecture Diagrams

## Current Setup (Without Traefik)

```
┌────────────┐
│  Internet  │
└──────┬─────┘
       │ Port 8080
       ▼
┌──────────────────────────┐
│   Your Server            │
│  ┌────────────────────┐  │
│  │  Nginx Container   │  │
│  │  Port 80 → 8080    │  │
│  └─────────┬──────────┘  │
│            │              │
│  ┌─────────▼──────────┐  │
│  │ PHP-FPM Container  │  │
│  │      Port 9000     │  │
│  └─────────┬──────────┘  │
│            │              │
│  ┌─────────▼──────────┐  │
│  │  MySQL Container   │  │
│  │      Port 3306     │  │
│  └────────────────────┘  │
└──────────────────────────┘

Access: http://yourserver:8080
```

## With Traefik Setup

```
┌────────────┐
│  Internet  │
└──────┬─────┘
       │ Ports 80/443
       ▼
┌──────────────────────────────────────────────┐
│   Your Server                                 │
│                                               │
│  ┌────────────────────────────────────────┐  │
│  │      Traefik Container                 │  │
│  │  • Reverse Proxy                       │  │
│  │  • SSL/TLS Termination                 │  │
│  │  • Service Discovery                   │  │
│  │  • Load Balancing                      │  │
│  └───┬────────────────────────────────┬───┘  │
│      │                                │       │
│      │ traefik-public network         │       │
│      │                                │       │
│  ┌───▼────────────────────┐  ┌───────▼─────────┐
│  │  Viavi (nginx)         │  │  Other Apps     │
│  │  Container             │  │  (optional)     │
│  │  Port 80 (internal)    │  │                 │
│  └───┬────────────────────┘  └─────────────────┘
│      │                                          │
│      │ viavi-backend network                    │
│      │                                          │
│  ┌───▼────────────────────┐                    │
│  │  PHP-FPM Container     │                    │
│  │  Port 9000 (internal)  │                    │
│  └───┬────────────────────┘                    │
│      │                                          │
│  ┌───▼────────────────────┐                    │
│  │  MySQL Container       │                    │
│  │  Port 3306 (internal)  │                    │
│  └────────────────────────┘                    │
└──────────────────────────────────────────────┘

Access: https://your-domain.com (with SSL)
```

## Network Topology

```
┌─────────────────────────────────────────────────────┐
│  traefik-public (External Docker Network)           │
│                                                      │
│  ┌──────────┐        ┌──────────┐   ┌──────────┐  │
│  │ Traefik  │───────▶│  Nginx   │   │ Other    │  │
│  │          │        │ (Viavi)  │   │ Services │  │
│  └──────────┘        └─────┬────┘   └──────────┘  │
│                             │                       │
└─────────────────────────────┼───────────────────────┘
                              │
┌─────────────────────────────┼───────────────────────┐
│  viavi-backend (Internal Network)                   │
│                             │                        │
│                      ┌──────▼──────┐                │
│                      │   PHP-FPM   │                │
│                      └──────┬──────┘                │
│                             │                        │
│                      ┌──────▼──────┐                │
│                      │    MySQL    │                │
│                      └─────────────┘                │
└─────────────────────────────────────────────────────┘
```

## Traffic Flow

### HTTP Request Flow (with Traefik)

```
1. User requests: https://viavi.example.com
   │
   ▼
2. DNS resolves to your server's IP
   │
   ▼
3. Request hits Traefik on port 443
   │
   ▼
4. Traefik checks Host header and routes based on labels
   │
   ▼
5. Traefik forwards to nginx container (port 80)
   │
   ▼
6. Nginx receives request and checks URL path
   │
   ▼
7. For PHP files: Nginx → PHP-FPM (port 9000)
   │
   ▼
8. PHP-FPM executes PHP code
   │
   ▼
9. PHP connects to MySQL if needed (port 3306)
   │
   ▼
10. Response flows back: PHP → Nginx → Traefik → User
```

### Without Traefik (Current Setup)

```
1. User requests: http://yourserver:8080
   │
   ▼
2. Request directly hits nginx on port 8080
   │
   ▼
3. Nginx processes request
   │
   ▼
4. For PHP: Nginx → PHP-FPM → MySQL
   │
   ▼
5. Response: MySQL → PHP → Nginx → User
```

## Key Differences

| Aspect | Without Traefik | With Traefik |
|--------|----------------|--------------|
| **Entry Point** | Nginx directly | Traefik (reverse proxy) |
| **Port Exposure** | Port 8080 exposed | Ports 80/443 for all services |
| **SSL/TLS** | Manual setup required | Automatic Let's Encrypt |
| **Service Count** | One service per port | Multiple services via routing |
| **URL** | http://server:8080 | https://domain.com |
| **Network** | Single bridge network | Two networks (public/private) |

## Label-Based Routing

Traefik uses Docker labels to configure routing:

```yaml
labels:
  # Tell Traefik to manage this container
  traefik.enable: "true"
  
  # Define routing rule (by hostname)
  traefik.http.routers.viavi-https.rule: "Host(`viavi.example.com`)"
  
  # Which port to forward to
  traefik.http.services.viavi.loadbalancer.server.port: "80"
  
  # SSL configuration
  traefik.http.routers.viavi-https.tls.certresolver: "letsencrypt"
```

This means:
- When Traefik sees a request for `viavi.example.com`
- It forwards the request to the nginx container's port 80
- Handles SSL/TLS automatically
- No need to modify nginx configuration

## Multi-Service Example

With Traefik, you can easily host multiple applications:

```
Traefik (Ports 80/443)
    │
    ├─→ viavi.example.com → Nginx (Viavi App)
    │
    ├─→ monitoring.example.com → Grafana
    │
    ├─→ git.example.com → Gitea
    │
    └─→ cloud.example.com → Nextcloud
```

All with automatic HTTPS and single IP address!

## Security Layers

```
┌──────────────────────────────────────┐
│  Internet (Untrusted)                │
└───────────────┬──────────────────────┘
                │
        ┌───────▼────────┐
        │    Firewall    │ ← Only 80/443 open
        └───────┬────────┘
                │
        ┌───────▼────────┐
        │    Traefik     │ ← SSL termination, rate limiting
        └───────┬────────┘
                │
        ┌───────▼────────┐
        │     Nginx      │ ← Web server, static files
        └───────┬────────┘
                │
        ┌───────▼────────┐
        │    PHP-FPM     │ ← Application logic
        └───────┬────────┘
                │
        ┌───────▼────────┐
        │     MySQL      │ ← Database (not exposed)
        └────────────────┘
```

## Advantages Summary

### With Traefik:
✅ Automatic HTTPS with Let's Encrypt  
✅ Automatic certificate renewal  
✅ Host multiple apps on one server  
✅ Service discovery via Docker labels  
✅ Built-in load balancing  
✅ Web dashboard for monitoring  
✅ Middleware for auth, rate limiting, etc.  
✅ Professional production setup  

### Without Traefik:
✅ Simpler for development  
✅ Fewer moving parts  
✅ Direct port access  
❌ Manual SSL setup  
❌ One service per port  
❌ No automatic certificate renewal  
❌ Limited production features  
