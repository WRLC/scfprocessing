<?php
declare(strict_types=1);

session_start();
require_once 'connect.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('Database connection not available in create_session.php');
    header('Location: login.php?login=dberror');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$usernameInput = trim((string)($_POST['username'] ?? ''));
$passwordInput = trim((string)($_POST['password'] ?? ''));

if ($usernameInput === '' || $passwordInput === '') {
    header('Location: login.php?login=false');
    exit;
}

$sql = "SELECT staffkey, name, pw, admin, temp FROM Staff WHERE name = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log('Prepare failed in create_session.php: ' . $conn->error);
    header('Location: login.php?login=dberror');
    exit;
}

$stmt->bind_param('s', $usernameInput);
$stmt->execute();
$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$user) {
    header('Location: login.php?login=false');
    exit;
}

// Current logic: plain-text comparison.
// Long-term: replace with password_verify().
if ((string)$user['pw'] !== $passwordInput) {
    header('Location: login.php?login=false');
    exit;
}

session_regenerate_id(true);

$_SESSION['staffkey'] = (string)$user['staffkey'];
$_SESSION['user_id'] = (string)$user['name'];
$_SESSION['admin'] = (string)$user['admin'];
$_SESSION['temp'] = (string)$user['temp'];
$_SESSION['start'] = time();

// 30 minutes:
$_SESSION['expire'] = time() + 1800;

header('Location: index.php');
exit;