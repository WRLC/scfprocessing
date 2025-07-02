<?php
require_once('connect.php');
$title = $_POST['title'];
$university = $_POST['university'];
$startDate = $_POST['startDate'];
$endDate = $_POST['endDate'];

$startDate = date("Y-m-d 00:00:00", strtotime($startDate));
$endDate = date("Y-m-d 23:59:59", strtotime($endDate));

$goal = $_POST['goal'];
$sql = "INSERT INTO project (id, title, university, startDate, endDate, goal, archive) VALUES (NULL,'$title', '$university', '$startDate','$endDate','$goal',NULL)";
if ($conn->query($sql) === TRUE) {
	header( 'Location: project.php?submit=true' ) ;
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close(); 
mysqli_close($conn);
?>