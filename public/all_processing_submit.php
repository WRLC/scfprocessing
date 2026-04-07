<?php
declare(strict_types=1);

require_once 'connect.php';

date_default_timezone_set('America/New_York');

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not available.');
}

$Name = trim((string)($_POST['Name'] ?? ''));
$traytemp = trim((string)($_POST['TrayLocation'] ?? ''));
$Count = trim((string)($_POST['Count'] ?? ''));
$Full = trim((string)($_POST['Full'] ?? ''));
$Verify = trim((string)($_POST['Verify'] ?? ''));
$Checked = trim((string)($_POST['Checked'] ?? ''));
$Library = trim((string)($_POST['Library'] ?? ''));

if ($Library === '' || $traytemp === '' || $Count === '' || $Checked === '' || $Verify === '') {
    header('Location: processing.php?submit=blank');
    exit;
}

$PCode = substr($traytemp, -2);
$timestamp = date('Y-m-d H:i:s');

/*
|--------------------------------------------------------------------------
| Check for duplicate tray/shelf barcode
|--------------------------------------------------------------------------
*/
$checkSql = "SELECT 1 FROM ProcessingAll WHERE ptraylocation = ? LIMIT 1";
$checkStmt = $conn->prepare($checkSql);

if (!$checkStmt) {
    die('Prepare failed: ' . $conn->error);
}

$checkStmt->bind_param('s', $traytemp);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$isDuplicate = $checkResult instanceof mysqli_result && $checkResult->num_rows > 0;
$checkStmt->close();

if ($isDuplicate) {
    header('Location: processing.php?submit=false');
    exit;
}

/*
|--------------------------------------------------------------------------
| Normalize nullable fields
|--------------------------------------------------------------------------
*/
$fullValue = ($Full !== '') ? $Full : null;

/*
|--------------------------------------------------------------------------
| Insert record
|--------------------------------------------------------------------------
*/
$insertSql = "
    INSERT INTO ProcessingAll (
        ProcessingKey,
        ptimestamp,
        pname,
        ptraylocation,
        pcode,
        pcount,
        pfull,
        pverify,
        pchecked,
        plibrary,
        cctimestamp,
        ccname,
        cccount,
        ccverify,
        ccchecked,
        updated
    ) VALUES (
        NULL,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        ?
    )
";

$insertStmt = $conn->prepare($insertSql);

if (!$insertStmt) {
    die('Prepare failed: ' . $conn->error);
}

$insertStmt->bind_param(
    'ssssssssss',
    $timestamp,   // ptimestamp
    $Name,        // pname
    $traytemp,    // ptraylocation
    $PCode,       // pcode
    $Count,       // pcount
    $fullValue,   // pfull
    $Verify,      // pverify
    $Checked,     // pchecked
    $Library,     // plibrary
    $timestamp    // updated
);

if ($insertStmt->execute()) {
    $insertStmt->close();
    $conn->close();
    header('Location: processing.php?submit=true');
    exit;
}

$error = $insertStmt->error;
$insertStmt->close();
$conn->close();

echo 'Error: ' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8');
