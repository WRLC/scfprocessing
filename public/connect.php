<?php

////////////define connection ////////////
$connect = 'localhost';

////////////connect to localhost ////////////
if($connect == 'localhost') {

	error_reporting( E_ALL );
	$servername = $_ENV['DB_SERVERNAME'] ?? getenv('DB_SERVERNAME');
	$username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME');
	$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');
	$dbname = $_ENV['DB_DBNAME'] ?? getenv('DB_DBNAME');
// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
	if ($conn->connect_error) {
	    die("Oops! Connection failed: " . $conn->connect_error);
	} 
	else {
  //  echo 'Connected to Localhost';
	}
}

////////////connect to Google Cloud ////////////
if($connect == 'google') {

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
 	
}

// Connect to test DB from Don. 11/25/19

if($connect == 'mysql.wrlc.org') {

	error_reporting( E_ALL );
	//$servername = "mysql.wrlc.org";
    // moved db server to VLAN 1010 - don 20120220
	$servername = $_ENV['TEST_SERVERNAME'] ?? getenv('TEST_SERVERNAME');;
$username = $_ENV['TEST_USERNAME'] ?? getenv('TEST_USERNAME');
$password = $_ENV['TEST_PASSWORD'] ?? getenv('TEST_PASSWORD');
$dbname = $_ENV['TEST_DBNAME'] ?? getenv('TEST_DBNAME');
// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
	if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
	} 
 	
}



?>
