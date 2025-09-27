<?php
/**
 * Search results file results.php
 * 
 * The results file takes the POST value from the upload.php page
 * and searched the DB for a %LIKE% serial (number) value and returns the results
 */
include('connection.php');
include('app/helpers.php');
header("Content-type: text/plain");

if (isset($_REQUEST['serial']) && !empty($_REQUEST['serial'])) {
  // Check rate limiting for searches (max 20 searches per hour)
  if (!checkRateLimit('search', 20, 3600)) {
    echo "Too many search attempts. Please wait before trying again.";
    exit;
  }
  
  $serial = trim($_POST['serial']);
  
  // Validate input length to prevent abuse
  if (strlen($serial) > 100) {
    echo "Search term too long. Please use a shorter search term.";
    exit;
  }
  
  // Use prepared statement to prevent SQL injection
  $query = "SELECT * FROM alignments WHERE serial LIKE ?";
  $stmt = $connection->prepare($query);
  
  if (!$stmt) {
    error_log("Failed to prepare search query: " . $connection->error);
    echo "Search temporarily unavailable. Please try again later.";
    exit;
  }
  
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
