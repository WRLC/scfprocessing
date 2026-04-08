<?php
require_once('connect.php');
//Convert POST form variables
$ProcessingKey = $_POST['ProcessingKey'];

//$pname = $_POST['pname'];


$ptraylocation = filter_var($_POST['ptraylocation'], FILTER_SANITIZE_STRING);
$ptraylocation = !empty($ptraylocation) ? "'$ptraylocation'" : "NULL";

$pcount = filter_var($_POST['pcount'], FILTER_SANITIZE_STRING);
$pcount = !empty($pcount) ? "'$pcount'" : "NULL";

$pfull = filter_var($_POST['pfull'], FILTER_SANITIZE_STRING);
$pfull = !empty($pfull) ? "'$pfull'" : "NULL";

$pverify = filter_var($_POST['pverify'], FILTER_SANITIZE_STRING);
$pverify = !empty($pverify) ? "'$pverify'" : "NULL";

$pchecked = filter_var($_POST['pchecked'], FILTER_SANITIZE_STRING);
$pchecked = !empty($pchecked) ? "'$pchecked'" : "NULL";

$plibrary = filter_var($_POST['plibrary'], FILTER_SANITIZE_STRING);
$plibrary = !empty($plibrary) ? "'$plibrary'" : "NULL";

//$ccname = $_POST['ccname'];
$cccount = filter_var($_POST['cccount'], FILTER_SANITIZE_STRING);
$cccount = !empty($cccount) ? "'$cccount'" : "NULL";

$ccverify = filter_var($_POST['ccverify'], FILTER_SANITIZE_STRING);
$ccverify = !empty($ccverify) ? "'$ccverify'" : "NULL";

$ccchecked = filter_var($_POST['ccchecked'], FILTER_SANITIZE_STRING);
$ccchecked = !empty($ccchecked) ? "'$ccchecked'" : "NULL";

$ccscan = filter_var($_POST['ccscan'], FILTER_SANITIZE_STRING);
$ccscan = !empty($ccscan) ? "'$ccscan'" : "NULL";

$cctimestamp = filter_var($_POST['cctimestamp'], FILTER_SANITIZE_STRING);
$cctimestamp = !empty($cctimestamp) ? "'$cctimestamp'" : "NULL";

$ptimestamp = filter_var($_POST['ptimestamp'], FILTER_SANITIZE_STRING);
$ptimestamp = !empty($ptimestamp) ? "'$ptimestamp'" : "NULL";


$traytemp = filter_var($_POST['ptraylocation'], FILTER_SANITIZE_STRING);
$pcode = substr($traytemp, -2);


$sql ="UPDATE ProcessingAll 
SET 

	ptraylocation = $ptraylocation,
	pcode = '$pcode',
	pcount = $pcount,
	pfull = $pfull,
	pverify = $pverify,
	pchecked = $pchecked,
	plibrary = $plibrary,
	cccount = $cccount,
	ccverify = $ccverify,
	cctimestamp = $cctimestamp,
	ptimestamp = $ptimestamp,
	ccchecked = $ccchecked,
    ccscan = $ccscan,
    updated = CURRENT_TIMESTAMP
	
WHERE
    ProcessingKey = $ProcessingKey";
	
	if ($conn->query($sql) === TRUE) {
	 header( 'Location: edit.php?id='.$ProcessingKey) ;
   // echo "success!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();

mysqli_close($conn);



?>
