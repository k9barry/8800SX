<?php
$server = "db";
$username = "viavi";
$password = rtrim(file_get_contents("/run/secrets/db_password"));
$database = "viavi";
$connection = mysqli_connect("$server","$username","$password");
$select_db = mysqli_select_db($connection, $database);
if(!$select_db)
{
	echo("connection terminated");
}
?>
