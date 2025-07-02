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
<div class="card-content blue-text">
<span class="card-title" style="padding-left:50px;"><i class="material-icons medium" style="margin:2px 0 0 -65px; position:absolute;">access_time</i> Time Card Clock-Out: <?php echo $_SESSION['user_id']; ?><br />
</span>
<div class="row">
<?php
	
	



//if($submit == 'true') echo '<div class="card-title" style="color:#4CAF50;">Success! You have checked in at<br /> '.$currentdateformatted.'.</div>';

$formurl = 'timecardout_submit.php'; ?>
<!-- ****  Javascript to submit the form to Google Sheets, then return to this page if successful **** --> 
<script type="text/javascript">var submitted=false;</script>
<iframe name="hidden_iframe" id="hidden_iframe" style="display:none;" onload="if(submitted) {window.location='timecard.php';}"></iframe>
<form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="POST" target="hidden_iframe" onsubmit="submitted=true;">

<?php 

$sessionuser = $_SESSION['user_id'];
$today = date("Ymd");

$sql = "SELECT * FROM stafftimecards WHERE TimeCardName = '$name'";

$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))
{
	
$TimeCardMatchDate = strtotime($row['TimeCardCheckIn']);
$CurrentTimeCard = date('Ymd', $TimeCardMatchDate);

if($CurrentTimeCard == $today AND $row['TimeCardCheckOut'] == NULL) 
{
$timecardkey = $row['TimeCardKey'];	
$showbutton = 'true';
$timecardcheckout = $row['TimeCardCheckOut'];
}
else { 
$showbutton = 'false';
$timecardcheckout = $row['TimeCardCheckOut'];
}

}

if ($showbutton == 'true') {

echo '<input type="hidden" name="TimeCardKey" value="'.$timecardkey.'" />
<input type="hidden" name="submit" value="Submit" />
<br />
<br />';

 echo '<button class="btn waves-effect waves-light center green" type="submit" >Confirm Clock-Out
    <i class="material-icons right">timer_off</i>
  </button>'; 
  }
  else 
  
  {

	  
$old_checkout_timestamp = strtotime($timecardcheckout);
$checkout_date = date('m/d/Y g:ia', $old_checkout_timestamp);
	  
	  echo 'Checked out at '.$checkout_date.'';
 }
  
  echo '</form>';

 
 mysqli_close($conn);
      ?>
</div>
</div>
</div>
</div>
</div>


<!--JavaScript at end of body for optimized loading-->
<?php include('footer.php'); ?>
</body>
</html>