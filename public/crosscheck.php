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
    <div class="card white lighten-1  mt-5">
        <div class="card-content teal-text"> <span class="card-title teal lighten-5 teal-text bold center">Cross Check Form: <?php echo $_SESSION['user_id']; ?></span>
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

if($submit == 'true') echo '<div id="hideMe" class="card-title green-text center">Success!</div>';
if($submit == 'blank') echo '<div id="hideMe" class="card-title red-text center">Form not submitted. Please fill in all required fields.</div>';

$formurl = 'all_crosscheck_submit.php'; ?>
            <!--<script type="text/javascript">var submitted=false;</script>
            <iframe name="hidden_iframe" id="hidden_iframe" style="display:none;" onload="if(submitted) {window.location='crosscheck.php?submit=true';}"></iframe>
            <form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="POST" target="hidden_iframe" onsubmit="submitted=true;">-->
             <form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="POST">
            <?php 
			  
			  		  
			  
$namestaff = 'ccname';
$namebarcode = 'cctraylocation';
$namescan = 'ccscan';
$namecount = 'cccount';
$nameas = 'ccchecked';
$nameverify = 'ccverify';

?>
            <input type="hidden" name="usp" value="pp_url" />
            <input type="hidden" name="<?php echo $namestaff; ?>" value="<?php echo $name; ?>" />
            <div class="row">
                <div class="input-field col s12"> <i class="material-icons prefix">line_style</i>
                <select name="ProcessingKey">
                    <option value="" disabled selected>Select Tray/Shelf Barcode</option>
                    <?php
					
					
					
					
	$i = 0;				
					
/////Display all items with Cross Checked Items	  
$sql = "SELECT * FROM ProcessingAll ORDER by ptimestamp DESC";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))
{
	
		if ($row['ccname'] == NULL) {
		
echo '<option value="'.$row['ProcessingKey'].'">'.$row['ptraylocation'].'</option>';

$i = $i + 1;
		}
	
}

mysqli_close($conn);					
					

?>
                  </select>
                <?php
	if($i > 0) echo '<span class="red-text center" style="position:absolute; font-size:12px; margin:-5px 0 0 44px;">There are currently '.$i.' unfinished cross checked trays/shelves</label>';
	else echo '<label class="green-text">All Cross Check Items Have Been Processed.</span>';
	?>
              </div>
              </div>
            <div class="row">
                <div class="input-field col s12"> <i class="material-icons prefix">add_shopping_cart</i>
                <input name="<?php echo $namecount; ?>" id="icon_prefix3" type="text" class="validate">
                
                <label for="icon_prefix3">Tray/Shelf Count</label>
                <span class="helper-text" data-error="wrong" data-success="Complete">Numbers Only</span> </div>
              </div>

              <div class="row">
                <div class="input-field col s10"> <i class="material-icons prefix">library_books</i>
                <label>
                    <input type="checkbox" value="Yes" name="<?php echo $namescan; ?>" />
                    <span>Scan in Items in Alma</span> </label>
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
            
                <br />
               <span class="new badge white red-text left" data-badge-caption="All Fields Required"></span>
            
            <input type="hidden" name="submit" value="Submit" />
            <br />
            <br />
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