<?php if (isset($_SESSION['user_id']) and $_SESSION['admin'] == 'yes') {
    // Grab user data from the database using the user_id
    // Let them access the "logged in only" pages
    $now = time(); // Checking the time now when home page starts.

    if ($now > $_SESSION['expire']) {
        session_destroy();
        header("Location: login.php");
    }

} else {
    // Redirect them to the login page
    header("Location: index.php");
}
