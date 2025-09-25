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
        <div class="card-content blue-text"> <span class="card-title center">Library Locations</span>
        <a class="btn waves-effect waves-light right indigo darken-1" href="libraries_new.php">New<i class="material-icons left">account_balance</i></a>
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
          <tr>
              <th colspan="3" class="left blue-grey-text"></th>
             
			  
			                 
          </tr>
        </thead>

        <tbody> ';
			
			  /////Get Staff information	  
$sql = "SELECT * FROM LibraryLocations ORDER by university ASC";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{
               	
			echo'<tr>
		<td class="blue-grey-text" width="40%"><a class="left" href="libraries_edit.php?id='.$row['librarykey'].'">'.$row['university'].'</a></td>
		<td class="blue-grey-text left" width="50%">'.$row['libname'].'</td>
        <td class="blue-grey-text right">'.$row['code'].'</td>
        </tr>';
			
}
// Connection will be closed in footer.php
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