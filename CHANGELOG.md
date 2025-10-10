# Changelog

All notable changes to the Viavi 8800SX Database project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Created SECURITY.md with comprehensive security policy and vulnerability reporting guidelines
- Created .github/copilot-instructions.md with development guidelines for GitHub Copilot and developers
- Created CHANGELOG.md to track all future changes following Keep a Changelog format
- Created data/web/app/security-headers.php for centralized security header management
- Added MIME type validation for file uploads (prevents malicious file uploads)
- Added HTML escaping to all user-controlled output in error messages

### Changed
- Completely rewrote README.md with comprehensive documentation including:
  - Features list, prerequisites, and quick start guide
  - Detailed installation and usage instructions
  - Configuration options and file format documentation
  - Architecture diagram and troubleshooting section
- Updated data/web/main.php with security improvements:
  - Case-insensitive file extension checking
  - MIME type validation using finfo_file()
  - HTML escaping to prevent XSS attacks
- Updated data/web/upload.php to use relative URLs instead of hardcoded localhost
- Added security headers to upload.php and alignments-index.php

### Security
- Fixed potential XSS vulnerability in filename display
- Added MIME type validation to prevent malicious file uploads disguised as .txt files
- Implemented security headers (X-Frame-Options, CSP, X-Content-Type-Options, etc.)
- Fixed hardcoded localhost URL to support deployment on any host/port
- Documented security considerations and best practices in SECURITY.md
- Added security hardening checklist for production deployments

## [1.0.0] - Initial Release

### Added
- Docker Compose setup for Viavi 8800SX service monitor data parsing
- PHP 8.3.2-FPM based web application
- MySQL database integration for storing test records
- File upload functionality for .txt files from Viavi 8800SX service monitor
- CRUD operations for alignment records
- Search functionality for database records
- Multi-language support (English, Spanish, French, German, Italian, Portuguese, Dutch, Russian, Chinese, Japanese, Indonesian, Czech)
- Bootstrap 4.5.0 based responsive UI
- PHPMyAdmin integration for database management
- Automated file parsing and database insertion
- BLOB storage for complete service records
- Docker image CI/CD pipeline via GitHub Actions

### Features
- Upload multiple .txt files simultaneously
- Automatic parsing of filename to extract:
  - Test datetime
  - Radio model
  - Serial number
- Duplicate file detection
- File extension validation
- Search and view alignment records
- Export and manage records through PHPMyAdmin

### Configuration
- Configurable database password via `/secrets/db_password.txt`
- File upload limits (128MB max upload size, 1000 max files)
- Configurable disallowed file extensions for security
- Multi-language support configuration

---

## How to Use This Changelog

### For Contributors
When making changes, add them under the `[Unreleased]` section using these categories:

- **Added** - for new features
- **Changed** - for changes in existing functionality
- **Deprecated** - for soon-to-be removed features
- **Removed** - for now removed features
- **Fixed** - for any bug fixes
- **Security** - for vulnerability fixes

### For Maintainers
When releasing a new version:
1. Move items from `[Unreleased]` to a new version section
2. Add the version number and date: `## [X.Y.Z] - YYYY-MM-DD`
3. Create a new empty `[Unreleased]` section
4. Update version links at the bottom of the file

### Version Format
- **MAJOR** version (X.0.0) - incompatible API changes
- **MINOR** version (0.X.0) - backwards-compatible new functionality
- **PATCH** version (0.0.X) - backwards-compatible bug fixes

---

[Unreleased]: https://github.com/k9barry/viavi/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/k9barry/viavi/releases/tag/v1.0.0
