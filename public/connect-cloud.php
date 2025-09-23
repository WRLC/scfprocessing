<?php
error_reporting( E_ALL );
$servername = $_ENV['GC_SERVERNAME'] ?? getenv('GC_SERVERNAME');
$username = $_ENV['GC_USERNAME'] ?? getenv('GC_USERNAME');
$password = $_ENV['GC_PASSWORD'] ?? getenv('GC_PASSWORD');
$dbname = $_ENV['GC_DBNAME'] ?? getenv('GC_DBNAME');
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






