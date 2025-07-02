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
        <div class="card-content blue-text"> <span class="card-title center">Projects</span>
        <a href="deaccessionin.php" class="waves-effect purple left waves-light btn"><i class="material-icons left">timer</i>Track Time</a> 
        <a class="btn waves-effect waves-light right purple darken-1" href="project_new.php">New Project<i class="material-icons left">developer_board</i></a>
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

if($submit == 'true') echo '<div id="hideMe" class="card-title" style="color:#4CAF50;">Success!</div>'; ?>
           
              <div class="row">
               
                  
              <?php 

 echo '</div>
        <div class="row"> <div class="input-field col s12">
		<table class="highlight">
        <thead>
          
        </thead>

        <tbody> <tr>
        <th class="blue-grey-text">Project</th>
        <th class="blue-grey-text center">University</th>
        <th class="blue-grey-text center">Goal</th>
        <th class="blue-grey-text center">Staffing Hours</th>
        <th class="blue-grey-text center">Staffing Cost</th>    
        <th class="blue-grey-text center">Value (Billing)</th>
        <th class="blue-grey-text center">Ratio</th>              
    </tr>';



    $beginurl = $_GET['begin'];
$endurl = $_GET['end'];




			
			  /////Get Staff information	  
$sql = "SELECT * FROM project ORDER by endDate ASC";
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

               	
			echo'<tr>
            <td class="blue-grey-text top" width="40%">';
            if($archive=='yes') echo '<i class="material-icons left">archive</i> ';
            echo '<a class="left" href="project_details.php?id='.$row['id'].'">'.$row['title'].'</a>
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



//// Rounded time card hours ////
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

$num = $totalhours.$decimalminutes;
$int = (int)$num;
$float = (float)$num;
$staffcost = $float * 21;
$staffcost2 = number_format($staffcost, 2, '.', ',');

    echo '<td class="blue-grey-text center">'.$float.'</td>'; }
    else echo '<td class="blue-grey-text center">--</td>';



    $num2 = $cccount;
    $int2 = (int)$num2;
    $float2 = (float)$num2;    
$value = $float2*1.70;
$value2 = number_format($value, 2, '.', ',');
      

$ratio = $value / $staffcost;

$ratio = round($ratio);

       echo '<td class="blue-grey-text center">$'.$staffcost2.'</td>';
       echo '<td class="blue-grey-text center">$'.$value2.'</td>';
       echo '<td class="blue-grey-text center">'.$ratio.':1<br />';
       
       
       if(round($ratio) >= 4) $color = "blue";
       if(round($ratio) == 3) $color = "green";
       if(round($ratio) <= 2) $color = "red";
       
       $starsize = '14px';
       
   if(round($ratio) >= 4)  {
       
         echo '<p><i class="material-icons '.$color.'-text center" style="font-size:'.$starsize.';">star</i><i class="material-icons '.$color.'-text center" style="font-size:'.$starsize.';">star</i><i class="material-icons '.$color.'-text center" style="font-size:'.$starsize.';">star</i><i class="material-icons '.$color.'-text center" style="font-size:'.$starsize.';">star</i><i class="material-icons '.$color.'-text center" style="font-size:'.$starsize.';">star</i></p>';}
         
         if(round($ratio) == 3) 
         echo '<p><i class="material-icons green-text center" style="font-size:'.$starsize.';">star</i><i class="material-icons green-text center" style="font-size:'.$starsize.';">star</i><i class="material-icons green-text center" style="font-size:'.$starsize.';">star</i><i class="material-icons green-text center" style="font-size:'.$starsize.';">star_border</i><i class="material-icons green-text center" style="font-size:'.$starsize.';">star_border</i></p>';
         
          if(round($ratio) <= 2) 
         echo '<p><i class="material-icons red-text center" style="font-size:'.$starsize.';">star_half</i><i class="material-icons red-text center" style="font-size:'.$starsize.';">star_border</i><i class="material-icons red-text center" style="font-size:'.$starsize.';">star_border</i><i class="material-icons red-text center" style="font-size:'.$starsize.';">star_border</i><i class="material-icons red-text center" style="font-size:'.$starsize.';">star_border</i></p>';
         
         
         
         if(round($ratio) >= 4) 
       
         echo '<p class="'.$color.'-text center" style="font-size:11px;">High Performance</p>';
         
         
         if(round($ratio) == 3) 
         echo '<p class="'.$color.'-text center" style="font-size:11px;">Efficient Performance</p>';
         
         
         
          if(round($ratio) <= 2) 
           echo '<p class="'.$color.'-text center" style="font-size:11px;">Below Expectations</p>';
         
       
       
       
       
       
      echo  '</td>
        </tr>';
			
}
mysqli_close($conn);
      echo '  </tbody>
      </table>
	 </div>
	 </div>
  </div>
</div>

<!--JavaScript at end of body for optimized loading-->';
include('footer.php'); ?>
</body>
</html>