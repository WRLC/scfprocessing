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
$selectedlibrary = $_GET['library'];



$beginurformatted = date("Y-m-d", strtotime($beginurl));
$endurlformattted = date("Y-m-d", strtotime($endurl));

if((isset($beginurl) AND $beginurl != '') AND (isset($endurl) AND $endurl != ''))

{
$daterange = ' AND (cctimestamp BETWEEN "'.$beginurformatted.' 00:00:00" AND "'.$endurlformattted.' 23:59:59") ';
}
else { 
$daterange ='';
}

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
<div class="col s12 push-m1 m10"><br /><br /><br />
<div class="no-print">
</div>
<div class="card white lighten-1">
<div class="no-print">
 <ul class="collapsible">
    <li>
      <div class="collapsible-header grey-text lighten-4"><i class=" material-icons blue-grey-text">date_range</i><i class=" material-icons blue-grey-text">business</i>FILTER</div>
      <div class="collapsible-body">
      <span>
      
     <form method="get">
	<div class="input-field col s6"> <i class="material-icons blue-grey-text prefix">date_range</i>
				<input name="begin" id="datepicker"
                
                <?php
				
				if(isset($beginurl) and $beginurl !='') echo 'value="'.$beginurl.'"'; 
				//else echo 'value=""';
				
				?>
                
                 type="text" class="validate">
				<label for="icon_prefix3">Start Date</label>
                   </div>
			
				<div class="input-field col s6"> <i class="material-icons indigo-text prefix">date_range</i>
				<input name="end" id="datepicker2"
                
                 <?php
				
				if(isset($endurl) and $endurl !='') echo 'value="'.$endurl.'"'; 
				//else echo 'value=""';
				
				?>
                
                
                              
                
                 type="text" class="validate">
                 <label for="icon_prefix3">End Date</label>
                  </div>
                  
                  
                  
                  
                  <div class="row">
                <div class="input-field col s6"> <i class="material-icons blue-grey-text prefix">business</i>
                  <select name="library">
                   
                    <?php
					
					if(isset($selectedlibrary) AND $selectedlibrary != '')
					
					echo ' <option value="'.$selectedlibrary.'" selected>'.$selectedlibrary.'</option>
					<option value="">All Libraries</option>';
					else
				echo ' <option value="" disabled selected>All Libraries</option>';	
					
	  /////Get Staff information	  
$sql = "SELECT university from LibraryLocations order by university ASC";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{				

$library = $row['university'];
					
		echo '<option value="'.$library.'">'.$library.'</option>';
	
}			
	//mysqli_close($conn);				
					
					


?>
                  </select>
                  <label>Select Library</label>
                </div>
              </div>
                  
                  
				  
				
	 
		<div style="border-bottom:1px solid #eee;" class="input-field col s12">		  
			       
 
       
			 <?php
			 
			 if(isset($beginurl) AND isset($endurl)) {
				 echo '<a class="btn waves-effect waves-light left red" href="billing.php">Clear <i class="material-icons left">clear</i></a>';
				 
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

<div style="padding:24px 13px!important;" class="card-content blue-grey-text">

<span class="card-title center">SCF Billing Counts Report</span>
<?php

if(isset($selectedlibrary) AND $selectedlibrary != '')
echo '<span class="card-title center">'.$selectedlibrary.'</span>';

if((isset($beginurl) AND $beginurl != '') AND (isset($endurl) AND $endurl != '')) {
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


<table class="striped" style="border:2px solid #ccc!important;">
<thead>
<tr><th class="blue-grey white-text center">Library</th><th class="blue-grey white-text center">Volumes</th><th class="blue-grey white-text center">Oversized Books</th><th class="blue-grey white-text center">Boxes</th><th class="blue-grey white-text center">Clamshells</th><th class="blue-grey white-text center">Flat Boxes</th><th class="blue-grey white-text center">Long Boxes</th><th class="blue-grey white-text center">Shelf Rentals</th><th class="blue-grey white-text center">Deaccessioned</th></tr>
</thead>
<tbody>
<?php


if(isset($selectedlibrary) and $selectedlibrary !='')
$sql = "SELECT university from LibraryLocations WHERE university = '$selectedlibrary' order by university ASC";
else
$sql = "SELECT university from LibraryLocations order by university ASC";

///// Get Library Locations /////////
//$sql = "SELECT university from LibraryLocations WHERE university ='American Books'";
$query = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{
	
	//if(isset($selectedlibrary) and $selectedlibrary !='')
//	
//	$library = $selectedlibrary;
//	
//	else
	
	$library = $row['university'];
		
	
///// Names ///////
	$sqlname = "SELECT plibrary,SUM(cccount) FROM ProcessingAll WHERE plibrary = '$library' $daterange";
	$queryname = mysqli_query($conn, $sqlname);
while ($rowname = mysqli_fetch_array($queryname))
{ 

//echo $rowname['SUM(cccount)'];

if(isset($rowname['SUM(cccount)']) AND $rowname['plibrary'] !=='WRLC Books (OUP)')  {
	
	$show ='true';
echo '<tr><td style="border-left:1px solid #eee;">'.$library.'</td>';

}
	
///// Volumes without special counts ///////
	$sqlsum = "SELECT plibrary,SUM(cccount) FROM ProcessingAll WHERE (pcode <>'BX' AND pcode <>'SR' AND pcode <> 'RB' AND pcode <> 'XX' AND pcode <> 'CB' AND pcode <> 'GB' AND pcode <> 'LB' AND pcode <> 'WD') AND (plibrary = '$library') $daterange";
	$querysum = mysqli_query($conn, $sqlsum);
while ($rowsum = mysqli_fetch_array($querysum))
{ 
if($rowsum['SUM(cccount)'] > 0 AND $rowsum['plibrary'] !=='WRLC Books (OUP)') {
echo '<td class="center" style="border-left:1px solid #eee;">'.number_format($rowsum['SUM(cccount)']).'</td>';
}
else 
{
if($show == 'true')
echo '<td style="border-left:1px solid #eee;"></td>';
}
}


////// Oversized  /////////

$sqlXX = "SELECT plibrary, SUM(cccount) FROM ProcessingAll WHERE (pcode ='XX') AND (plibrary = '$library') $daterange";
	$queryXX = mysqli_query($conn, $sqlXX);
while ($rowXX = mysqli_fetch_array($queryXX))

{ 

if($rowXX['SUM(cccount)'] > 0 AND $rowXX['plibrary'] !=='WRLC Books (OUP)')
echo '<td class="center" style="border-left:1px solid #eee;">'.number_format($rowXX['SUM(cccount)']).'</td>';
else 
{
if($show == 'true')
echo '<td style="border-left:1px solid #eee;"></td>';
}
}

////// Boxes  /////////

$sqlBX = "SELECT plibrary, SUM(cccount) FROM ProcessingAll WHERE (pcode ='RB' or pcode ='BX') AND (plibrary = '$library') $daterange";
	$queryBX = mysqli_query($conn, $sqlBX);
while ($rowBX = mysqli_fetch_array($queryBX))

{ 

if($rowBX['SUM(cccount)'] > 0 AND $rowBX['plibrary'] !=='WRLC Books (OUP)')
echo '<td class="center" style="border-left:1px solid #eee;">'.number_format($rowBX['SUM(cccount)']).'</td>';
else 
{
if($show == 'true')
echo '<td style="border-left:1px solid #eee;"></td>';
}
}


////// Clamshells  /////////

$sqlCB = "SELECT plibrary, SUM(cccount) FROM ProcessingAll WHERE (pcode ='CB') AND (plibrary = '$library') $daterange";
	$queryCB = mysqli_query($conn, $sqlCB);
while ($rowCB = mysqli_fetch_array($queryCB))

{ 

if($rowCB['SUM(cccount)'] > 0 AND $rowCB['plibrary'] !=='WRLC Books (OUP)')
echo '<td class="center" style="border-left:1px solid #eee;">'.number_format($rowCB['SUM(cccount)']).'</td>';
else 
{
if($show == 'true')
echo '<td style="border-left:1px solid #eee;"></td>';
}
}

////// Flat Boxes  /////////

$sqlGB = "SELECT plibrary, SUM(cccount) FROM ProcessingAll WHERE (pcode ='GB') AND (plibrary = '$library') $daterange";
	$queryGB = mysqli_query($conn, $sqlGB);
while ($rowGB = mysqli_fetch_array($queryGB))

{ 

if($rowGB['SUM(cccount)'] > 0 AND $rowGB['plibrary'] !=='WRLC Books (OUP)')
echo '<td class="center" style="border-left:1px solid #eee;">'.number_format($rowGB['SUM(cccount)']).'</td>';
else 
{
if($show == 'true')
echo '<td style="border-left:1px solid #eee;"></td>';
}
}

////// Long Boxes  /////////

$sqlLB = "SELECT plibrary, SUM(cccount) FROM ProcessingAll WHERE (pcode ='LB') AND (plibrary = '$library') $daterange";
	$queryLB = mysqli_query($conn, $sqlLB);
while ($rowLB = mysqli_fetch_array($queryLB))

{ 

if($rowLB['SUM(cccount)'] > 0 AND $rowLB['plibrary'] !=='WRLC Books (OUP)')
echo '<td class="center" style="border-left:1px solid #eee; border-right:1px solid #eee;">'.number_format($rowLB['SUM(cccount)']).'</td>';
else 
{
if($show == 'true')
echo '<td style="border-left:1px solid #eee; border-right:1px solid #eee;"></td>';
}
}

////// Shelf Rentals /////////

$sqlLB = "SELECT plibrary, SUM(cccount) FROM ProcessingAll WHERE (pcode ='SR') AND (plibrary = '$library') $daterange";
	$queryLB = mysqli_query($conn, $sqlLB);
while ($rowLB = mysqli_fetch_array($queryLB))

{ 

if($rowLB['SUM(cccount)'] > 0 AND $rowLB['SUM(cccount)'] !=='' AND $rowLB['plibrary'] !=='WRLC Books (OUP)')
echo '<td class="center" style="border-left:1px solid #eee; border-right:1px solid #eee;">'.number_format($rowLB['SUM(cccount)']).'</td>';
else 
{
if($show == 'true')
echo '<td style="border-left:1px solid #eee; border-right:1px solid #eee;"></td>';
}
}


////// Deasseccioned materials  /////////

$sqlWD = "SELECT plibrary, SUM(cccount) FROM ProcessingAll WHERE pcode ='WD' AND (plibrary = '$library') $daterange";
	$queryWD = mysqli_query($conn, $sqlWD);
while ($rowWD = mysqli_fetch_array($queryWD))

{ 

if($rowWD['SUM(cccount)'] > 0 AND $rowWD['plibrary'] !=='WRLC Books (OUP)')
echo '<td class="center" style="border-left:1px solid #eee;">'.number_format($rowWD['SUM(cccount)']).'</td>';
else 
{
if($show == 'true')
echo '<td style="border-left:1px solid #eee;"></td>';
}
}


{ 
if($show == 'true')
echo '</tr>';
}

}
	$show ='false';
}


echo '<tr><td style="border-top:2px solid #ccc; border-bottom:2px solid #ccc;" class="green black-text lighten-4">Total Count:</td>';

/////Volumes Total ///////

if(isset($selectedlibrary) AND $selectedlibrary !='')

$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode <>'BX' AND pcode <>'SR' AND pcode <>'WD' AND pcode <> 'RB' AND pcode <> 'XX' AND pcode <> 'CB' AND pcode <> 'GB' AND pcode <> 'LB') AND (plibrary = '$selectedlibrary') $daterange";

else

$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode <>'BX' AND pcode <>'SR' AND pcode <>'WD' AND pcode <> 'RB' AND pcode <> 'XX' AND pcode <> 'CB' AND pcode <> 'GB' AND pcode <> 'LB') AND (plibrary != 'WRLC Books (OUP)') $daterange";



//$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (plibrary != 'WRLC Books (OUP)') $daterange";
	$query = mysqli_query($conn, $sql);
	while ($row = mysqli_fetch_array($query))
	{ 
	echo '<td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;">'.number_format($row['SUM(cccount)']);
	
	$volumevalue= $row['SUM(cccount)']*.75;
	///echo '<br />($';	
	///echo number_format($volumevalue,2,".",",");
	///echo ')';
	echo '</td>';
	}

/////Oversized Total ///////


if(isset($selectedlibrary) AND $selectedlibrary !='')
$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode ='XX') AND (plibrary = '$selectedlibrary') $daterange";
else
$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode ='XX') AND (plibrary != 'WRLC Books (OUP)') $daterange";

	$query = mysqli_query($conn, $sql);
	while ($row = mysqli_fetch_array($query))
	{ 
	echo '<td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;">'.number_format($row['SUM(cccount)']);
	
	$oversizedvalue= $row['SUM(cccount)']*.75;
	///echo '<br />($';	
	///echo number_format($oversizedvalue,2,".",",");
	///echo ')';
	echo '</td>';
	}
	
/////Boxes Total ///////

if(isset($selectedlibrary) AND $selectedlibrary !='')
$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode ='RB' or pcode ='BX') AND (plibrary = '$selectedlibrary') $daterange";
else
$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode ='RB' or pcode ='BX') AND (plibrary != 'WRLC Books (OUP)') $daterange";
	$query = mysqli_query($conn, $sql);
	while ($row = mysqli_fetch_array($query))
	{ 
	echo '<td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;">'.number_format($row['SUM(cccount)']);
	
	$boxesvalue= $row['SUM(cccount)']*2.65;
///	echo '<br />($';	
	///echo number_format($boxesvalue,2,".",",");
	///echo ')';
	///echo '</td>';
	}

/////Clamshells Total ///////
if(isset($selectedlibrary) AND $selectedlibrary !='')
$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode ='CB') AND (plibrary = '$selectedlibrary') $daterange";
else
$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode ='CB') AND (plibrary != 'WRLC Books (OUP)') $daterange";
	$query = mysqli_query($conn, $sql);
	while ($row = mysqli_fetch_array($query))
	{ 
	echo '<td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;">'.number_format($row['SUM(cccount)']);
	
	$clamshellsvalue= $row['SUM(cccount)']*2.65;
	///echo '<br />($';	
	///echo number_format($clamshellsvalue,2,".",",");
	///echo ')';
	echo '</td>';
	}
	
/////Flat Boxes Total ///////
if(isset($selectedlibrary) AND $selectedlibrary !='')
$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode ='GB') AND (plibrary = '$selectedlibrary') $daterange";
else
$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode ='GB') AND (plibrary != 'WRLC Books (OUP)') $daterange";
	$query = mysqli_query($conn, $sql);
	while ($row = mysqli_fetch_array($query))
	{ 
	echo '<td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;">'.number_format($row['SUM(cccount)']);
	
	$flatboxesvalue= $row['SUM(cccount)']*2.65;
	///echo '<br />($';	
	///echo number_format($flatboxesvalue,2,".",",");
	///echo ')';
	echo '</td>';
	}
	
/////Long Boxes Total ///////
if(isset($selectedlibrary) AND $selectedlibrary !='')
$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode ='LB') AND (plibrary = '$selectedlibrary') $daterange";
else
$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode ='LB') AND (plibrary != 'WRLC Books (OUP)') $daterange";
	$query = mysqli_query($conn, $sql);
	while ($row = mysqli_fetch_array($query))
	{ 
	echo '<td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;">'.number_format($row['SUM(cccount)']);
	
	$longboxesvalue= $row['SUM(cccount)']*2.65;
	//echo '<br />($';	
//	echo number_format($longboxesvalue,2,".",",");
//	echo ')';
	echo '</td>';
	}
	
/////Shelf Rentals Total ///////
if(isset($selectedlibrary) AND $selectedlibrary !='')
$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode ='SR') AND (plibrary = '$selectedlibrary') $daterange";
else
$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode ='SR') AND (plibrary != 'WRLC Books (OUP)') $daterange";
	$query = mysqli_query($conn, $sql);
	while ($row = mysqli_fetch_array($query))
	{ 
	echo '<td class="green black-text black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;">'.number_format($row['SUM(cccount)']);
	
	$srvalue= $row['SUM(cccount)']*2.00;
	//echo '<br />($';	
//	echo number_format($srvalue,2,".",",");
//	echo ')';
	echo '</td>';
	}





    if(isset($selectedlibrary) AND $selectedlibrary !='')
$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode ='WD') AND (plibrary = '$selectedlibrary') $daterange";
else
$sql = "SELECT SUM(cccount) FROM ProcessingAll WHERE (pcode ='WD') AND (plibrary != 'WRLC Books (OUP)') $daterange";
	$query = mysqli_query($conn, $sql);
	while ($row = mysqli_fetch_array($query))
	{ 
	echo '<td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;">'.number_format($row['SUM(cccount)']);
	
	$wdvalue= $row['SUM(cccount)']*1.70;
///	echo '<br />($';	
	///echo number_format($boxesvalue,2,".",",");
	///echo ')';
	 '</td>';
	}



//// End Totals
echo '</tr>';

echo '<tr><th class="green darken-1 white-text">Value:</th><th class="green darken-1 white-text center">$'.number_format($volumevalue,2,".",",").'</th><th class="green darken-1 white-text center">$'.number_format($oversizedvalue,2,".",",").'</th><th class="green darken-1 white-text center">$'.number_format($boxesvalue,2,".",",").'</th><th class="green darken-1 white-text center">$'.number_format($clamshellsvalue,2,".",",").'</th><th class="green darken-1 white-text center">$'.number_format($flatboxesvalue,2,".",",").'</th><th class="green darken-1 white-text center">$'.number_format($longboxesvalue,2,".",",").'</th><th class="green darken-1 white-text center">$'.number_format($srvalue,2,".",",").'</th><th class="green darken-1 white-text center">$'.number_format($wdvalue,2,".",",").'</th></tr>';




echo '</table>';

echo '<br /><br />';
$total = ($volumevalue + $oversizedvalue + $boxesvalue + $clamshellsvalue + $flatboxesvalue + $longboxesvalue + $srvalue + $wdvalue);
echo '<h4 class="center">Total: $'.number_format($total,2,".",",").'</h4>';

//// Close DB connection ////    
mysqli_close($conn);
      echo '
      </div>
      </div>
	 
	  
   
</div></div>';




echo '<!--JavaScript at end of body for optimized loading-->';
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
