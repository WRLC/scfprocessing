<?php
error_reporting( E_ALL );
$servername = $_ENV['OLD_GC_SERVERNAME'] ?? getenv('OLD_GC_SERVERNAME');
$username = $_ENV['OLD_GC_USERNAME'] ?? getenv('OLD_GC_USERNAME');
$password = $_ENV['OLD_GC_PASSWORD'] ?? getenv('OLD_GC_PASSWORD');
$dbname = $_ENV['OLD_GC_DBNAME'] ?? getenv('OLD_GC_DBNAME');
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {

    die("Connection failed: " . $conn->connect_error);
} 
 else {
    echo 'Connected to Google Cloud';
}
?>






