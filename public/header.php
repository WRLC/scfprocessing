<?php

// Set timeout duration (30 minutes)
$timeout_duration = 1800; // 1800 seconds = 30 minutes

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity'])) {
        $elapsed_time = time() - $_SESSION['last_activity'];
        if ($elapsed_time > $timeout_duration) {
            // Destroy the session and redirect to login
            session_unset();
            session_destroy();
            header("Location: login.php");
            exit();
        }
    }
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
} else {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<?php include('connect.php'); ?>
<?php

////site-wide variables
$currentdate = date("Ymd");
$currentdateformatted = date("m/d/Y - g:ia");
$currentmdy = date("m/d/Y");
$name     = isset($_SESSION['user_id'])   ? $_SESSION['user_id']   : '';
$temp     = isset($_SESSION['temp'])      ? $_SESSION['temp']      : '';
$admin    = isset($_SESSION['admin'])     ? $_SESSION['admin']     : '';
$staffkey = isset($_SESSION['staffkey'])  ? $_SESSION['staffkey']  : '';
$working = 'false'; // Default value
$CheckedInFlag = 'false'; // Default value
$CheckedOutFlag = 'false'; // Default value
$account = 'false'; // Default value

//get current month and last day of month for The billing URL so it does not load the full data on load
// ?begin=Nov+01%2C+2019&end=Nov+30%2C+2019

$lastday = date("t", strtotime($currentdate));
$month = date("M", strtotime($currentdate));
$year = date("Y", strtotime($currentdate));

$billingmonth = '?begin='.$month.' 01, '.$year.'&end='.$month.' '.$lastday.', '.$year;



?>

<html>
<head>
<!-- https://materializecss.com/getting-started.html -->
<!--Import Google Icon Font-->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<!--Import materialize.css-->
<link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>
<link type="text/css" rel="stylesheet" href="css/print.css"  media="print"/>
<link type="text/css" rel="stylesheet" href="css/style.css"  media="screen,projection"/>
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
<?php include 'refile/include/refresh.php';?>

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
<body class="blue-grey lighten-5">
<div class="no-print">




  <ul id="dropdown1" class="dropdown-content">
    <li><a href="altcall.php">Add Item Call Number</a>
    <li class="divider"></li>
    <li><a href="in1.php">Add Internal Note 1</a>
    <li class="divider"></li>
    <li><a href="notecall.php">Add IN1/ICN</a>
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
    <li><a href="export-form.php">Export to CSV</a></li>
    <li class="divider"></li>
    <?php
	
//	if ( isset( $_SESSION['user_id'] ) AND $_SESSION['admin'] == 'yes' AND $_SESSION['user_id'] =='Tammy Hennig')
 //  echo '<li><a href="https://datastudio.google.com/s/hEdmRzONLTo" target="_blank">Processing List CSV<i style="font-size:10px;" class="material-icons right small">filter_none</i></a></li>
  //  <li class="divider"></li>'; 
	?>
    <li><a href="unfilled.php">Unfilled Trays</a></li>
    <li class="divider"></li>
     <?php
//	if ( isset( $_SESSION['user_id'] ) AND $_SESSION['admin'] == 'yes' AND $_SESSION['user_id'] =='Tammy Hennig')
//   echo '
//    <li><a href="https://datastudio.google.com/s/o6TgjlJmW-w" target="_blank">Monthly Billing Counts<i style="font-size:10px;" class="material-icons right small">filter_none</i></a></li>
//    <li class="divider"></li>'; 
	?>
    <li><a href="billing.php<?php echo $billingmonth;?>">Billing Counts</a></li>
    <li class="divider"></li>
    <li><a href="storage.php">Storage Counts</a></li>
    <li class="divider"></li>
    <li><a href="project.php">Deaccession Projects</a>
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

$sqld = "SELECT * FROM deaccessionHours WHERE staffID = '$staffkey'";
$queryd 	= mysqli_query($conn, $sqld);
while ($rowd = mysqli_fetch_array($queryd))
{
$time_out = strtotime($rowd['time_Out']);
$staffID = $rowd['staffID'];
}
if($time_out == NULL AND $staffID == $staffkey)
echo '<li><a href="deaccessionin.php" class="waves-effect waves-light btn purple"><i class="material-icons left">developer_board</i>Project Hours Active</a></li>';

?>



        <?php

echo '<li><a href="index.php"><i class="material-icons left">house</i>Home</a></li>';





	  if ( isset( $_SESSION['user_id'] ) AND $_SESSION['admin'] == 'yes') { echo '
	  <li><a class="dropdown-trigger" href="#!" data-target="dropdown-reports"><i class="material-icons left">vpn_key</i>Admin<i class="material-icons right">arrow_drop_down</i></a></li>'; } ?>
        <li><a class="dropdown-trigger" href="#!" data-target="dropdown1"><i class="material-icons left">settings</i>Tools<i class="material-icons right">arrow_drop_down</i></a></li>
        <li><a class="dropdown-trigger" href="#!" data-target="dropdown-account">
          <?php
		if ($working == 'true') echo '<i class="material-icons left">timer</i> ';
		?>
          <i class="material-icons left">account_circle</i><?php echo isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['user_id']) : 'Guest'; ?><i class="material-icons right">arrow_drop_down</i></a></li>
      </ul>
    </div>
  </nav>
  <ul class="sidenav" id="mobile-demo">

  <?php

$sqld = "SELECT * FROM deaccessionHours WHERE staffID = '$staffkey'";
$queryd 	= mysqli_query($conn, $sqld);
while ($rowd = mysqli_fetch_array($queryd))
{
$time_out = strtotime($rowd['time_Out']);
$staffID = $rowd['staffID'];
}
if($time_out == NULL AND $staffID == $staffkey)
echo '<li><a href="deaccessionin.php"><i class="material-icons left">lock_open</i><span style="color:purple;">Project Hours Active</span></a></li>';

?>








    <?php
	   if ( isset( $_SESSION['user_id'] ) AND $_SESSION['admin'] == 'yes') { echo '
        <li><a href="reports.php"><i class="material-icons left">vpn_key</i>Admin</a></li>
		
		'; } ?>
    <li><a href="index.php"><i class="material-icons left">settings</i>Tools</a></li>
    <li><a href="#"><i class="material-icons left">account_circle</i><?php echo isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['user_id']) : 'Guest'; ?></a></li>
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
