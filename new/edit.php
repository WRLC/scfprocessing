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

//If not admin account, go to login
if ($_SESSION['admin'] !=="yes") {
	 // Redirect them to the login page
    header("Location: login.php");
}
?>
<?php include('header.php'); ?>

  <div class="row">
    <div class="col s12 push-m3 m6">
      <div class="card white lighten-1">
        <div class="card-content blue-text"> <span class="card-title green-text center">Update Tray/Shelf</span>
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

if($submit == 'true') echo '<div id="hideMe" class="card-title center" style="color:#4CAF50;">Success!</div>';


$formurl = 'all_edit_submit.php'; ?>
            <!-- ****  Javascript to submit the form to Google Sheets, then return to this page if successful **** --> 
           <!-- <script type="text/javascript">var submitted=false;</script>
            <iframe name="hidden_iframe" id="hidden_iframe" style="display:none;" onload="if(submitted) {window.location='edit.php?submit=true&id=<?php echo $id; ?>';}"></iframe>
            <form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="POST" target="hidden_iframe" onsubmit="submitted=true;">-->
            <form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="POST">
            
            
              
               
                <input type="hidden" name="ProcessingKey" value="<?php echo $id; ?>" />
                
                  <?php 
				  
				  
				  /////Display all items with Cross Checked Items	  
$sql3 = "SELECT pname,ccname FROM ProcessingAll WHERE ProcessingKey = $id";

//$sql 	= 'SELECT * FROM scfprocessing.ProcessingForm';
$query3 	= mysqli_query($conn, $sql3);
while ($row3 = mysqli_fetch_array($query3))

{
                
              echo '<div class="row"><div class="input-field col s6"><i class="material-icons left">account_circle</i> Processed by '.$row3['pname'].'</div>';
			  if(isset($row3['ccname']) AND $row3['ccname'] !=NULL)
			  echo  '<div class="row"><div class="input-field purple-text col s6"><i class="material-icons left">account_circle</i> Cross Checked by '.$row3['ccname'].'</div></div>';
			 // echo '</div>';
                  
            
}
		  
		echo '<div class="input-field col s12"> <i class="material-icons prefix">account_balance</i>';	  
			  
			  
$namelibrary = 'plibrary';
$namestaff = 'pname';
$namebarcode = 'ptraylocation';
$namecount = 'pcount';
$namefull = 'pfull';
$nameas = 'pchecked';
$nameverify = 'pverify';

/////Display all items with Cross Checked Items	  
$sql = "SELECT * FROM ProcessingAll WHERE ProcessingKey = $id";

//$sql 	= 'SELECT * FROM scfprocessing.ProcessingForm';
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{

//hidden name input to stave who made original edits	
echo '
 <input type="hidden" name="pname" value="'.$row['pname'].'" />
 <input type="hidden" name="ccname" value="'.$row['ccname'].'" />
 
 ';
//Dropdown list of libraries
	 echo '
	<select name="plibrary" class="validate">
	<span class="helper-text" data-error="wrong" data-success="Complete">Select Library</span><option value="'.$row['plibrary'].'" selected>'.$row['plibrary'].'</option>';	  
	
/////Display Library Locations  
$sql2 = "SELECT university FROM LibraryLocations ORDER by university ASC";
$query2 	= mysqli_query($conn, $sql2);
while ($row2 = mysqli_fetch_array($query2))
{
echo '<option value="'.$row2['university'].'">'.$row2['university'].'</option>';
}
}

echo '</select>
                </div>
              </div>
              <div class="row">';

//Get the rest of the items for Processing			 
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{
	//Tray Barcode
                echo '<div class="input-field col s12"> <i class="material-icons prefix">line_style</i>
                  <input name="'.$namebarcode.'" value="'.$row['ptraylocation'].'" id="icon_prefix2" type="text" class="validate">
                  <label for="icon_prefix2">Tray/Shelf Barcode</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Scan in Tray/Shelf Barcode</span> </div>
              </div>
              <div class="row">';
			  
			  //Processing Count
                echo '<div class="input-field col s6"> <i class="material-icons prefix">add_shopping_cart_circle</i>
				<input name="'.$namecount.'" value="'.$row['pcount'].'" id="icon_prefix3" type="number" class="validate">';
				echo '<label for="icon_prefix3">Processing Count</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Numbers Only</span> </div>';
				  
				  
				  
				  
			//Cross Check Count	  
				  if(isset($row['cccount']) AND $row['cccount'] !=NULL) {
				  
				  echo '<div class="input-field col s6"> <i class="material-icons purple-text prefix">add_shopping_cart_circle</i>';
				echo '<input name="cccount" value="'.$row['cccount'].'" id="icon_prefix3" type="number" class="validate">';
                  echo '<label for="icon_prefix3">Cross Check Count</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Numbers Only</span> </div>';
				  
				  }
				  
				  else { echo '<input type="hidden" name="cccount" value="" />'; }
				  
	 
				  
				  
              echo '</div>';
			  
// Processing Full
			  
             echo ' <div class="row">
                <div class="input-field col s6"> <i class="material-icons prefix">shopping_cart_circle</i>
                  <label>
                    <input type="checkbox" value="Yes"';
					
					if(isset($row['pfull']) AND $row['pfull'] == 'Yes') echo ' checked ';
					
					echo ' name="pfull" />
                    <span>Tray/Shelf Full?</span> </label>
                </div> 
				</div>';
			 
			 
			 
			 
			 
			 
           // Processing Verify
		   echo '<div class="row">';
			  
                echo '<div class="input-field col s6"> <i class="material-icons prefix">search</i>
                  <label>
                    <input type="checkbox" value="Yes"';
					if(isset($row['pverify']) AND $row['pverify'] == 'Yes') echo ' checked ';
					
					echo' name="pverify" />
                    <span>Processing Alma Search</span> </label>
                </div>';
				
				
				
				//CC Verify
				  if(isset($row['ccname']) AND $row['ccname'] !=NULL) {
				
				echo '<div class="input-field col s6"> <i class="material-icons purple-text prefix">search</i>
                  <label>
                    <input type="checkbox" value="Yes"';
					if(isset($row['ccverify']) AND $row['ccverify'] == 'Yes') echo ' checked ';
					
					echo' name="ccverify" />
                    <span>Cross Check Alma Search</span> </label>
                </div>';
				  }
				  
				   
				  
				  else { echo '<input type="hidden" name="ccverify" value="" />'; }
				
				
              echo '</div>';
			  
			  
			  
			    // Processing checked
		   echo '<div class="row">';
			  
                echo '<div class="input-field col s6"> <i class="material-icons prefix">spellcheck</i>
                  <label>
                    <input type="checkbox" value="Yes"';
					if(isset($row['pchecked']) AND $row['pchecked'] == 'Yes') echo ' checked ';
					
					echo' name="pchecked" />
                    <span>Processing Verified</span> </label>
                </div>';
				
				
				
				//CC Verify
				  if(isset($row['ccname']) AND $row['ccname'] !=NULL) {
				
				echo '<div class="input-field col s6"> <i class="material-icons purple-text prefix">spellcheck</i>
                  <label>
                    <input type="checkbox" value="Yes"';
					if(isset($row['ccchecked']) AND $row['ccchecked'] == 'Yes') echo ' checked ';
					
					echo' name="ccchecked" />
                    <span>Cross Check Verified</span> </label>
                </div>';
				  }
				  
				  else { echo '<input type="hidden" name="ccchecked" value="" />';
				  
				  }
				
				
              echo '</div>';
			  
			  
			  
			  
			  
			  
		
					}
					
					
				
				
				
              echo '
              <input type="hidden" name="submit" value="Submit" />
			  <br /><br />
			  <a class="btn waves-effect waves-light left red" href="all_delete.php?id='.$id.'">Delete <i class="material-icons left">delete_forever</i></a>
              <button class="btn waves-effect waves-light right green" type="submit" >Update <i class="material-icons right">send</i> </button>
           </div>';
			
			mysqli_close($conn);
          echo ' </form>
		  </div>
        </div>
      
	   <br />
    <a class="btn waves-effect waves-light right blue" href="list.php#'.$id.'">Return to List <i class="material-icons left">keyboard_return</i></a><br /><br /></div>
    </div>
  </div>


<!--JavaScript at end of body for optimized loading-->';
include('footer.php'); ?>
</body>
</html>