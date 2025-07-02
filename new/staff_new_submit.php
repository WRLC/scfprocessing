<?php
require_once('connect.php');

$name = $_POST['name'];
$pw = $_POST['pw'];
$admin = $_POST['admin'];
$temp = $_POST['temp'];



$sql = "INSERT INTO Staff (staffkey, name, pw, admin, temp) VALUES (NULL,'$name', '$pw', '$admin','$temp')";

if ($conn->query($sql) === TRUE) {
	//header( 'Location: processing.php?submit=true' ) ;
    
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();

mysqli_close($conn);


?>