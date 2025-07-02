<?php

////////////define connection ////////////
$connect = 'google';

////////////connect to localhost ////////////
if($connect == 'localhost') {

	error_reporting( E_ALL );
	$servername = $_ENV['NEW_DB_SERVERNAME'] ?? getenv('NEW_DB_SERVERNAME');
	$username = $_ENV['NEW_DB_USERNAME'] ?? getenv('NEW_DB_USERNAME');
	$password = $_ENV['NEW_DB_PASSWORD'] ?? getenv('NEW_DB_PASSWORD');
	$dbname = $_ENV['NEW_DB_DBNAME'] ?? getenv('NEW_DB_DBNAME');
// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
	if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
	} 
	else {
    echo 'Connected to Localhost';
	}
}

////////////connect to Google Cloud ////////////
if($connect == 'google') {

	error_reporting( E_ALL );
	$servername = $_ENV['NEW_GC_SERVERNAME'] ?? getenv('NEW_GC_SERVERNAME');
	$username = $_ENV['NEW_GC_USERNAME'] ?? getenv('NEW_GC_USERNAME');
	$password = $_ENV['NEW_GC_PASSWORD'] ?? getenv('NEW_GC_PASSWORD');
	$dbname = $_ENV['NEW_GC_DBNAME'] ?? getenv('NEW_GC_DBNAME');
// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
	if ($conn->connect_error) {

    die("Connection failed: " . $conn->connect_error);
	} 
 	
}
?>