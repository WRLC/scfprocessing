<?php
require_once('connect.php');

//$TimeCardKey = $_POST['TimeCardKey'];
$TimeCardKey = filter_var($_POST['TimeCardKey'], FILTER_SANITIZE_STRING);

$sql ="UPDATE stafftimecards 
SET 
    TimeCardCheckOut = CURRENT_TIMESTAMP
	
WHERE
    TimeCardKey = $TimeCardKey";

if ($conn->query($sql) === TRUE) {
	//header( 'Location: timecard.php?submit=true' ) ;  
	echo 'Success!';
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

mysqli_close($conn);
?>