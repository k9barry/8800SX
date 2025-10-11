# GitHub Copilot Instructions for Viavi 8800SX Project

## Project Overview
This is a Docker Compose project that processes output files from a Viavi 8800SX service monitor, parses them, and stores the data in a MySQL database. The application also stores service records as BLOBs in the database.

## Technology Stack
- **Backend**: PHP 8.3.2-FPM
- **Database**: MySQL
- **Web Server**: Nginx (via Docker Compose)
- **Frontend**: Bootstrap 4.5.0, jQuery 3.5.1
- **Containerization**: Docker & Docker Compose

## Code Style and Conventions

### PHP Guidelines
1. **Security First**: Always use prepared statements for database queries
2. **Input Validation**: Validate and sanitize all user inputs
3. **File Uploads**: 
   - Always validate file types using MIME types, not just extensions
   - Check file sizes against configured limits
   - Store uploaded files outside the web root when possible
4. **Error Handling**: Use try-catch blocks and log errors appropriately
5. **Documentation**: Add PHPDoc comments for all functions and classes

### Database Interactions
1. Always use parameterized queries (prepared statements)
2. Never concatenate user input into SQL queries
3. Use appropriate data types in the schema
4. Handle database connection errors gracefully

### File Structure
```
/data/web/
├── nginx.conf         # Nginx configuration
└── app/
    ├── config.php     # Application configuration & DB connection
    ├── main.php       # File upload handler
    ├── upload.php     # Upload UI
    ├── alignments-*.php  # CRUD operations
    └── locales/       # Internationalization files
/data/db/init/
└── init-db.sql        # Database initialization script
```

## Security Considerations

### Critical Security Rules
1. **Never hardcode credentials** - Use environment variables
2. **Validate all inputs** - Both client and server-side
3. **Use CSRF tokens** - Protect all forms
4. **Implement rate limiting** - Prevent abuse
5. **Keep dependencies updated** - Regular security patches
6. **Use HTTPS** - In production environments
7. **Set security headers** - CSP, X-Frame-Options, etc.

### File Upload Security
- Validate MIME types server-side
- Restrict file extensions using allowlist
- Set maximum file size limits
- Scan uploaded files for malware if possible
- Store files with unique names to prevent overwrites

## Development Workflow

### Before Making Changes
1. Review existing code patterns
2. Check for similar implementations
3. Consider security implications
4. Update documentation if needed

### Testing
1. Test file uploads with various file types
2. Verify database operations don't have SQL injection vulnerabilities
3. Test with invalid inputs to ensure proper error handling
4. Check that uploaded files are processed correctly

### Docker Commands
```bash
# Start services
docker compose up -d

# View logs
docker compose logs -f

# Rebuild after changes
docker compose down
docker compose build --no-cache
docker compose up -d

# Access PHP container
docker compose exec web bash
```

## Common Patterns

### Database Query Pattern
```php
// Correct - Using prepared statements
$stmt = $connection->prepare("SELECT * FROM alignments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
```

### Input Validation Pattern
```php
// Validate and sanitize input
$filename = basename($_FILES['file']['name']);
$path_info = pathinfo($filename);
$extension = strtolower($path_info['extension']);

// Check against allowlist
$allowed_extensions = ['txt'];
if (!in_array($extension, $allowed_extensions)) {
    throw new Exception("Invalid file type");
}

// Validate MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $_FILES['file']['tmp_name']);
finfo_close($finfo);

if ($mime_type !== 'text/plain') {
    throw new Exception("Invalid file format");
}
```

### Error Handling Pattern
```php
try {
    // Database operation
    $stmt = $connection->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error: " . $connection->error);
    }
    // ... execute query
} catch (Exception $e) {
    error_log($e->getMessage());
    // Show user-friendly message
    echo "An error occurred. Please try again.";
}
```

## Internationalization (i18n)
- Use the `translate()` helper function for all user-facing text
- Add translations to appropriate locale files in `/data/web/app/locales/`
- Support languages: en, es, fr, de, it, pt, nl, ru, cn, jp, in, cz

## Environment Variables
- `DB_PASSWORD_FILE`: Path to file containing database password
- Configure in `docker-compose.yml`

## Debugging Tips
1. Check Docker logs: `docker compose logs web`
2. Check PHP errors in container: `docker compose exec web tail -f /var/log/php-errors.log`
3. Enable error reporting in development (disable in production)
4. Use `var_dump()` and `error_log()` for debugging

## Performance Considerations
1. Limit database queries in loops
2. Use appropriate indexes on database tables
3. Optimize file upload sizes (currently limited to 128MB)
4. Consider caching for frequently accessed data

## Code Review Checklist
- [ ] SQL queries use prepared statements
- [ ] User inputs are validated and sanitized
- [ ] File uploads are properly validated
- [ ] Errors are handled gracefully
- [ ] Security best practices are followed
- [ ] Code is documented
- [ ] No sensitive data is hardcoded
- [ ] Changes are backwards compatible

## Additional Resources
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Docker Best Practices](https://docs.docker.com/develop/dev-best-practices/)
