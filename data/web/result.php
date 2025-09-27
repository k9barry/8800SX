<?php
/**
 * Search results file results.php
 * 
 * The results file takes the POST value from the upload.php page
 * and searched the DB for a %LIKE% serial (number) value and returns the results
 */
include('connection.php');
header("Content-type: text/plain");

if (isset($_REQUEST['serial']) && !empty($_REQUEST['serial'])) {
  $serial = trim($_POST['serial']);
  
  // Use prepared statement to prevent SQL injection
  $query = "SELECT * FROM alignments WHERE serial LIKE ?";
  $stmt = $connection->prepare($query);
  $search_term = '%' . $serial . '%';
  $stmt->bind_param("s", $search_term);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      echo "*************************************************************************************************************************\r\n";
      echo "Id: " . htmlspecialchars($row['id']) . "\r\n";
      echo "Serial: " . htmlspecialchars($row['serial']) . "\r\n";
      echo "Model: " . htmlspecialchars($row['model']) . "\r\n";
      echo "Alignment Date " . htmlspecialchars($row['datetime']) . "\r\n";
      echo "Results: " . "\r\n";
      echo htmlspecialchars($row["file"]) . "\r\n";
      echo "\r\n";
      echo "\r\n";
    }
  } else {
    echo "Search Result:  \r\n";
    echo "\r\n";
    echo "No serial number found";
    echo "\r\n";
  }
  $stmt->close();
  $connection->close();
}
