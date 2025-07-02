<?php
require_once('connect.php');
//Convert POST form variables
$librarykey = $_POST['librarykey'];
$university = $_POST['university'];
$sql ="UPDATE LibraryLocations 
SET 
   	university = '$university'
WHERE
    librarykey = $librarykey";
	
	if ($conn->query($sql) === TRUE) {
	// header( 'Location: index.php?submit=true' ) ;
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
mysqli_close($conn);
?>