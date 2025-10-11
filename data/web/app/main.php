<?php
error_log('[DEBUG] main.php started');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php-errors.log');


/**
 * The main.php file does the uploading of the files.
 * 
 * mySql table entry order::
 * 
 * 1. String $datetime Datetime entry of when viavi test was ran
 * 2. String $model Model of radio tested
 * 3. String $serial Serial Number of Model tested
 * 4. String $file_contents Text file of the results of testing
 * 5. String $filename[$i] Name of the saved file
 * 
 * The order of events is:
 * 1. ./uploads folder is cleared of contents (unlink)
 * 2. Load the config.php file to connect to DB
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

// Create uploads directory if it doesn't exist
if (!file_exists($dirname)) {
    mkdir($dirname, 0766, true);
    error_log("[DEBUG] Created uploads directory: " . $dirname);
    // Write a dummy index file to prevent directory listing
    file_put_contents($dirname . '/index.php', '');
}

array_map('unlink', glob("$dirname/*")); // Remove all files from upload folder
require_once('config.php');

    // Set INSERT statement and prepare
    $sql = "INSERT INTO alignments (datetime, model, serial, file, filename) VALUES (?, ?, ?, ?, ?)";
    $statement = $link->prepare($sql);

    // Get all filenames from DB and place in array $db
    $sqlFind = 'SELECT `filename` FROM `alignments`';
    $result = mysqli_query($link, $sqlFind);
$db = []; // create empty array
    while ($row = mysqli_fetch_row($result)) {
  array_push($db, $row[0]);
    }

    if (isset($_REQUEST['file-upload'])) {

  // Loop thru upladed files and get info to put into DB
    for ($i = 0; $i < count($_FILES['multiple_files']['name']); $i++) {
      $filename[] = basename($_FILES['multiple_files']['name'][$i]);
      error_log("[DEBUG] Processing file: " . $filename[$i]);

      //Skip all files not ending in .txt
      $path_part = pathinfo($filename[$i]);
      $path_ext = strtolower($path_part['extension']);
      error_log("[DEBUG] File extension: " . $path_ext);
      if ($path_ext <> "txt") {
        $msg .= "File " . htmlspecialchars($filename[$i]) . " does not end in '.txt' unable to upload<br>";
        error_log("[DEBUG] Skipping file (not .txt): " . $filename[$i]);
        continue;
      } else {
      // Validate MIME type for additional security
            $tempname = $_FILES['multiple_files']['tmp_name'][$i];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $tempname);
            finfo_close($finfo);
      
            if ($mime_type !== 'text/plain') {
        $msg .= "File " . htmlspecialchars($filename[$i]) . " has invalid MIME type. Only text files allowed.<br>";
                continue;
            }

      // Save the remaining files to the upload folder
            $targetpath = $dirname . "/" . $filename[$i];
      if (!move_uploaded_file($tempname, $targetpath)) {
          $msg .= "File " . htmlspecialchars($filename[$i]) . " failed to upload to server.<br>";
          error_log("[ERROR] Failed to move uploaded file to: " . $targetpath);
          continue;
      }
      error_log("[DEBUG] File successfully moved to: " . $targetpath);

      // Check if uploaded filename is not in array of DB names
            if (!in_array($filename[$i], $db)) {

        // Fix the datetime from the filename to insert into DB
                $filevalue = explode('-', $filename[$i]);
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
                $hour = substr($filevalue[3], 0, 2);
                $minute = substr($filevalue[3], 2, 2);
                $second = substr($filevalue[3], 4, 2);

        // Set Strings to insert into DB
        $datetime = "" . $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second . "";
                $model = $filevalue[0];
                $serial = $filevalue[1];
        $file_contents = file_get_contents($targetpath); // Read the file contents to String String once it been uploaded for insert into DB
        
        if ($file_contents === false) {
            $msg .= "File " . htmlspecialchars($filename[$i]) . " could not be read from disk.<br>";
            error_log("[ERROR] Failed to read file contents from: " . $targetpath);
            continue;
        }
        error_log("[DEBUG] File contents read successfully, length: " . strlen($file_contents));

      // Bind the statement and execute
            $statement->bind_param("sssss", $datetime, $model, $serial, $file_contents, $filename[$i]);
        if ($statement->execute()) {
          $count++;
          $msg .= "<font color='green'>File " . htmlspecialchars($filename[$i]) . " uploaded successfully</font><br>";
        } else {
          $msg .= " File " . htmlspecialchars($filename[$i]) . " Error!<br>";
        }
            } else {
        $msg .= "File " . htmlspecialchars($filename[$i]) . " already exists in DB unable to upload<br>";
            }
        }
    }
}
if ($count > 0) {
  $msg .= "<br><font color='green'>!!!!! " . $count . " files uploaded successfuly to the DB !!!!!</font><br>";
}
error_log('[DEBUG] main.php completed');
?>
