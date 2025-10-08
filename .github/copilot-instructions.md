# GitHub Copilot Instructions for 8800SX

## Project Overview

The 8800SX project is a multi-container Docker Compose application that processes output files from Viavi 8800SX service monitors and parses the information into a MySQL database. The application provides a web interface for uploading files, searching records, and viewing alignment data.

## Tech Stack

- **Backend**: PHP 8.3 with PHP-FPM
- **Database**: MariaDB 10.11
- **Web Server**: Nginx
- **Frontend**: Bootstrap 4.5.0, jQuery 3.5.1
- **Infrastructure**: Docker Compose with three services
- **Reverse Proxy**: Traefik (optional, for production)
- **File Processing**: Custom PHP scripts for parsing Viavi service monitor files

## Architecture

### Multi-Container Setup (v3.0.0)

```
┌─────────────────┐     ┌─────────────────┐
│   viavi-web     │────▶│   viavi-db      │
│ (Nginx + PHP)   │     │   (MariaDB)     │
└─────────────────┘     └─────────────────┘
        │
        ▼
    Traefik
  (viavi.example.com)
```

**Services:**
- `viavi-web`: Nginx web server and PHP-FPM application
- `viavi-db`: MariaDB database server
- `viavi`: Unified container (backward compatibility, optional)

## Key Components

### Data Processing
- `data/web/upload.php` - File upload interface for Viavi service monitor files
- `data/web/result.php` - File processing and database insertion
- `data/web/connection.php` - MySQL database connection
- `data/init-db.sql` - Database schema initialization

### Web Application
- `data/web/app/` - Main application directory
- `data/web/app/alignments-*.php` - CRUD operations for alignment records
- `data/web/app/config.php` - Application configuration
- `data/web/app/locales/` - Internationalization (English, Russian, Indonesian)

### Configuration
- `docker-compose.yml` - Multi-container orchestration with Traefik integration
- `Dockerfile` - Web service container (Nginx + PHP-FPM)
- `Dockerfile.unified` - Unified container (backward compatibility)
- `.env` - Environment variables (DB_PASSWORD, DB_HOST, etc.)

## Development Guidelines

### Code Style
- Follow existing PHP coding patterns in the codebase
- Use consistent indentation (mixed tabs/spaces as per existing files)
- Maintain HTML5 standards with Bootstrap 4 classes
- Keep MySQL queries parameterized to prevent SQL injection

### File Organization
- PHP application files go in `data/web/app/`
- Static assets in `data/web/` root
- Database initialization in `data/init-db.sql`
- Docker configuration at repository root

### Security Considerations
- Database passwords are managed via environment variables in `.env` file
- File uploads are restricted by extension (see `config.php` `$upload_disallowed_exts`)
- Input sanitization using `htmlspecialchars()` and `mysqli_real_escape_string()`
- File upload size limited to 128MB (configurable in Dockerfile)
- Traefik handles SSL/TLS certificates automatically with Let's Encrypt

### Database Schema
- Primary table: `alignments` with columns for datetime, model, serial, etc.
- BLOB storage for original service monitor files
- Foreign key relationships for data integrity

### Environment Setup
1. Clone repository
2. Copy `.env.example` to `.env` and configure database credentials
3. Create Traefik network: `docker network create traefik` (if using Traefik)
4. Run `docker compose up -d`
5. Access application via Traefik at configured hostname (e.g., `viavi.example.com`)

## Common Tasks

### Adding New Features
- Create new PHP files in `data/web/app/` following existing patterns
- Update navigation in `navbar.php` if needed
- Add translations to `locales/` files for internationalization
- Test with Docker Compose locally

### Database Changes
- Modify `data/init-db.sql` for schema changes
- Update `config-tables-columns.php` for new table metadata
- Ensure backward compatibility with existing data

### File Upload Handling
- Extend allowed file types in `config.php` if needed
- Implement parsing logic in `result.php`
- Store file metadata in database with BLOB for file contents

### UI/UX Updates
- Use Bootstrap 4 classes for consistent styling
- Update templates following existing HTML structure
- Maintain responsive design principles
- Test across different screen sizes

## Testing

The project uses Docker integration testing:
1. Build Docker containers: `docker compose build`
2. Run Docker Compose: `docker compose up -d`
3. Test file upload functionality via Traefik hostname or directly at container
4. Verify database operations: `docker compose exec viavi-db mysql -u viavi -p`
5. Test search and CRUD operations
6. Run automated tests: `./test.sh`

## Deployment

- GitHub Actions CI/CD builds Docker images on push to main
- Container registry: `ghcr.io/k9barry/8800sx`
- Production deployment via Docker Compose

## Contribution Guidelines

When making changes:
1. Maintain existing code patterns and structure
2. Test thoroughly with sample Viavi files
3. Ensure Docker containers build successfully
4. Verify database operations work correctly
5. Check responsive design on mobile devices
6. Update documentation if adding new features

## File Upload Notes

The application processes Viavi 8800SX service monitor files which contain:
- Alignment data
- Device serial numbers and models
- Measurement timestamps
- Binary measurement data stored as BLOBs

Files are uploaded through the web interface and parsed into structured database records while preserving the original file content for reference.