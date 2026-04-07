<?php
require_once('connect.php');
//Convert POST form variables

$CCName = filter_var($_POST['ccname'], FILTER_SANITIZE_STRING);
$ProcessingKey = filter_var($_POST['ProcessingKey'], FILTER_SANITIZE_STRING);
$CCCount = filter_var($_POST['cccount'], FILTER_SANITIZE_STRING);
$CCVerify = filter_var($_POST['ccverify'], FILTER_SANITIZE_STRING);
$CCChecked = filter_var($_POST['ccchecked'], FILTER_SANITIZE_STRING);
$CCScan = filter_var($_POST['ccscan'], FILTER_SANITIZE_STRING);


if($CCCount =='' OR $CCVerify =='' OR $CCChecked =='')
header( 'Location: crosscheck.php?submit=blank' ) ;

else {



$sql ="UPDATE ProcessingAll 
SET 
	cctimestamp = CURRENT_TIMESTAMP,
    ccname = '$CCName',
	cccount = '$CCCount',
	ccverify = '$CCVerify',
	ccchecked = '$CCChecked',
    ccscan = '$CCScan',
    updated = CURRENT_TIMESTAMP
	
WHERE
    ProcessingKey = $ProcessingKey";
	
	if ($conn->query($sql) === TRUE) {
	header( 'Location: crosscheck.php?submit=true' ) ;
   // echo "success!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
}

$conn->close();

mysqli_close($conn);



?>
