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
<?php require_once('config.php'); ?>
<?php require_once('config-tables-columns.php'); ?>
<?php require_once('helpers.php'); ?>
<?php require_once('main.php'); ?>
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
                        <form method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="multiple_files"><?php translate('Select Multiple Files - then press SUBMIT') ?></label>
                                <input type="file" name="multiple_files[]" id="multiple_files" class="form-control" multiple required />
                            </div>
                            <div class="form-group">
                                <input type="submit" id="file-upload" name="file-upload" value="<?php translate('Submit') ?>" class="btn btn-success" />
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
        });
    </script>
</body>
</html>
