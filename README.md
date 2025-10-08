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

### Standard Deployment

\`\`\`bash
# Clone repository
git clone https://github.com/k9barry/8800SX.git
cd 8800SX

# Configure environment
cp .env.example .env
nano .env  # Set DB_PASSWORD and other variables

# Create Traefik network (if using Traefik)
docker network create traefik

# Start application
docker compose up -d
\`\`\`

The viavi-web service will be available through Traefik at `viavi.example.com` (update host in docker-compose.yml).

### Unified Container Deployment (Backward Compatibility)

For a single unified container deployment:

\`\`\`bash
# Start unified container profile
docker compose --profile unified up -d
\`\`\`

Or using Docker run:

\`\`\`bash
docker pull ghcr.io/k9barry/8800sx:latest

docker run -d \
  --name viavi \
  -p 8080:80 \
  -e DB_PASSWORD=your_secure_password \
  -v viavi_data:/var/lib/mysql \
  -v viavi_uploads:/var/www/html/uploads \
  ghcr.io/k9barry/8800sx:latest
\`\`\`

Access at http://localhost:8080

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
- `viavi`: Unified container (backward compatibility, profile-based)

### Unified Container (Backward Compatibility)

The unified container uses **supervisord** to manage Nginx, PHP-FPM, and MariaDB in one container.

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
| `viavi_unified_data` | Database files (unified) | viavi |
| `viavi_unified_uploads` | Uploaded files (unified) | viavi |

### Networks

| Network | Purpose |
|---------|---------|
| `viavi-internal` | Internal communication between services |
| `traefik` | External Traefik reverse proxy network |

## 🔨 Building Locally

### Multi-Container Build

\`\`\`bash
# Clone repository
git clone https://github.com/k9barry/8800SX.git
cd 8800SX

# Build web service image
docker compose build viavi-web

# Or use build script
./build.sh
\`\`\`

### Unified Container Build

\`\`\`bash
# Build unified image
docker build -f Dockerfile.unified -t viavi:local .
\`\`\`

## 🧪 Testing

Run the automated test suite:

\`\`\`bash
./test.sh
\`\`\`

## 📊 Management

### View Logs

\`\`\`bash
# Multi-container logs
docker compose logs -f viavi-web
docker compose logs -f viavi-db

# Or for unified container
docker logs -f viavi

# Specific service logs (inside viavi-web container)
docker exec viavi-web tail -f /var/log/nginx/access.log
\`\`\`

### Database Access

\`\`\`bash
# Multi-container MySQL shell
docker exec -it viavi-db mysql -u viavi -p viavi

# Database backup
docker compose exec viavi-db mysqldump -u viavi -p viavi > backup.sql

# Database restore
docker compose exec -i viavi-db mysql -u viavi -p viavi < backup.sql

# Or for unified container
docker exec -it viavi mysql -u viavi -p viavi
docker exec viavi mysqldump -u viavi -p viavi > backup.sql
docker exec -i viavi mysql -u viavi -p viavi < backup.sql
\`\`\`

### Shell Access

\`\`\`bash
# Multi-container
docker exec -it viavi-web /bin/bash
docker exec -it viavi-db /bin/bash

# Unified container
docker exec -it viavi /bin/bash
\`\`\`

## 🔒 Security

- Automated vulnerability scanning with Trivy
- Non-root user execution where possible
- Secure password management via environment variables
- Input validation and SQL injection prevention
- See [SECURITY.md](SECURITY.md) for security policy

## 📦 Releases

This project uses semantic versioning (SemVer).

- **v3.0.0**: Multi-container architecture with Traefik integration
- Latest stable: \`ghcr.io/k9barry/8800sx:latest\` (unified container for backward compatibility)
- Specific version: \`ghcr.io/k9barry/8800sx:3.0.0\`

See [Releases](https://github.com/k9barry/8800SX/releases) for version history.

## 🔄 Upgrading

### From v2.x (Multi-Container)

1. **Backup your data:**
   \`\`\`bash
   docker compose exec db mysqldump -u viavi -p viavi > backup.sql
   \`\`\`

2. **Stop old containers:**
   \`\`\`bash
   docker compose down
   \`\`\`

3. **Start unified container:**
   \`\`\`bash
   docker run -d \
     --name viavi \
     -p 8080:80 \
     -e DB_PASSWORD=your_password \
     -v viavi_data:/var/lib/mysql \
     -v viavi_uploads:/var/www/html/uploads \
     ghcr.io/k9barry/8800sx:latest
   \`\`\`

4. **Restore data if needed:**
   \`\`\`bash
   docker exec -i viavi mysql -u viavi -pyour_password viavi < backup.sql
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
