<?php /** @noinspection PhpUndefinedVariableInspection */
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


//$name = $_SESSION['user_id'];
//$barcode = $_GET['barcode'];
$id = $_GET['id'];

?>
<?php include('header.php'); ?>

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
#loading {
   width: 100%;
   height: 100%;
   top: 0;
   left: 0;
   position: fixed;
   display: block;
   opacity: 0.9;
   background-color: #fff;
   z-index: 99;
   text-align: center;
}

</style>


<div id="loading">
  




 <div class="preloader-wrapper big active" style="margin-top:200px;">
      <div class="spinner-layer spinner-blue">
        <div class="circle-clipper left">
          <div class="circle"></div>
        </div><div class="gap-patch">
          <div class="circle"></div>
        </div><div class="circle-clipper right">
          <div class="circle"></div>
        </div>
      </div>

      <div class="spinner-layer spinner-red">
        <div class="circle-clipper left">
          <div class="circle"></div>
        </div><div class="gap-patch">
          <div class="circle"></div>
        </div><div class="circle-clipper right">
          <div class="circle"></div>
        </div>
      </div>

      <div class="spinner-layer spinner-yellow">
        <div class="circle-clipper left">
          <div class="circle"></div>
        </div><div class="gap-patch">
          <div class="circle"></div>
        </div><div class="circle-clipper right">
          <div class="circle"></div>
        </div>
      </div>

      <div class="spinner-layer spinner-green">
        <div class="circle-clipper left">
          <div class="circle"></div>
        </div><div class="gap-patch">
          <div class="circle"></div>
        </div><div class="circle-clipper right">
          <div class="circle"></div>
        </div>
      </div>
    </div>

</div>








<div>
<div class="row">
<div class="col s12 push-m3 m6">
<div class="card white lighten-1">
<div class="card-content blue-text">

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
<?php 





              echo '
              </div>
              <div class="row"> <div class="input-field col s12">';
			 
echo ' <table class="blue-grey-text">
        <thead>
          <tr>
              <th></th>
              <th></th>
              <th></th>
          </tr>
        </thead>

        <tbody>';
		
		
/////Display all items with Cross Checked Items	  
$sql = "SELECT pcount,cccount,ptraylocation FROM ProcessingAll WHERE ProcessingKey = $id";

//$sql 	= 'SELECT * FROM scfprocessing.ProcessingForm';
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{

$barcode = $row['ptraylocation'];

echo '<span class="card-title center"><span class="red-text">*Experimental:</span> Counts For Tray Barcode: '.$barcode.'</span>';
			
			//$barcode = 'R27M09S13T07 CC';

$barcodestrip = strtok($barcode,' ');

			
echo '<tr><td>SCF: Processing Count</td><td>'.$row['pcount'].' Volumes</td><td><td></tr>';

if(isset($row['cccount']))
echo '<tr><td>SCF: Cross Check Count</td><td>'.$row['cccount'].' Volumes</td><td><td></tr>';

}

//Get volume count for tray from Alma API
$ItemCall = $barcodestrip;
$analytics_key = $_ENV['CANNED_REPORTS'] ?? getenv('CANNED_REPORTS');
 $analytics_url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=%2Fshared%2FShared+storage+institution%2FReports%2FCanned+Reports%2FTray+Check+-+Barcode+Count&limit=250&col_names=true&apikey='.$analytics_key.'&filter=%3Csawx%3Aexpr%20xsi%3Atype%3D%22sawx%3Alogical%22%20op%3D%22or%22%20xmlns%3Asaw%3D%22com.siebel.analytics.web%2Freport%2Fv1.1%22%20%0A%20%20xmlns%3Asawx%3D%22com.siebel.analytics.web%2Fexpression%2Fv1.1%22%20%0A%20%20xmlns%3Axsi%3D%22http%3A%2F%2Fwww.w3.org%2F2001%2FXMLSchema-instance%22%20%0A%20%20xmlns%3Axsd%3D%22http%3A%2F%2Fwww.w3.org%2F2001%2FXMLSchema%22%0A%20%3E%0A%20%20%20%20%20%20%20%20%20%20%20%20%3Csawx%3Aexpr%20xsi%3Atype%3D%22sawx%3Alist%22%20op%3D%22beginsWith%22%3E%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%3Csawx%3Aexpr%20xsi%3Atype%3D%22sawx%3AsqlExpression%22%3E%22Physical%20Item%20Details%22.%22Internal%20Note%201%22%3C%2Fsawx%3Aexpr%3E%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%3Csawx%3Aexpr%20xsi%3Atype%3D%22xsd%3Astring%22%3E'.$ItemCall.'%3C%2Fsawx%3Aexpr%3E%3C%2Fsawx%3Aexpr%3E%0A%20%20%20%20%20%20%20%20%20%20%20%20%3Csawx%3Aexpr%20xsi%3Atype%3D%22sawx%3Alist%22%20op%3D%22beginsWith%22%3E%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%3Csawx%3Aexpr%20xsi%3Atype%3D%22sawx%3AsqlExpression%22%3E%22Physical%20Item%20Details%22.%22Item%20Call%20Number%22%3C%2Fsawx%3Aexpr%3E%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%3Csawx%3Aexpr%20xsi%3Atype%3D%22xsd%3Astring%22%3E'.$ItemCall.'%3C%2Fsawx%3Aexpr%3E%3C%2Fsawx%3Aexpr%3E%3C%2Fsawx%3Aexpr%3E';
 $analytics_xml = simplexml_load_file($analytics_url) or die("ERROR: Item Call Number not found.");
   $row_count = 0;
           if (!empty($analytics_xml)) {
               foreach ($analytics_xml->QueryResult->ResultXml->rowset->Row as $item ) {
                   $row_count++;
               }
               echo '<tr><td>Alma - Number of Items in Tray</td><td>'.$row_count.' Volumes</td><td><td></tr>';
           }
// End Get volume count for tray from Alma API


echo '</tbody>
      </table>
	  </div>
	  </div>
	  <a class="btn waves-effect waves-light left green" href="list.php#'.$id.'">Return to List<i class="material-icons left">keyboard_return</i></a></div></div>

<!--JavaScript at end of body for optimized loading-->';
include('footer.php'); ?>

<script language="javascript" type="text/javascript">
     $(window).load(function() {
     $('#loading').hide();
  });
  
  window.onbeforeunload = function () { $('#loading').show(); } 
</script>


</body>
</html>