<?php
declare(strict_types=1);

require_once 'connect.php';

date_default_timezone_set('America/New_York');

if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('Database connection not available in edit submit page');
    header('Location: list.php?submit=dberror');
    exit;
}

$processingKeyRaw = trim((string)($_POST['ProcessingKey'] ?? ''));

if ($processingKeyRaw === '' || !ctype_digit($processingKeyRaw)) {
    header('Location: list.php?submit=blank');
    exit;
}

$ProcessingKey = (int)$processingKeyRaw;

$ptraylocation = trim((string)($_POST['ptraylocation'] ?? ''));
$pcount        = trim((string)($_POST['pcount'] ?? ''));
$pfull         = trim((string)($_POST['pfull'] ?? ''));
$pverify       = trim((string)($_POST['pverify'] ?? ''));
$pchecked      = trim((string)($_POST['pchecked'] ?? ''));
$plibrary      = trim((string)($_POST['plibrary'] ?? ''));

$cccount       = trim((string)($_POST['cccount'] ?? ''));
$ccverify      = trim((string)($_POST['ccverify'] ?? ''));
$ccchecked     = trim((string)($_POST['ccchecked'] ?? ''));
$ccscan        = trim((string)($_POST['ccscan'] ?? ''));

$cctimestamp   = trim((string)($_POST['cctimestamp'] ?? ''));
$ptimestamp    = trim((string)($_POST['ptimestamp'] ?? ''));

$pcode = $ptraylocation !== '' ? substr($ptraylocation, -2) : '';

$updated = date('Y-m-d H:i:s');

/*
|--------------------------------------------------------------------------
| Normalize blank values to NULL
|--------------------------------------------------------------------------
*/
$ptraylocation = $ptraylocation !== '' ? $ptraylocation : null;
$pcode         = $pcode !== '' ? $pcode : null;
$pcount        = $pcount !== '' ? $pcount : null;
$pfull         = $pfull !== '' ? $pfull : null;
$pverify       = $pverify !== '' ? $pverify : null;
$pchecked      = $pchecked !== '' ? $pchecked : null;
$plibrary      = $plibrary !== '' ? $plibrary : null;

$cccount       = $cccount !== '' ? $cccount : null;
$ccverify      = $ccverify !== '' ? $ccverify : null;
$ccchecked     = $ccchecked !== '' ? $ccchecked : null;
$ccscan        = $ccscan !== '' ? $ccscan : null;

$cctimestamp   = $cctimestamp !== '' ? $cctimestamp : null;
$ptimestamp    = $ptimestamp !== '' ? $ptimestamp : null;

$sql = "
    UPDATE ProcessingAll
    SET
        ptraylocation = ?,
        pcode = ?,
        pcount = ?,
        pfull = ?,
        pverify = ?,
        pchecked = ?,
        plibrary = ?,
        cccount = ?,
        ccverify = ?,
        cctimestamp = ?,
        ptimestamp = ?,
        ccchecked = ?,
        ccscan = ?,
        updated = ?
    WHERE ProcessingKey = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log('Prepare failed in edit submit page: ' . $conn->error);
    header('Location: edit.php?id=' . $ProcessingKey . '&submit=dberror');
    exit;
}

$stmt->bind_param(
    'ssssssssssssssi',
    $ptraylocation,
    $pcode,
    $pcount,
    $pfull,
    $pverify,
    $pchecked,
    $plibrary,
    $cccount,
    $ccverify,
    $cctimestamp,
    $ptimestamp,
    $ccchecked,
    $ccscan,
    $updated,
    $ProcessingKey
);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();

    header('Location: edit.php?id=' . $ProcessingKey . '&submit=true');
    exit;
}

$errorCode = $conn->errno;
$errorText = $stmt->error;

$stmt->close();
$conn->close();

error_log("Update failed in edit submit page: [$errorCode] $errorText");

header('Location: edit.php?id=' . $ProcessingKey . '&submit=dberror');
exit;