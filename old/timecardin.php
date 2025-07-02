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
<span class="card-title" style="padding-left:50px;"><i class="material-icons medium" style="margin:2px 0 0 -65px; position:absolute;">access_time</i> Time Card Clock-In: <?php echo $_SESSION['user_id']; ?><br />
</span>
<div class="row">

<?php
	
if ($account != 'true') {
	 
	 
if ($clockedin == 'true' AND $indateformatted == $currentdate){ echo '<br /><div class="card-title" style="color:#4CAF50;">You have checked in at '.$intime.' on '.$indate.'.</div>';	

	 }

$formurl = 'https://docs.google.com/forms/d/e/1FAIpQLSdeQ38d3L_4LU5nVTCBupcuC4584fZ7aHVJBuVqGXrK9Ln53g/formResponse'; ?>
<!-- ****  Javascript to submit the form to Google Sheets, then return to this page if successful **** --> 
<script type="text/javascript">var submitted=false;</script>
<iframe name="hidden_iframe" id="hidden_iframe" style="display:none;" onload="if(submitted) {window.location='refresh.php';}"></iframe>
<form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="GET" target="hidden_iframe" onsubmit="submitted=true;">

<?php 

$namestaff = 'entry.1923401355';

?>
<input type="hidden" name="usp" value="pp_url" />
<input type="hidden" name="<?php echo $namestaff; ?>" value="<?php echo $name; ?>" />
<input type="hidden" name="submit" value="Submit" />
<br />
<br />
<?php 

if($clockedin != 'true') echo '<button class="btn waves-effect waves-light center green" type="submit" >Confirm Clock-In
    <i class="material-icons right">timer</i>
  </button></form>';

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