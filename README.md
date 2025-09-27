# 8800SX Viavi Service Monitor Database

[![Docker Image CI](https://github.com/k9barry/8800SX/actions/workflows/docker-image.yml/badge.svg)](https://github.com/k9barry/8800SX/actions/workflows/docker-image.yml)
[![Tests](https://github.com/k9barry/8800SX/actions/workflows/tests.yml/badge.svg)](https://github.com/k9barry/8800SX/actions/workflows/tests.yml)
[![Code Quality](https://github.com/k9barry/8800SX/actions/workflows/code-quality.yml/badge.svg)](https://github.com/k9barry/8800SX/actions/workflows/code-quality.yml)
[![codecov](https://codecov.io/gh/k9barry/8800SX/branch/main/graph/badge.svg)](https://codecov.io/gh/k9barry/8800SX)
[![License](https://img.shields.io/github/license/k9barry/8800SX)](LICENSE)
[![Security](https://img.shields.io/badge/security-see%20SECURITY.md-blue)](SECURITY.md)
[![PHP Version](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)](https://docs.docker.com/compose/)
[![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?logo=mysql&logoColor=white)](https://mysql.com/)
[![GitHub Issues](https://img.shields.io/github/issues/k9barry/8800SX)](https://github.com/k9barry/8800SX/issues)
[![GitHub Stars](https://img.shields.io/github/stars/k9barry/8800SX)](https://github.com/k9barry/8800SX/stargazers)
[![Contributions Welcome](https://img.shields.io/badge/contributions-welcome-brightgreen.svg?style=flat)](#🤝-contributing)

A Docker-based web application for parsing and managing output files from Viavi 8800SX service monitors. This application stores alignment test results in a MySQL database with full file content preservation and provides a web interface for searching and viewing test data.

## 🚀 Features

- **File Upload**: Bulk upload of Viavi 8800SX .txt test result files
- **Database Storage**: Complete test data stored in MySQL with file content as BLOB
- **Web Interface**: Search and browse test results through phpMyAdmin-style interface
- **Duplicate Prevention**: Automatic detection and prevention of duplicate file uploads
- **Data Parsing**: Intelligent parsing of filename format to extract test metadata
- **Dockerized**: Complete Docker Compose setup for easy deployment
- **Security**: Input validation, SQL injection prevention, and secure file handling

## 📋 Requirements

- Docker Engine 20.10+
- Docker Compose 2.0+
- Minimum 2GB RAM
- 10GB free disk space (for database and uploaded files)

## 🛠️ Quick Start

### 1. Clone Repository
```bash
git clone https://github.com/k9barry/8800SX.git
cd 8800SX
```

### 2. Configure Database Password
**⚠️ Important**: Change the default database password for security
```bash
# Edit the password file (replace 'ChangeMe' with a strong password)
nano secrets/db_password.txt
```

### 3. Start Services
```bash
# Start all services in detached mode
docker compose up -d

# Check service status
docker compose ps
```

### 4. Access Application
- **Web Interface**: http://localhost:8080
- **phpMyAdmin**: Access via the "phpMyAdmin" button in the web interface

## 📁 File Format Requirements

The application expects Viavi 8800SX files in this naming format:
```
{MODEL}-{SERIAL}-{DATE}-{TIME}.txt
```

**Examples:**
- `APX8000-12345678-20231215-143022.txt`
- `XTL5000-87654321-12152023-091545.txt`

**Format Details:**
- **MODEL**: Radio model identifier
- **SERIAL**: Serial number of the tested device  
- **DATE**: Test date (MMDDYYYY or YYYYMMDD format)
- **TIME**: Test time (HHMMSS format)
- **Extension**: Must be `.txt`

## 🔧 Usage

### Uploading Files

1. Navigate to http://localhost:8080
2. Click "Select Multiple Files" and choose your Viavi .txt files
3. Click "Submit" to upload
4. Files are automatically parsed and stored in the database
5. Duplicate files are automatically detected and skipped

### Searching Data

1. Use the search box on the main page to find records
2. Search terms match across all fields (ID, model, serial, etc.)
3. Click on any record to view detailed information
4. Access phpMyAdmin for advanced database queries

### Database Management

The application provides direct access to phpMyAdmin for advanced database operations:
- View raw data
- Export data to various formats
- Run custom SQL queries
- Manage database structure

## 🏗️ Architecture

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│     Nginx       │────│    PHP-FPM       │────│     MySQL       │
│   (Web Server)  │    │ (Application)    │    │   (Database)    │
│                 │    │                  │    │                 │
│ Port: 8080      │    │ Files: /uploads  │    │ Data: viavi DB  │
└─────────────────┘    └──────────────────┘    └─────────────────┘
```

### Components

- **nginx**: Web server and reverse proxy
- **php-fpm**: PHP 8.3 with mysqli extension for database connectivity
- **mysql**: MySQL 8.4 database server
- **volumes**: Persistent storage for database and uploaded files

## 🔒 Security Features

- **SQL Injection Prevention**: All queries use prepared statements
- **File Upload Security**: Validation, sanitization, and size limits
- **Input Validation**: All user inputs are properly validated and escaped
- **Container Security**: Non-root user execution and Alpine Linux base images
- **Secret Management**: Database passwords stored securely using Docker secrets
- **Network Isolation**: Services communicate only through internal Docker network

For detailed security information, see [SECURITY.md](SECURITY.md).

## 🐳 Docker Configuration

### Services

| Service | Image | Purpose | Ports |
|---------|-------|---------|-------|
| web | nginx:alpine | Web server | 8080:80 |
| php-fpm | custom (PHP 8.3) | Application runtime | Internal |
| db | mysql:8.4 | Database | Internal |

### Volumes

- `db_data`: MySQL database files
- `mysql_logs`: MySQL log files  
- `./data/web/uploads`: Uploaded files (mounted as bind volume)

### Health Checks

All services include health checks for monitoring:
- **nginx**: HTTP endpoint check
- **php-fpm**: PHP-FPM status check
- **mysql**: MySQL ping check

## 🔧 Development

### Local Development Setup

```bash
# Clone repository
git clone https://github.com/k9barry/8800SX.git
cd 8800SX

# Set up development environment
cp secrets/db_password.txt secrets/db_password.dev.txt

# Start development stack
docker compose up --build
```

### File Structure

```
8800SX/
├── data/
│   ├── web/                 # Web application files
│   │   ├── app/            # Main application
│   │   ├── config/         # Nginx configuration
│   │   └── uploads/        # Uploaded files (created at runtime)
│   └── init-db.sql         # Database initialization
├── secrets/
│   └── db_password.txt     # Database password
├── docker-compose.yml      # Docker services configuration
├── Dockerfile             # PHP-FPM container build
└── README.md
```

## 🐛 Troubleshooting

### Common Issues

**Services won't start:**
```bash
# Check logs
docker compose logs

# Check individual service
docker compose logs nginx
docker compose logs php-fpm
docker compose logs db
```

**Database connection errors:**
```bash
# Verify database password
cat secrets/db_password.txt

# Check MySQL service
docker compose exec db mysql -u viavi -p viavi
```

**File upload errors:**
```bash
# Check PHP configuration
docker compose exec php-fpm php -i | grep upload

# Check uploads directory permissions
docker compose exec php-fpm ls -la /var/www/html/uploads
```

**Application not accessible:**
```bash
# Check port binding
docker compose ps
curl http://localhost:8080
```

### Reset Application

```bash
# Stop services
docker compose down

# Remove volumes (⚠️ This deletes all data!)
docker compose down -v

# Restart clean
docker compose up -d
```

## 📊 Monitoring

### Service Health

```bash
# Check all service health
docker compose ps

# View service logs
docker compose logs -f

# Monitor resource usage
docker stats
```

### Database Monitoring

Access phpMyAdmin to monitor:
- Database size and growth
- Query performance
- Table statistics
- Storage usage

## ⚠️ Production Deployment

**Security Checklist:**
- [ ] Change default database password
- [ ] Deploy behind HTTPS reverse proxy
- [ ] Configure firewall rules
- [ ] Set up regular backups
- [ ] Enable container logging
- [ ] Monitor resource usage
- [ ] Keep images updated

**Recommended Production Setup:**
```bash
# Use production-ready reverse proxy
version: "3.8"
services:
  traefik:
    image: traefik:v2.9
    # ... SSL/TLS configuration

  8800sx:
    # Your application
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.8800sx.tls=true"
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Issues**: [GitHub Issues](https://github.com/k9barry/8800SX/issues)
- **Security**: See [SECURITY.md](SECURITY.md) for reporting security vulnerabilities
- **Documentation**: Check this README and inline code comments

---

## 📸 Screenshots

### Main Interface
![Main Interface](https://github.com/k9barry/8800SX/assets/16656369/0c9ba0b5-dd22-4f9d-a76a-404b8d11aaaa)

*The main interface showing file upload functionality and database search capabilities.*
