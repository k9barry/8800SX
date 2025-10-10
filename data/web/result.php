<?php
/**
 * Search results file results.php
 * 
 * The results file takes the POST value from the upload.php page
 * and searched the DB for a %LIKE% serial (number) value and returns the results
 */
include('connection.php');
header("Content-type: text/plain");
if ($_REQUEST['serial']) {
  $serial = $_POST['serial'];

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
    echo "Search Result:  \r\n";
    echo "\r\n";
    echo "No serial number found";
    echo "\r\n";
  }
  mysqli_free_result($result);
  mysqli_close($connection);
}
