<?php
require_once('connect.php');

$Name = $_POST['Name'];
$TrayLocation = $_POST['TrayLocation'];
$Count = $_POST['Count'];
$Full = $_POST['Full'];
$Verify = $_POST['Verify'];
$Checked = $_POST['Checked'];
$Library = $_POST['Library'];

$sql = "INSERT INTO ProcessingForm (ProcessingKey, Timestamp, Name, TrayLocation, Count, Full, Verify, Checked, Library) VALUES (NULL,CURRENT_TIMESTAMP, '$Name', '$TrayLocation','$Count', '$Full', '$Verify', '$Checked', '$Library')";

if ($conn->query($sql) === TRUE) {
	//header( 'Location: processing.php?submit=true' ) ;
    
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();

mysqli_close($conn);


?>