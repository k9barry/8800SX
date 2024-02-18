<?php
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

    //skip all files not ending in .txt
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
        $month = substr($filevalue[2], 0, 2);
        $day = substr($filevalue[2], 2, 2);
        $year = substr($filevalue[2], 4, 4);
        $hour = substr($filevalue[3], 0, 2);
        $minute = substr($filevalue[3], 2, 2);
        $second = substr($filevalue[3], 4, 2);

        // Set variables to insert into DB
        $datetime = "" . $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second . "";
        $model = $filevalue[0];
        $serial = $filevalue[1];
        $file_contents = file_get_contents($targetpath); // Read the file contents to string variable once it been uploaded for insert into DB

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