# 8800SX Viavi Service Monitor Database

[![Docker Build](https://github.com/k9barry/8800SX/actions/workflows/docker-build.yml/badge.svg)](https://github.com/k9barry/8800SX/actions/workflows/docker-build.yml)
[![License](https://img.shields.io/github/license/k9barry/8800SX)](LICENSE)
[![Version](https://img.shields.io/github/v/tag/k9barry/8800SX)](https://github.com/k9barry/8800SX/releases)

A multi-container Docker Compose application for parsing and managing output files from Viavi 8800SX service monitors. This application stores alignment test results in a MySQL database with full file content preservation and provides a web interface for searching and viewing test data.

## 🚀 Features

- **Multi-Container Architecture**: Separate services for web (Nginx + PHP-FPM) and database (MariaDB)
- **Traefik Integration**: Built-in support for Traefik reverse proxy with automatic HTTPS
- **File Upload**: Bulk upload of Viavi 8800SX .txt test result files
- **Database Storage**: Complete test data stored in MySQL with file content as BLOB
- **Web Interface**: Search and browse test results
- **Duplicate Prevention**: Automatic detection and prevention of duplicate file uploads
- **Data Parsing**: Intelligent parsing of filename format to extract test metadata
- **Security**: Input validation, SQL injection prevention, and secure file handling
- **Multi-architecture**: Supports both amd64 and arm64 platforms

## 📋 Requirements

- Docker Engine 20.10+
- Docker Compose v2.0+
- Minimum 2GB RAM
- 10GB free disk space (for database and uploaded files)
- Traefik network (for production deployment with Traefik)

## 🛠️ Quick Start

You have two deployment options:

### Option 1: Production Deployment (Recommended - Uses Pre-built Images)

Use this method for production servers. It pulls pre-built, tested images from GitHub Container Registry without needing to build locally.

\`\`\`bash
# Clone repository
git clone https://github.com/k9barry/8800SX.git
cd 8800SX

# Configure environment
cp .env.example .env
nano .env  # Set DB_PASSWORD and other variables

# Create Traefik network (if using Traefik)
docker network create traefik

# Start application with pre-built images
docker compose -f docker-compose.prod.yml up -d
\`\`\`

**Advantages:**
- ✅ Faster deployment (no build time)
- ✅ Consistent, tested images
- ✅ Lower resource requirements on server
- ✅ Automatic updates with image tags

**Available image tags:**
- `ghcr.io/k9barry/8800sx:main-web` - Latest from main branch
- `ghcr.io/k9barry/8800sx:3.0.1-web` - Specific version (recommended for production)
- `ghcr.io/k9barry/8800sx:3.0-web` - Latest minor version 3.0.x
- `ghcr.io/k9barry/8800sx:3-web` - Latest major version 3.x

### Option 2: Development Deployment (Build from Source)

Use this method for local development or when you need to modify the application code.

\`\`\`bash
# Clone repository
git clone https://github.com/k9barry/8800SX.git
cd 8800SX

# Configure environment
cp .env.example .env
nano .env  # Set DB_PASSWORD and other variables

# Create Traefik network (if using Traefik)
docker network create traefik

# Start application and build from source
docker compose up -d
\`\`\`

**Advantages:**
- ✅ Immediate testing of code changes
- ✅ Full control over build process
- ✅ No external dependencies (except Docker Hub for base images)

The viavi-web service will be available:
- Through Traefik at `viavi.example.com` (update host in docker-compose.yml or docker-compose.prod.yml)
- Directly at http://localhost:8080

## 📁 File Format Requirements

The application expects Viavi 8800SX files in this naming format:
\`\`\`
{MODEL}-{SERIAL}-{DATE}-{TIME}.txt
\`\`\`

Example: \`TEST-123456-20231215-143022.txt\`

- **MODEL**: Device model identifier
- **SERIAL**: Device serial number
- **DATE**: Date in YYYYMMDD format
- **TIME**: Time in HHMMSS format

## 🏗️ Architecture

### Multi-Container Setup (Default)

```
┌─────────────────┐     ┌─────────────────┐
│   viavi-web     │────▶│   viavi-db      │
│  (Nginx + PHP)  │     │   (MariaDB)     │
└─────────────────┘     └─────────────────┘
        │
        ▼
    Traefik
   (Optional)
```

**Services:**
- `viavi-web`: Nginx web server and PHP-FPM application server
- `viavi-db`: MariaDB database server

## 🔧 Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `DB_ROOT_PASSWORD` | MySQL root password (viavi-db) | `RootChangeMe` |
| `DB_NAME` | MySQL database name | `viavi` |
| `DB_USER` | MySQL database user | `viavi` |
| `DB_PASSWORD` | MySQL database password | `ChangeMe` |
| `DB_HOST` | MySQL host (for viavi-web) | `viavi-db` |

### Docker Volumes

| Volume | Purpose | Service |
|--------|---------|---------|
| `viavi_data` | Database files (persistent) | viavi-db |
| `viavi_uploads` | Uploaded test files (persistent) | viavi-web |

### Networks

| Network | Purpose |
|---------|---------|
| `viavi-internal` | Internal communication between services |
| `traefik` | External Traefik reverse proxy network |

## 🔨 Building Locally

If you're developing or customizing the application, you can build the Docker image locally:

\`\`\`bash
# Clone repository
git clone https://github.com/k9barry/8800SX.git
cd 8800SX

# Build web service image
docker compose build viavi-web

# Or build and start in one command
docker compose up -d --build
\`\`\`

To test with a specific version tag locally:
\`\`\`bash
# Build with a version tag
docker build -t ghcr.io/k9barry/8800sx:local-web .

# Update docker-compose.yml to use your local tag
# Change: image: ghcr.io/k9barry/8800sx:main-web
# To:     image: ghcr.io/k9barry/8800sx:local-web
\`\`\`

## 🔄 Switching Between Deployment Modes

### Using the Standard docker-compose.yml

The default `docker-compose.yml` file is configured for local development (builds from source). To switch to production mode (pre-built images):

1. Edit `docker-compose.yml`
2. Comment out the `build:` section
3. Uncomment the `image:` line

**Example:**
```yaml
  viavi-web:
    # For local development - build from source:
    # build:
    #   context: .
    #   dockerfile: Dockerfile
    # For production - use pre-built image:
    image: ghcr.io/k9barry/8800sx:main-web
    container_name: viavi-web
```

### Using Separate Compose Files (Recommended)

Alternatively, use the appropriate compose file for your use case:
- **Development:** `docker compose up -d` (uses docker-compose.yml)
- **Production:** `docker compose -f docker-compose.prod.yml up -d`

This approach keeps both configurations available without editing files.

## 📊 Management

### View Logs

\`\`\`bash
# Service logs
docker compose logs -f viavi-web
docker compose logs -f viavi-db

# Specific service logs (inside viavi-web container)
docker exec viavi-web tail -f /var/log/nginx/access.log
\`\`\`

### Database Access

\`\`\`bash
# MySQL shell
docker exec -it viavi-db mysql -u viavi -p viavi

# Database backup
docker compose exec viavi-db mysqldump -u viavi -p viavi > backup.sql

# Database restore
docker compose exec -i viavi-db mysql -u viavi -p viavi < backup.sql
\`\`\`

### Shell Access

\`\`\`bash
docker exec -it viavi-web /bin/bash
docker exec -it viavi-db /bin/bash
\`\`\`

## 🔒 Security

- Automated vulnerability scanning with Trivy
- Non-root user execution where possible
- Secure password management via environment variables
- Input validation and SQL injection prevention
- See [SECURITY.md](SECURITY.md) for security policy

## 📦 Releases and Container Images

This project uses semantic versioning (SemVer) and publishes Docker images to GitHub Container Registry.

### Pre-built Container Images

Docker images are automatically built and published for every release and commit to the main branch:

| Tag Format | Example | Use Case |
|------------|---------|----------|
| `main-web` | `ghcr.io/k9barry/8800sx:main-web` | Latest development version |
| `{version}-web` | `ghcr.io/k9barry/8800sx:3.0.1-web` | Specific version (recommended for production) |
| `{major}.{minor}-web` | `ghcr.io/k9barry/8800sx:3.0-web` | Latest patch in minor version |
| `{major}-web` | `ghcr.io/k9barry/8800sx:3-web` | Latest minor in major version |
| `{sha}-web` | `ghcr.io/k9barry/8800sx:abc1234-web` | Specific commit |

### Current Version

- **Latest Release**: v3.0.1
- **Architecture**: Multi-container setup with Traefik integration
- **Recommended Image**: `ghcr.io/k9barry/8800sx:3.0.1-web`

### Pulling Images

Images are public and can be pulled without authentication:

\`\`\`bash
# Pull latest stable version
docker pull ghcr.io/k9barry/8800sx:3.0.1-web

# Pull latest development version
docker pull ghcr.io/k9barry/8800sx:main-web
\`\`\`

See [Releases](https://github.com/k9barry/8800SX/releases) for complete version history and [Packages](https://github.com/k9barry/8800SX/pkgs/container/8800sx) for all available images.

## 🔄 Upgrading

### From Previous Versions

1. **Backup your data:**
   \`\`\`bash
   docker compose exec viavi-db mysqldump -u viavi -p viavi > backup.sql
   \`\`\`

2. **Stop old containers:**
   \`\`\`bash
   docker compose down
   \`\`\`

3. **Pull latest changes and start:**
   \`\`\`bash
   git pull
   docker compose up -d
   \`\`\`

4. **Restore data if needed:**
   \`\`\`bash
   docker compose exec -i viavi-db mysql -u viavi -p viavi < backup.sql
   \`\`\`

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Issues**: [GitHub Issues](https://github.com/k9barry/8800SX/issues)
- **Security**: See [SECURITY.md](SECURITY.md) for reporting vulnerabilities

## 🙏 Acknowledgments

Built for processing Viavi 8800SX service monitor test results.
