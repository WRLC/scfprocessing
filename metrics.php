<?php
session_start();

if ( isset( $_SESSION['user_id'] ) AND $_SESSION['admin'] =='yes' ) {
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
<div>
<div class="row" >

<div class="col s12 push-m3 m6" style="top:710px;">

<div class="card white lighten-1">
<div class="card-content blue-grey-text" >
<span class="card-title center">Temp Staff<br />
Goal: 200 Volumes per hour<br /></span> 
<!-- <a class="btn waves-effect waves-light right indigo darken-1" href="staff_new.php">New<i class="material-icons left">account_circle</i></a>-->
<div class="row">
<style>
#hideMe {
    -moz-animation: cssAnimation 0s ease-in 3s forwards;
    /* Firefox */
    -webkit-animation: cssAnimation 0s ease-in 3s forwards;
    /* Safari and Chrome */
    -o-animation: cssAnimation 0s ease-in 3s forwards;
    /* Opera */
    animation: cssAnimation 0s ease-in 3s forwards;
    -webkit-animation-fill-mode: forwards;
    animation-fill-mode: forwards;
}
@keyframes cssAnimation {
    to {
        width:0;
        height:0;
        overflow:hidden;
    }
}
@-webkit-keyframes cssAnimation {
    to {
        width:0;
        height:0;
        visibility:hidden;
    }
}
</style>
<?php

$monday = date( 'm/d/Y', strtotime( 'monday this week' ) );
$friday = date( 'm/d/Y', strtotime( 'friday this week' ) );

//echo $monday.$friday;


$name = $_SESSION['user_id'];
$submit = $_GET['submit'];

function getRatio($num1, $num2){
    for($i = $num2; $i > 1; $i--) {
        if(($num1 % $i) == 0 && ($num2 % $i) == 0) {
            $num1 = $num1 / $i;
            $num2 = $num2 / $i;
        }
    }
    return "$num1 : $num2";
}
 

if($submit == 'true') echo '<div id="hideMe" class="card-title" style="color:#4CAF50;">Success!</div>'; ?>
<div class="row">
<?php 

// set values for loop for sums
$ptotal = 0;
$cctotal = 0;
$workcount = 0;
$daysort = '>= DATE(NOW())';
$nowformatted = date('Y-m-d H:i:s');
$nowdate = date('Y-m-d');
//$daysort = '>= DATEADD(day, -1, convert(date, GETDATE()))';


              echo '
              </div>
			  <div class="row">';
			  
			  
			  
			  
			  
			  
//define sums of processed and cross checked items as 0 to loop to create a total counts			  
$psum = 0;
$ccsum = 0;
$totalsum = 0;
$workedsum = 0;
$valuesum = 0;
$costsum = 0;




	 /////Get Staff information	  
$sql = "SELECT * FROM Staff WHERE temp = 'yes' and name != 'Charlena Jordan' ORDER by name ASC";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{
	
	$staffname = $row['name'];
	$id = $row['staffkey'];
	
	$sqlp = "SELECT SUM(pcount) FROM ProcessingAll WHERE pname = '$staffname' AND ptimestamp $daysort";
//$sqlp = "SELECT SUM(pcount) FROM ProcessingAll WHERE pname = '$staffname'";
$queryp	= mysqli_query($conn, $sqlp);
while ($rowp = mysqli_fetch_array($queryp))
	{
		if(isset($rowp['SUM(pcount)']))
		$sumpcount = $rowp['SUM(pcount)'];	
		else $sumpcount = 0;
		//$psum = $psum + $sumpcount;
		
	}
	
	
	
	
	
	
	
	
$sqlcc = "SELECT SUM(cccount) FROM ProcessingAll WHERE ccname = '$staffname' AND cctimestamp $daysort";
//$sqlcc = "SELECT SUM(cccount) FROM ProcessingAll WHERE ccname = '$staffname'";
$querycc	= mysqli_query($conn, $sqlcc);
while ($rowcc = mysqli_fetch_array($querycc))
	{
		$sumcccount = $rowcc['SUM(cccount)'];	
		//$cccsum = $cccsum + $sumcccount;
		
	}

	$volumestotal = $sumcccount + $sumpcount;
	
	
	
$sqldiff = "SELECT TIMESTAMPDIFF(HOUR, TimeCardCheckIn, TimeCardCheckOut) AS difference, TimeCardCheckIn, TimeCardCheckOut, TimeCardName FROM stafftimecards WHERE TimeCardName = '$staffname' AND TimeCardName != 'Charlena Jordan' AND TimeCardCheckIn $daysort";

$querydiff 	= mysqli_query($conn, $sqldiff);
while ($rowdiff = mysqli_fetch_array($querydiff))

{
$timecardin = $rowdiff['TimeCardCheckIn'];
$timecardout = $rowdiff['TimeCardCheckOut'];
$difference = $rowdiff['difference'];
$timecardname = $rowdiff['TimeCardName'];


$TimeCardMatchDate = strtotime($rowdiff['TimeCardCheckIn']);


$incompare = date('Y-m-d', $TimeCardMatchDate);


	if(isset($rowdiff['TimeCardCheckIn']) AND $rowdiff['TimeCardCheckIn'] !==NULL) {
		$CheckedIn = date('g:ia', $TimeCardMatchDate);
		}
		else {$CheckedIn = NULL; }
		
$TimeCardMatchDate2 = strtotime($rowdiff['TimeCardCheckOut']);

	if(isset($rowdiff['TimeCardCheckOut']) AND $rowdiff['TimeCardCheckOut'] !==NULL) {
		$CheckedOut = date('g:ia', $TimeCardMatchDate2);
		}

if(isset($timecardout) AND $timecardout !==NULL ) {
	
$endtime = $timecardout;

}

else $endtime = $nowformatted;

}
	
$diff2 = abs(strtotime($timecardin) - strtotime($endtime));
$tmins2 = $diff2/60;
$hours2 = floor($tmins2/60);
$mins2 = $tmins2%60;

$mindecimal = $mins2 / 60;
$worked = $hours2 + $mindecimal;
$worked = number_format($worked, 2, '.', '');


$average = $volumestotal / $worked;
$average = number_format($average);
$workcount = $workcount + 1;

$value = $volumestotal * .75;
$cost = $worked * 21;
$ratio = $value / $cost;

setlocale(LC_MONETARY, 'en_US');
$cost = money_format('%(#10n', $cost);

setlocale(LC_MONETARY, 'en_US');
$value = money_format('%(#10n', $value);



 




if(isset($timecardin) AND $staffname == $timecardname) {

	
	 echo '  <div class="col s12 m6">
	 <div class="card">';
	 
	 echo '
       <!--  <div class="card-image">
          <img src="images/sample-1.jpg">
		  
          <span class="card-title black-text">'.$ptotal.'</span>
        </div> -->
        <div class="card-content center">';
		
		echo '<h5>'.$staffname.'</h5>';
		
		  
		  echo '<h2 class="card-title center">Processing Counts</h2>';
		  		  
		  echo '<table class="highlight" style="background-color:#f5f5f5; padding:10px; border-radius:10px;">
<thead>

</thead>
<tbody>';

if($average < 1) $average = 0;
		  
		  
		  if(isset($sumcccount)) $sumcccount = $sumcccount;
		  else $sumcccount = '0';
		  
		   if(isset($sumpcount)) $sumpcount = $sumpcount;
		  else $sumpcount = '0';
		  
		  echo '<tr><td style="padding:20px; border:none;">Processed: </td><td style="padding:20px;" class="right">'.$sumpcount.'</td></tr>
		  <tr><td style="padding:20px;">Cross Checked:</td><td style="padding:20px;" class="right"> '.$sumcccount.'</td></tr>
		  <tr style="border:none;"><td style="padding:20px;">Total: </td><td style="padding:20px;" class="right">'.$volumestotal.'</td></tr>
		  </tbody>
		</table>
		<br /><br />';
		//create totals for each category
		$psum = $psum + $sumpcount;
		$ccsum = $ccsum + $sumcccount;
		$workedsum = $workedsum + $worked;
		$totalsum = $totalsum + $volumestotal;
		$valuesum = $valuesum + $value;
		$costsum = $costsum + $cost;
		
		if(round($ratio) >= 4) $color = "blue";
		if(round($ratio) == 3) $color = "green";
		if(round($ratio) <= 2) $color = "red";
		
		
	if(round($ratio) >= 4)  {
		
          echo '<p><i class="material-icons '.$color.'-text center" style="font-size:30px;">star</i><i class="material-icons '.$color.'-text center" style="font-size:30px;">star</i><i class="material-icons '.$color.'-text center" style="font-size:30px;">star</i><i class="material-icons '.$color.'-text center" style="font-size:30px;">star</i><i class="material-icons '.$color.'-text center" style="font-size:30px;">star</i></p>';}
		  
		  if(round($ratio) == 3) 
		  echo '<p><i class="material-icons green-text center" style="font-size:30px;">star</i><i class="material-icons green-text center" style="font-size:30px;">star</i><i class="material-icons green-text center" style="font-size:30px;">star</i><i class="material-icons green-text center" style="font-size:30px;">star_border</i><i class="material-icons green-text center" style="font-size:30px;">star_border</i></p>';
		  
		   if(round($ratio) <= 2) 
		  echo '<p><i class="material-icons red-text center" style="font-size:30px;">star_half</i><i class="material-icons red-text center" style="font-size:30px;">star_border</i><i class="material-icons red-text center" style="font-size:30px;">star_border</i><i class="material-icons red-text center" style="font-size:30px;">star_border</i><i class="material-icons red-text center" style="font-size:30px;">star_border</i></p>';
		  
		  
		  
		  if(round($ratio) >= 4) 
		
          echo '<p class="'.$color.'-text center" style="font-size:18px;">High Performance</p>';
		  
		  
		  if(round($ratio) == 3) 
		  echo '<p class="'.$color.'-text center" style="font-size:18px;">Efficient Performance</p>';
		  
		  
		  
		   if(round($ratio) <= 2) 
		    echo '<p class="'.$color.'-text center" style="font-size:18px;">Below Expectations</p>';
		  
		  
		
		  
		
		  echo '<h2 class="card-title center">Value Metrics</h2>';
		
		 echo '<table class="highlight">
<thead>
<tr>
<th></th><th></th>           
</tr>
</thead>
<tbody>';
		
		if(isset($CheckedIn)) echo '<tr><td>Checked in:</td><td class="right">'.$CheckedIn.'</td></tr>';
		if(isset($CheckedOut)) echo '<tr><td>Checked Out:</td><td class="right">'.$CheckedOut.'</td></tr>';
		
		echo '<tr><td>Hours Worked:</td><td class="right">'.$worked.'</td></tr>
		<tr><td>Volumes per Hour:</td><td class="right">'.$average.' </td></tr>
		<tr><td>Accessions Income:</td><td class="right">$'.$value.'</td></tr>
		<tr><td>Salary:</td><td class="right">$'.$cost.'</td></tr>';
		
		if(round($ratio) >= 4) $color = "blue";
		if(round($ratio) == 3) $color = "green";
		if(round($ratio) <= 2) $color = "red";
		
		echo '<tr class="'.$color.'-text"><td style="font-weight:bold;">Ratio (AI:S):</td><td style="font-weight:bold;" class="right">'.round($ratio).':1</td></tr>';
		
		
		
		// echo '<tr><td style="color:'.$color.';">Ratio (AI:S):</td><td class="right" style="color:'.$color.';">'.getRatio(number_format($value), number_format($cost)).'</td></tr>';
	echo '	</tbody>
		</table>
		<br /><br />
		  
        </div>
        <div class="card-action center blue-grey white-text">
          <a class="white-text hoverable" href="staff_edit.php?id='.$id.'">Edit Account</a> <a class="right" href="hours.php?name='.$staffname.'"> <i class="material-icons">timer</i></a>
        </div>
      </div>
    </div>';
// reset values to 0 for next loop
$ptotal = 0;
$cctotal = 0;
	} }
	
   if($workcount == 0) echo '<h4 class="center">No Temp Staff Processing Today.</h4>';
	
 echo ' </div> </div> </div></div>';
 
 

 
 
 
 
 
mysqli_close($conn);

/// Begin Weekly Stats

$averagesum = $totalsum / $workedsum;
$averagesum = number_format($averagesum);

$valuesum = money_format('%(#10n', $valuesum);
$costsum = money_format('%(#10n', $costsum);

$ratiosum = $valuesum / $costsum;
      
	  
	  echo '<div class="col s12 m12" style="position:absolute; margin-left:-10px; top:-700px;">
	 <div class="card">
      
        <div class="card-content blue-grey-text center"><h4>Temp Staff Efficiency Metrics</h4>
		
		<h2 class="card-title center">'.$currentmdy.'</h2>
		
		
		<table class="highlight" style="background-color:#f5f5f5; padding:10px; border-radius:10px;">';
		
		if(round($ratiosum) >= 4) $color = "blue";
		if(round($ratiosum) == 3) $color = "green";
		if(round($ratiosum) <= 2) $color = "red";
		
		if(round($ratiosum) >= 4) 
		
          echo '<p><i class="material-icons blue-text center" style="font-size:60px;">star</i><i class="material-icons blue-text center" style="font-size:60px;">star</i><i class="material-icons blue-text center" style="font-size:60px;">star</i><i class="material-icons blue-text center" style="font-size:60px;">star</i><i class="material-icons blue-text center" style="font-size:60px;">star</i></p>';
		  
		  if(round($ratiosum) == 3) 
		  echo '<p><i class="material-icons green-text center" style="font-size:60px;">star</i><i class="material-icons green-text center" style="font-size:60px;">star</i><i class="material-icons green-text center" style="font-size:60px;">star</i><i class="material-icons green-text center" style="font-size:60px;">star_border</i><i class="material-icons green-text center" style="font-size:60px;">star_border</i></p>';
		  
		   if(round($ratiosum) <= 2) 
		  echo '<p><i class="material-icons red-text center" style="font-size:60px;">star_half</i><i class="material-icons red-text center" style="font-size:60px;">star_border</i><i class="material-icons red-text center" style="font-size:60px;">star_border</i><i class="material-icons red-text center" style="font-size:60px;">star_border</i><i class="material-icons red-text center" style="font-size:60px;">star_border</i></p>';
		  
		  
		  if(round($ratiosum) >= 4) 
		
          echo '<p class="blue-text center" style="font-size:18px;">High Performance</p>';
		  
		  
		  if(round($ratiosum) == 3) 
		  echo '<p class="green-text center" style="font-size:18px;">Efficient Performance</p>';
		  
		  
		  
		   if(round($ratiosum) <= 2) 
		    echo '<p class="red-text center" style="font-size:18px;">Below Expectations</p>';
		  
		  
		
		 echo '
		  <table class="highlight">

<thead>
<tr>
<th></th><th></th>           
</tr>
</thead>
<tbody>

		
		<tr><td>Accessions:</td><td class="right">'.$totalsum.'</td></tr>
		<tr><td>Accessions per Hour:</td><td class="right">'.$averagesum.'</td></tr>
		<tr><td>Accessions Income:</td><td class="right">$'.$valuesum.'</td></tr>
		<tr><td>Staffing Hours:</td><td class="right">'.$workedsum.'</td></tr>
		<tr><td>Staffing Cost:</td><td class="right">$'.$costsum.'</td></tr>';
		
		if(round($ratiosum) >= 4) $color = "blue";
		if(round($ratiosum) == 3) $color = "green";
		if(round($ratiosum) <= 2) $color = "red";
		
		echo '<tr class="'.$color.'-text"><td style="font-weight:bold;">Ratio (AI:SC):</td><td style="font-weight:bold;" class="right">'.round($ratiosum).':1</td></tr>';
		
		
		
		
		
		echo '
		
		</tbody>
		</table>
		<br><br>
		  
        </div>
		
		
		
		
		
		
       
      </div>
    </div>';
	  
	  
               echo ' </div>';
			 
			  
			  echo ' </div>
  </div>
</div>

<!--JavaScript at end of body for optimized loading-->';
include('footer.php'); ?>
</body>
</html>