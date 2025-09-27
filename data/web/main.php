<?php
/**
 * The main.php file does the uploading of the files.
 * 
 * mySql table entry order:
 * 
 * 1. String $datetime Datetime entry of when viavi test was ran
 * 2. String $model Model of radio tested
 * 3. String $serial Serial Number of Model tested
 * 4. String $file_contents Text file of the results of testing
 * 5. String $filename[$i] Name of the saved file
 * 
 * The order of events is:
 * 1. ./uploads folder is cleared of contents (unlink)
 * 2. Load the connection.php file to connect to DB
 * 3. POST from upload.php page calls main.php to upload all selected files
 * 4. SET and PREPARE the INSERT statement
 * 5. Array $db Get all filenames in the DB and place in array
 * 6. Loop thru upladed files and get info to put into DB
 * 7. Skip all files not ending in .txt add $msg stating such
 * 8. Save the remaining files to the upload folder
 * 9. Check if uploaded filename is not in array of DB names,
 *      if it is then file is a duplicate and can be skipped,
 *      add $msg stating such
 * 10. If the filename is NOT in the DB we need to add it,
 *      EXPLODE the $filename[$i] at the "-" to get the $filevalue
 * 11. Fix the datetime from the filename to insert into DB
 * 12. Set Strings to insert into DB
 * 13. Bind the mySql statement and execute it to enter into DB
 * 
 */
$count = 0;  //Get count of successfully uploaded records
$msg = "";
$dirname = "uploads";

// Ensure uploads directory exists and is secure
if (!is_dir($dirname)) {
    mkdir($dirname, 0755, true);
}

// Add security: prevent directory traversal and add index.php to prevent directory listing
$index_file = $dirname . "/index.php";
if (!file_exists($index_file)) {
    file_put_contents($index_file, "<?php header('HTTP/1.0 403 Forbidden'); exit; ?>");
}

array_map('unlink', glob("$dirname/*")); // Remove all files from upload folder
include('connection.php');

// Set INSERT statement and prepare
$sql = "INSERT INTO alignments (datetime, model, serial, file, filename) VALUES (?, ?, ?, ?, ?)";
$statement = $connection->prepare($sql);

if (!$statement) {
    die("Prepare failed: " . $connection->error);
}

// Get all filenames from DB and place in array $db using prepared statement
$sqlFind = 'SELECT `filename` FROM `alignments`';
$stmt = $connection->prepare($sqlFind);
if (!$stmt) {
    die("Prepare failed: " . $connection->error);
}
$stmt->execute();
$result = $stmt->get_result();
$db = []; // create empty array
while ($row = $result->fetch_row()) {
  array_push($db, $row[0]);
}
$stmt->close();

if (isset($_REQUEST['file-upload'])) {
  // Validate that files were actually uploaded
  if (!isset($_FILES['multiple_files']) || !is_array($_FILES['multiple_files']['name'])) {
    $msg .= "No files were uploaded.<br>";
  } else {
    // Loop thru upladed files and get info to put into DB
    for ($i = 0; $i < count($_FILES['multiple_files']['name']); $i++) {
      // Skip empty file uploads
      if ($_FILES['multiple_files']['error'][$i] == UPLOAD_ERR_NO_FILE) {
        continue;
      }
      
      // Check for upload errors
      if ($_FILES['multiple_files']['error'][$i] !== UPLOAD_ERR_OK) {
        $msg .= "Upload error for file " . htmlspecialchars($_FILES['multiple_files']['name'][$i]) . "<br>";
        continue;
      }
      
      // Sanitize filename to prevent directory traversal
      $original_filename = $_FILES['multiple_files']['name'][$i];
      $filename[] = basename($original_filename);
      $filename[$i] = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename[$i]); // Remove dangerous characters
      
      // Validate filename length
      if (strlen($filename[$i]) > 255) {
        $msg .= "Filename too long: " . htmlspecialchars($original_filename) . "<br>";
        continue;
      }

      //Skip all files not ending in .txt
      $path_part = pathinfo($filename[$i]);
      $path_ext = strtolower($path_part['extension'] ?? '');
      if ($path_ext !== "txt") {
        $msg .= "File " . htmlspecialchars($filename[$i]) . " does not end in '.txt' unable to upload<br>";
      } else {
        // Validate file size (e.g., max 10MB)
        if ($_FILES['multiple_files']['size'][$i] > 10 * 1024 * 1024) {
          $msg .= "File " . htmlspecialchars($filename[$i]) . " is too large (max 10MB)<br>";
          continue;
        }

        // Save the remaining files to the upload folder
        $tempname = $_FILES['multiple_files']['tmp_name'][$i];
        $targetpath = $dirname . "/" . $filename[$i];
        
        // Additional security: verify it's actually uploaded file
        if (!is_uploaded_file($tempname)) {
          $msg .= "Security error: File " . htmlspecialchars($filename[$i]) . " was not properly uploaded<br>";
          continue;
        }
        
        if (!move_uploaded_file($tempname, $targetpath)) {
          $msg .= "Failed to move uploaded file: " . htmlspecialchars($filename[$i]) . "<br>";
          continue;
        }

        // Check if uploaded filename is not in array of DB names
        if (!in_array($filename[$i], $db)) {
          // Validate file content is actually text
          $file_contents = file_get_contents($targetpath);
          if ($file_contents === false) {
            $msg .= "Error reading file: " . htmlspecialchars($filename[$i]) . "<br>";
            unlink($targetpath); // Clean up
            continue;
          }
          
          // Validate filename format (should contain hyphens for parsing)
          if (substr_count($filename[$i], '-') < 3) {
            $msg .= "File " . htmlspecialchars($filename[$i]) . " does not have expected format (model-serial-date-time.txt)<br>";
            unlink($targetpath); // Clean up
            continue;
          }

          // Fix the datetime from the filename to insert into DB
          $filevalue = explode('-', $filename[$i]);
          
          // Validate we have enough parts
          if (count($filevalue) < 4) {
            $msg .= "File " . htmlspecialchars($filename[$i]) . " does not have expected format<br>";
            unlink($targetpath); // Clean up
            continue;
          }
          
          $check_filename = substr($filevalue[2], 0, 2);
          if ($check_filename <> "20") {
            $month = substr($filevalue[2], 0, 2);
            $day = substr($filevalue[2], 2, 2);
            $year = substr($filevalue[2], 4, 4);
          } else {
            $month = substr($filevalue[2], 4, 2);
            $day = substr($filevalue[2], 6, 2);
            $year = substr($filevalue[2], 0, 4);
          }
          
          // Extract time parts with validation
          $time_part = str_replace('.txt', '', $filevalue[3]);
          if (strlen($time_part) < 6) {
            $msg .= "File " . htmlspecialchars($filename[$i]) . " has invalid time format<br>";
            unlink($targetpath); // Clean up
            continue;
          }
          
          $hour = substr($time_part, 0, 2);
          $minute = substr($time_part, 2, 2);
          $second = substr($time_part, 4, 2);

          // Validate date/time values
          if (!checkdate($month, $day, $year) || $hour > 23 || $minute > 59 || $second > 59) {
            $msg .= "File " . htmlspecialchars($filename[$i]) . " has invalid date/time values<br>";
            unlink($targetpath); // Clean up
            continue;
          }

          // Set Strings to insert into DB
          $datetime = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $year, $month, $day, $hour, $minute, $second);
          $model = htmlspecialchars($filevalue[0]);
          $serial = htmlspecialchars($filevalue[1]);

          // Bind the statement and execute with error handling
          if (!$statement->bind_param("sssss", $datetime, $model, $serial, $file_contents, $filename[$i])) {
            $msg .= "Bind failed for file " . htmlspecialchars($filename[$i]) . ": " . $statement->error . "<br>";
            unlink($targetpath); // Clean up
            continue;
          }
          
          if (!$statement->execute()) {
            $msg .= "Execute failed for file " . htmlspecialchars($filename[$i]) . ": " . $statement->error . "<br>";
            unlink($targetpath); // Clean up
            continue;
          }

          $count++;
          $msg .= "<font color='green'>File " . htmlspecialchars($filename[$i]) . " uploaded successfully</font><br>";
        } else {
          $msg .= "File " . htmlspecialchars($filename[$i]) . " already exists in DB unable to upload<br>";
          unlink($targetpath); // Clean up duplicate
        }
      }
    }
  }
}
if ($count > 0) {
  $msg .= "<br><font color='green'>!!!!! " . $count . " files uploaded successfully to the DB !!!!!</font><br>";
}

// Clean up prepared statement
if ($statement) {
    $statement->close();
}
$connection->close();
?>
