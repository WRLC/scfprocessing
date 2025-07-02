<?php
require_once('connect.php');

$university = $_POST['university'];

$sql = "INSERT INTO LibraryLocations (librarykey, university) VALUES (NULL,'$university')";

if ($conn->query($sql) === TRUE) {
	//header( 'Location: processing.php?submit=true' ) ;
    
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();

mysqli_close($conn);


?>