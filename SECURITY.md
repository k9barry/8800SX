# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| main    | :white_check_mark: |
| < main  | :x:                |

## Reporting a Vulnerability

If you discover a security vulnerability in the 8800SX project, please report it responsibly by following these steps:

### How to Report

1. **Do NOT** create a public issue for security vulnerabilities
2. Send an email to the repository maintainer through GitHub's private vulnerability reporting feature
3. Include the following information:
   - Description of the vulnerability
   - Steps to reproduce the issue
   - Potential impact assessment
   - Any suggested fixes (if available)

### What to Expect

- **Response Time**: We aim to acknowledge receipt within 48 hours
- **Investigation**: Security reports will be investigated within 7 days
- **Updates**: You will receive regular updates on the progress
- **Resolution**: Critical vulnerabilities will be patched within 30 days
- **Disclosure**: We follow coordinated disclosure practices

## Security Measures Implemented

### Application Security

- **SQL Injection Prevention**: All database queries use prepared statements with proper parameter binding
- **CSRF Protection**: All forms include CSRF tokens to prevent cross-site request forgery attacks
- **XSS Prevention**: All user inputs are properly escaped using htmlspecialchars() and urlencode()
- **File Upload Security**: 
  - File type validation (only .txt files allowed)
  - MIME type validation for uploaded files
  - File size limits (10MB maximum)
  - Filename sanitization to prevent directory traversal
  - Upload directory protection with index.php
- **Input Validation**: All user inputs are sanitized and validated with length restrictions
- **Output Encoding**: HTML special characters are properly escaped
- **Error Handling**: Database errors are logged, not exposed to users
- **Rate Limiting**: Basic rate limiting for uploads (5/hour) and searches (20/hour)
- **Session Security**: Secure cookie settings with HttpOnly and SameSite attributes

### Infrastructure Security

- **Container Security**: 
  - Non-root user execution in containers
  - Alpine Linux base images for minimal attack surface
  - Health checks for service monitoring
- **Network Security**: 
  - Internal network isolation
  - No direct database access from outside
  - Proper port exposure (only 8080 for web interface)
- **Secret Management**: 
  - Database passwords stored in Docker secrets (multi-container) or environment variables (unified container)
  - No hardcoded credentials in source code
  - `.env` file in `.gitignore` to prevent accidental commits

### Database Security

- **Authentication**: MySQL user with limited privileges
- **Random Root Password**: Root password is randomly generated
- **Network Isolation**: Database only accessible within Docker network
- **Data Validation**: Input validation before database operations

## Security Best Practices for Users

### Deployment Security

1. **Change Default Password**: 
   - For multi-container setup: Update `secrets/db_password.txt` with a strong password
   - For unified container: Copy `.env.example` to `.env` and set a secure `DB_PASSWORD`
2. **Network Security**: Deploy behind a reverse proxy (nginx, Traefik, etc.)
3. **HTTPS**: Enable SSL/TLS for production deployments
4. **Firewall**: Restrict access to port 8080 to authorized networks only
5. **Updates**: Keep Docker images and dependencies updated
6. **Monitoring**: Implement logging and monitoring for suspicious activities

### File Security

1. **File Validation**: Only upload files from trusted sources
2. **Regular Cleanup**: Monitor the uploads directory for unusual files
3. **Backup**: Regularly backup your database and uploaded files
4. **Access Control**: Limit who can access the upload interface

## Known Security Considerations

1. **File Storage**: Uploaded files are stored in the database as BLOBs and in the filesystem
2. **Authentication**: Currently no built-in user authentication system
3. **Rate Limiting**: Basic rate limiting implemented (may need enhancement for production)
4. **Audit Logging**: Basic error logging only, consider adding comprehensive audit trails
5. **Session Management**: Secure session handling implemented with proper cookie settings

## Security Updates

Security updates will be:
- Clearly marked in release notes
- Tagged with security labels
- Announced through GitHub security advisories
- Documented in the changelog

## Contact

For security-related questions or concerns, please use GitHub's security advisory feature or contact the maintainers through official channels.

---

**Note**: This is a development/testing application. For production use, consider additional security measures such as:
- Web Application Firewall (WAF)
- Intrusion Detection System (IDS)
- Regular security audits
- User authentication and authorization
- Enhanced logging and monitoring