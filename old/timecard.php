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
        <div class="card-content blue-text"> <span class="card-title" style="padding-left:20px;"><i class="material-icons small" style="margin:2px 0 0 -35px; position:absolute;">access_time</i> Daily Time Card</span>
          <div class="row">
            <?php

   
$name = $_SESSION['user_id'];
$submit = $_GET['submit'];
/////////Get list of current WRLC Staff. If name does not match, they will see the time card information.
$sheetid = '1KfWokXDlJWp4nbRBMLW-JZfd-AnQ2bUXWo_pqHJueg8';
$url = 'https://spreadsheets.google.com/feeds/list/'.$sheetid.'/1/public/values?alt=json';
$file= file_get_contents($url);

$json = json_decode($file);
$rows = $json->{'feed'}->{'entry'};

foreach ($rows as $row) {
	$firstname = $row->{'gsx$firstname'}->{'$t'};
	$lastname = $row->{'gsx$lastname'}->{'$t'};
	$jobtitle = $row->{'gsx$jobtitle'}->{'$t'};
	$image = $row->{'gsx$image'}->{'$t'};
	$combinedname = $firstname.' '.$lastname;
	
	 if ($combinedname == $name) { 
	 
	 if(isset($image) AND $image != '')
	 
	echo ' <img src="'.$image.'" height="100px" class="small circle">
	
	<span class="card-title">'.$name.'</span>
	<p>'.$jobtitle.'</p>';
	
	$account = 'true';
	
	 }	 
}

if($account != 'true') { echo '<img src="https://www.wrlc.org/sites/all/files/generic_avatar_300.gif" height="100px" class="small circle">
<span class="card-title">'.$name.'</span>'; 
}
   
   ?>
            <?php
	
	echo '
        </div>';

	if ($account != 'true') {
 
if ($indateformatted == $currentdate AND $checkinname == $name) { 	

         echo ' 
       
          <p class="green-text"><i style="position:absolute; margin-left:-25px;" class="material-icons">check_circle</i> Clocked in at '.$intime.' on '.$indate.'</p>';
		  
		  $checkin = 'true';

}	

	
	if ($checkin != 'true')
	echo '<a href="timecardin.php" class="btn waves-effect waves-light center green">Time Card Clock-In
    <i class="material-icons right">timer</i>
  </a><br />'; 
  
	 
if ($clockedout == 'true') { 	

         echo ' 
       
          <p class="green-text"><i style="position:absolute; margin-left:-25px;" class="material-icons">check_circle</i> Clocked out at '.$outtime.' on '.$outdate.'</p>';
		  
		  $checkout = 'true';
		  
		  if(isset($hoursround) AND $hoursround !='') echo '<br />'. $hoursround.' hours worked';

}	
}
	
	if($clockedout != 'true' AND $clockedin == 'true') {
	echo '<br /><a href="timecardout.php" class="btn waves-effect waves-light center green">Time Card Clock-out
    <i class="material-icons right">timer_off</i>
  </a>'; 
  
	}

?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<center>
  <i>
  <p style="width:50%; margin-top:-20px;">If your Clock-In or Clock-Out times do not immediately appear, wait a few seconds and then refresh the page. <b>DO NOT resubmit your time card.</b>  If you continue to receive errors, please contact Tammy.</p>
  </i>
</center>

<!--JavaScript at end of body for optimized loading-->
<?php include('footer.php'); ?>
</body>
</html>