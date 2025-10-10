<?php
/**
 * Index file upload.php
 * 
 * Starts by loading the main.php file then
 * displays Viavi 8800SX Database search and upload page.
 * 
 */
include('app/security-headers.php');
include('main.php');
?>

<html>

<head>
    <title>Viavi 8800SX Database</title>
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

<body>
    <div class="container">
        <div class="table-responsive">
            <h2 align="center">Viavi 8800SX Database</h2><br />
            <form align="center" action="/app/alignments-index.php"
                target="_self" method="POST">
                <div class="form-group">
                    <input type="submit" value="Alignment Database" class="btn btn-success" />
                </div>
            </form>
            <div class="box">
                <!--<div class="box">
                    <form action="result.php" method="POST">
                        <div class="form-group">
                            <label for="image">Search Database</label>
                            <input type="text" name="serial" size="30" class="form-control">
                        </div>
                        <div class="form-group">
                            <input type="submit" value="Search" class="btn btn-success" />
                        </div>
                    </form>
                </div>-->
                <div class="box">
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="image">Select Multiple Files - then press SUBMIT</label>
                            <input type="file" name="multiple_files[]" class="form-control" multiple required />
                        </div>
                        <div class="form-group">
                            <input type="submit" id="file-upload" name="file-upload" value="Submit"
                                class="btn btn-success" />
                        </div>
                        <p align="center" class="error"><?php if (!empty($msg)) { echo $msg; } ?></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>