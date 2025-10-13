# Changelog

All notable changes to the Viavi 8800SX Database project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.4] - 2025-10-13

### Automate CHANGELOG updates with PR title and description for every release

## Overview

This PR updates the semantic versioning workflow to automatically populate the CHANGELOG with PR title and description for every release, eliminating the need for manual [Unreleased] section management.

## Problem

The previous workflow required contributors to manually edit the CHANGELOG.md file by adding changes to an [Unreleased] section. This approach had several issues:
- Manual CHANGELOG editing created merge conflicts
- Inconsistent formatting across entries
- Extra maintenance overhead for contributors
- Risk of forgetting to document changes

## Solution

The workflow now automatically extracts the PR title and description when a PR is merged to main, and uses this information to create properly formatted CHANGELOG entries and GitHub releases.

### Workflow Changes

**Before:**
```markdown
## [Unreleased]
### Added
- Feature manually added by contributor
```
Workflow would move this to a version section on release.

**After:**
The workflow now:
1. Extracts PR title and description using GitHub CLI
2. Creates a new version section directly: `## [X.Y.Z] - YYYY-MM-DD`
3. Uses PR title as the heading: `### <PR Title>`
4. Includes PR description as the content
5. Updates version comparison links automatically

### File Changes

**`.github/workflows/semantic-versioning.yml`**
- Added "Get PR details" step to extract PR information
- Modified CHANGELOG update logic to insert version sections with PR content
- Simplified release creation using echo commands for proper variable expansion
- Removed dependency on [Unreleased] section

**`CHANGELOG.md`**
- Removed `## [Unreleased]` section
- Removed `[Unreleased]` version comparison link
- Updated documentation for contributors: document changes in PR descriptions instead of CHANGELOG
- Updated maintainer documentation to reflect fully automated process

**`.github/copilot-instructions.md`**
- Updated semantic versioning workflow documentation
- Added emphasis on writing clear, descriptive PR titles and descriptions
- Removed references to manual [Unreleased] section management

## Benefits

✅ **Zero manual CHANGELOG maintenance** - Contributors only write PR descriptions  
✅ **No merge conflicts** - CHANGELOG is only modified by automated workflow  
✅ **Consistent formatting** - All entries follow the same structure  
✅ **Better documentation** - PR descriptions are more detailed since they become the changelog  
✅ **Improved code reviews** - Better PR descriptions lead to better review discussions  
✅ **Complete automation** - Every release is properly documented with date and description  

## Usage

Contributors should now write clear, descriptive PR titles and descriptions using these categories:
- **Added** - new features
- **Changed** - changes to existing functionality
- **Deprecated** - soon-to-be removed features
- **Removed** - removed features
- **Fixed** - bug fixes
- **Security** - security fixes

The PR description becomes the changelog entry, so quality matters!

## Testing

- ✅ YAML syntax validated with Python yaml parser
- ✅ Created test script simulating CHANGELOG updates with sample PR data
- ✅ Verified correct version insertion and link generation
- ✅ Code review completed with no issues
- ✅ End-to-end workflow logic verified

## Breaking Change

This is a workflow breaking change:
- No more [Unreleased] section in CHANGELOG.md
- Contributors must adapt to documenting changes in PR descriptions
- Existing changelog entries remain unchanged
- Only future releases use the new format

Closes #[issue-number]

<!-- START COPILOT CODING AGENT SUFFIX -->



<details>

<summary>Original prompt</summary>

> Made it so that every patch, minor or major release causes the CHANGELOG to be updated with the date of the release and the title and description of the PR merge.  make sure the CHANGELOG has a release section for every release version and there is no need for the unreleased section then.


</details>



<!-- START COPILOT CODING AGENT TIPS -->
---

💡 You can make Copilot smarter by setting up custom instructions, customizing its development environment and configuring Model Context Protocol (MCP) servers. Learn more [Copilot coding agent tips](https://gh.io/copilot-coding-agent-tips) in the docs.

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

[3.0.4]: https://github.com/k9barry/viavi/releases/tag/v3.0.4

[3.0.3]: https://github.com/k9barry/viavi/releases/tag/v3.0.3
[3.0.2]: https://github.com/k9barry/viavi/releases/tag/v3.0.2
[3.0.1]: https://github.com/k9barry/viavi/releases/tag/v3.0.1
[2.1.0]: https://github.com/k9barry/viavi/releases/tag/v2.1.0
[1.0.0]: https://github.com/k9barry/viavi/releases/tag/v1.0.0
