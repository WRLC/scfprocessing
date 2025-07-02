<?php
require_once('connect.php');
//Convert POST form variables

$id = $_GET['id'];
//$table = $_GET['table'];

//if($table = 'CrossCheckForm') $key = 'CCKey';
//else $key = 'ProcessingKey';

$sql ="DELETE FROM ProcessingAll WHERE ProcessingKey = $id";

if ($conn->query($sql) === TRUE) {
	header( 'Location: list.php?submit=true' ) ;
    
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();

mysqli_close($conn);


?>