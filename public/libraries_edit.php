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
        <div class="card-content blue-text"> <span class="card-title">Update Library Location</span>
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

$id = $_GET['id'];

if($submit == 'true') echo '<div id="hideMe" class="card-title" style="color:#4CAF50;">Success!</div>';


$formurl = 'libraries_update.php'; ?>
            <!-- ****  Javascript to submit the form to Google Sheets, then return to this page if successful **** --> 
            <script type="text/javascript">var submitted=false;</script>
            <iframe name="hidden_iframe" id="hidden_iframe" style="display:none;" onload="if(submitted) {window.location='libraries.php?submit=true';}"></iframe>
            <form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="POST" target="hidden_iframe" onsubmit="submitted=true;">
              
               
                <input type="hidden" name="librarykey" value="<?php echo $id; ?>" />
              <div class="row">
               
                  
              <?php 
			  
		  
	


              echo '
              </div>
              ';
			
			  /////Get Staff information	  
$sql = "SELECT * FROM LibraryLocations WHERE librarykey = $id";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{
                echo '<div class="row"><div class="input-field col s12"> <i class="material-icons prefix">account_circle</i>
                  <input name="university" value="'.$row['university'].'" id="icon_prefix2" type="text" class="validate">
                  <label for="icon_prefix2">Library Name</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Enter Full Name of Library Location</span> </div>
              </div>
             ';

             $libname = $row['libname'];
             $libcode = $row['code'];
           
					}

?>


                    <select name="libname" class="validate">
                    <span class="helper-text" data-error="wrong" data-success="Complete">Select Library</span>
                    <option value="" selected><?php echo $libname; ?></option>
                    <?php
					
/////Display Library Locations  
$sql = "SELECT DISTINCT libname FROM LibraryLocations ORDER by libname ASC";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))
{
echo '<option value="'.$row['libname'].'">'.$row['libname'].'</option>';
}
// Connection will be closed in footer.php
?>
               </select>
                 <span class="new badge white red-text right" data-badge-caption="Required"></span>

                 <?php  

					
					echo '
                    <input type="hidden" name="code" value="'.$libcode.'" />
              <input type="hidden" name="submit" value="Submit" />
			  <br /><br />
			  <a class="btn waves-effect waves-light left red" href="libraries_delete.php?id='.$id.'">Delete Location<i class="material-icons left">delete_forever</i></a>
              <button class="btn waves-effect waves-light right green" type="submit" >Update <i class="material-icons right">send</i> </button>
           ';

			// Connection will be closed in footer.php
          echo ' </form></div>
        </div>
      </div>
	   <br />
    <a class="btn waves-effect waves-light right blue" href="libraries.php">Return to List <i class="material-icons left">keyboard_return</i></a><br /><br />
    </div>
  </div>
</div>
<p class="center">Note: If you rename or delete a location, the orginial name will still be assigned to previously processed tray records.  The content with new name will be listed separately from the old.  It is recommended you do not rename unless necessary.</p>

<!--JavaScript at end of body for optimized loading-->';
include('footer.php'); ?>
</body>
</html>