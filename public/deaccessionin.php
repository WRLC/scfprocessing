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
<?php include('header.php'); ?>
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
<div class="row center">
 <div class="col s12 push-m3 m6">



<div class="card white lighten-1">
<div class="card-content blue-text">
<span class="card-title" style="padding-left:50px;"><i class="material-icons medium" style="margin:2px 0 0 -65px; position:absolute;">access_time</i> Deaccession Clock-In: <?php echo $_SESSION['user_id']; ?><br />
</span>


<?php $submit = $_GET['success'];

if($submit == 'true') echo '<div id="hideMe" class="card-title green-text center">Success! You have logged your time.</div>';

?>


<div class="row">

 <!-- ****  Javascript to submit the form to Google Sheets, then return to this page if successful **** --> 
 <script type="text/javascript">var submitted=false;</script>
    <iframe name="hidden_iframe" id="hidden_iframe" style="display:none;" onload="if(submitted) {window.location='deaccessionin.php';}"></iframe>

<?php 

$sessionuser = $_SESSION['user_id'];
$staffkey = $_SESSION['staffkey'];
$today = date("Ymd");


$sql = "SELECT * FROM deaccessionHours WHERE staffID = '$staffkey'";
$x = 0;
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{
$id = $row['id']; 
$time_In = $row['time_In'];    

$TimeCardMatchDate = strtotime($row['time_In']);
$CurrentTimeCard = date('Ymd', $TimeCardMatchDate);

$old_checkout_timestamp = strtotime($row['time_In']);
$checkin_date = date('m/d/Y g:ia', $old_checkout_timestamp);

$time_out = strtotime($row['time_Out']);
$projectID = $row['projectID']; 

$staffID = $row['staffID']; 

if(isset($time_in) AND $time_out !=NULL)
$x = $x + 1;
else $x = $x + $x;

}


//echo $x;

if($time_out == NULL AND $staffID == $staffkey)
//create flag to show/hide form
$flag = 'true';
else $flag = 'false';
if($flag =='false') {



    $formurl = 'deaccessionin_submit.php'; 
   
    echo '<form autocomplete="off" action="'.$formurl.'" class="col s12" method="POST" target="hidden_iframe" onsubmit="submitted=true;">';
   echo ' <div class="row"><div class="input-field col s12"> 
      <select name="projectID" class="validate">
        <span class="helper-text" data-error="wrong" data-success="Complete">Select Project</span>
        <option value="" disabled selected>Select Project</option>';
       
        
/////Display Project Titles  
$sql2 = "SELECT id,title FROM project WHERE archive !='yes' ORDER by title ASC";
$query2 = mysqli_query($conn, $sql2);
while ($row2 = mysqli_fetch_array($query2))
{
echo '<option value="'.$row2['id'].'">'.$row2['title'].'</option>';
}
//mysqli_close($conn);					

      echo '</select>
     
   
      </div></div><div class="row"><div class="input-field col s12">';





    echo '<input type="hidden" name="staffID" value="'.$staffkey.'" />
    <input type="hidden" name="submit" value="Submit" />';
    echo '<button class="btn waves-effect waves-light center green" type="submit" >Begin
        <i class="material-icons right">timer</i>
      </button></form>';
    
}
else {




 if($CurrentTimeCard == $today AND $time_out == NULL) {
	  echo 'Checked in at '.$checkin_date;
      
      /////Display Project Titles  
$sql3 = "SELECT id,title FROM project WHERE id = $projectID";
$query3 = mysqli_query($conn, $sql3);
while ($row3 = mysqli_fetch_array($query3))
{
    echo '<br />'.$row3['title'];

}
// Connection will be closed in footer.php

      
     


      $formurl2 = 'deaccessionin_update.php'; ?>
      <!-- ****  Javascript to submit the form to Google Sheets, then return to this page if successful **** --> 
      <script type="text/javascript">var submitted=false;</script>
      <iframe name="hidden_iframe2" id="hidden_iframe2" style="display:none;" onload="if(submitted) {window.location='deaccessionin.php?success=true';}"></iframe>
      <form autocomplete="off" action="<?php echo $formurl2; ?>" class="col s12" method="POST" target="hidden_iframe2" onsubmit="submitted=true;">

      <?php echo '
      <input type="hidden" name="staffID" value="'.$staffkey.'" />
      <input type="hidden" name="time_In" value="'.$time_In.'" />
      <input type="hidden" name="id" value="'.$id.'" />
      
      <input type="hidden" name="submit" value="Submit" />
      <div style="margin-top:20px;"><button class="btn waves-effect waves-light center red" type="submit" >End
      <i class="material-icons right">timer</i>
    </button></form></div></div></div>';
  }
  
}
 
  
 
      ?>
</div>
</div>
</div>
</div>
</div>


<!--JavaScript at end of body for optimized loading-->
<?php include('footer.php'); ?>
</body>
</html>