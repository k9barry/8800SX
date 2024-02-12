<html>

<head>
  <title>Multiple File Upload Form</title>
  <link rel="stylesheet" href="bootstrap.min.css" />
</head>
<style>
  .box {
    width: 100%;
    max-width: 600px;
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
include('connection.php');
if (isset($_REQUEST['file-upload'])) {
  for ($i = 0; $i < count($_FILES['multiple_files']['name']); $i++) {
    $filename[] = basename($_FILES['multiple_files']['name'][$i]);
    $uploadfile = $_FILES['multiple_files']['tmp_name'][$i];
    $targetpath = "uploads/" . $filename[$i];
    move_uploaded_file($uploadfile, $targetpath);
  
  $filename = implode(', ', $_FILES['multiple_files']['name']);

  $filevalue = explode('-', $filename);
  $month = substr($filevalue[2], 0, 2);
  $day = substr($filevalue[2], 2, 2);
  $year = substr($filevalue[2], 4, 4);
  $hour = substr($filevalue[3], 0, 2);
  $minute = substr($filevalue[3], 2, 2);
  $second = substr($filevalue[3], 4, 2);

  $time = $filevalue[3];
  $datetime = "" . $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second . "";
  $model = $filevalue[0];
  $serial = $filevalue[1];
  $file = $filevalue[4];

  $query = "INSERT into alignments (datetime, model, serial, file, filename) VALUES ('$datetime', '$model', '$serial', '$file', '$filename')";
  echo ($query . "<br>");
  $insert_query = mysqli_query($connection, $query);

  if ($insert_query > 0) {
    $msg = "Images uploaded successfuly";
  } else {
    $msg = "Error!";
  }
}}
?>

<body>
  <div class="container">
    <div class="table-responsive">
      <h3 align="center">Multiple Image Upload Form</h3><br />
      <div class="box">
        <form method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label for="image">Select Multiple Image</label>
            <input type="file" name="multiple_files[]" class="form-control" multiple required />
          </div>
          <div class="form-group">
            <input type="submit" id="file-upload" name="file-upload" value="Submit" class="btn btn-success" />
          </div>
          <p class="error"><?php if (!empty($msg)) {
                              echo $msg;
                            } ?></p>
        </form>
      </div>
    </div>
  </div>
</body>

</html>