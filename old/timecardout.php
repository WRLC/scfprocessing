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
<div style="margin: auto; width: 50%; padding: 10px;">
<div class="row center">
<div class="col s12 m12">
<div class="card white lighten-1">
<div class="card-content blue-text">
<span class="card-title" style="padding-left:50px;"><i class="material-icons medium" style="margin:2px 0 0 -65px; position:absolute;">access_time</i> Time Card Clock-Out: <?php echo $_SESSION['user_id']; ?><br />
</span>
<div class="row">
<?php
	
	if ($account != 'true') {
	 
if ($clockedout == 'true'){ echo '<br /><div class="card-title" style="color:#4CAF50;">You have checked out at '.$outtime.' on '.$outdate.'.</div>';	

	 }



//if($submit == 'true') echo '<div class="card-title" style="color:#4CAF50;">Success! You have checked in at<br /> '.$currentdateformatted.'.</div>';

$formurl = 'https://docs.google.com/forms/d/e/1FAIpQLScbAVOtrAnbXLKhAgbrI-a-PCbva4RxAYphPKs_oSF2tSXZGQ/formResponse'; ?>
<!-- ****  Javascript to submit the form to Google Sheets, then return to this page if successful **** --> 
<script type="text/javascript">var submitted=false;</script>
<iframe name="hidden_iframe" id="hidden_iframe" style="display:none;" onload="if(submitted) {window.location='refresh.php';}"></iframe>
<form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="GET" target="hidden_iframe" onsubmit="submitted=true;">

<!-- <form action="https://docs.google.com/forms/d/e/1FAIpQLSelZJBA1YQg4vJx6OTHotkmQ-TCZx24q2peSQ_BoMqKllqfDQ/formResponse" method="GET"> -->

<?php 

$namestaff = 'entry.1923401355';

?>
<input type="hidden" name="usp" value="pp_url" />
<input type="hidden" name="<?php echo $namestaff; ?>" value="<?php echo $name; ?>" />
<input type="hidden" name="submit" value="Submit" />
<br />
<br />
<?php 


if($clockedout != 'true' AND $clockedin == 'true') { echo '<button class="btn waves-effect waves-light center green" type="submit" >Confirm Clock-Out
    <i class="material-icons right">timer_off</i>
  </button>'; }
  
  else echo '<p>Be sure to Clock in, first!</p>';
  
 
  echo '</form>';

 }
      ?>
</div>
</div>
</div>
</div>
</div>
</div>

<!--JavaScript at end of body for optimized loading-->
<?php include('footer.php'); ?>
</body>
</html>