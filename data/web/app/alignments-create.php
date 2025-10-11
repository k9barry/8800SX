<?php
require_once('config.php');
require_once('helpers.php');
session_start();
/**
 * Handles creation of new alignment records securely.
 * @author Viavi 8800SX
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF token check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die(translate('Invalid CSRF token.'));
    }
    $upload_results = array();
    if (!empty($_FILES)) {
        foreach ($_FILES as $key => $value) {
            if ($value['error'] != UPLOAD_ERR_NO_FILE) {
                $this_upload = handleFileUpload($_FILES[$key]);
                $upload_results[] = $this_upload;
                if (!in_array(true, array_column($this_upload, 'error')) && !array_key_exists('error', $this_upload)) {
                    $_POST[$key] = $this_upload['success'];
                }
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
        $stmt = $link->prepare("INSERT INTO `alignments` (`datetime`, `model`, `serial`, `file`, `entered`, `filename`) VALUES (?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([ $datetime, $model, $serial, $file, $entered, $filename ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = $e->getMessage();
        }
        if (!isset($error)) {
            $new_id = mysqli_insert_id($link);
            header("location: alignments-read.php?id=$new_id");
        } else {
            foreach ($upload_results as $result) {
                if (isset($result['success'])) {
                    unlink($upload_target_dir . $result['success']);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php translate('Add New Record') ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
</head>
<?php require_once('navbar.php'); ?>
<body>
    <section class="pt-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <div class="page-header">
                        <h2><?php translate('Add New Record') ?></h2>
                    </div>
                    <?php print_error_if_exists(@$upload_errors); ?>
                    <?php print_error_if_exists(@$error); ?>
                    <p><?php translate('add_new_record_instructions') ?></p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">

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

                        <input type="submit" class="btn btn-primary" value="<?php translate('Create') ?>">
                        <a href="alignments-index.php" class="btn btn-secondary"><?php translate('Cancel') ?></a>
                    </form>
                    <p><small><?php translate('required_fiels_instructions') ?></small></p>
                </div>
            </div>
        </div>
    </section>
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