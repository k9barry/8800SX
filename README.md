# 8800SX Viavi Service Monitor Database

[![Docker Build](https://github.com/k9barry/8800SX/actions/workflows/docker-build.yml/badge.svg)](https://github.com/k9barry/8800SX/actions/workflows/docker-build.yml)
[![License](https://img.shields.io/github/license/k9barry/8800SX)](LICENSE)
[![Version](https://img.shields.io/github/v/tag/k9barry/8800SX)](https://github.com/k9barry/8800SX/releases)

A unified Docker container for parsing and managing output files from Viavi 8800SX service monitors. This application stores alignment test results in a MySQL database with full file content preservation and provides a web interface for searching and viewing test data.

## 🚀 Features

- **Unified Container**: Single Docker image containing Nginx, PHP-FPM, and MariaDB
- **File Upload**: Bulk upload of Viavi 8800SX .txt test result files
- **Database Storage**: Complete test data stored in MySQL with file content as BLOB
- **Web Interface**: Search and browse test results
- **Duplicate Prevention**: Automatic detection and prevention of duplicate file uploads
- **Data Parsing**: Intelligent parsing of filename format to extract test metadata
- **Security**: Input validation, SQL injection prevention, and secure file handling
- **Multi-architecture**: Supports both amd64 and arm64 platforms

## 📋 Requirements

- Docker Engine 20.10+
- Minimum 2GB RAM
- 10GB free disk space (for database and uploaded files)

## 🛠️ Quick Start

### Option 1: Docker Run (Simplest)

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

### Option 2: Docker Compose

\`\`\`bash
# Clone repository
git clone https://github.com/k9barry/8800SX.git
cd 8800SX

# Configure environment
cp .env.example .env
nano .env  # Set DB_PASSWORD

# Start application
docker compose up -d
\`\`\`

### Option 3: Docker Compose with Traefik

For production deployments with automatic HTTPS, see \`docker-compose.yml\` for complete Traefik integration example.

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

The unified container uses **supervisord** to manage three services in one container.

## 🔧 Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| \`DB_PASSWORD\` | MySQL database password | \`ChangeMe\` |

### Volume Mounts

| Volume | Purpose |
|--------|---------|
| \`/var/lib/mysql\` | Database files (persistent) |
| \`/var/www/html/uploads\` | Uploaded test files (persistent) |
| \`/var/log/mysql\` | MySQL logs (optional) |

## 🔨 Building Locally

\`\`\`bash
# Clone repository
git clone https://github.com/k9barry/8800SX.git
cd 8800SX

# Build image
docker build -t viavi:local .

# Or use build script
./build.sh

# Test the build
./test.sh
\`\`\`

## 🧪 Testing

Run the automated test suite:

\`\`\`bash
./test.sh
\`\`\`

The test script will:
1. Build the Docker image
2. Start a test container
3. Verify all services are running
4. Test HTTP connectivity
5. Verify database is accessible
6. Check file structure

## 📊 Management

### View Logs

\`\`\`bash
# All logs
docker logs viavi

# Follow logs
docker logs -f viavi

# Specific service logs (inside container)
docker exec viavi tail -f /var/log/nginx/access.log
docker exec viavi tail -f /var/log/mysql/error.log
\`\`\`

### Database Access

\`\`\`bash
# MySQL shell
docker exec -it viavi mysql -u viavi -p viavi

# Database backup
docker exec viavi mysqldump -u viavi -p viavi > backup.sql

# Database restore
docker exec -i viavi mysql -u viavi -p viavi < backup.sql
\`\`\`

### Shell Access

\`\`\`bash
docker exec -it viavi /bin/bash
\`\`\`

## 🔒 Security

- Automated vulnerability scanning with Trivy
- Non-root user execution where possible
- Secure password management via environment variables
- Input validation and SQL injection prevention
- See [SECURITY.md](SECURITY.md) for security policy

## 📦 Releases

This project uses semantic versioning (SemVer) starting from v3.0.0.

- **v3.x.x**: Unified container architecture with .env configuration
- Latest stable: \`ghcr.io/k9barry/8800sx:latest\`
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
