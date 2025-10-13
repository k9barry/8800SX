<?php
require_once('config.php');
require_once('helpers.php');
require_once('config-tables-columns.php');
session_start();
/**
 * Handles updating alignment records securely.
 * @author Viavi 8800SX
 */

if (isset($_POST["id"]) && !empty($_POST["id"])) {
    // CSRF token check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die(translate('Invalid CSRF token.'));
    // ...existing code...
    $id = $_POST["id"];
    $upload_results = array();
    $upload_errors = array();
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 15) === 'cruddiy_backup_') {
            $originalKey = substr($key, 15);
            if (isset($_FILES[$originalKey]) && $_FILES[$originalKey]['error'] == UPLOAD_ERR_OK) {
                $this_upload = handleFileUpload($_FILES[$originalKey]);
                $upload_results[] = $this_upload;
                if (!in_array(true, array_column($this_upload, 'error')) && !array_key_exists('error', $this_upload)) {
                    $_POST[$originalKey] = $this_upload['success'];
                    unlink($config->getUploadTargetDir() . $_POST['cruddiy_backup_' . $originalKey]);
                }
            } else {
                $_POST[$originalKey] = $value;
            }
        }
        if (substr($key, 0, 15) === 'cruddiy_delete_') {
            $deleteKey = substr($key, 15);
            if (isset($_POST['cruddiy_delete_' . $deleteKey]) && $_POST['cruddiy_delete_' . $deleteKey]) {
                $_POST[$deleteKey] = '';
                @unlink($config->getUploadTargetDir() . $_POST['cruddiy_backup_' . $deleteKey]);
            }
        }
    }
    $upload_errors = array();
    foreach ($upload_results as $result) {
        if (isset($result['error'])) {
            $upload_errors[] = $result['error'];
        }
    }
    if (!in_array(true, array_column($upload_results, 'error'))) {
        $datetime = trim(filter_var($_POST["datetime"], FILTER_SANITIZE_STRING));
        $model = trim(filter_var($_POST["model"], FILTER_SANITIZE_STRING));
        $serial = trim(filter_var($_POST["serial"], FILTER_SANITIZE_STRING));
        $file = trim(filter_var($_POST["file"], FILTER_SANITIZE_STRING));
        $entered = trim(filter_var($_POST["entered"], FILTER_SANITIZE_STRING));
        $filename = trim(filter_var($_POST["filename"], FILTER_SANITIZE_STRING));
        $stmt = $link->prepare("UPDATE `alignments` SET `datetime`=?,`model`=?,`serial`=?,`file`=?,`entered`=?,`filename`=? WHERE `id`=?");
        try {
            $stmt->execute([ $datetime, $model, $serial, $file, $entered, $filename, $id ]);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
        if (!isset($error)){
            header("location: alignments-read.php?id=$id");
        } else {
            $uploaded_files = array();
            foreach ($upload_results as $result) {
                if (isset($result['success'])) {
                    // Delete the uploaded files if there were any error while saving postdata in DB
                    unlink($config->getUploadTargetDir() . $result['success']);
                }
            }
        }

    }
}
// Check existence of id parameter before processing further
$_GET["id"] = trim($_GET["id"]);
if(isset($_GET["id"]) && !empty($_GET["id"])){
    // Get URL parameter
    $id =  trim($_GET["id"]);

    // Prepare a select statement
    $sql = "SELECT * FROM `alignments` WHERE `id` = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        // Set parameters
        $param_id = $id;

        // Bind variables to the prepared statement as parameters
        if (is_int($param_id)) $__vartype = "i";
        elseif (is_string($param_id)) $__vartype = "s";
        elseif (is_numeric($param_id)) $__vartype = "d";
        else $__vartype = "b"; // blob
        mysqli_stmt_bind_param($stmt, $__vartype, $param_id);

        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);

            if(mysqli_num_rows($result) == 1){
                /* Fetch result row as an associative array. Since the result set
                contains only one row, we don't need to use while loop */
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                // Retrieve individual field value

                $datetime = htmlspecialchars($row["datetime"] ?? "");
					$model = htmlspecialchars($row["model"] ?? "");
					$serial = htmlspecialchars($row["serial"] ?? "");
					$file = htmlspecialchars($row["file"] ?? "");
					$entered = htmlspecialchars($row["entered"] ?? "");
					$filename = htmlspecialchars($row["filename"] ?? "");
					

            } else{
                // URL doesn't contain valid id. Redirect to error page
                header("location: error.php");
                exit();
            }

        } else{
            translate('stmt_error') . "<br>".$stmt->error;
        }
    }

    // Close statement
    mysqli_stmt_close($stmt);

}  else{
    // URL doesn't contain id parameter. Redirect to error page
    header("location: error.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php translate('Update Record') ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
</head>
<?php require_once('navbar.php'); ?>
<body>
    <section class="pt-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <div class="page-header">
                        <h2><?php translate('Update Record') ?></h2>
                    </div>
                    <?php print_error_if_exists(@$upload_errors); ?>
                    <?php print_error_if_exists(@$error); ?>
                    <p><?php translate('update_record_instructions') ?></p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post" enctype="multipart/form-data">

                        <div class="form-group">
                                            <label for="datetime">datetime*</label>
                                            <input type="datetime-local" name="datetime" id="datetime" class="form-control" value="<?php echo empty($datetime) ? "" : date("Y-m-d\TH:i:s", strtotime(@$datetime)); ?>">
                                        </div>
						<div class="form-group">
                                            <label for="model">model*</label>
                                            <input type="text" name="model" id="model" maxlength="25" class="form-control" value="<?php echo @$model; ?>">
                                        </div>
						<div class="form-group">
                                            <label for="serial">serial*</label>
                                            <input type="text" name="serial" id="serial" maxlength="25" class="form-control" value="<?php echo @$serial; ?>">
                                        </div>
						<div class="form-group">
                                            <label for="file">file*</label>
                                            
<input type="file" name="file" id="file" class="form-control">
<input type="hidden" name="cruddiy_backup_file" id="cruddiy_backup_file" value="<?php echo @$file; ?>">
<?php if (isset($file) && !empty($file)) : ?>
<div class="custom-control custom-checkbox">
    <input type="checkbox" class="custom-control-input" id="cruddiy_delete_file" name="cruddiy_delete_file" value="1">
    <label class="custom-control-label" for="cruddiy_delete_file">
<?php translate("Delete:") ?>: <a href="uploads/<?php echo $file ?>" target="_blank"><?php echo $file ?></a>    </label>
</div>
<?php endif ?>

                                        </div>
						<div class="form-group">
                                            <label for="entered">entered*</label>
                                            <input type="datetime-local" name="entered" id="entered" class="form-control" value="<?php echo empty($entered) ? "" : date("Y-m-d\TH:i:s", strtotime(@$entered)); ?>">
                                        </div>
						<div class="form-group">
                                            <label for="filename">filename*</label>
                                            <input type="text" name="filename" id="filename" maxlength="255" class="form-control" value="<?php echo @$filename; ?>">
                                        </div>

                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                        <p>
                            <input type="submit" class="btn btn-primary" value="<?php translate('Edit') ?>">
                            <a href="javascript:history.back()" class="btn btn-secondary"><?php translate('Cancel') ?></a>
                        </p>
                        <hr>
                        <p>
                            <a href="alignments-read.php?id=<?php echo $_GET["id"];?>" class="btn btn-info"><?php translate('View Record') ?></a>
                            <a href="alignments-delete.php?id=<?php echo $_GET["id"];?>" class="btn btn-danger"><?php translate('Delete Record') ?></a>
                            <a href="alignments-index.php" class="btn btn-primary"><?php translate('Back to List') ?></a>
                        </p>
                        <p><?php translate('required_fiels_instructions') ?></p>
                    </form>
                </div>
            </div>
        </div>
    </section>
</body>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>