<?php
include('connection.php');
header("Content-type: text/plain");
if ($_REQUEST['serial']) {
	$serial = $_POST['serial'];

	if (empty($serial)) {
		$make = 'You must enter a serial number to search!';
	} else {
		$make = 'No match found!';
		$query = "SELECT * FROM alignments WHERE serial LIKE '%$serial%'";
		$result = mysqli_query($connection, $query);

		if ($row = mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				echo "*************************************************************************************************************************\r\n";
				echo "Id: " . $row['id'] . "\r\n";
				echo "Serial: " . $row['serial'] . "\r\n";
				echo "Model: " . $row['model'] . "\r\n";
				echo "Alignment Date " . $row['datetime'] . "\r\n";
				echo "Results: " . "\r\n";
				echo "" . $row["file"] . "\r\n";
				echo "\r\n";
				echo "\r\n";
			}
		} else {
			echo "Search Result \r\n";

			print($make);
		}
		mysqli_free_result($result);
		mysqli_close($connection);
	}
}
