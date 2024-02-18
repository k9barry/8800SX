<?php

// Set mysql connect variables
$host = "db";
$username = "viavi";
$password = "8800SX";
$database = "viavi";

$connection = new mysqli($host, $username, $password, $database);

if ($connection->connect_error) {
	die($connection->connect_error);
}
