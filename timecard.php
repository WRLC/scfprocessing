<?php
session_start();

if ( isset( $_SESSION['user_id'] ) ) {
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

  <div class="row center">
    <div class="col s12 push-m3 m6">
      <div class="card white lighten-1">
        <div class="card-content blue-text"> <span class="card-title" style="padding-left:20px;"><i class="material-icons small" style="margin:2px 0 0 -35px; position:absolute;">access_time</i> Daily Time Card</span>
          <div class="row">
            <?php

   
$name = $_SESSION['user_id'];
$submit = $_GET['submit'];
$temp = $_SESSION['temp'];

	 
	
	

	

 echo '<img src="https://www.wrlc.org/sites/all/files/generic_avatar_300.gif" height="100px" class="small circle">
<span class="card-title">'.$name.'</span>'; 

   
   ?>
            <?php
	
	echo '
        </div>';

	if ($temp == 'yes') {
//Connect to Table	
$sql = "SELECT * FROM stafftimecards WHERE TimeCardName = '$name'";
//Loop
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))
{
	
//Set Timecard in time to variable to compare with today's date
	
$TimeCardMatchDate = strtotime($row['TimeCardCheckIn']);
$TimeCardIn = date('Ymd', $TimeCardMatchDate);



if($TimeCardIn == $currentdate AND $row['TimeCardCheckOut'] != '') {
$TimeCardCheckOutOld = strtotime($row['TimeCardCheckOut']);
$CheckedOut = date('m/d/Y g:ia', $TimeCardCheckOutOld);
}


//Set check in time to display version



$TimeCardMatchDate = strtotime($row['TimeCardCheckIn']);
$TimeCardInDisplay = date('m/d/Y g:ia', $TimeCardMatchDate);



//If the formatted timecard in date matches today's date, create a variable. Otherwise the variable says "false"
 if($TimeCardIn == $currentdate) {
	 $CheckedIn = $TimeCardIn; }
	 
	 else $CheckedIn = 'false';

 }
}
//End Loop
mysqli_close($conn);
//if in current date matches Date in row, display that row's check in date, else display the button.
		
	 if($CheckedIn == $currentdate) {
	  
 echo 'Checked in at '.$TimeCardInDisplay.'';
  }	
  
  else
       
         echo '<a href="timecardin.php" class="btn waves-effect waves-light center green">Time Card Clock-In
    <i class="material-icons right">timer</i>
  </a><br />'; 
		
//If checkin date matches today and checkout date is NOT Null

if($CheckedIn == $currentdate AND $CheckedOut =='')

echo '<br /><br /><a href="timecardout.php" class="btn waves-effect waves-light center green">Time Card Clock-out
    <i class="material-icons right">timer_off</i>
  </a>'; 
  
 elseif($CheckedIn == $currentdate AND $CheckedOut !='')
 
  echo '<br />Checked out at '.$CheckedOut.'';
  
  else echo '';

	
	

?>
          </div>
        </div>
      </div>
    </div>
  </div>

<center>
  <i>
  <p style="width:50%; margin-top:-20px;">If your Clock-In or Clock-Out times do not immediately appear, wait a few seconds and then refresh the page. <b>DO NOT resubmit your time card.</b> If you continue to receive errors, please contact Tammy.</p>
  </i>
</center>

<!--JavaScript at end of body for optimized loading-->
<?php include('footer.php'); ?>
</body>
</html>