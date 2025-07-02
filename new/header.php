<!DOCTYPE html>
<?php include('connect.php'); ?>
<html>
<head>
<!-- https://materializecss.com/getting-started.html -->
<!--Import Google Icon Font-->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<!--Import materialize.css-->
<link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>
<link type="text/css" rel="stylesheet" href="css/print.css"  media="print"/>
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />

<!--Let browser know website is optimized for mobile-->
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<style>
@media only screen and (max-width: 450px) {
.mobile-hide {
	display: none;
}
.mobile-show {
	font-size:24px;
}
}
 @media only screen and (min-width: 451px) {
.mobile-show {
	display: none;
}
}
</style>
</head>
<body class="grey lighten-3">
<div class="no-print">
  <ul id="dropdown1" class="dropdown-content">
    <li><a href="https://scfutils.wrlc.org/update-field/alt-call" target="_blank">Add Item Call Number<i style="font-size:10px;" class="material-icons right small">filter_none</i></a>
    <li class="divider"></li>
    <li><a href="https://scfutils.wrlc.org/update-field/int-note" target="_blank">Add Internal Note 1<i style="font-size:10px;" class="material-icons right small">filter_none</i></a>
    <li class="divider"></li>
    <li><a href="processing.php">Tray/Shelf Location Form</a></li>
    <li class="divider"></li>
    <li><a href="crosscheck.php">Cross Check Form</a>
    <li class="divider"></li>
    <li><a href="index.php">View All</a></li>
  </ul>
  <ul id="dropdown-reports" class="dropdown-content">
    <!--<li><a href="list.php">Daily List</a></li>
    <li class="divider"></li>-->
    <li><a href="list.php?order=ptimestamp&sort=DESC&date=WEEK">Edit Processing Records</a></li>
    <li class="divider"></li>
    <li><a href="https://datastudio.google.com/s/hEdmRzONLTo" target="_blank">Processing List CSV<i style="font-size:10px;" class="material-icons right small">filter_none</i></a></li>
    <li class="divider"></li>
    <li><a href="unfilled.php">Unfilled Trays</a></li>
    <li class="divider"></li>
    <li><a href="https://datastudio.google.com/s/o6TgjlJmW-w" target="_blank">Monthly Billing Counts<i style="font-size:10px;" class="material-icons right small">filter_none</i></a></li>
    <li class="divider"></li>
    <li><a href="billing.php">Billing Counts</a></li>
    <li class="divider"></li>
    <li><a href="metrics.php">Temp Staff Daily Metrics</a></li>
    <li class="divider"></li>
    <!-- <li> <a href="https://datastudio.google.com/u/1/reporting/1s3fk8vW6-vesgIyMDvfTB4jscEeKaCYH/page/2T1l" target="_blank">Staff Time Cards</a></li>
    <li class="divider"></li>-->
    
    <li><a href="libraries.php">Manage Library Locations</a></li>
    <li class="divider"></li>
    <li><a href="staff.php">Manage Staff Accounts</a></li>
    <li class="divider"></li>
    <!--<li><a href="https://datastudio.google.com/s/ou6dcSn6KJM" target="_blank">Processing Goals</a></li>--> 
    
    <!--<li><a href="reports.php">View All</a></li>-->
  </ul>
  <ul id="dropdown-account" class="dropdown-content">
    <?php
////site-wide variables
$currentdate = date("Ymd");
$currentdateformatted = date("m/d/Y - g:ia");
$currentmdy = date("m/d/Y");
$name = $_SESSION['user_id'];
$temp = $_SESSION['temp'];
$admin = $_SESSION['admin'];


	
	 if ($temp != 'yes') $account = 'true';
 
  if ($temp == 'yes') {
       echo ' <li><a href="timecard.php"><i class="material-icons left">timer</i>Time Card</a></li>';
    }


//Connect to Table	
$sql = "SELECT * FROM stafftimecards WHERE TimeCardName = '$name'";
//Loop
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))
{
	
//Set Timecard in time to variable to compare with today's date
	
$TimeCardMatchDate2 = strtotime($row['TimeCardCheckIn']);
$TimeCardIn2 = date('Ymd', $TimeCardMatchDate2);

if($TimeCardIn2 == $currentdate AND $row['TimeCardCheckOut'] != '') {
$TimeCardCheckOutOld2 = strtotime($row['TimeCardCheckOut']);
$CheckedOut2 = date('m/d/Y g:ia', $TimeCardCheckOutOld2);
}

//Set check in time to display version

$TimeCardMatchDate2 = strtotime($row['TimeCardCheckIn']);
$TimeCardInDisplay2 = date('m/d/Y g:ia', $TimeCardMatchDate2);

//If the formatted timecard in date matches today's date, create a variable. Otherwise the variable says "false"
 if($TimeCardIn2 == $currentdate) {
	 $CheckedInFlag = 'true'; }
	 
	 else $CheckedInFlag = 'false';
	 
	 
 if($TimeCardIn2 == $currentdate AND $row['TimeCardCheckOut'] != '') {
	 $CheckedOutFlag = 'true'; }
	 
	 else $CheckedOutFlag = 'false';

 }

//End Loop
	
	
if($CheckedInFlag =='true')
{
	 $working = 'true';
	 $clockedin = 'true';
		echo '<li class="divider"></li><li><a href="#!"><i class="material-icons left">check</i> Clocked In</a></li>';
}
	
if($CheckedOutFlag =='true') echo '<li><a href="#!"><i class="material-icons left">check</i> Clocked Out</a></li>';
	
  ?>
    <li><a href="kill_session.php"><i class="material-icons left">lock_open</i>Logout</a></li>
  </ul>
  <nav>
    <div class="nav-wrapper blue"> <span class="brand-logo"><img class="hide-on-med-and-down" src="images/wrlc-logo-white.png" height="50px" style="margin:-5px 0 0 5px; position:relative; vertical-align: middle;"><span class="mobile-hide"> SCF Processing</span><span class="mobile-show"> SCF Processing</span></span> <a href="#" data-target="mobile-demo" class="sidenav-trigger"><i class="material-icons">menu</i></a>
      <ul class="right hide-on-med-and-down">
        <?php
	  if ( isset( $_SESSION['user_id'] ) AND $_SESSION['admin'] == 'yes') { echo '
	  <li><a class="dropdown-trigger" href="#!" data-target="dropdown-reports"><i class="material-icons left">vpn_key</i>Admin<i class="material-icons right">arrow_drop_down</i></a></li>'; } ?>
        <li><a class="dropdown-trigger" href="#!" data-target="dropdown1"><i class="material-icons left">settings</i>Tools<i class="material-icons right">arrow_drop_down</i></a></li>
        <li><a class="dropdown-trigger" href="#!" data-target="dropdown-account">
          <?php
		if ($working == 'true') echo '<i class="material-icons left">timer</i> ';
		?>
          <i class="material-icons left">account_circle</i><?php echo $_SESSION['user_id'] ?><i class="material-icons right">arrow_drop_down</i></a></li>
      </ul>
    </div>
  </nav>
  <ul class="sidenav" id="mobile-demo">
    <?php
	   if ( isset( $_SESSION['user_id'] ) AND $_SESSION['admin'] == 'yes') { echo '
        <li><a href="reports.php"><i class="material-icons left">vpn_key</i>Admin</a></li>
		
		'; } ?>
    <li><a href="index.php"><i class="material-icons left">settings</i>Tools</a></li>
    <li><a href="#"><i class="material-icons left">account_circle</i><?php echo $_SESSION['user_id'] ?></a></li>
    <li><a href="kill_session.php"><i class="material-icons left">lock_open</i>Logout</a></li>
  </ul>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.sidenav');
    var instances = M.Sidenav.init(elems, options);
  });

  // Or with jQuery

  $(document).ready(function(){
    $('.sidenav').sidenav();
  });
  
  </script> 
</div>
