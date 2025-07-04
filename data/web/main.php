<?php
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
array_map('unlink', glob("$dirname/*")); // Remove all files from upload folder
include('connection.php');

// Set INSERT statement and prepare
$sql = "INSERT INTO alignments (datetime, model, serial, file, filename) VALUES (?, ?, ?, ?, ?)";
$statement = $connection->prepare($sql);

// Get all filenames from DB and place in array $db
$sqlFind = 'SELECT `filename` FROM `alignments`';
$result = mysqli_query($connection, $sqlFind);
$db = []; // create empty array
while ($row = mysqli_fetch_row($result)) {
  array_push($db, $row[0]);
}

if (isset($_REQUEST['file-upload'])) {

  // Loop thru upladed files and get info to put into DB
  for ($i = 0; $i < count($_FILES['multiple_files']['name']); $i++) {
    $filename[] = basename($_FILES['multiple_files']['name'][$i]);

    //Skip all files not ending in .txt
    $path_part = pathinfo($filename[$i]);
    $path_ext = $path_part['extension'];
    if ($path_ext <> "txt") {
      $msg .= "File " . $filename[$i] . " does not end in '.txt' unable to upload<br>";
    } else {

      // Save the remaining files to the upload folder
      $tempname = $_FILES['multiple_files']['tmp_name'][$i];
      $targetpath = $dirname . "/" . $filename[$i];
      move_uploaded_file($tempname, $targetpath);

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

        // Bind the statement and execute
        $statement->bind_param("sssss", $datetime, $model, $serial, $file_contents, $filename[$i]);
        $statement->execute();  // Execute the mysql statement

        if ($statement) {
          $count++;
          $msg .= "<font color='green'>File " . $filename[$i] . " uploaded successfuly</font><br>";
        } else {
          $msg .= " File" . $filename[$i] . " Error!<br>";
        }
      } else {
        $msg .= "File " . $filename[$i] . " already exists in DB unable to upload<br>";
      }
    }
  }
}
if ($count > 0) {
  $msg .= "<br><font color='green'>!!!!! " . $count . " files uploaded successfuly to the DB !!!!!</font><br>";
}
?>
