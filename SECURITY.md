# Security Policy

## Supported Versions

We release security updates for the following versions:

| Version | Supported          |
| ------- | ------------------ |
| latest  | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

We take the security of the Viavi 8800SX Database project seriously. If you have discovered a security vulnerability, we appreciate your help in disclosing it to us in a responsible manner.

### How to Report a Security Vulnerability

**Please do NOT report security vulnerabilities through public GitHub issues.**

Instead, please report them via one of the following methods:

1. **GitHub Security Advisories** (Preferred)
   - Go to the repository's Security tab
   - Click "Report a vulnerability"
   - Fill out the advisory form with details

2. **Email**
   - Send an email to the repository maintainers
   - Include "[SECURITY]" in the subject line
   - Provide detailed information about the vulnerability

### What to Include in Your Report

Please include the following information in your report:

- **Type of vulnerability** (e.g., SQL injection, XSS, file upload bypass)
- **Location** of the affected code (file path and line numbers)
- **Step-by-step instructions** to reproduce the issue
- **Proof of concept** or exploit code (if applicable)
- **Potential impact** of the vulnerability
- **Suggested fix** (if you have one)

### Response Timeline

- **Initial Response**: Within 48 hours
- **Status Update**: Within 7 days
- **Fix Timeline**: Depends on severity (see below)

### Severity Levels

We use the CVSS (Common Vulnerability Scoring System) to assess severity:

- **Critical (9.0-10.0)**: Fix within 1-7 days
- **High (7.0-8.9)**: Fix within 7-30 days
- **Medium (4.0-6.9)**: Fix within 30-90 days
- **Low (0.1-3.9)**: Fix as time permits

## Security Best Practices for Users

If you're deploying this application, please follow these security guidelines:

### 1. Environment Configuration

- **Change default passwords**: Update `/secrets/db_password.txt` with a strong password
- **Use environment variables**: Never hardcode sensitive data
- **Enable HTTPS**: Use SSL/TLS certificates in production
- **Restrict network access**: Use firewalls and security groups

### 2. File Upload Security

- **Validate file types**: The application only accepts `.txt` files
- **Monitor upload directory**: Regularly check `/data/web/uploads/` for suspicious files
- **Set file size limits**: Default is 128MB, adjust based on needs
- **Scan uploads**: Consider implementing antivirus scanning for uploaded files

### 3. Database Security

- **Use strong passwords**: Minimum 16 characters with mixed case, numbers, and symbols
- **Limit database access**: Only the application should have direct database access
- **Regular backups**: Implement automated backup procedures
- **Update regularly**: Keep MySQL version up to date

### 4. Docker Security

- **Keep images updated**: Regularly pull latest base images
- **Scan for vulnerabilities**: Use `docker scan` to check images
- **Limit container privileges**: Don't run containers as root
- **Use secrets management**: Docker secrets or external secret managers

### 5. Application Security

- **Keep dependencies updated**: Regularly update PHP, libraries, and frameworks
- **Monitor logs**: Check application and web server logs for suspicious activity
- **Implement rate limiting**: Prevent brute force and DoS attacks
- **Regular security audits**: Periodically review code and configuration

## Known Security Considerations

### Current Implementation

1. **File Upload Validation**
   - Currently validates file extensions (`.txt` only)
   - Recommendation: Add MIME type validation for additional security

2. **CSRF Protection**
   - Forms do not currently implement CSRF tokens
   - Recommendation: Implement CSRF token validation for all state-changing operations

3. **Input Sanitization**
   - Basic sanitization is implemented
   - Recommendation: Implement comprehensive input validation framework

4. **Password Storage**
   - Database password stored in plaintext file
   - Current approach: File-based secrets (suitable for Docker)
   - Production recommendation: Use Docker secrets or external secret manager

5. **SQL Injection Protection**
   - Prepared statements are used for database queries
   - Status: ✅ Protected

## Security Updates

Security updates will be announced via:
- GitHub Security Advisories
- Release notes
- CHANGELOG.md

## Acknowledgments

We would like to thank the following individuals for responsibly disclosing security vulnerabilities:

*(This section will be updated as vulnerabilities are reported and fixed)*

## Disclosure Policy

- We follow a **coordinated disclosure** model
- We will work with you to understand and validate the vulnerability
- We will develop a fix and release it before public disclosure
- We will credit you in our security advisories (unless you prefer to remain anonymous)

## Security Hardening Checklist

For production deployments, ensure the following:

- [ ] Changed default database password
- [ ] Enabled HTTPS with valid SSL certificate
- [ ] Configured firewall rules (only expose necessary ports)
- [ ] Disabled PHP error display (`display_errors = Off`)
- [ ] Enabled PHP error logging
- [ ] Set appropriate file permissions (no world-writable files)
- [ ] Implemented rate limiting on upload endpoints
- [ ] Regular security updates applied
- [ ] Database backups configured
- [ ] Monitoring and alerting configured
- [ ] Security headers configured (CSP, X-Frame-Options, etc.)

## Contact

For security-related questions or concerns, please contact the repository maintainers through the repository's issue tracker or security advisory system.

---

**Last Updated**: 2025-10-10

Thank you for helping keep the Viavi 8800SX Database project secure!
