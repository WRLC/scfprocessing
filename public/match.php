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
<div style="margin: auto; width: 100%; padding: 10px;">
<div style="margin-top:-26px;" class="card white lighten-1">
  <h3 style="padding:20px 20px 0; font-size:2em;" class="purple-text"><a name="top"></a>SCF Tray Processing List</h3>
  
  
  
 <div class="fixed-action-btn">
  <a class="btn-floating btn-large red">
    <i class="large material-icons">menu</i>
  </a>
  <ul>
    
    
    <li><a class="btn-floating blue" href="list.php"><i class="material-icons">format_line_spacing</i></a></li>
    <li><a class="btn-floating red" href="match.php"><i class="material-icons">warning</i></a></li>
    <li><a class="btn-floating yellow darken-1 black-text" href="#top"><i class="material-icons black-text">arrow_upward</i></a></li>
   <!-- <li><a class="btn-floating blue"><i class="material-icons">attach_file</i></a></li>-->
  </ul>
</div>
  
  
  
  <style>
.alert {background-color:#ff0000; color:#fff; text-align:center;}
.flag {color:orange;}
table {margin:10px auto; width:95%;}
td,th {
/*font-size:11px !important;*/
border-radius:0!important;}
</style>
  </head>
  <body>
  <div class="row">
    <form class="col s12">
      <div class="row">
        <div style="margin-right:50px;"> <a class="waves-effect waves-light btn right" href="?order=ptimestamp&sort=DESC&date=YEAR" style="margin-left:5px;"><i class="material-icons left">date_range</i>Year</a> <a class="waves-effect waves-light btn right" href="?order=ptimestamp&sort=DESC&date=MONTH" style="margin-left:5px;"><i class="material-icons left">date_range</i>Month</a> <a class="waves-effect waves-light btn right" href="?order=ptimestamp&sort=DESC&date=WEEK" style="margin-left:5px;"><i class="material-icons left">date_range</i>Week</a> <a class="waves-effect waves-light btn right" href="?order=ptimestamp&sort=DESC&date=DAY" style="margin-left:5px;"><i class="material-icons left">date_range</i>Day</a> <a class="waves-effect waves-light btn blue right" href="list.php" style="margin-left:5px;"><i class="material-icons left">star</i>All</a> </div>
        <div class="input-field col s3"> <i class="material-icons prefix">search</i>
          <input id="icon_prefix"
          <?php 
		  $searchform = $_GET['search'];
		  if(isset($searchform)) echo 'value="'.$searchform.'"';
		  ?>
           name="search" type="text" class="validate">
          <label for="icon_prefix">Search (Any Tray Number Value)</label>
        </div>
        <?php
		
		 if(isset($searchform)) echo '
<br />
<a class="btn waves-effect waves-light left red" href="list.php">Clear <i class="material-icons left">clear</i></a><br />';

?>
      </div>
    </form>
    <table class="highlight responsive-table">
      <?php 
////Sorting variables
$sort = $_GET['sort'];

//sort by non-matching counts
$sortdifference = $_GET['difference'];

$order = $_GET['order'];
if(isset($order)) $order = $order;
else $order = "ptraylocation";

$date = $_GET['date'];
if(isset($date)) $date = $date;

$search = '%'.$_GET['search'].'%';

if(isset($search)) {
$searchstring = "WHERE ptraylocation LIKE '$search' ";
}

else $searchstring = '';

if(isset($date))
$searchstring = "WHERE ptimestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 1 $date) ";

$sort = $_GET['sort'];

switch ($sort) {
    case "ASC":
		$sort2 = "DESC";  
        break;
    case "DESC":
       $sort2 = "ASC";
        break;
		 case "":
        $sort2 = "ASC";
        break;
    default:
         $sort2 = "ASC";
		 $sort = "ASC";
}

$processingcolor = '#7986cb; color:#fff;';
$crosscolor = '#90caf9; color:#fff;';

echo '
<thead>
<tr>
<th class="white-text purple darken-2 center" colspan="6">Processing</th>
<th class="white-text blue darken-2 center" colspan="3">Cross Check</th>
<th class="white-text red center"><i class="material-icons white-text">warning</i></th>
</tr>
</thead>
<thead>
<tr><th style="border-left:1px solid #ddd;" class="purple-text grey lighten-4 center"><a href="?order=ptraylocation&sort='.$sort2.'">Tray Number</a></th>
<th class="purple-text grey lighten-4 center"><a href="?order=plibrary&sort='.$sort2.'">Library</a></th>
<th class="purple-text grey lighten-4 center"><a href="?order=pname&sort='.$sort2.'">Name</a></th>
<th class="purple-text grey lighten-4 center"><a href="?order=ptimestamp&sort='.$sort2.'">Time</a></th>
<th class="purple-text grey lighten-4 center"><a href="?order=pcount&sort='.$sort2.'">Count</a></th>
<th class="purple-text grey lighten-4 center"><a href="?order=pfull&sort='.$sort2.'">Full</a></th>

<th style="border-left:1px solid #ddd;" class="grey lighten-4 center"><a href="?order=ccname&sort='.$sort2.'">Name</a></th>
<th class="grey lighten-4  center"><a href="?order=cctimestamp&sort='.$sort2.'">Time</a></th>
<th class="grey lighten-4  center"><a href="?order=cccount&sort='.$sort2.'">Count</a></th>

<th class="grey lighten-4 center"><a href="match.php">Match</a></th>

</tr></thead><tbody>';

  
$sql = "SELECT * FROM ProcessingAll $searchstring ORDER by $order $sort";

$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))
{
	if (isset($row['cccount']) AND$row['pcount'] != $row['cccount'] )
	$difference = abs($row['pcount'] - $row['cccount']);
	elseif (isset($row['cccount']) AND $row['pcount'] == $row['cccunt']) $difference = NULL;
	else $difference = NULL;
	
$old_processing_timestamp = strtotime($row['ptimestamp']);
$processing_date = date('m/d/Y g:ia', $old_processing_timestamp);

if(isset($row['cctimestamp']) AND $row['cctimestamp'] != NULL ) {
$old_cc_timestamp = strtotime($row['cctimestamp']);
$cc_date = date('m/d/Y g:ia', $old_cc_timestamp); }
else $cc_date = NULL;

if ($difference > 0) { 

echo '<tr><td style="border-left:1px solid #ddd; min-width:150px;"><a name="'.$row['ProcessingKey'].'"></a><a href="edit.php?id='.$row['ProcessingKey'].'">'.$row['ptraylocation'].'</a></td>
	<td style="text-align:center;">'.$row['plibrary'].'</td>
	<td>'.$row['pname'].'</td>
	<td style="min-width:160px;">'.$processing_date.'</td>
	<td style="text-align:center; max-width:20px;"><a href="almacount.php?id='.$row['ProcessingKey'].'">'.$row['pcount'].'</a></td>
	<td style="text-align:center;">'.$row['pfull'].'</td>';
	echo '<td style="text-align:center; border-left:1px solid #ddd;">'.$row['ccname'].'</td>';
	echo '<td style="min-width:160px;">'.$cc_date.'</td>
	<td style="text-align:center; border-right:1px solid #ddd;">'.$row['cccount'].'</td>';
	echo '<td style="border-right:1px solid #ddd;">';
	 if ($difference > 0) {echo '<span class=" new badge red" data-badge-caption="">'.$difference.'</span>'; }
	
	echo'</td></tr>';
}
}

// Connection will be closed in footer.php

?>
        </tbody>
      
    </table>
  </div>
</div>

<?php include('footer.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.fixed-action-btn');
    var instances = M.FloatingActionButton.init(elems, options);
  });

  // Or with jQuery

  $(document).ready(function(){
    $('.fixed-action-btn').floatingActionButton();
  });
  </script>
  
</body>
</html>