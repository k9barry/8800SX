<?php
require_once('security-headers.php');
require_once('config.php');
require_once('config-tables-columns.php');
require_once('helpers.php');
require_once('navbar.php');
/**
 * Securely views the text file of a record.
 * @author Viavi 8800SX
 */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>" . translate('Invalid record ID.') . "</div>";
    exit;
}
$id = intval($_GET['id']);
$sql = "SELECT filename, file FROM alignments WHERE id = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo "<div class='alert alert-warning'>" . translate('Record not found.') . "</div>";
    $stmt->close();
    $link->close();
    exit;
}
$stmt->bind_result($filename, $file_contents);
$stmt->fetch();
$stmt->close();
$link->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Alignment File</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        pre {
            background: #f8f9fa;
            padding: 1em;
            border-radius: 5px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h4>Viewing: <?php echo htmlspecialchars($filename); ?></h4>
            <a href="alignments-index.php" class="btn btn-secondary btn-sm float-right ml-2">Back to List</a>
            <a href="alignments-pdf.php?id=<?php echo urlencode($id); ?>" class="btn btn-info btn-sm float-right" target="_blank">Create PDF</a>
        </div>
        <div class="card-body">
            <pre><?php echo htmlspecialchars($file_contents); ?></pre>
        </div>
    </div>
</div>
</body>
</html>
