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
<div>
  <div class="row">
    <div class="col s12 push-m1 m10">
      <div class="card white lighten-1">
        <div class="card-content blue-text"> <span class="card-title center">Project Details</span>
        <a class="btn waves-effect waves-light left blue" href="project.php">Return to Projects <i class="material-icons left">keyboard_return</i></a>
   
        <a class="btn waves-effect waves-light right green darken-1" href="project_edit.php?id=<?php echo $_GET['id'];?>">Edit Project<i class="material-icons left">developer_board</i></a>
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

$name = $_SESSION['user_id'];
$submit = $_GET['submit'];
$pageprojectID = $_GET['id'];

if($submit == 'true') echo '<div id="hideMe" class="card-title" style="color:#4CAF50;">Success!</div>'; ?>
           
              <div class="row"></div>

        
               
                  
              <?php 

 echo '
        <div class="row"> <div class="input-field col s12">
		<table class="highlight">
        <thead>
          
        </thead>

        <tbody> <tr>
        <th class="blue-grey-text">Project</th>
        <th class="blue-grey-text center">University</th>
        <th class="blue-grey-text center">Goal</th>
        <th class="blue-grey-text center">Hours (decimal)</th>
        <th class="blue-grey-text center">Est. Cost</th>  
    </tr>';



    $beginurl = $_GET['begin'];
$endurl = $_GET['end'];




			
			  /////Get Staff information	  
$sql = "SELECT * FROM project WHERE id = $pageprojectID";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{

  $startDate = $row['startDate'];
  $endDate = $row['endDate'];
  $archive = $row['archive'];

$startDateformatted = date("m/d/Y", strtotime($startDate));
$endDateformattted = date("m/d/Y", strtotime($endDate));

$beginurformatted = date("Y-m-d", strtotime($startDate));
$endurlformattted = date("Y-m-d", strtotime($endDate));



//2019-04-22 08:06:23

$daterange = ' AND (ptimestamp BETWEEN "'.$beginurformatted.' 00:00:00" AND "'.$endurlformattted.' 23:59:59") ';

$projectID = $row['id'];
$title = $row['title'];

               	
			echo'<tr>
            <td class="blue-grey-text top" width="50%">';
            if($archive=='yes') echo '<i class="material-icons left">archive</i> ';
            echo '<a class="left" href="project_edit.php?id='.$row['id'].'">'.$row['title'].'</a>
            <br />'.$startDateformatted.' - '.$endDateformattted.'</td>';

$university = $row['university'];
            $sql2 = "SELECT libname, university FROM LibraryLocations WHERE librarykey = '$university'";
            $query2 	= mysqli_query($conn, $sql2);
            while ($row2 = mysqli_fetch_array($query2))
            
            {
                $university = $row2['university'];
                echo '<td class="blue-grey-text center" width="10%">'.$row2['libname'].'</td>';
            }




            $sql3 = "Select SUM(cccount) from ProcessingAll WHERE pcode = 'WD' AND plibrary = '$university' $daterange";
            $query3 	= mysqli_query($conn, $sql3);
            while ($row3 = mysqli_fetch_array($query3))
            
            {
                $cccount = $row3['SUM(cccount)'];
               // echo '<td class="blue-grey-text center" width="10%"> -  '.$row3['SUM(cccount)'].'</td>';
            }
            $goal = $row['goal'];
            $percentcompleted = $cccount / $goal;
            $percent = round( $percentcompleted * 100 );
	echo '<td class="blue-grey-text center" width="10%">';



    
  




    
    echo ' '.$cccount.'/'.$row['goal'].' ('.$percent.'%) ';
    if($percent >= '100') echo'<br /><i class="material-icons" style="color:#4CAF50;">stars</i>';
    echo '<div class="progress">
    <div class="determinate" style="width: '.$percent.'%"></div>
</div>';
    echo '</td>';

  // $sql4 = "SELECT SUM(TIMESTAMPDIFF(HOUR, time_In, time_Out)) as time_diff_in_hours FROM deaccessionHours WHERE projectID ='$projectID'";
   // $sql4 = "SELECT CAST(DATEDIFF(ss, time_In, time_Out) AS decimal(precision, scale)) FROM deaccessionHours WHERE projectID ='$projectID'";
   $sql4 = "SELECT time_Out, TIMESTAMPDIFF(MINUTE, time_In, time_Out) as hours_Worked FROM deaccessionHours WHERE projectID ='$projectID'";
   $x = 0;
    $query4 = mysqli_query($conn, $sql4);
    while ($row4 = mysqli_fetch_array($query4))
    
    {
      //  echo $row4['hours_Worked'];
     // echo $row4['time_Out'].', ';
if(isset($row4['hours_Worked']) AND $row4['time_Out' !==NULL])

$y = $row4['hours_Worked'];
else $y = 0;
$x = $x + $y;
    }
if($x > 0) {
   $totalhours = $x / 60;
   $totalhours =(int)$totalhours;
   $totalminutes = $x;

   $mins = $x%60;

   $rounded_mins = round($mins / 15) * 15;
   $rounded_hours = round($totalhours);



//// Rounded hours ////
$diff2 = $x;
$tmins2 = $diff2/60;
$hours2 = floor($tmins2/60);
$mins2 = $tmins2%60;

$decimalmins2 = $diff2/60/60;
$decimalmins2 = number_format($decimalmins2, 2, '.', '');
$decimalmins2 = str_replace('.50', '.5', $decimalmins2);

   $decimaltime = $minutes/60/60;
   $decimaltime = number_format($decimaltime, 2, '.', '');
$decimaltime = str_replace('.50', '.5', $decimaltime);

   if($rounded_mins < 14) $rounded_mins = '00';

   if($rounded_mins ==15) $decimalminutes = '.25';
   elseif ($rounded_mins ==30) $decimalminutes = '.5';
   elseif ($rounded_mins ==45) $decimalminutes = '.75';
   else $decimalminutes = '';

    echo '<td class="blue-grey-text center">'.$totalhours.$decimalminutes.'</td>'; }
    else echo '<td class="blue-grey-text center">--</td>';
$value = $cccount*1.70;
$value = number_format($value, 2, '.', ',');
       echo '<td class="blue-grey-text center">$'.$value.'</td>
        </tr>';
			
} ?>







<?php 
      echo '  </tbody>
      </table>
	 </div>
	 </div>
  </div>
'; ?>



<div class="row"> <div class="input-field col s12 center">


<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
  google.charts.load("current", {packages:["calendar"]});
  google.charts.setOnLoadCallback(drawChart);

  function drawChart() {
   var dataTable = new google.visualization.DataTable();
   dataTable.addColumn({ type: 'date', id: 'Date' });
   dataTable.addColumn({ type: 'number', id: 'Won/Loss' });
   dataTable.addRows([
      // Many rows omitted for brevity.
<?php $sql4 ="SELECT date(cctimestamp) as stat_day, SUM(cccount)
from ProcessingAll WHERE pcode = 'WD' AND plibrary = '$university'
GROUP BY date(cctimestamp)
order by stat_day";

$query4 = mysqli_query($conn, $sql4);
while ($row4 = mysqli_fetch_array($query4))
{
    $cccount2 = $row4['SUM(cccount)'];
    $statday = $row4['stat_day'];
    $stattime = strtotime($statday);
    $statY = date('Y',$stattime);
    $statm = date('m',$stattime)-1;
    $statd = date('d',$stattime);
    echo '[ new Date('.$statY.', '.$statm.', '.$statd.'), '.$cccount2.' ],';
}
?>
    ]);
   var chart = new google.visualization.Calendar(document.getElementById('calendar_basic'));
   var options = {
     title: "Deaccessions by Date",
     height: 350,
     noDataPattern: {
           backgroundColor: '#eee',
           color: '#eee'
         },
     calendar: {
      focusedCellColor: {
        stroke: '#d3362d',
        strokeOpacity: 1,
        strokeWidth: 1,
      },
      monthOutlineColor: {
        stroke: '#981b48',
        strokeOpacity: 0.8,
        strokeWidth: 2
      }},
   };
   chart.draw(dataTable, options);
}
</script>
<center>
<div id="calendar_basic" style="width: 1000px; height: 350px;"></div>
</center>
</div></div>

<?php
echo '
<div class="row"> <div class="input-field col s12">
<table class="highlight">
<thead>
</thead>
<tbody> <tr>
<th class="blue-grey-text">Staff</th>
<th class="blue-grey-text center">Hours (decimal)</th>
<th class="blue-grey-text center">Items</th>
             
</tr>';
//get staff id numbers to display names from table
$sqlp = "SELECT DISTINCT staffID FROM deaccessionHours WHERE projectID = $pageprojectID";
$queryp = mysqli_query($conn, $sqlp);
while ($rowp = mysqli_fetch_array($queryp))
    {
    $staffID = $rowp['staffID'];
    echo '<tr>';

//display names
$sqln = "SELECT name from Staff WHERE staffkey = $staffID";
$x = 0;
 $queryn = mysqli_query($conn, $sqln);
 while ($rown = mysqli_fetch_array($queryn))
 {
$name = $rown['name'];
echo '<td class="blue-grey-text" width="10%">'.$name.'</td>';
 }

echo '<td class="blue-grey-text center" width="10%">';
//display hours worked
$sqlt = "SELECT time_Out, TIMESTAMPDIFF(MINUTE, time_In, time_Out) as hours_Worked FROM deaccessionHours WHERE projectID ='$projectID' AND staffID = $staffID";
$x = 0;
$queryt = mysqli_query($conn, $sqlt);
while ($rowt = mysqli_fetch_array($queryt))
 {
    if(isset($rowt['hours_Worked']) AND $rowt['time_Out' !==NULL])
    $y = $rowt['hours_Worked'];
    else $y = 0;
    $x = $x + $y;
        }
    if($x > 0) {
       $totalhours = $x / 60;
       $totalhours =(int)$totalhours;
       $totalminutes = $x;
       $mins = $x%60;
       $rounded_mins = round($mins / 15) * 15;
       $rounded_hours = round($totalhours);


       if($rounded_mins ==15) $decimalminutes = '.25';
   elseif ($rounded_mins ==30) $decimalminutes = '.5';
   elseif ($rounded_mins ==45) $decimalminutes = '.75';
   else $decimalminutes = '';


    echo '<div class="blue-grey-text center" width="10%">'.$rounded_hours.$decimalminutes.'</div>';
 }

echo '</td>';
//display item count
$sql5 = "SELECT SUM(cccount) from ProcessingAll WHERE pcode = 'WD' AND plibrary = '$university' AND ccname = '$name' $daterange";
            $query5 	= mysqli_query($conn, $sql5);
            while ($row5 = mysqli_fetch_array($query5))
            {
                $cccount = $row5['SUM(cccount)'];
               echo '<td class="blue-grey-text center" width="10%">'.$row5['SUM(cccount)'].'</td>';
            }
            $goal = $row['goal'];
            if ($goal > 0) {
                $percentcompleted = $cccount / $goal;
                $percent = round( $percentcompleted * 100 );
            } else {
                $percentcompleted = 0;
                $percent = 0;
            }

echo '</tr>';
        }
echo '</tbody>
</table>
</div></div></div>';



// Connection will be closed in footer.php

echo '<!--JavaScript at end of body for optimized loading-->';
include('footer.php'); ?>
</body>
</html>