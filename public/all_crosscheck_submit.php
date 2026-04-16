<?php
declare(strict_types=1);

require_once 'connect.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('Database connection not available in all_crosscheck_submit.php');
    header('Location: crosscheck.php?submit=dberror');
    exit;
}

$ccName = trim((string)($_POST['ccname'] ?? ''));
$processingKeyRaw = trim((string)($_POST['ProcessingKey'] ?? ''));
$ccCount = trim((string)($_POST['cccount'] ?? ''));
$ccVerify = trim((string)($_POST['ccverify'] ?? ''));
$ccChecked = trim((string)($_POST['ccchecked'] ?? ''));
$ccScan = trim((string)($_POST['ccscan'] ?? ''));

if ($processingKeyRaw === '' || !ctype_digit($processingKeyRaw)) {
    header('Location: crosscheck.php?submit=blank');
    exit;
}

$processingKey = (int)$processingKeyRaw;

if ($ccCount === '' || $ccVerify === '' || $ccChecked === '') {
    header('Location: crosscheck.php?submit=blank');
    exit;
}

$sql = "
    UPDATE ProcessingAll
    SET
        cctimestamp = CURRENT_TIMESTAMP,
        ccname = ?,
        cccount = ?,
        ccverify = ?,
        ccchecked = ?,
        ccscan = ?,
        updated = CURRENT_TIMESTAMP
    WHERE ProcessingKey = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log('Prepare failed in all_crosscheck_submit.php: ' . $conn->error);
    header('Location: crosscheck.php?submit=dberror');
    exit;
}

$stmt->bind_param(
    'sssssi',
    $ccName,
    $ccCount,
    $ccVerify,
    $ccChecked,
    $ccScan,
    $processingKey
);

if ($stmt->execute()) {
    $stmt->close();
    header('Location: crosscheck.php?submit=true');
    exit;
}

$errorCode = $conn->errno;
$errorText = $stmt->error;
$stmt->close();

error_log("Update failed in all_crosscheck_submit.php: [$errorCode] $errorText");
header('Location: crosscheck.php?submit=dberror');
exit;
?>