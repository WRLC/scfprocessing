<?php
require_once('connect.php');

$Name = filter_var($_POST['Name'], FILTER_SANITIZE_STRING);

$traytemp = filter_var($_POST['TrayLocation'], FILTER_SANITIZE_STRING);

$TrayLocation = filter_var($_POST['TrayLocation'], FILTER_SANITIZE_STRING);
$TrayLocation = !empty($TrayLocation) ? "'$TrayLocation'" : "NULL";

echo $traytemp;

$sql = "SELECT ptraylocation FROM ProcessingAll WHERE ptraylocation = '$traytemp'";
$i = 0;
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))
{
	$i = $i + 1;
}


if($i > 0)

header( 'Location: processing.php?submit=false' ) ;

else {
	
	
$Count = filter_var($_POST['Count'], FILTER_SANITIZE_STRING);
$Full = filter_var($_POST['Full'], FILTER_SANITIZE_STRING);
$Full = !empty($Full) ? "'$Full'" : "NULL";
$Verify = filter_var($_POST['Verify'], FILTER_SANITIZE_STRING);
$Checked = filter_var($_POST['Checked'], FILTER_SANITIZE_STRING);
$Library = filter_var($_POST['Library'], FILTER_SANITIZE_STRING);

$PCode = substr($traytemp, -2);


if($Library =='' OR $traytemp =='' OR $Count =='' OR $Checked =='' OR $Verify =='')
header( 'Location: processing.php?submit=blank' ) ;

else {


$sql = "INSERT INTO ProcessingAll (ProcessingKey, ptimestamp, pname, ptraylocation, pcode, pcount, pfull, pverify, pchecked, plibrary, cctimestamp, ccname, cccount, ccverify, ccchecked) VALUES (NULL, CURRENT_TIMESTAMP, '$Name', $TrayLocation, '$PCode', '$Count', $Full, '$Verify', '$Checked', '$Library', NULL, NULL, NULL, NULL, NULL)";

if ($conn->query($sql) === TRUE) {
	header( 'Location: processing.php?submit=true' ) ;
    
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
}
}
$conn->close();

mysqli_close($conn);


?>