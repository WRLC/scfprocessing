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
<div class="row">
    <div class="col s12 push-m3 m6">
    <div class="card white lighten-1">
        <div class="card-content blue-text"> <span class="card-title center">Unfilled Trays</span>
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

if($submit == 'true') echo '<div id="hideMe" class="card-title green center">Success!</div>';
if($submit == 'blank') echo '<div id="hideMe" class="card-title red center">Success!</div>';

$formurl = 'edit.php'; ?>
            <!--<script type="text/javascript">var submitted=false;</script>
            <iframe name="hidden_iframe" id="hidden_iframe" style="display:none;" onload="if(submitted) {window.location='crosscheck.php?submit=true';}"></iframe>
            <form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="POST" target="hidden_iframe" onsubmit="submitted=true;">-->
             <form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="GET">
            <?php 
			  
			  		  
			  
$namestaff = 'ccname';
$namebarcode = 'cctraylocation';
$namecount = 'cccount';
$nameas = 'ccchecked';
$nameverify = 'ccverify';

?>
            <input type="hidden" name="usp" value="pp_url" />
            <input type="hidden" name="<?php echo $namestaff; ?>" value="<?php echo $name; ?>" />
            <div class="row">
                <div class="input-field col s12"> <i class="material-icons prefix">line_style</i>
                <select name="id">
                    <option value="" disabled selected>Select Tray/Shelf Barcode</option>
                    <?php
					
					
					
					
	$i = 0;				
					
/////Display all items with Cross Checked Items	  
$sql = "SELECT pfull, ProcessingKey, pcount, ptraylocation FROM ProcessingAll ORDER by pcount DESC";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))
{
	
	if($row['pfull'] !== 'Yes') {
		
echo '<option value="'.$row['ProcessingKey'].'">'.$row['ptraylocation'].' - '.$row['pcount'].' items in tray</option>';

$i = $i + 1;
		}
	
}

mysqli_close($conn);					
					

?>
                  </select>
                <?php
	if($i > 0) echo '<span class="red-text center" style="position:absolute; font-size:12px; margin:-5px 0 0 44px;">There are currently '.$i.' unfilled trays/shelves</label>';
	else echo '<label class="green-text">All Trays Have Been Filled.</span>';
	?>
              </div>
              </div>
           
            
              
            
            
            <input type="hidden" name="submit" value="Submit" />
            <br />
            <br />
            <button class="btn waves-effect waves-light right green" type="submit" >Update <i class="material-icons right">send</i> </button>
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