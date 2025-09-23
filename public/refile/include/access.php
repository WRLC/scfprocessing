<?php
session_start();

// Set timeout duration (30 minutes)
$timeout_duration = 1800; // 1800 seconds = 30 minutes

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity'])) {
        $elapsed_time = time() - $_SESSION['last_activity'];
        if ($elapsed_time > $timeout_duration) {
            // Destroy the session and redirect to login
            session_unset();
            session_destroy();
            header("Location: ../login.php");
            exit();
        }
    }
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
} else {
    header("Location: ../login.php");
    exit();
}

// Safely access session and GET variables
$name = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
$submit = isset($_GET['submit']) ? $_GET['submit'] : '';
$cardname = isset($_GET['name']) ? $_GET['name'] : '';
$beginurl = isset($_GET['begin']) ? $_GET['begin'] : '';
$endurl = isset($_GET['end']) ? $_GET['end'] : '';

// Format dates only if values are provided
$beginurformatted = $beginurl ? date("Y-m-d", strtotime($beginurl)) : '';
$endurlformattted = $endurl ? date("Y-m-d", strtotime($endurl)) : '';

// Apply date filter if both dates are available
if (!empty($beginurl) && !empty($endurl)) {
    $daterange = ' AND (TimeCardCheckIn BETWEEN "' . $beginurformatted . ' 00:00:00" AND "' . $endurlformattted . ' 23:59:59") ';
} else {
    $daterange = '';
}
?>