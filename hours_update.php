<?php
require_once('connect.php');
//Convert POST form variables
$TimeCardCheckIn = $_POST['TimeCardCheckIn'];
$TimeCardCheckOut = $_POST['TimeCardCheckOut'];
$TimeCardKey = $_POST['TimeCardKey'];

$sql ="UPDATE stafftimecards 
SET 
   	TimeCardCheckIn = '$TimeCardCheckIn',
	TimeCardCheckOut = '$TimeCardCheckOut'
WHERE
    TimeCardKey = $TimeCardKey";
	
	if ($conn->query($sql) === TRUE) {
	// header( 'Location: index.php?submit=true' ) ;
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
mysqli_close($conn);
?>