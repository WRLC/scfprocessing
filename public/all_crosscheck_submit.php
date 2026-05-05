<?php
declare(strict_types=1);

require_once 'connect.php';

date_default_timezone_set('America/New_York');

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

if ($ccName === '' || $ccCount === '' || $ccVerify === '' || $ccChecked === '') {
    header('Location: crosscheck.php?submit=blank');
    exit;
}

$timestamp = date('Y-m-d H:i:s');

$sql = "
    UPDATE ProcessingAll
    SET
        cctimestamp = ?,
        ccname = ?,
        cccount = ?,
        ccverify = ?,
        ccchecked = ?,
        ccscan = ?,
        updated = ?
    WHERE ProcessingKey = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log('Prepare failed in all_crosscheck_submit.php: ' . $conn->error);
    header('Location: crosscheck.php?submit=dberror');
    exit;
}

$stmt->bind_param(
    'sssssssi',
    $timestamp,
    $ccName,
    $ccCount,
    $ccVerify,
    $ccChecked,
    $ccScan,
    $timestamp,
    $processingKey
);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header('Location: crosscheck.php?submit=true');
    exit;
}

$errorCode = $conn->errno;
$errorText = $stmt->error;
$stmt->close();
$conn->close();

error_log("Update failed in all_crosscheck_submit.php: [$errorCode] $errorText");
header('Location: crosscheck.php?submit=dberror');
exit;