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

  <div class="row">
     <div class="col s12 push-m4 m4">
      <div class="card white lighten-1 mt-5">
        <div class="card-content teal-text"> <span class="card-title teal lighten-5 bold center">Tray/Shelf Location Form: <?php echo $_SESSION['user_id']; ?></span>
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

if($submit == 'true') echo '<div id="hideMe" class="card-title center" style="color:#4CAF50;">Success!</div>';
if($submit == 'false') echo '<div id="hideMe" class="card-title red-text center">This Tray/Shelf Barcode has already been processed. Please try another.</div>';
if($submit == 'blank') echo '<div id="hideMe" class="card-title red-text center">Form not submitted. Please fill in all required fields.</div>';


$formurl = 'all_processing_submit.php'; ?>
            <!-- ****  Javascript to submit the form to Google Sheets, then return to this page if successful **** --> 
           <!-- <script type="text/javascript">var submitted=false;</script>
            <iframe name="hidden_iframe" id="hidden_iframe" style="display:none;" onload="if(submitted) {window.location='processing.php?submit=true';}"></iframe>
           <form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="POST" target="hidden_iframe" onsubmit="submitted=true;">-->
           <form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="POST">
            
              
              
              <?php 
			  
$Name = $_POST['Name'];
$TrayLocation = $_POST['TrayLocation'];
$Count = $_POST['Count'];
$Full = $_POST['Full'];
$Verify = $_POST['Verify'];
$Checked = $_POST['Checked'];
$Library = $_POST['Library'];			  
			  
			  
			  
$namelibrary = 'Library';
$namestaff = 'Name';
$namebarcode = 'TrayLocation';
$namecount = 'Count';
$namefull = 'Full';
$nameas = 'Checked';
$nameverify = 'Verify';
?>
             
              <input type="hidden" name="<?php echo $namestaff; ?>" value="<?php echo $name; ?>" />
              <div class="row">
                <div class="input-field col s12"> <i class="material-icons prefix">account_balance</i> 
                  <select name="<?php echo $namelibrary; ?>" class="validate">
                    <span class="helper-text" data-error="wrong" data-success="Complete">Select Library</span>
                    <option value="" disabled selected>Select Library</option>
                    <?php
					
/////Display Library Locations  
$sql = "SELECT university FROM LibraryLocations ORDER by university ASC";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))
{
echo '<option value="'.$row['university'].'">'.$row['university'].'</option>';
}
// Connection will be closed in footer.php					
?>
                  </select>
                 <span class="new badge white red-text right" data-badge-caption="Required"></span>
                </div>
              </div>
              <div class="row">
                <div class="input-field col s12"> <i class="material-icons prefix">line_style</i>
                  <input name="<?php echo $namebarcode; ?>" id="icon_prefix2" type="text" class="validate">
                  <label for="icon_prefix2">Tray/Shelf Barcode</label>
                  <span class="new badge white red-text right" data-badge-caption="Required"></span>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Scan in Tray/Shelf Barcode</span> </div>
              </div>
               
              <div class="row">
                <div class="input-field col s12"> <i class="material-icons prefix">add_shopping_cart</i>
                  <input name="<?php echo $namecount; ?>" id="icon_prefix3" type="text" class="validate">
                  <label for="icon_prefix3">Tray/Shelf Count</label>
                   <span class="new badge white red-text right" data-badge-caption="Required"></span>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Numbers Only</span> </div>
              </div>
            
              
              <div class="row">
              
              <span class="new badge white red-text left" data-badge-caption="All Checkboxes required except 'Tray/Shelf Full?'"></span>
              </div>
              <div class="row">
                <div class="input-field col s6"> <i class="material-icons prefix">shopping_cart</i>
                  <label>
                    <input type="checkbox" value="Yes" name="<?php echo $namefull; ?>" />
                    <span>Tray/Shelf Full?</span> </label>
                </div>
              </div>
              <div class="row">
                <div class="input-field col s10"> <i class="material-icons prefix">search</i>
                  <label>
                    <input type="checkbox" value="Yes" name="<?php echo $nameas; ?>" />
                    <span>Advanced Search in Alma Completed</span> </label>
                    
                </div>
                
              </div>
              <div class="row">
                <div class="input-field col s10"> <i class="material-icons prefix">spellcheck</i>
                  <label>
                    <input type="checkbox" value="Yes" name="<?php echo $nameverify; ?>" />
                    <span>Verified all information is correct</span> </label>
                </div>
              </div>
              
              <input type="hidden" name="submit" value="Submit" />
              <br /><br />
              
              <button class="btn waves-effect waves-light right green" type="submit" >Submit <i class="material-icons right">send</i> </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  
<!--JavaScript at end of body for optimized loading-->
<?php include('footer.php'); ?>
</body>
</html>