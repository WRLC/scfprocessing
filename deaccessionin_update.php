<?php
require_once('connect.php');
//Convert POST form variables
$id = $_POST['id'];
$staffID = filter_var($_POST['staffID'], FILTER_SANITIZE_STRING);
$time_In = $_POST['time_In'];

$sql ="UPDATE deaccessionHours 
SET 
	time_Out = CURRENT_TIMESTAMP
WHERE
    id = $id";
	
	if ($conn->query($sql) === TRUE) {
	// header( 'Location: deaccessionin.php?submit=true' ) ;
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
mysqli_close($conn);
?>