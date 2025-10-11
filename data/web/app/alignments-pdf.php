<?php
require_once('security-headers.php');
require_once('config.php');
require_once('helpers.php');
/**
 * Securely generates a PDF of the text file for printing.
 * @author Viavi 8800SX
 */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die(translate('Invalid record ID.'));
}
$id = intval($_GET['id']);
$link = mysqli_connect($db_server, $db_user, $db_password, $db_name);
if ($link === false) {
    error_log(mysqli_connect_error());
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
$sql = "SELECT filename, file FROM alignments WHERE id = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $stmt->close();
    $link->close();
    die(translate('Record not found.'));
}
$stmt->bind_result($filename, $file_contents);
$stmt->fetch();
$stmt->close();
$link->close();
require_once(__DIR__ . '/tcpdf/tcpdf.php');

$pdf = new TCPDF('P', 'mm', 'A4'); // Landscape, millimeters, A4
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Viavi 8800SX');
$pdf->SetTitle($filename);
$pdf->SetSubject('Alignment File');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->AddPage();
$pdf->SetFont('courier', '', 8); // Smaller font for more columns

// Split file contents into lines
$lines = explode("\n", $file_contents);
foreach ($lines as $line) {
    // Output each line in a cell with no wrapping
    $pdf->Cell(0, 5, $line, 0, 1, 'L', 0, '', 0, false, 'T', 'C');
}

$pdf->Output($filename . '.pdf', 'I');
exit;
