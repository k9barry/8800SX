# Changelog

All notable changes to the Viavi 8800SX Database project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
## [3.0.1] - 2025-10-13
### Added
- New semantic versioning workflow for automated release management
  - Supports version bumping based on PR labels (major, minor, patch)
  - Defaults to patch version bump if no label is specified
  - Automatically creates GitHub releases with version tags

### Changed
- Streamlined CI/CD pipeline by replacing Docker-specific workflows with semantic versioning workflow

### Fixed
- Semantic versioning workflow now automatically updates CHANGELOG.md when releasing new versions
  - Moves items from [Unreleased] section to new version section with date
  - Updates version comparison links at the end of the file
- Semantic versioning workflow now automatically updates README.md version badge
- Fixed trailing spaces in semantic-versioning.yml workflow file

### Removed
- Removed docker-image.yml workflow (replaced by semantic versioning workflow)
- Removed docker-publish.yml workflow (replaced by semantic versioning workflow)

## [2.1.0] - 2025-10-11

### Added
- Comprehensive security policy with vulnerability reporting process (SECURITY.md)
- Development guidelines for GitHub Copilot and contributors (.github/copilot-instructions.md)
- Centralized security header management (data/web/app/security-headers.php)
- MIME type validation for file uploads to prevent malicious file disguises
- HTML escaping for all user-controlled output
- Database initialization directory structure (data/db/init/)

### Changed
- Complete documentation overhaul with detailed README.md including:
  - Feature descriptions and prerequisites
  - Step-by-step installation and usage guides
  - Configuration options and troubleshooting
  - Architecture overview
- Reorganized application file structure for better maintainability:
  - Consolidated application files in data/web/app/
  - Simplified Docker configuration with single Dockerfile
  - Improved directory organization for database files
- Enhanced security in file upload handler (main.php):
  - Case-insensitive file extension validation
  - MIME type verification using finfo_file()
  - XSS prevention through proper output escaping
- Updated Docker configuration:
  - Fixed PHP-FPM directory creation in build process
  - Corrected database initialization paths
  - Updated .gitignore for better database file management

### Removed
- Legacy connection.php file (merged into config.php)
- Unused result.php file
- TCPDF from version control (now downloaded during build)
- Obsolete config directory structure

### Fixed
- Docker build failures due to missing directory structures
- Incorrect database initialization file paths
- Hardcoded localhost URLs preventing flexible deployment

### Security
- XSS vulnerability in filename display
- File upload security with MIME type validation
- Comprehensive security headers (X-Frame-Options, CSP, X-Content-Type-Options, etc.)
- Production deployment security checklist and best practices

## [1.0.0] - 2024-10-01

### Added
- Initial Docker Compose setup for Viavi 8800SX service monitor
- PHP 8.3.2-FPM web application
- MySQL database with PHPMyAdmin integration
- File upload system for .txt service monitor output files
- Automated parsing and database storage of test records
- CRUD operations for alignment records
- Multi-language support (12 languages)
- Bootstrap 4.5.0 responsive UI
- Search and filter functionality
- BLOB storage for complete service records

## How to Use This Changelog

### For Contributors
Add changes to the `[Unreleased]` section using these categories:
- **Added** - new features
- **Changed** - changes to existing functionality
- **Deprecated** - soon-to-be removed features
- **Removed** - removed features
- **Fixed** - bug fixes
- **Security** - security fixes

### For Maintainers
When releasing a new version, the semantic versioning workflow automatically:
1. Moves items from `[Unreleased]` to a new version section
2. Adds version number and date: `## [X.Y.Z] - YYYY-MM-DD`
3. Creates new empty `[Unreleased]` section
4. Updates version comparison links below
5. Updates the README.md version badge

**Note**: This process is now automated through GitHub Actions when PRs are merged to main. Just ensure you add the appropriate PR label (major, minor, or patch) to control the version bump.

### Version Format
- **MAJOR** (X.0.0) - Incompatible API changes
- **MINOR** (0.X.0) - Backwards-compatible new functionality
- **PATCH** (0.0.X) - Backwards-compatible bug fixes

---

[Unreleased]: https://github.com/k9barry/viavi/compare/v3.0.2...HEAD
[3.0.2]: https://github.com/k9barry/viavi/releases/tag/v3.0.2
[3.0.1]: https://github.com/k9barry/viavi/releases/tag/v3.0.1
[2.1.0]: https://github.com/k9barry/viavi/releases/tag/v2.1.0
[1.0.0]: https://github.com/k9barry/viavi/releases/tag/v1.0.0
