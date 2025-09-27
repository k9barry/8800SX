# GitHub Copilot Instructions for 8800SX

## Project Overview

The 8800SX project is a Docker Compose application that processes output files from Viavi 8800SX service monitors and parses the information into a MySQL database. The application provides a web interface for uploading files, searching records, and viewing alignment data.

## Tech Stack

- **Backend**: PHP 8.3 with PHP-FPM
- **Database**: MySQL 8.4.2
- **Web Server**: Nginx
- **Frontend**: Bootstrap 4.5.0, jQuery 3.5.1
- **Infrastructure**: Docker Compose
- **File Processing**: Custom PHP scripts for parsing Viavi service monitor files

## Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     Nginx       │───▶│    PHP-FPM      │───▶│     MySQL       │
│   (Port 8080)   │    │   (PHP 8.3)     │    │   (Database)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
        │                       │                       │
        ▼                       ▼                       ▼
  Static Files           PHP Application         Viavi Data
  Bootstrap CSS          Upload/Parse            Alignments
  jQuery/JS              Search Interface        BLOB Storage
```

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
- `docker-compose.yml` - Service orchestration
- `Dockerfile` - PHP-FPM container configuration
- `secrets/db_password.txt` - Database password (not in repository)

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
- Database passwords are managed via Docker secrets
- File uploads are restricted by extension (see `config.php` `$upload_disallowed_exts`)
- Input sanitization using `htmlspecialchars()` and `mysqli_real_escape_string()`
- File upload size limited to 128MB (configurable in Dockerfile)

### Database Schema
- Primary table: `alignments` with columns for datetime, model, serial, etc.
- BLOB storage for original service monitor files
- Foreign key relationships for data integrity

### Environment Setup
1. Clone repository
2. Create `secrets/db_password.txt` with database password
3. Run `docker compose up -d`
4. Access application at `http://localhost:8080`

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

The project currently uses manual testing:
1. Build Docker containers: `docker build . --file Dockerfile --tag test-image`
2. Run Docker Compose: `docker compose up -d`
3. Test file upload functionality at `http://localhost:8080`
4. Verify database operations through phpMyAdmin interface
5. Test search and CRUD operations

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