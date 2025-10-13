# Changelog

All notable changes to the Viavi 8800SX Database project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.2] - 2025-10-13
### Fixed
- Updated CHANGELOG.md to include missing version 3.0.2 section

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
Document your changes in the Pull Request description. Use these categories for clarity:
- **Added** - new features
- **Changed** - changes to existing functionality
- **Deprecated** - soon-to-be removed features
- **Removed** - removed features
- **Fixed** - bug fixes
- **Security** - security fixes

### For Maintainers
When a PR is merged to main, the semantic versioning workflow automatically:
1. Creates a new version section with: `## [X.Y.Z] - YYYY-MM-DD`
2. Adds the PR title and description as the release notes
3. Updates version comparison links
4. Updates the README.md version badge
5. Creates a GitHub release with the same information

**Note**: Ensure you add the appropriate PR label (major, minor, or patch) to control the version bump. The PR title and description become the changelog entry, so write them clearly and descriptively.

### Version Format
- **MAJOR** (X.0.0) - Incompatible API changes
- **MINOR** (0.X.0) - Backwards-compatible new functionality
- **PATCH** (0.0.X) - Backwards-compatible bug fixes

---

[3.0.3]: https://github.com/k9barry/viavi/releases/tag/v3.0.3
[3.0.2]: https://github.com/k9barry/viavi/releases/tag/v3.0.2
[3.0.1]: https://github.com/k9barry/viavi/releases/tag/v3.0.1
[2.1.0]: https://github.com/k9barry/viavi/releases/tag/v2.1.0
[1.0.0]: https://github.com/k9barry/viavi/releases/tag/v1.0.0
