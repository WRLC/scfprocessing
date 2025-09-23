<?php

require_once('connect.php');
//Convert POST form variables

$staffkey = $_POST['staffkey'];
$name = $_POST['name'];
$pw = $_POST['pw'];
$admin = $_POST['admin'];
$temp = $_POST['temp'];


$sql ="UPDATE Staff 
SET 
    name = '$name',
	pw = '$pw',
	admin = '$admin',
	temp = '$temp'
	
WHERE
    staffkey = $staffkey";
	
	if ($conn->query($sql) === TRUE) {
	// header( 'Location: index.php?submit=true' ) ;
    
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();

mysqli_close($conn);
	
	?>