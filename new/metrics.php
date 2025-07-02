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
<div class="row">
<div class="col s12 push-m3 m6">
<div class="card white lighten-1">
<div class="card-content blue-grey-text">
<span class="card-title center">Temp Staff Metrics<br />
Goal: 200 Volumes per hour<br />
<?php echo $currentmdy; ?></span> 
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

$name = $_SESSION['user_id'];
$submit = $_GET['submit'];

if($submit == 'true') echo '<div id="hideMe" class="card-title" style="color:#4CAF50;">Success!</div>'; ?>
<div class="row">
<?php 

// set values for loop for sums
$ptotal = 0;
$cctotal = 0;
$workcount = 0;
$daysort = '>= DATE(NOW())';
$nowformatted = date('Y-m-d H:i:s');
//$daysort = '>= DATEADD(day, -1, convert(date, GETDATE()))';


              echo '
              </div>
			  <div class="row">';
	
	 /////Get Staff information	  
$sql = "SELECT * FROM Staff WHERE temp = 'yes' ORDER by name ASC";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{
	
	$staffname = $row['name'];
	$id = $row['staffkey'];
		
	// AND ptimestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
$sqlp = "SELECT SUM(pcount) FROM ProcessingAll WHERE pname = '$staffname' AND ptimestamp $daysort";
//$sqlp = "SELECT SUM(pcount) FROM ProcessingAll WHERE pname = '$staffname'";
$queryp	= mysqli_query($conn, $sqlp);
while ($rowp = mysqli_fetch_array($queryp))
	{
		if(isset($rowp['SUM(pcount)']))
		$sumpcount = $rowp['SUM(pcount)'];	
		else $sumpcount = 0;
		
	}
	
$sqlcc = "SELECT SUM(cccount) FROM ProcessingAll WHERE ccname = '$staffname' AND cctimestamp $daysort";
//$sqlcc = "SELECT SUM(cccount) FROM ProcessingAll WHERE ccname = '$staffname'";
$querycc	= mysqli_query($conn, $sqlcc);
while ($rowcc = mysqli_fetch_array($querycc))
	{
		$sumcccount = $rowcc['SUM(cccount)'];	
		
	}

	$volumestotal = $sumcccount + $sumpcount;
	
$sqldiff = "SELECT TIMESTAMPDIFF(HOUR, TimeCardCheckIn, TimeCardCheckOut) AS difference, TimeCardCheckIn, TimeCardCheckOut FROM stafftimecards WHERE TimeCardName = '$staffname' AND TimeCardCheckIn $daysort";

$querydiff 	= mysqli_query($conn, $sqldiff);
while ($rowdiff = mysqli_fetch_array($querydiff))

{
$timecardin = $rowdiff['TimeCardCheckIn'];
$timecardout = $rowdiff['TimeCardCheckOut'];
$difference = $rowdiff['difference'];


$TimeCardMatchDate = strtotime($rowdiff['TimeCardCheckIn']);

	if(isset($rowdiff['TimeCardCheckIn']) AND $rowdiff['TimeCardCheckIn'] !==NULL) {
		$CheckedIn = date('g:ia', $TimeCardMatchDate);
		}
		
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


	
	 echo '  <div class="col s12 m6">
	 <div class="card">
       <!--  <div class="card-image">
          <img src="images/sample-1.jpg">
		  
          <span class="card-title black-text">'.$ptotal.' test</span>
        </div> -->
        <div class="card-content center">';
		
		
		if($average >=175) 
		
          echo '<p><i class="material-icons green-text center" style="font-size:72px;">trending_up</i></p>';
		  
		  if($average <=174 AND $average >=75) 
		  echo '<p><i class="material-icons orange-text center" style="font-size:72px;">trending_flat</i></p>';
		  
		   if($average <=74 AND $average >=0) 
		  echo '<p><i class="material-icons red-text center" style="font-size:72px;">trending_down</i></p>';
		  		  
		  echo '<table class="highlight">
<thead>
<tr>
<th></th><th></th>           
</tr>
</thead>
<tbody>';

if($average < 1) $average = 0;
		  
		  
		  if(isset($sumcccount)) $sumcccount = $sumcccount;
		  else $sumcccount = '0';
		  
		   if(isset($sumpcount)) $sumpcount = $sumpcount;
		  else $sumpcount = '0';
		  
		  echo '<tr><td>Processed: </td><td class="right">'.$sumpcount.'</td></tr>
		  <tr><td>Cross Checked:</td><td class="right"> '.$sumcccount.'</td></tr>
		  <tr><td>Total: </td><td class="right">'.$volumestotal.'</td></tr>
		  </tbody>
		</table>
		<br /><br />';
		if(isset($CheckedIn)) echo '<p>Checked in: '.$CheckedIn.'</p>';
		if(isset($CheckedOut)) echo '<p>Checked Out: '.$CheckedOut.'</p>';
		echo '<p>Hours Worked: '.$worked.'</p>
		<p>'.$average.' Volumes per hour</p>
		  
        </div>
        <div class="card-action center blue-grey white-text">
          <a class="white-text hoverable" href="staff_edit.php?id='.$id.'">'.$staffname.'</a> <a class="right" href="hours.php?name='.$staffname.'"> <i class="material-icons">timer</i></a>
        </div>
      </div>
    </div>';
// reset values to 0 for next loop
$ptotal = 0;
$cctotal = 0;
	}
	
   if($workcount == 0) echo '<h4 class="center">No Temp Staff Processing Today.</h4>';
	
 echo ' </div>';
 
mysqli_close($conn);
      echo '  
              </div> </div>
  </div>
</div>

<!--JavaScript at end of body for optimized loading-->';
include('footer.php'); ?>
</body>
</html>