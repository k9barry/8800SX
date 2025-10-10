<?php
/**
 * Security Headers
 * 
 * This file sets security headers for all pages to protect against common vulnerabilities.
 * Include this file at the top of each page that needs security headers.
 */

// Prevent clickjacking attacks
header("X-Frame-Options: SAMEORIGIN");

// Prevent MIME type sniffing
header("X-Content-Type-Options: nosniff");

// Enable XSS protection in browsers
header("X-XSS-Protection: 1; mode=block");

// Referrer policy - only send referrer to same origin
header("Referrer-Policy: strict-origin-when-cross-origin");

// Content Security Policy (adjust as needed for your application)
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://code.jquery.com https://cdn.jsdelivr.net https://stackpath.bootstrapcdn.com https://kit.fontawesome.com; style-src 'self' 'unsafe-inline' https://stackpath.bootstrapcdn.com; font-src 'self' https://kit.fontawesome.com https://ka-f.fontawesome.com; img-src 'self' data: https:;");

// Permissions Policy (formerly Feature Policy)
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// Strict Transport Security (HSTS) - only enable this if using HTTPS
// Uncomment the line below when deploying with HTTPS
// header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

?>
