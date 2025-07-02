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
<?php 


$name = $_SESSION['user_id'];
$submit = $_GET['submit'];
$id = $_GET['id'];
$hoursname = $_GET['name']; 



?>
<div style="margin: auto; width: 50%; padding: 10px;">
  <div class="row">
    <div class="col s12 m12">
      <div class="card white lighten-1">
        <div class="card-content blue-text"> <span class="card-title center">Update Hours for <?php 
		
		
		$sql = "SELECT * FROM stafftimecards WHERE TimeCardKey = $id";

//$sql 	= 'SELECT * FROM scfprocessing.ProcessingForm';
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{
	
	
	$TimeCardDateOld = strtotime($row['TimeCardCheckIn']);
	$CheckedInDate = date('m/d/Y', $TimeCardDateOld);
	
	
echo $row['TimeCardName'].' - '.$CheckedInDate;

$TimeCardName = $row['TimeCardName'];

//}
		
		
		
		
		 $hoursname; ?></span>
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



if($submit == 'true') echo '<div id="hideMe" class="card-title" style="color:#4CAF50;">Success!</div>';


$formurl = 'hours_update.php'; ?>
            <!-- ****  Javascript to submit the form to Google Sheets, then return to this page if successful **** --> 
            <script type="text/javascript">var submitted=false;</script>
            <iframe name="hidden_iframe" id="hidden_iframe" style="display:none;" onload="if(submitted) {window.location='hours_edit.php?submit=true&id=<?php echo $id; ?>';}"></iframe>
            <form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="POST" target="hidden_iframe" onsubmit="submitted=true;">
              
               
                <input type="hidden" name="TimeCardKey" value="<?php echo $id; ?>" />
              <div class="row">
                <div class="input-field col s12">
                  
              <?php 
			  
	          echo '</div>
              </div>
              <div class="row">';
			 
                echo '<div class="input-field col s12"> <i class="material-icons prefix">date_range</i>
                  <input name="TimeCardCheckIn" value="'.$row['TimeCardCheckIn'].'" id="icon_prefix2" type="text" class="validate">
                  <label for="icon_prefix2">Enter Date as YYYY-MM-DD HH:MM:SS (24 hour time)</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Clocked In</span> </div>
              </div>
              <div class="row">
                <div class="input-field col s12"> <i class="material-icons prefix">date_range</i>';
				

                  echo ' <input name="TimeCardCheckOut" value="'.$row['TimeCardCheckOut'].'" id="icon_prefix2" type="text" class="validate">
				  <label for="icon_prefix3">Enter Date as YYYY-MM-DD HH:MM:SS (24 hour time)</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Clocked Out</span> </div>
              </div>
             
              <input type="hidden" name="submit" value="Submit" />
			  <br /><br />
			  <a class="btn waves-effect waves-light left red" href="hours_delete.php?id='.$id.'">Delete <i class="material-icons left">delete_forever</i></a>
              <button class="btn waves-effect waves-light right green" type="submit" >Update <i class="material-icons right">send</i> </button>
           '; }
			
			mysqli_close($conn);
          echo ' </form></div>
        </div>
      </div>
	   <br />
    <a class="btn waves-effect waves-light right blue" href="hours.php?name='.$TimeCardName.'">Return to List <i class="material-icons left">keyboard_return</i></a><br /><br />
    </div>
  </div>
</div>

<!--JavaScript at end of body for optimized loading-->';
include('footer.php'); ?>
</body>
</html>