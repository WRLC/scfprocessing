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
?>

<?php function writeChart($used,$remaining,$university,$code) {
    echo "<script>
    google.charts.load('current', {
            'packages': ['corechart']
          });
          google.charts.setOnLoadCallback(chart$code);

          function chart$code() {
            var data = google.visualization.arrayToDataTable([
              ['University', 'Counts'],
              ['Used',$used],
              ['Remaining', $remaining]
            ]);
            var options = {
                
             legend: 'none',
              pieHole: 0.25,
              fontSize: 13,
              width: 250,
              height: 250,
             
              pieSliceText: 'value',
              colors: ['#4FC3F7','#81C784'],
              backgroundColor: 'transparent',
              chartArea:{
                left:10,
                right:10,
                top: 10,
                bottom:10
               
                
            }
              
            };
            var chart = new google.visualization.PieChart(document.getElementById('chart$code'));
            chart.draw(data, options);
          }
          </script>";
  }
?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<style>
    .chartsize {
        display: inline-block;
        border: 0px solid #fff;
       /* border-radius: .75rem; */
    }
    </style>






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
<div class="col s12 push-m1 m10"><br /><br /><br />
<div class="no-print">
</div>
<div class="card white lighten-1">
<div style="padding:24px 13px!important;" class="card-content blue-grey-text">
<span class="card-title center">SCF Modules 2 and 3 Storage Report</span>
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
<ul>

<?php
// allotted cubic feet counts for each university
$AUcf ="22266";
$CUcf ="28685";
$GAcf ="7860";
$UDCcf ="7335";
$GMcf ="15063";
$GTcf ="62738";
$GWcf ="76229";
$HUcf ="26199";
$MUcf ="2489";
$WRLCcf ="2500";
$totalcf = "251364";
// Voyager final counts serve as base number added to accessions
//$AUbase ="12981";
//$CUbase ="18363";
//$GAbase ="916";
//$UDCbase ="1385";
//$GMbase ="10630";
//$GTbase ="42998";
//$GWbase ="57898";
//$HUbase ="4252";
//$MUbase ="1839";

//$totalbase ="151618";
$AUbase ="12981";
$CUbase ="18363";
$GAbase ="916";
$UDCbase ="1385";
$GMbase ="10630";
$GTbase ="42998";
$GWbase ="57898";
$HUbase ="4252";
$MUbase ="1839";
$WRLCbase ="356";
$totalbase ="151618";








$x = 0;
$codecount = 0;
$final = 0;

echo '<center><table class="striped" style="border:2px solid #ccc!important; width:90%;">
<thead>
<tr><th class="blue-grey white-text center">Institution</th>
<th class="blue-grey white-text center"></th>
<th class="blue-grey white-text center">Space Used</th>
<th class="blue-grey white-text center">Space Available</th></tr>
</thead>
<tbody>';

$sql = "SELECT DISTINCT libname, code from LibraryLocations WHERE code !='NSA' AND code !='DCPL' ORDER BY libname";
//$sql = "SELECT count(libname) from LibraryLocations WHERE code !='WRLC' AND code !='NSA' AND code !='GA' AND code !='DCPL' ORDER BY libname";
$query = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))
{ 
    $libname = $row['libname'];
    $code = $row['code'];
    $cftext2 = $code.'cf';
    $cf2 = $$cftext2;
    $cf2 = $cf2;

    $displaycf2= number_format((float)$cf2, 0, '.', $thousands_sep = ',');

    echo '<td class="center" style="border-left:1px solid #eee;"><h5>'.$libname.'</h5>
    <h6>'.$displaycf2.' Cubic Feet Reserved</h6>';
    $libcode = $row['code'];

//$sql = "SELECT count(libname) from LibraryLocations WHERE code !='WRLC' AND code !='NSA' AND code !='GA' AND code !='DCPL' ORDER BY libname";
$sql2 = "SELECT * from LibraryLocations WHERE code ='$code'";
echo '<uL align="left" style="margin-left:40px; list-style-type: circle!important;">';
$query2 = mysqli_query($conn, $sql2);
while ($row2 = mysqli_fetch_array($query2))
{  
    $university = $row2['university'];
    echo '<li>'.$university.'</li>';
    
$sql3 = "SELECT count(pcode), pcode FROM ProcessingAll LEFT JOIN LibraryLocations ON LibraryLocations.university=ProcessingAll.plibrary WHERE ProcessingAll.plibrary ='$university' GROUP BY pcode ORDER by ProcessingAll.pcode";
$query3 = mysqli_query($conn, $sql3);
while ($row3 = mysqli_fetch_array($query3))
{ 
$pcode3count = $row3['count(pcode)'];
$pcode3 = $row3['pcode'];
$sqlpercent = "SELECT code, cf FROM SizeCode";
$querypercent = mysqli_query($conn, $sqlpercent);
while ($rowpercent = mysqli_fetch_array($querypercent))
{ 
    $code = $rowpercent['code'];
    $cf = $rowpercent['cf'];
   $cfvalue = $rowpercent['cf'];
if($pcode3 == $code AND $pcode3count !='') {
   $codesf = $cfvalue * $pcode3count;

if(isset($code)) {
$x = $x + $codesf;
$codecount = $codecount + 1;
}
$y = $x;  
}

}
//echo $pcode3.' - '.$pcode3count.' = '.$cfvalue.' Square Feet<br />';
}


$x = 0;
//echo 'Code Count: '.$codecount;

if($codecount == 0) { 
    //echo 'No count';
//echo '</p>';
}
else {
//echo ' - Total: '.$y.' Square Feet</p>';

$z = $y + $z; }

$codecount = 0; 
}






$basetext = $libcode.'base';
$base = $$basetext;
$base = $base;

$cftext = $libcode.'cf';
$cf = $$cftext;
$cf = $cf;

$universitytotal = $z + $base;
$percent = $universitytotal / $cf;
$percent = round( $percent * 100 );
$cfavailable = $cf - $universitytotal;
$percentleft = 100 - $percent;

//$testtotal = (int)$universitytotal;
$displayuniversitytotal= number_format((float)$universitytotal, 1, '.', $thousands_sep = ',');
$displaycfavailable= number_format((float)$cfavailable, 1, '.', $thousands_sep = ',');

echo '</ul></td>
<td>';
//display pie chart
$code = $libcode;
$university = $university;
$used =$universitytotal;
$remaining = $cfavailable;    
writeChart($used,$remaining,$university,$code); 
echo '<div id="chart'.$code.'" class="chartsize"></div>';
//end pie chart
echo '</td>
<td class="center" style="border-left:1px solid #eee;">';
   
//$testtotal = number_format($testtotal,2);
//echo '<h3>University Total from DB: '.$z.'<br/>Total From Voyager: '.$base.'<br/>Combinded Total: ';
echo '<h6>'.$displayuniversitytotal.' Cubic Feet<br/>('.$percent.'% Used)</h6>';

echo '</td>
<td class="center" style="border-left:1px solid #eee;"><h6>'.$displaycfavailable.' Cubic Feet<br />('.$percentleft.'% remaining)</h6>';
$final = $final + $z;
$z = 0;
echo '</td></tr>';
}
$voyager = $final + $totalbase;

$finalused = $totalcf - $voyager;

$finalpercent = $voyager / $totalcf;
$finalpercent = round( $finalpercent * 100 );
$finalpercentleft = 100 - $finalpercent;


$displaytotalcf= number_format((float)$totalcf, 0, '.', $thousands_sep = ',');
$displayvoyager= number_format((float)$voyager, 1, '.', $thousands_sep = ',');
$displayfinalused= number_format((float)$finalused, 1, '.', $thousands_sep = ',');
//echo '<hr /><h2>Final SCF Processing DB Total: '.$final.'</h2><br />';
echo '<tr>
<td class="blue-grey white-text center" style="border-left:1px solid #eee;"><h5>Totals:<br/>'.$displaytotalcf.' Cubic Feet</h5></td>
<td class="blue-grey white-text center" style="border-left:1px solid #eee;">';

//display pie chart
$code = 'all';
$university = 'Total';
$used =$voyager;
$remaining = $finalused;    
writeChart($used,$remaining,$university,$code); 
echo '<div id="chart'.$code.'" class="chartsize"></div>';
//end pie chart

echo '</td>
<td class="blue-grey white-text center" style="border-left:1px solid #eee;"><h5>'.$displayvoyager.' Cubic feet used ('.$finalpercent.'%)</h5></td>
<td class="blue-grey white-text center" style="border-left:1px solid #eee;"><h5>'.$displayfinalused.' Cubic Feet Remaining('.$finalpercentleft.'%)</h5></td>
</tr>';
echo '</tbody></table>';
mysqli_close($conn);

      
    echo '  <p><br /><br />** 1000 cubic feet of storage can hold an average of 20,000 volumes or 700 archival boxes. However, these are only estimates. Actual storage quantity is based on width of volume, box size, and other factors.</p>
    </div>
      </div>  
      </div>
      </div>
      <!--JavaScript at end of body for optimized loading-->';
//footer
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