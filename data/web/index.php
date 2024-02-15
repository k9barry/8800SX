<html>

<head>
  <title>Multiple File Upload Form</title>
  <link rel="stylesheet" href="bootstrap.min.css" />
</head>
<style>
  .box {
    width: 100%;
    max-width: 1200px;
    background-color: #f9f9f9;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 16px;
    margin: 0 auto;
  }

  .error {
    color: red;
    font-weight: 700;
  }
</style>

<?php
$count = 0;
$msg = "";
$dirname = "uploads";
if (is_dir($dirname)) {
  mkdir('/var/www/html/uploads', 0775, true);
}
array_map('unlink', glob("$dirname/*")); // Remove all files from upload folder
include('connection.php');

// Get filenames from DB
$sqlFind = 'SELECT `filename` FROM `alignments`';
$result = mysqli_query($connection, $sqlFind);
$db = []; // create empty array
while ($row = mysqli_fetch_row($result)) {
  array_push($db, $row[0]);
}

if (isset($_REQUEST['file-upload'])) {
  for ($i = 0; $i < count($_FILES['multiple_files']['name']); $i++) {
    $filename[] = basename($_FILES['multiple_files']['name'][$i]);

    //skip all files not ending in .txt
    $path_part = pathinfo($filename[$i]);
    $path_ext = $path_part['extension'];
    if ($path_ext <> "txt") {
      $msg .= "File " . $filename[$i] . " does not end in '.txt' unable to upload<br>";
      continue;
    }

    // Save the remaining files to the upload folder
    $uploadfile = $_FILES['multiple_files']['tmp_name'][$i];
    $targetpath = $dirname . "/" . $filename[$i];
    move_uploaded_file($uploadfile, $targetpath);

    // Check if filename matches array of DB names
    if (in_array($filename[$i], $db)) {
      $msg .= "File " . $filename[$i] . " already exists in DB unable to upload<br>";
      continue;
    }

    // Fix the datetime from the filename to insert into DB
    $filevalue = explode('-', $filename[$i]);
    $month = substr($filevalue[2], 0, 2);
    $day = substr($filevalue[2], 2, 2);
    $year = substr($filevalue[2], 4, 4);
    $hour = substr($filevalue[3], 0, 2);
    $minute = substr($filevalue[3], 2, 2);
    $second = substr($filevalue[3], 4, 2);
    $datetime = "" . $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second . "";

    // Set more variables to insert into DB
    $model = $filevalue[0];
    $serial = $filevalue[1];

    // Read the file contents to variable once it been uploaded for insert into DB
    $file_contents = file_get_contents($targetpath);

    $query = "INSERT INTO alignments (datetime, model, serial, file, filename) VALUES ('$datetime', '$model', '$serial', '$file_contents', '$filename[$i]')";
    $insert_query = mysqli_query($connection, $query);

    if ($insert_query > 0) {
      $count++;
      $msg .= "File " . $filename[$i] . " uploaded successfuly<br>";
    } else {
      $msg .= " File" . $filename[$i] . " Error!<br>";
    }
  }
}
if ($count > 0) {
  $msg .= "<br><b>!!!!! " . $count . " files uploaded successfuly to the DB !!!!!</b><br>";
}
?>

<body>
  <div class="container">
    <div class="table-responsive">
      <h2 align="center">Multiple File Upload Form</h2><br />
      <form align="center" action="http://localhost:8888/index.php?route=/sql&db=viavi&table=alignments&pos=0" target="_blank">
        <input type="submit" value="phpMyAdmin" class="btn btn-success" />
      </form>
      <div class="box">
        <form method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label for="image">Select Multiple Files - then press SUBMIT</label>
            <input type="file" name="multiple_files[]" class="form-control" multiple required />
          </div>
          <div class="form-group">
            <input type="submit" id="file-upload" name="file-upload" value="Submit" class="btn btn-success" />
          </div>
          <p align="center" class="error"><?php if (!empty($msg)) {
                                            echo $msg;
                                          } ?></p>
        </form>
      </div>
    </div>
  </div>
</body>

</html>