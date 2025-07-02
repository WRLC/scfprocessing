<?php
require_once('connect.php');
//Convert POST form variables
$id = $_GET['id'];
$sql ="DELETE FROM LibraryLocations WHERE librarykey = $id";

if ($conn->query($sql) === TRUE) {
	header( 'Location: libraries.php?submit=true' ) ;
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
mysqli_close($conn);
?>