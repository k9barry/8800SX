<?php
$server = "db";
$username = "viavi";
$password = "8800SX";
$database = "viavi";
$connection = mysqli_connect("$server","$username","$password");
$select_db = mysqli_select_db($connection, $database);
if(!$select_db)
{
	echo("connection terminated");
}
?>
