<?php
session_start();
require_once('connect.php');
//Convert POST form variables
$ProcessingKey = $_GET['id'];
$name = $_SESSION['user_id'];
//$pname = $_POST['pname'];


$sql ="UPDATE ProcessingAll 
SET 

	ptraylocation = 'WD',
	pcode = 'WD',
    pname = '$name',
    ccname = '$name',
	updated = CURRENT_TIMESTAMP
	
WHERE
    ProcessingKey = $ProcessingKey";
	
	if ($conn->query($sql) === TRUE) {
	 header( 'Location: edit.php?id='.$ProcessingKey) ;
    echo "success!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close(); 

mysqli_close($conn);



?>