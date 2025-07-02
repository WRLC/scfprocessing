<!DOCTYPE html>
<html>
  <head>
  <!-- https://materializecss.com/getting-started.html -->
  <!--Import Google Icon Font-->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!--Import materialize.css-->
  <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>
  <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />

  <!--Let browser know website is optimized for mobile-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  </head>
  <body class="grey lighten-3">
<ul id="dropdown1" class="dropdown-content">
    <li><a href="https://scfutils.wrlc.org/update-field/alt-call" target="_blank">Add Item Call Number</a>
  <li class="divider"></li>
    <li><a href="https://scfutils.wrlc.org/update-field/int-note" target="_blank">Add Internal Note 1</a>
  <li class="divider"></li>
    <li><a href="location.php">Tray/Shelf Location Form</a></li>
    <li class="divider"></li>
    <li><a href="crosscheck.php">Cross Check Form</a>
  <li class="divider"></li>
    <li><a href="index.php">View All</a></li>
  </ul>
<ul id="dropdown-reports" class="dropdown-content">
    <li><a href="list.php">Daily List</a></li>
    <li class="divider"></li>
    <li><a href="https://datastudio.google.com/s/lLDSOvft1oc" target="_blank">Processing List</a></li>
    <li class="divider"></li>
    <li><a href="https://datastudio.google.com/open/1TVcFgEBeUz6BTsG5kBVtua5CWQoQjBPk" target="_blank">Monthly Billing Counts</a></li>
    <li class="divider"></li>
    <li> <a href="https://datastudio.google.com/u/1/reporting/1s3fk8vW6-vesgIyMDvfTB4jscEeKaCYH/page/2T1l" target="_blank">Staff Time Cards</a></li>
    <li class="divider"></li>
    <li><a href="https://datastudio.google.com/s/ou6dcSn6KJM" target="_blank">Processing Goals</a></li>
    <li class="divider"></li>
    <li><a href="reports.php">View All</a></li>
  </ul>
<ul id="dropdown-account" class="dropdown-content">
    <?php
   
$name = $_SESSION['user_id'];
   
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
	 
	 $account = 'true';
	 
	 } 
	 
}

 
 if ($account != 'true') {
       echo ' <li><a href="timecard.php"><i class="material-icons left">timer</i>Time Card</a></li>';
    }

   
   ?>
    <?php 

$currentdate = date("Ymd");
$name = $_SESSION['user_id'];
	
	$currentdateformatted = date("m/d/Y - g:ia");

  $sheetid = '14aCVSsqjzhdlfCdQ1d2cP7Qzpr5Tccf3DAUmZLsBgHo';
$url = 'https://spreadsheets.google.com/feeds/list/'.$sheetid.'/1/public/values?alt=json';
$file= file_get_contents($url);

$json = json_decode($file);
$rows = $json->{'feed'}->{'entry'};

foreach ($rows as $row) {
	
	 $checkoutname = $row->{'gsx$inname'}->{'$t'};
	 $outdate = $row->{'gsx$date'}->{'$t'};
	 $outdateformatted = $row->{'gsx$dateformatted'}->{'$t'};
	 $outtime = $row->{'gsx$outtime'}->{'$t'};
	 $hoursround = $row->{'gsx$durationrounded'}->{'$t'};
	 
	 $checkinname = $row->{'gsx$inname'}->{'$t'};
	 $indate = $row->{'gsx$date'}->{'$t'};
	 $indateformatted = $row->{'gsx$dateformatted'}->{'$t'};
	 $intime = $row->{'gsx$intime'}->{'$t'};
	 
	 if ($indateformatted == $currentdate AND $checkinname == $name) { echo '<li class="divider"></li><li><a href="#!"><i class="material-icons left">check</i> Clocked In</a></li>';
	 
	 $working = 'true';
	 $clockedin = 'true';
	 
	 }
	 
	  if ($outdateformatted == $currentdate AND $checkoutname == $name AND $outtime !="") { echo '<li><a href="#!"><i class="material-icons left">check</i> Clocked Out</a></li>';
	  $working = 'true';
	  $clockedout = 'true';
	  }
}
  
  ?>
    <li><a href="kill_session.php"><i class="material-icons left">lock_open</i>Logout</a></li>
  </ul>

<nav>
    <div class="nav-wrapper blue"> <span class="brand-logo"><img class="hide-on-med-and-down" src="images/wrlc-logo-white.png" height="50px" style="margin:-5px 0 0 5px; position:relative; vertical-align: middle;"> SCF Processing</span> <a href="#" data-target="mobile-demo" class="sidenav-trigger"><i class="material-icons">menu</i></a>
    <ul class="right hide-on-med-and-down">
        <?php
	  if ( isset( $_SESSION['user_id'] ) AND ($_SESSION['user_id'] =='Tammy Hennig' OR $_SESSION['user_id'] =='Admin')) { echo '
	  <li><a class="dropdown-trigger" href="#!" data-target="dropdown-reports"><i class="material-icons left">bar_chart</i>Reports<i class="material-icons right">arrow_drop_down</i></a></li>'; } ?>
        <li><a class="dropdown-trigger" href="#!" data-target="dropdown1"><i class="material-icons left">settings</i>Tools<i class="material-icons right">arrow_drop_down</i></a></li>
        
        <!-- <li><a class="dropdown-trigger" href="#!" data-target="dropdown-utils"><i class="material-icons left">search</i>A.S.<i class="material-icons right">arrow_drop_down</i></a></li>-->
        
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
	  if ( isset( $_SESSION['user_id'] ) AND ($_SESSION['user_id'] =='Tammy Hennig' OR $_SESSION['user_id'] =='Admin') ) { echo '
        <li><a href="reports.php"><i class="material-icons left">bar_chart</i>Reports</a></li>'; } ?>
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