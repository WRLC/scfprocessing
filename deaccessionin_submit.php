<?php
require_once('connect.php');
//$staffID = $_POST['staffID'];
$staffID = filter_var($_POST['staffID'], FILTER_SANITIZE_STRING);
$projectID = filter_var($_POST['projectID'], FILTER_SANITIZE_STRING);
$sql = "INSERT INTO deaccessionHours (id, staffID, time_In, time_Out, projectID) VALUES (NULL, '$staffID', CURRENT_TIMESTAMP, NULL, '$projectID')";
if ($conn->query($sql) === TRUE) {
	header( 'Location: deaccessionin.php?submit=true' ) ; 
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
mysqli_close($conn);
?>