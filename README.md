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

The viavi-web service will be available:
- Through Traefik at `viavi.example.com` (update host in docker-compose.yml)
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

\`\`\`bash
# Clone repository
git clone https://github.com/k9barry/8800SX.git
cd 8800SX

# Build web service image
docker compose build viavi-web
\`\`\`

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

## 📦 Releases

This project uses semantic versioning (SemVer) with automated release management.

### Automated Releases

When a PR is merged to main:
- A new version is automatically created based on PR labels (`major`, `minor`, `patch`)
- If no label is present, defaults to `patch` version bump
- A GitHub Release is created with version details and Docker image tags
- Docker images are built and pushed to GitHub Container Registry

### Docker Images

- Latest release: \`ghcr.io/k9barry/8800sx:latest\`
- Specific version: \`ghcr.io/k9barry/8800sx:3.0.0\`

See [Releases](https://github.com/k9barry/8800SX/releases) for version history.

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

### Version Labels

When submitting a PR, add one of these labels to control versioning:
- `major` - Breaking changes (e.g., 2.0.0 → 3.0.0)
- `minor` - New features (e.g., 3.0.0 → 3.1.0)
- `patch` - Bug fixes (e.g., 3.0.0 → 3.0.1)

If no label is added, the version will be bumped as a `patch` by default.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Issues**: [GitHub Issues](https://github.com/k9barry/8800SX/issues)
- **Security**: See [SECURITY.md](SECURITY.md) for reporting vulnerabilities

## 🙏 Acknowledgments

Built for processing Viavi 8800SX service monitor test results.
