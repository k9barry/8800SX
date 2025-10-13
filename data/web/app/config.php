<?php

/**
 * Configuration file for Viavi 8800SX application
 *
 * This file contains all configuration variables and their values.
 *
 * @author Viavi 8800SX
 */

// ============================================================================
// DATABASE CONFIGURATION
// ============================================================================

/**
 * Database server hostname
 */
$db_server = 'db';

/**
 * Database name
 */
$db_name = 'viavi';

/**
 * Database username
 */
$db_user = 'viavi';

/**
 * Database password (loaded from file for security)
 */
$passwordFile = getenv("DB_PASSWORD_FILE");
if (! $passwordFile) {
    throw new Exception("DB_PASSWORD_FILE environment variable is not set");
}
if (! file_exists($passwordFile)) {
    throw new Exception("Password file not found: " . $passwordFile);
}
$db_password = trim(file_get_contents($passwordFile));

// ============================================================================
// APPLICATION CONFIGURATION
// ============================================================================

/**
 * Application name displayed in the UI
 */
$appname = '8800SX';

/**
 * Current language code (e.g., 'en', 'es', 'fr')
 */
$language = 'en';

/**
 * Number of records to display per page in list views
 */
$no_of_records_per_page = 10;

/**
 * HTTP or HTTPS protocol for the current request
 */
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';

/**
 * Full domain URL including protocol (e.g., 'https://example.com')
 */
$domain = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

// ============================================================================
// FILE UPLOAD CONFIGURATION
// ============================================================================

/**
 * Maximum file size allowed for uploads (in bytes)
 * Default: 5MB
 */
$upload_max_size = 5000000;

/**
 * Target directory for uploaded files (relative to app directory)
 */
$upload_target_dir = "uploads/";

/**
 * Whether to keep the uploads directory when regenerating CRUD files
 */
$upload_persistent_dir = true;

/**
 * Array of file extensions that are not allowed for upload
 * Includes executable files, scripts, and potentially dangerous file types
 */
$upload_disallowed_exts = [
    'php', 'php3', 'php4', 'php5', 'php7', 'phtml',  // PHP and PHP-like files
    'html', 'htm', 'js', 'jsp', 'asp', 'aspx',       // HTML, JavaScript, and Server-side scripts
    'exe', 'bat', 'sh', 'bin',                       // Executable and shell script files
    'sql', 'sqlite', 'db',                           // Database files
    'htaccess', 'htpasswd',                          // Apache server files
    'pl', 'py', 'cgi',                               // Script files (Perl, Python, CGI)
    'jar', 'war', 'ear',                             // Java archives
    'vbs', 'ps1', 'psm1',                            // Script files (VBScript, PowerShell)
    'wsf', 'scf',                                    // Windows Script files
    'reg',                                           // Registry files
    'swf',                                           // Adobe Flash files
    'lnk',                                           // Windows shortcut files
];

// ============================================================================
// DATABASE CONNECTION
// ============================================================================

/**
 * Database connection object (mysqli)
 * Used by all CRUD files for database operations
 */
$link = mysqli_connect($db_server, $db_user, $db_password, $db_name);

if (! $link) {
    throw new Exception("Database connection failed: " . mysqli_connect_error());
}

// Set character set
$query = "SHOW VARIABLES LIKE 'character_set_database'";
if ($result = mysqli_query($link, $query)) {
    while ($row = mysqli_fetch_row($result)) {
        if (! $link->set_charset($row[1])) {
            throw new Exception(
                "Error loading character set " .
                htmlspecialchars($row[1]) . ": " .
                htmlspecialchars($link->error)
            );
        }
    }
}

// ============================================================================
// TRANSLATIONS
// ============================================================================

/**
 * Validate language code to prevent path traversal
 */
if (! preg_match('/^[a-z]{2}$/', $language)) {
    throw new Exception("Invalid language code: " . $language);
}

/**
 * Translation strings for the current language
 * Used by the translate() helper function
 */
$localeFile = __DIR__ . "/locales/{$language}.php";
if (! file_exists($localeFile)) {
    throw new Exception("Translation file not found: " . $localeFile);
}
$translations = include($localeFile);
