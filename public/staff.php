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
    <div class="col s12 push-m3 m6">
      <div class="card white lighten-1">
        <div class="card-content blue-text"> <span class="card-title center">Staff Accounts</span>
        <a class="btn waves-effect waves-light right indigo darken-1" href="staff_new.php">New<i class="material-icons left">account_circle</i></a>
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





              echo '
              </div>
              <div class="row"> <div class="input-field col s12">
			  <table class="highlight">
        <thead>
          <tr>
              <th class="left blue-grey-text"></th>
             
			  
			                 
          </tr>
        </thead>

        <tbody> ';
			
			  /////Get Staff information	  
$sql = "SELECT * FROM Staff ORDER by name ASC";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{
               	
			echo'	
          <tr>
		<td class="blue-grey-text"> ';
		
		if(isset($row['temp']) AND $row['temp'] == 'yes') echo '<i class="material-icons blue-grey-text left">transfer_within_a_station</i>';
			elseif(isset($row['admin']) AND $row['admin'] == 'yes') echo '<i class="material-icons blue-grey-text left">vpn_key</i>';
			else echo '<i class="material-icons left">account_circle</i>';
		
		echo ' <a class="left" href="staff_edit.php?id='.$row['staffkey'].'">'.$row['name'].'</a>
          ';
			
		
			if(isset($row['temp']) AND $row['temp'] == 'yes') echo '<a class="right" href="hours.php'.$billingmonth.'&name='.$row['name'].'"> <i class="material-icons">timer</i></a> ';
			
			echo '</td>
			</tr>';
			
}
// Connection will be closed in footer.php
      echo '  </tbody>
      </table>
				
				
				
				
				
				
				
				
				
				
              </div>';
             
             
           
					
					
					
  echo '  </div>
  </div>
</div>

<!--JavaScript at end of body for optimized loading-->';
include('footer.php'); ?>
</body>
</html>