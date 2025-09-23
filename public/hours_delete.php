<?php
require_once('connect.php');
//Convert POST form variables
$id = $_GET['id'];
$sql ="DELETE FROM stafftimecards WHERE TimeCardKey = $id";

if ($conn->query($sql) === TRUE) {
	header( 'Location: hours_edit.php?submit=true' ) ;
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
mysqli_close($conn);
?>