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
// or use alma.internal_note_1
 $datasourcealt='https://wrlc-scf.alma.exlibrisgroup.com/view/sru/01WRLC_SCF?version=1.2&operation=searchRetrieve&recordSchema=marcxml&query=alma.alterCallNumber="'.$barcodestrip.'"';
 $xml = simplexml_load_file($datasourcealt);
 $count = $xml->numberOfRecords;
 if (empty($xml) OR $count==0) {
      echo '<tr><td colspan="3">Alma: Alt Call Numbers Barcode "'.$barcode.'" does not exist.  Please Try again.';
 }
 else {
      echo '<tr><td>Alma: Alternate Call Number</td><td> '.$count.' Volumes</td><td><a class="btn waves-effect waves-light right pink darken-1" href="https://wrlc-scf.alma.exlibrisgroup.com/view/sru/01WRLC_SCF?version=1.2&operation=searchRetrieve&recordSchema=marcxml&query=alma.alterCallNumber='.$barcodestrip.'" target="_blank">Source<i class="material-icons left">code</i></a></td></tr>';
 }
			


$datasourceint='https://wrlc-scf.alma.exlibrisgroup.com/view/sru/01WRLC_SCF?version=1.2&operation=searchRetrieve&recordSchema=marcxml&query=alma.internal_note_1="'.$barcodestrip.'"';
 $xml2 = simplexml_load_file($datasourceint);
 $count2 = $xml2->numberOfRecords;
 if (empty($xml2) OR $count2==0) {
      echo '<tr><td colspan="3">Alma: Internal Note 1 Barcode "'.$barcode.'" does not exist.  Please Try again.';
 }
 else {
      echo '<tr><td>Alma: Internal Note 1</td><td> '.$count2.' Volumes</td><td><a class="btn waves-effect waves-light right pink darken-1" href="https://wrlc-scf.alma.exlibrisgroup.com/view/sru/01WRLC_SCF?version=1.2&operation=searchRetrieve&recordSchema=marcxml&query=alma.internal_note_1='.$barcodestrip.'" target="_blank">Source<i class="material-icons left">code</i></a></td></tr>';
 }

echo '<tr><td>SCF: Processing Count</td><td>'.$row['pcount'].' Volumes</td><td><td></tr>';

if(isset($row['cccount']))
echo '<tr><td>SCF: Cross Check Count</td><td>'.$row['cccount'].' Volumes</td><td><td></tr>';

}

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