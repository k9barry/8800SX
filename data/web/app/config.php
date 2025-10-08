<?php

// Database configuration - support both environment variables and file-based config
$db_server              = getenv('DB_HOST') ?: 'viavi-db';
$db_name                = getenv('DB_NAME') ?: 'viavi';
$db_user                = getenv('DB_USER') ?: 'viavi';

// Get database password from environment variable or file
$db_password = getenv('DB_PASSWORD');
if ($db_password === false) {
    // Fallback to file-based password for backward compatibility
    $db_password_file = getenv("DB_PASSWORD_FILE");
    if ($db_password_file === false) {
        die("Error: Neither DB_PASSWORD nor DB_PASSWORD_FILE environment variable is set.");
    }
    if (!file_exists($db_password_file)) {
        die("Error: Database password file not found at: " . htmlspecialchars($db_password_file));
    }
    $db_password = trim(file_get_contents($db_password_file));
    if ($db_password === false || $db_password === '') {
        die("Error: Failed to read database password from file.");
    }
}

$no_of_records_per_page = '10';
$appname                = '8800SX';
$language               = 'en';
$translations           = include("locales/$language.php");


$upload_max_size        = 5000000; // default 5MB
$upload_target_dir      = "uploads/"; // relative to core/app
$upload_persistent_dir  = true; // Do not delete uploads folder when regenerating CRUD files
$upload_disallowed_exts = array(
    'php', 'php3', 'php4', 'php5', 'php7', 'phtml', // PHP and PHP-like files
    'html', 'htm', 'js', 'jsp', 'asp', 'aspx',      // HTML, JavaScript, and Server-side scripts
    'exe', 'bat', 'sh', 'bin',                      // Executable and shell script files
    'sql', 'sqlite', 'db',                          // Database files
    'htaccess', 'htpasswd',                         // Apache server files
    'pl', 'py', 'cgi',                              // Script files (Perl, Python, CGI)
    'jar', 'war', 'ear',                            // Java archives
    'vbs', 'ps1', 'psm1',                           // Script files (VBScript, PowerShell)
    'wsf', 'scf',                                   // Windows Script files
    'reg',                                          // Registry files
    'swf',                                          // Adobe Flash files
    'lnk',                                          // Windows shortcut files
);


$protocol               = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
$domain                 = $protocol . '://' . $_SERVER['HTTP_HOST']; // Replace domain with your domain name. (Locally typically something like localhost)

try {
    $link = mysqli_connect($db_server, $db_user, $db_password, $db_name);
} catch (mysqli_sql_exception $e) {
    $error_msg = $e->getMessage();
    // Provide more helpful error message for common socket issues
    if (strpos($error_msg, 'No such file or directory') !== false) {
        error_log("Database connection failed: MySQL socket not available. The database may still be initializing.");
        die("Database connection failed: The database is still starting up. Please wait a moment and refresh the page.");
    }
    error_log("Database connection failed: " . $error_msg);
    die("Database connection failed: " . htmlspecialchars($error_msg));
}
if (!$link) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Database connection failed: " . htmlspecialchars(mysqli_connect_error()));
}

$query = "SHOW VARIABLES LIKE 'character_set_database'";
if ($result = mysqli_query($link, $query)) {
    while ($row = mysqli_fetch_row($result)) {
        if (!$link->set_charset($row[1])) {
            printf("Error loading character set %s: %s\n", $row[1], $link->error);
            exit();
        } else {
            // printf("Current character set: %s", $link->character_set_name());
        }
    }
}

?>
