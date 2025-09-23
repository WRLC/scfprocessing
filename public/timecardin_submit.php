<?php
require_once('connect.php');
//$Name = $_POST['TimeCardName'];
$Name = filter_var($_POST['TimeCardName'], FILTER_SANITIZE_STRING);
$sql = "INSERT INTO stafftimecards (TimeCardKey, TimeCardName, TimeCardCheckIn, TimeCardCheckOut) VALUES (NULL, '$Name', CURRENT_TIMESTAMP, NULL)";
if ($conn->query($sql) === TRUE) {
	//header( 'Location: timecard.php?submit=true' ) ; 
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
mysqli_close($conn);
?>