<?php
session_start();

if ( isset( $_SESSION['user_id'] ) AND $_SESSION['user_id'] =='Tammy Hennig' ) {
    // Grab user data from the database using the user_id
    // Let them access the "logged in only" pages
	$now = time(); // Checking the time now when home page starts.

        if ($now > $_SESSION['expire']) {
            session_destroy();
            header("Location: login.php");
        }
	
	
} else {
    // Redirect them to the login page
    header("Location: login.php");
}
?>
<?php include('header.php'); ?>


<div style="margin: auto; width: 100%; padding: 10px;">



  

 
      <div style="margin-top:-26px;" class="card white lighten-1">
       
        <h3 style="padding:20px 20px 0; font-size:2em;" class="purple-text">SCF Tray Processing List</h3>
       


<style>

.alert {background-color:#ff0000; color:#fff; text-align:center;}
.flag {color:orange;}


td,th {
font-size:11px !important;
border-radius:0!important;}


</style>


</head>
<body>
<table class="highlight responsive-table">

<?php 

$processingcolor = '#7986cb; color:#fff;';
$crosscolor = '#90caf9; color:#fff;';


echo '
<thead>
<tr>
<th class="white-text purple darken-2 center" colspan="7">Processing</th>
<th class="white-text blue darken-2 center" colspan="5">Cross Check</th>
<th class="white-text teal center">Match</th>
</tr>
</thead>
<thead>
<tr><th class="purple-text purple lighten-4 center">Tray Number</th>
<th class="purple-text purple lighten-4 center">Library</th>
<th class="purple-text purple lighten-4 center">Name</th>
<th class="purple-text purple lighten-4 center">Time</th>
<th class="purple-text purple lighten-4 center">Count</th>
<th class="purple-text purple lighten-4 center">Full</th>
<th class="purple-text purple lighten-4 center">Search</th>


<th class="white-text light-blue  center">Tray Number</th>
<th class="white-text light-blue  center">Name</th>
<th class="white-text light-blue  center">Time</th>
<th class="white-text light-blue center">Count</th>
<th class="white-text light-blue  center">Search</th>
<th class="teal"></th>



</tr></thead><tbody>';
?>
<?php

$i = 0;

// $sheetid = '1q5B7mUCZplrygb2viFSGkwNhYb7oLjTHwfZiikfp1rc';
$sheetid = '1exbCuvsm63PILUAwjZZk_h6DEgs0IqxQ3_PUGSN4cdc';
$url = 'https://spreadsheets.google.com/feeds/list/'.$sheetid.'/1/public/values?alt=json';
$file= file_get_contents($url);

$json = json_decode($file);
$rows = $json->{'feed'}->{'entry'};

foreach ($rows as $row) {
	




	
	 $timestamp = $row->{'gsx$timestamp'}->{'$t'};
	 $name = $row->{'gsx$name'}->{'$t'};
	 $barcode = $row->{'gsx$barcode'}->{'$t'};
	 $url = $row->{'gsx$url'}->{'$t'};
	 $count = $row->{'gsx$count'}->{'$t'};
	 $full = $row->{'gsx$full'}->{'$t'};
	 $verify = $row->{'gsx$verify'}->{'$t'};
	 $av = $row->{'gsx$av'}->{'$t'};
	 $library = $row->{'gsx$library'}->{'$t'};
	 
	 
	
	 
	 
	
	
	 
	 if ($i % 2 == 0){
echo "<tr>";
}
else{
echo "<tr>";

	 }
	
	if (isset($timestamp) AND $timestamp !='')  {
		
		
		
	echo '<td><a style="text-decoration:underline;" href="'.$url.'" target="_blank">'.$barcode.'</a></td>
	<td style="text-align:center;">'.$library.'</td>
	<td>'.$name.'</td>
	<td>'.$timestamp.'</td>
	<td style="text-align:center;">'.$count.'</td>
	<td style="text-align:center;">'.$full.'</td>
	<td style="text-align:center;">'.$av.'</td>';
	
	foreach ($rows as $row) {
		
	 $crosstimestamp = $row->{'gsx$crosstimestamp'}->{'$t'};
	 $crossname = $row->{'gsx$crossname'}->{'$t'};
	 $crossbarcode = $row->{'gsx$crossbarcode'}->{'$t'};
	 $crossurl = $row->{'gsx$crossurl'}->{'$t'};
	 $crosscount = $row->{'gsx$crosscount'}->{'$t'};
	 $crossverify = $row->{'gsx$crossverify'}->{'$t'};
	 $crossav = $row->{'gsx$crossav'}->{'$t'};
	 
	if ($barcode == $crossbarcode)  {
	
	echo '<td style="border-left:1px solid #03a9f4;"><a style="text-decoration:underline;" href="'.$crossurl.'" target="_blank">'.$crossbarcode.'</a></td>';
	
	
	
	if ($name == $crossname)  echo 	
	'<td><span class=" new badge orange" data-badge-caption="">'.$crossname.'</span></td>';
	else echo '<td>'.$crossname.'</td>';
	echo '<td>'.$crosstimestamp.'</td>
	<td style="text-align:center;">'.$crosscount.'</td>
	<td style="text-align:center;">'.$crossav.'</td>';
	
	 if ($count != $crosscount) {echo '<td><span class=" new badge red" data-badge-caption="">';
	 echo abs($count - $crosscount);
	 echo '</span></td>';}
	 else echo '<td style="text-align:center;">Yes</td>';
	
	
//	echo '<td>';
	
//	$ts1 = strtotime($timestamp);
// $ts2 = strtotime($crosstimestamp);     
// $seconds_diff = $ts2 - $ts1;                            
// $time = ($seconds_diff/60);
	
//	 echo $time;
//	echo '</td>';
	
	}
		 
	 } 
	echo '</tr>';
	$i++;
}

}
?>


</tbody></table>

</div>



</div>
<?php include('footer.php'); ?>
</body>
</html>
