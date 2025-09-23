<?php

require_once('connect.php');
//Convert POST form variables
$id = $_POST['id'];
$title = $_POST['title'];
$university = $_POST['university'];
$startDate = $_POST['startDate'];
$endDate = $_POST['endDate'];
$goal = $_POST['goal'];
$archive = $_POST['archive'];

$startDate = date("Y-m-d 00:00:00", strtotime($startDate));
$endDate = date("Y-m-d 23:59:59", strtotime($endDate));


$sql ="UPDATE project
SET 

    title = '$title',
    university = '$university',
    startDate = '$startDate',
    endDate = '$endDate',
    goal = '$goal',
    archive = '$archive'
	
WHERE
    id = $id";
	
	if ($conn->query($sql) === TRUE) {
	 header( 'Location: project_edit.php?submit=true&id='.$id.'' ) ;
    
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();

mysqli_close($conn);
	
	?>