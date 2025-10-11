# Viavi 8800SX Database Management System

[![Docker Image CI](https://github.com/k9barry/8800SX/actions/workflows/docker-image.yml/badge.svg)](https://github.com/k9barry/8800SX/actions/workflows/docker-image.yml)
[![License](https://img.shields.io/github/license/k9barry/viavi)](LICENSE)

A Docker-based web application for managing and analyzing output files from Viavi 8800SX service monitors. This system automatically parses test data files, stores them in a MySQL database, and provides a user-friendly interface for searching and managing service records.

![Viavi 8800SX Database Interface](https://github.com/k9barry/8800SX/assets/16656369/0c9ba0b5-dd22-4f9d-a76a-404b8d11aaaa)

## 📋 Table of Contents

- [Features](#-features)
- [Prerequisites](#-prerequisites)
- [Quick Start](#-quick-start)
- [Installation](#-installation)
- [Usage](#-usage)
- [Configuration](#-configuration)
- [File Format](#-file-format)
- [Architecture](#-architecture)
- [Security](#-security)
- [Contributing](#-contributing)
- [Troubleshooting](#-troubleshooting)
- [License](#-license)

## ✨ Features

- **Automated File Parsing**: Automatically extracts test data from Viavi 8800SX output files
- **Database Storage**: Stores parsed data in MySQL with full BLOB storage of original files
- **Web Interface**: User-friendly web interface for uploading and managing records
- **Search Functionality**: Quick search capabilities for finding specific test records
- **Multi-file Upload**: Upload multiple test files simultaneously
- **Duplicate Detection**: Automatically prevents duplicate entries
- **PHPMyAdmin Integration**: Direct database access for advanced users
- **Multi-language Support**: Interface available in 12 languages
- **Docker-based**: Easy deployment with Docker Compose
- **Responsive Design**: Bootstrap-based responsive UI

## 🔧 Prerequisites

Before you begin, ensure you have the following installed:

- [Docker](https://docs.docker.com/get-docker/) (version 20.10 or higher)
- [Docker Compose](https://docs.docker.com/compose/install/) (version 1.29 or higher)
- At least 2GB of free disk space
- Port 8080 available on your host machine

## 🚀 Quick Start

Get up and running in 3 steps:

```bash
# 1. Clone or pull the repository
docker pull ghcr.io/k9barry/8800sx

# 2. Set your database password
echo "YourSecurePassword123!" > secrets/db_password.txt

# 3. Start the application
docker compose up -d
```

Access the application at http://localhost:8080

## 📦 Installation

### Option 1: Using Docker Hub (Recommended)

```bash
# Pull the pre-built image
docker pull ghcr.io/k9barry/8800sx

# Navigate to the directory
cd viavi

# Configure database password
echo "YourSecurePassword123!" > secrets/db_password.txt

# Start services
docker compose up -d
```

### Option 2: Building from Source

```bash
# Clone the repository
git clone https://github.com/k9barry/viavi.git
cd viavi

# Set database password
echo "YourSecurePassword123!" > secrets/db_password.txt

# Build and start
docker compose build
docker compose up -d
```

### Verify Installation

```bash
# Check that all containers are running
docker compose ps

# View logs
docker compose logs -f
```

All containers should show as "Up" and healthy.

## 💻 Usage

### Uploading Test Files

1. Open your web browser and navigate to http://localhost:8080
2. Click the "Choose Files" button
3. Select one or more `.txt` files from your Viavi 8800SX service monitor
4. Click "Submit" to upload and process the files
5. Wait for the upload confirmation message

### Viewing Records

1. Click the "Alignment Database" button on the main page
2. Use the search box to filter records by any field
3. Click "View" on any record to see detailed information
4. Use pagination controls to navigate through records

### Managing Database

1. Click the "phpMyAdmin" button on the main page
2. Login with credentials:
   - **Server**: db
   - **Username**: viavi
   - **Password**: (the password from `secrets/db_password.txt`)
3. Access the `viavi` database to manage records directly

### Supported Operations

- **Create**: Add new records manually through the database interface
- **Read**: View individual records and their details
- **Update**: Modify existing records (via PHPMyAdmin)
- **Delete**: Remove unwanted records
- **Search**: Filter records by datetime, model, serial number, or filename

## ⚙️ Configuration

### Database Password

**Important**: Change the default password before deployment!

```bash
# Edit the password file
nano secrets/db_password.txt

# Or use echo
echo "YourNewSecurePassword" > secrets/db_password.txt
```

### Upload Limits

Edit `Dockerfile` to adjust upload limits:

```dockerfile
upload_max_filesize = 128M  # Maximum file size
max_file_uploads = 1000     # Maximum number of files
post_max_size = 128M        # Maximum POST size
```

After changes, rebuild:
```bash
docker compose down
docker compose build --no-cache
docker compose up -d
```

### Language Settings

Change the interface language in `/data/web/app/config.php`:

```php
$language = 'en';  // Options: en, es, fr, de, it, pt, nl, ru, cn, jp, in, cz
```

### Port Configuration

To change the default port (8080), edit `docker-compose.yml`:

```yaml
ports:
  - "8080:80"  # Change 8080 to your desired port
```

### Traefik Integration

For production deployments with SSL/TLS support and reverse proxy functionality, see:

- **[TRAEFIK-QUICKSTART.md](TRAEFIK-QUICKSTART.md)** - Quick start guide (10 minutes)
- **[TRAEFIK.md](TRAEFIK.md)** - Complete integration guide with examples

Traefik provides:
- Automatic HTTPS with Let's Encrypt
- Easy multi-domain hosting
- Load balancing and service discovery
- Built-in monitoring dashboard

## 📄 File Format

The application expects files from Viavi 8800SX with the following naming convention:

```
{MODEL}-{SERIAL}-{DATE}-{TIME}.txt
```

**Example**: `XTS2500-123456789-20231015-143022.txt`

Where:
- **MODEL**: Radio model being tested
- **SERIAL**: Serial number of the device
- **DATE**: Date of test (YYYYMMDD or MMDDYYYY format)
- **TIME**: Time of test (HHMMSS format)

The file content should be the standard text output from the Viavi 8800SX service monitor.

## 🏗️ Architecture

```
┌─────────────────────────────────────────────┐
│           Nginx Web Server                  │
│              (Port 8080)                    │
└─────────────┬───────────────────────────────┘
              │
┌─────────────┴───────────────────────────────┐
│         PHP-FPM 8.3.2                       │
│  ┌──────────────────────────────────────┐   │
│  │  Upload Interface (app/upload.php)   │   │
│  │  File Parser (app/main.php)          │   │
│  │  CRUD Operations (alignments-*.php)  │   │
│  └──────────────┬───────────────────────┘   │
└─────────────────┼───────────────────────────┘
                  │
┌─────────────────┴───────────────────────────┐
│           MySQL Database                    │
│  ┌──────────────────────────────────────┐   │
│  │  Table: alignments                   │   │
│  │  - id (INT, PRIMARY KEY)             │   │
│  │  - datetime (DATETIME)               │   │
│  │  - model (VARCHAR)                   │   │
│  │  - serial (VARCHAR)                  │   │
│  │  - file (LONGTEXT)                   │   │
│  │  - filename (VARCHAR)                │   │
│  └──────────────────────────────────────┘   │
└─────────────────────────────────────────────┘
```

### Components

- **Web Server**: Nginx serving PHP files
- **Application**: PHP 8.3.2-FPM with mysqli extension
- **Database**: MySQL for persistent storage
- **PHPMyAdmin**: Database management interface

### Data Flow

1. User uploads `.txt` files through web interface
2. `app/main.php` validates file type and checks for duplicates
3. Filename is parsed to extract metadata (model, serial, date, time)
4. File content and metadata are stored in MySQL
5. User can search and view records through CRUD interface

## 🔒 Security

This project takes security seriously. Please review our [Security Policy](SECURITY.md) for:

- Supported versions
- How to report vulnerabilities
- Security best practices
- Known security considerations
- Production hardening checklist

### Quick Security Tips

1. **Change the default password** in `secrets/db_password.txt`
2. **Use HTTPS** in production environments
3. **Restrict network access** using firewalls
4. **Keep Docker images updated** regularly
5. **Monitor upload directory** for suspicious files
6. **Enable security headers** in production

For detailed security information, see [SECURITY.md](SECURITY.md).

## 👥 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

Please ensure your code follows the project's coding standards and includes appropriate tests.

For development guidelines, see [.github/copilot-instructions.md](.github/copilot-instructions.md).

## 🐛 Troubleshooting

### Containers Won't Start

```bash
# Check logs
docker compose logs

# Ensure ports are available
lsof -i :8080

# Remove old containers and rebuild
docker compose down -v
docker compose up -d --build
```

### Database Connection Issues

```bash
# Verify database password
cat secrets/db_password.txt

# Check database container
docker compose exec db mysql -u viavi -p

# Reset database
docker compose down -v
docker compose up -d
```

### Upload Failures

- **Check file format**: Ensure files end with `.txt`
- **Check file size**: Default limit is 128MB
- **Check permissions**: Ensure upload directory is writable
- **Check logs**: `docker compose logs web`

### "File already exists" Error

The system prevents duplicate uploads. If you need to re-upload:
1. Access PHPMyAdmin
2. Delete the existing record from the `alignments` table
3. Try uploading again

### Port Already in Use

```bash
# Change port in docker-compose.yml
# Or stop conflicting service
sudo lsof -ti:8080 | xargs kill -9
```

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📚 Additional Documentation

- [Changelog](CHANGELOG.md) - Version history and changes
- [Security Policy](SECURITY.md) - Security guidelines and reporting
- [Copilot Instructions](.github/copilot-instructions.md) - Development guidelines

## 🙏 Acknowledgments

- Viavi Solutions for the 8800SX service monitor
- Bootstrap team for the responsive framework
- Docker community for containerization support

---

**Project Maintainer**: [k9barry](https://github.com/k9barry)

**Issues**: Report bugs or request features via [GitHub Issues](https://github.com/k9barry/viavi/issues)

**Last Updated**: October 2025
