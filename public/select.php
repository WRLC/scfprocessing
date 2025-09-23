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
<div style="margin: auto; width: 50%; padding: 10px;">
  <div class="row center">
    <div class="col s6 m12">
      <h4 class="red-text"><i style="position: absolute; margin-top:-10px;" class="medium material-icons">settings</i><span style="margin-left:70px;">Tools</span></h4>
      <?php if($working !='true' AND $account != 'true') { echo '
    <div class="card white lighten-1">
        <div class="card-content black-text">
          <span class="card-title">Time Card</span
		  <p>Be sure to clock in to your time card before beginning work.</p>
        </div>
      <a href="timecard.php" class="waves-effect waves-light btn-large"><i class="material-icons left">timer</i>Time Card</a>
        
   <br /><br />
      </div>';
	  
	} ?>
      <div class="card white lighten-1">
        <div class="card-content black-text"> <span class="card-title">SCF Processing Utilities</span> </div>
        <a href="https://scfutils.wrlc.org/update-field/alt-call" target="_blank" class="waves-effect waves-light btn-large">Add Item Call Number</a> <a href="https://scfutils.wrlc.org/update-field/int-note" target="_blank" class="waves-effect waves-light btn-large">Add Internal Note 1</a> <br />
        <br />
      </div>
      <div class="card white lighten-1">
        <div class="card-content black-text"> <span class="card-title">SCF Processing Forms</span> </div>
        <a href="form2.php" class="waves-effect waves-light btn-large">Tray/Shelf Location</a> <a href="crosscheck.php" class="waves-effect waves-light btn-large">Cross Check</a> <br />
        <br />
      </div>
    </div>
  </div>
</div>

<!--JavaScript at end of body for optimized loading-->
<?php include('footer.php'); ?>
</body>
</html>