<?php
require_once('connect.php');
//Convert POST form variables
$librarykey = $_POST['librarykey'];
$university = $_POST['university'];
$libname = $_POST['libname'];

switch ($libname) {
    case "American University":
        $code = 'AU';
      break;
    case "DC Public Library":
        $code = 'DCPL';
      break;
    case "Catholic University":
        $code = 'CU';
      break;
      case "Gallaudet University":
        $code = 'GA';
      break;
      case "George Mason University":
        $code = 'GM';
      break;
      case "George Washington University":
        $code = 'GW';
      break;
      case "Georgetown University":
        $code = 'GT';
      break;
      case "Howard University":
        $code = 'HU';
      break;
      case "Marymount University":
        $code = 'MU';
      break;
      case "National Security Archive":
        $code = 'NSA';
      break;
      case "University of DC":
        $code = 'UDC';
      break;
      case "WRLC":
        $code = 'WRLC';
      break;

    default:
    $code = 'TEST';
  }









$sql ="UPDATE LibraryLocations 
SET 
   	university = '$university',
    libname = '$libname',
    code = '$code'
    
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