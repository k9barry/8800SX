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
$password = trim(file_get_contents(getenv("DB_PASSWORD_FILE")));
$database = "viavi";

$connection = new mysqli($host, $username, $password, $database);

if ($connection->connect_error) {
    error_log("Database connection failed: " . $connection->connect_error);
    die("Database connection failed. Please try again later.");
}

// Set charset to prevent character set confusion attacks
if (!$connection->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $connection->error);
    die("Database configuration error.");
}
