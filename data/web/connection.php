<?php

// Set mysql connect variables
$host = "db";
$username = "viavi";
$password = trim(file_get_contents(getenv("DB_PASSWORD_FILE")));
$database = "viavi";

$connection = new mysqli($host, $username, $password, $database);

if ($connection->connect_error) {
	die($connection->connect_error);
}
