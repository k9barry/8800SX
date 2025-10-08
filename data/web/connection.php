<?php
/**
 * Connection file connection.php
 * 
 * Set mysql connection variables to connect to mySql DB
 * 
 * 1. String $host Host container name typically "db"
 * 2.String $username Username set when mySql DB was created
 * 3. String $password Password set in secrets/db_password.txt file
 * 4. String $database Database name set when mySql DB was created
 * 
 */

$host = "db";
$username = "viavi";

// Get database password from environment variable
$db_password_file = getenv("DB_PASSWORD_FILE");
if ($db_password_file === false) {
    die("Error: DB_PASSWORD_FILE environment variable is not set.");
}
if (!file_exists($db_password_file)) {
    die("Error: Database password file not found at: " . htmlspecialchars($db_password_file));
}
$password = trim(file_get_contents($db_password_file));
if ($password === false || $password === '') {
    die("Error: Failed to read database password from file.");
}

$database = "viavi";

try {
    $connection = new mysqli($host, $username, $password, $database);
} catch (mysqli_sql_exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

if ($connection->connect_error) {
    error_log("Database connection failed: " . $connection->connect_error);
    die("Database connection failed. Please try again later.");
}

// Set charset to prevent character set confusion attacks
if (!$connection->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $connection->error);
    die("Database configuration error.");
}
