<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>8800SX - Upload Files</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/6b773fe9e4.js" crossorigin="anonymous"></script>
    <style type="text/css">
        .page-header h2{
            margin-top: 0;
        }
        table tr td:last-child a{
            margin-right: 5px;
        }
        body {
            font-size: 14px;
        }
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
</head>
<?php require_once('security-headers.php'); ?>
<?php require_once('Config.php'); ?>
<?php require_once('config-tables-columns.php'); ?>
<?php require_once('navbar.php'); ?>
<body>
    <section class="pt-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-header clearfix">
                        <h2 class="float-left"><?php translate('Viavi 8800SX Database') ?></h2>
                        <a href="alignments-index.php" class="btn btn-success float-right"><?php translate('Alignment Database') ?></a>
                        <a href="javascript:history.back()" class="btn btn-secondary float-right mr-2"><?php translate('Back') ?></a>
                    </div>

                    <div class="box mt-4">
                        <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
                        <form method="post" action="main.php" enctype="multipart/form-data" autocomplete="off">
                            <input type="hidden" name="csrf_token" value="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ($_SESSION['csrf_token'] = bin2hex(random_bytes(32))); ?>">
                            <div class="form-group">
                                <label for="multiple_files"><?php translate('Select Multiple Files - then press SUBMIT') ?></label>
                                <input type="file" name="multiple_files[]" id="multiple_files" class="form-control" multiple required />
                            </div>
                            <div class="form-group">
                                <input type="button" id="file-upload" value="<?php translate('Submit') ?>" class="btn btn-success" />
                                <div id="batch-status"></div>
                            </div>
                            <?php if (!empty($msg)) { ?>
                                <div class="alert alert-info mt-3" role="alert">
                                    <?php echo $msg; ?>
                                </div>
                            <?php } ?>
                        </form>
                    </div>
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

        // Batch upload logic
        $('#file-upload').on('click', function(e) {
            e.preventDefault();
            var files = $('#multiple_files')[0].files;
            var batchSize = 10;
            var batchStatus = $('#batch-status');
            batchStatus.html('');

            if (files.length === 0) {
                batchStatus.html('<div class="error">' + <?php echo json_encode(translate('No files selected', false)); ?> + '</div>');
                return;
            }

            // Client-side file validation
            var maxSize = 128 * 1024 * 1024; // 128MB
            var validFiles = [];
            for (var i = 0; i < files.length; i++) {
                if (files[i].size > maxSize) {
                    batchStatus.append('<div class="error">' + files[i].name + ': ' + <?php echo json_encode(translate('File exceeds maximum size', false)); ?> + '</div>');
                    continue;
                }
                if (!files[i].name.toLowerCase().endsWith('.txt')) {
                    batchStatus.append('<div class="error">' + files[i].name + ': ' + <?php echo json_encode(translate('File must be .txt', false)); ?> + '</div>');
                    continue;
                }
                validFiles.push(files[i]);
            }

            if (validFiles.length === 0) {
                batchStatus.append('<div class="error">' + <?php echo json_encode(translate('No valid .txt files to upload', false)); ?> + '</div>');
                return;
            }

            // Function to upload files in batches
            function uploadBatch(batchIndex) {
                var totalBatches = Math.ceil(validFiles.length / batchSize);
                if (batchIndex >= totalBatches) {
                    batchStatus.append('<div class="alert alert-success mt-3">All batches uploaded.</div>');
                    return;
                }

                var formData = new FormData();
                var start = batchIndex * batchSize;
                var end = Math.min(start + batchSize, validFiles.length);
                for (var i = start; i < end; i++) {
                    formData.append('multiple_files[]', validFiles[i]);
                }
                formData.append('file-upload', 'Submit');

                batchStatus.append('<div>Uploading batch ' + (batchIndex + 1) + ' of ' + totalBatches + '...</div>');
                $.ajax({
                    url: 'main.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        batchStatus.append('<div class="alert alert-info mt-3">Batch ' + (batchIndex + 1) + ' uploaded.</div>');
                        uploadBatch(batchIndex + 1);
                    },
                    error: function(xhr) {
                        batchStatus.append('<div class="alert alert-danger mt-3">Error uploading batch ' + (batchIndex + 1) + ': ' + xhr.statusText + '</div>');
                    }
                });
            }

            // Start uploading the first batch
            uploadBatch(0);
        });
    });
</script>
</body>
</html>
