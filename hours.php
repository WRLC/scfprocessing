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
<?php include('header.php'); 

$name = $_SESSION['user_id'];
$submit = $_GET['submit'];
$cardname = $_GET['name'];

$beginurl = $_GET['begin'];
$endurl = $_GET['end'];

$beginurformatted = date("Y-m-d", strtotime($beginurl));
$endurlformattted = date("Y-m-d", strtotime($endurl));

if(isset($beginurl) AND isset($endurl))

//2019-04-22 08:06:23

$daterange = ' AND (TimeCardCheckIn BETWEEN "'.$beginurformatted.' 00:00:00" AND "'.$endurlformattted.' 23:59:59") ';

else $daterange ='';

?>

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
  $( function() {
    $( "#datepicker" ).datepicker();
  } );
  
  
   $( function() {
    $( "#datepicker2" ).datepicker();
  } );
  
  
  </script>

<div>
<div class="row">
<div class="col s12 push-m3 m6"><br /><br /><br />


<div class="no-print">


<a class="btn waves-effect waves-light left blue" style="position:absolute; margin-top:-45px;" href="staff.php">Return to Staff <i class="material-icons left">keyboard_return</i></a>

</div>

<div class="card white lighten-1">


<div class="no-print">



 <ul class="collapsible">
    <li>
      <div class="collapsible-header grey-text lighten-4"><i class=" material-icons blue-grey-text">date_range</i>Filter</div>
      <div class="collapsible-body">
      
      
      <span>
      
     <form method="get">
  
	<div class="input-field col s6"> <i class="material-icons blue-grey-text prefix">date_range</i>
				<input name="begin" id="datepicker"
                
                <?php
				
				if(isset($beginurl)) echo 'value="'.$beginurl.'"'; 
				
				?>
                
                
                 type="text" class="validate">
				<label for="icon_prefix3">Start Date</label>
                   </div>
			
				<div class="input-field col s6"> <i class="material-icons indigo-text prefix">date_range</i>
				<input name="end" id="datepicker2"
                
                 <?php
				
				if(isset($endurl)) echo 'value="'.$endurl.'"'; 
				
				?>
                
                
                 type="text" class="validate">
                 <label for="icon_prefix3">End Date</label>
                  </div>
				  
				
	 
		<div style="border-bottom:1px solid #eee;" class="input-field col s12">		  
			  
      <input type="hidden" name="submit" value="Submit" />
      
      
      
       <div class="row">
                <div class="input-field col s12"> <i class="material-icons blue-grey-text prefix">transfer_within_a_station</i>
                  <select name="name">
                    <option value="<?php echo $cardname; ?>" selected><?php echo $cardname; ?></option>
                    <?php
					
					
					
	  /////Get Staff information	  
$sql = "SELECT * FROM Staff WHERE temp = 'yes' ORDER by name ASC";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{				
					
		echo '<option value="'.$row['name'].'">'.$row['name'].'</option>';
	
}			
					
					
					


?>
                  </select>
                  <label>Select Name</label>
                </div>
              </div>
      
      
      
      
      
      
      
       
			 <?php
			 
			 if(isset($beginurl) AND isset($endurl)) {
				 echo '<a class="btn waves-effect waves-light left red" href="hours.php?name='.$cardname.'">Clear <i class="material-icons left">clear</i></a>';
				 
			 } 
			 
			 echo ' <button class="btn waves-effect waves-light right green" type="submit" >Filter <i class="material-icons right">filter_list</i> </button>';
			 
			 ?>
		
                 


</form>

<br />
<br />
<br />
      
      
     </div>
</div> 
      
</span></div>
    </li>
    
  </ul>









<div class="card-content blue-text">

<span class="card-title center">Time Cards for <?php echo $cardname; ?></span>
<?php
if(isset($beginurl) AND isset($endurl)) {
 echo '<div class="print center" style="margin:20px 0;">'.$beginurl.' - '.$endurl.'</div>';
}
?>

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



if($submit == 'true') echo '<div id="hideMe" class="card-title" style="color:#4CAF50;">Success!</div>'; ?>
<div class="row">
<div class="input-field col s12">
<table class="highlight blue-grey-text">
<thead>
  <tr>
    <th class="blue-grey-text">Date</th>
    <th class="blue-grey-text">Clocked In</th>
    <th class="blue-grey-text">Clocked Out</th>
    <th class="blue-grey-text">Hours (Decimal)</th>
  </tr>
</thead>
<tbody>
  <?php 
//// Function to round to the nearest 15 minute increment ////
	function roundTime($timestamp, $precision = 15) {
	$timestamp = strtotime($timestamp);
	$precision = 60 * $precision;
	return date('Y-m-d H:i:s', round($timestamp / $precision) * $precision);
  }
		
			  
//// SQL to get time cards ////
$sql = "SELECT * FROM stafftimecards WHERE (TimeCardName = '$cardname') $daterange ORDER BY TimeCardCheckIn DESC";
$query = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{	

	$TimeCardMatchDate = strtotime($row['TimeCardCheckIn']);
	$TimeCardIn = date('Ymd', $TimeCardMatchDate);


	$TimeCardCheckOutOld = strtotime($row['TimeCardCheckOut']);

	if(isset($row['TimeCardCheckOut']) AND $row['TimeCardCheckOut'] !==NULL) {

	$CheckedOut = date('g:ia', $TimeCardCheckOutOld);

	}

	else $CheckedOut = '';

	//Set check in time to display version

	$TimeCardMatchDate = strtotime($row['TimeCardCheckIn']);

	if(isset($row['TimeCardCheckIn']) AND $row['TimeCardCheckIn'] !==NULL) {
		$CheckedIn = date('g:ia', $TimeCardMatchDate);
		}
		else $CheckedIn = '';

	$TimeCardDateOld = strtotime($row['TimeCardCheckIn']);
	$CheckedInDate = date('m/d/Y', $TimeCardDateOld);

	$timecardnameloop = $row['TimeCardName'];

	// Absolute value of time difference in seconds
	$diff = abs(strtotime($row['TimeCardCheckIn']) - strtotime($row['TimeCardCheckOut']));

	$tmins = $diff/60;

		if(isset($row['TimeCardCheckIn']) AND $row['TimeCardCheckIn'] !==NULL AND isset($row['TimeCardCheckOut']) AND $row['TimeCardCheckOut']) {

		// Get hours
		$hours = round($tmins/60);

		// Get minutes
		$mins = $tmins%60;

		$rounded_mins = round($mins / 15) * 15;

		}
		else {
		$hours = '';

		// Get minutes
		$mins = '';
		}


 
//// Rounded time card hours ////
  $diff2 = abs(strtotime(roundTime($row['TimeCardCheckIn'])) - strtotime(roundTime($row['TimeCardCheckOut'])));
  $tmins2 = $diff2/60;
  $hours2 = floor($tmins2/60);
  $mins2 = $tmins2%60;

  $decimalmins2 = $diff2/60/60;
$decimalmins2 = number_format($decimalmins2, 2, '.', '');
$decimalmins2 = str_replace('.50', '.5', $decimalmins2);



//// If less than 15 minutes, display 00 instead of 0 ////
 if($mins2 < 14) $mins2 = '00';

//// Final display of row ////
	echo '<tr><td><a href="hours_edit.php?id='.$row['TimeCardKey'].'">'.$CheckedInDate.'</a></td><td>';
	
	if(!isset($row['TimeCardCheckIn']) OR $row['TimeCardCheckIn'] !==NULL OR $row['TimeCardCheckIn'] !== '0000-00-00 00:00:00')
	
	echo $CheckedIn;
	
	echo'</td><td>';
	if(!isset($row['TimeCardCheckOut']) OR $row['TimeCardCheckOut'] !==NULL OR $row['TimeCardCheckOut'] !== '0000-00-00 00:00:00')
	
	echo $CheckedOut;
	
	
	echo '</td><td>';
	
	if( $row['TimeCardCheckOut'] == NULL OR $row['TimeCardCheckOut'] == '0000-00-00 00:00:00')
	{
	$checkoutblank = 'true';
	echo '';
	}
	
	else echo $decimalmins2.'';
	echo '</td></tr>';
	$sum = $sum;
	$sum = $diff2 + $sum;
    
   
}

///**** Convert minutes to decimal */
$decimaltime = $sum/60/60;
$decimaltime = number_format($decimaltime, 2, '.', '');
$decimaltime = str_replace('.50', '.5', $decimaltime);

 $tmins3 = $sum/60;
  $hours3 = floor($tmins3/60);
  $mins3 = $tmins3%60;
if($mins3 < 14) $mins3 = '00';

if($checkoutblank == 'true') {
	
	echo '<h6 class="center" style="margin-top:-10px;">To see total hours and estimated pay, verify all dates are clocked out.</h6>';
	
}
else


 
 
 
 

 { echo '<h5 class="center" style="margin-top:-10px;">Hours Worked (Decimal): '.$decimaltime.'</h5>';

$percent = ($mins3 * 100) / (60);
$value = $hours3.'.'.$percent;
$value = number_format($value,2,".",",");
$pay = $value * 21;
$pay =  number_format($pay,2,".",",");


echo '<h5 class="center" style="margin-top:-10px;">Estimated Pay: $'.$pay.'</h5>';
}
//// Close DB connection ////    
mysqli_close($conn);
      echo '</tbody>
      </table>
      </div>
      </div>
	 
	  
   
</div></div>

<!--JavaScript at end of body for optimized loading-->';
include('footer.php'); ?>
  <script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.collapsible');
    var instances = M.Collapsible.init(elems, options);
  });

  // Or with jQuery

  $(document).ready(function(){
    $('.collapsible').collapsible();
  });
  </script>
</body>
</html>
