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
      <div class="card white lighten-1  mt-5">
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
$sql3 = "SELECT pname,ccname,pcode FROM ProcessingAll WHERE ProcessingKey = $id";

//$sql 	= 'SELECT * FROM scfprocessing.ProcessingForm';
$query3 	= mysqli_query($conn, $sql3);
while ($row3 = mysqli_fetch_array($query3))

{
if($row3['pcode'] =='WD')
    echo '<div class="row"><div class="input-field red-text col s6"><i class="material-icons left">account_circle</i> Deaccessioned by '.$row3['pname'].'</div>';
			 
else {
                
              echo '<div class="row"><div class="input-field col s6"><i class="material-icons left">account_circle</i> Processed by '.$row3['pname'].'</div>';
			  if(isset($row3['ccname']) AND $row3['ccname'] !=NULL)
			  echo  '<div class="row"><div class="input-field purple-text col s6"><i class="material-icons left">account_circle</i> Cross Checked by '.$row3['ccname'].'</div></div>';
			 // echo '</div>';
}            
            
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
	
	

	
	//echo $row['ptimestamp'];
	//Tray Barcode
                echo '<div class="input-field col s12"> <i class="material-icons prefix">line_style</i>
                  <input name="'.$namebarcode.'" value="'.$row['ptraylocation'].'" id="icon_prefix2" type="text" class="validate">
                  <label for="icon_prefix2">Tray/Shelf Barcode</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Scan in Tray/Shelf Barcode</span> </div>
              </div>
              <div class="row">';
			  
			  //Processing Count
                echo '<div class="input-field col s6"> <i class="material-icons prefix">add_shopping_cart</i>
				<input name="'.$namecount.'" value="'.$row['pcount'].'" id="icon_prefix3" type="number" class="validate">';
				echo '<label for="icon_prefix3">Processing Count</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Numbers Only</span> </div>';
				  
				  
				  
				  
			//Cross Check Count	  
				  if(isset($row['cccount']) AND $row['cccount'] !=NULL) {
				  
				  echo '<div class="input-field col s6"> <i class="material-icons purple-text prefix">add_shopping_cart</i>';
				echo '<input name="cccount" value="'.$row['cccount'].'" id="icon_prefix3" type="number" class="validate">';
                  echo '<label for="icon_prefix3">Cross Check Count</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Numbers Only</span> </div>';
				  
				  }
				  
				  else { echo '<input type="hidden" name="cccount" value="" />'; }
				  
				  
				  
				   //Processing Date
                echo '<div class="input-field col s6"> <i class="material-icons prefix">date_range</i>
				<input name="ptimestamp" value="'.$row['ptimestamp'].'" id="icon_prefix3" class="validate">';
				echo '<label for="icon_prefix3"></label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Processing Date Format YYYY-MM-DD HH:MM:SS (24hr)</span> </div>';
				  
				  
				  //Cross Check Date	  
				  if(isset($row['cccount']) AND $row['cccount'] !=NULL) {
				  
				  echo '<div class="input-field col s6"> <i class="material-icons purple-text prefix">date_range</i>';
				echo '<input name="cctimestamp" value="'.$row['cctimestamp'].'" id="icon_prefix3"  class="validate">';
                  echo '<label for="icon_prefix3"></label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Cross Check Date Format YYYY-MM-DD HH:MM:SS (24hr)</span> </div>';
				  
				  }
				  
				  else { echo '<input type="hidden" name="cctimestamp" value="" />'; }
				  
	 
				  
				  
              echo '</div>';
			  
// Processing Full
			  
             echo ' <div class="row">
                <div class="input-field col s6"> <i class="material-icons prefix">shopping_cart</i>
                  <label>
                    <input type="checkbox" value="Yes"';
					
					if(isset($row['pfull']) AND $row['pfull'] == 'Yes') echo ' checked ';
					
					echo ' name="pfull" />
                    <span>Tray/Shelf Full?</span> </label>
                </div> ';




                //CC Verify
                if(isset($row['ccname']) AND $row['ccname'] !=NULL) {
              
              echo '<div class="input-field col s6"> <i class="material-icons purple-text prefix">line_weight</i>
                <label>
                  <input type="checkbox" value="Yes"';
                  if(isset($row['ccscan']) AND $row['ccscan'] == 'Yes') echo ' checked ';
                  
                  echo' name="ccscan" />
                  <span>Scan in Items in Alma</span> </label>
              </div>';
                }
                
                 
                
                else { echo '<input type="hidden" name="ccscan" value="" />'; }





				echo '</div>';
			 
			 
			 
			 
			 
			 
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
			  
			  if(isset($row['updated']) AND $row['updated'] !=NULL) 
			    echo '<div class="center"><br /><br />Last Update: '.$row['updated'].'</div>';
			  
			  $pcode = $row['pcode'];
            $cccount = $row['cccount'];
					}
					
					
				
				
				
              echo '
              <input type="hidden" name="submit" value="Submit" />
			  <br /><br />

              <!-- Dropdown Structure -->
              <ul id="dropdown2" class="dropdown-content">
               
                <li><a href="all_delete.php?id='.$id.'">Delete <i class="material-icons left">delete_forever</i></a></li>
                <li class="divider"></li>';
                if($pcode !='WD' AND $cccount !=NULL) {
              echo '<li><a href="deaccession_submit.php?id='.$id.'">Deaccession<i class="material-icons left">compare_arrows</i></a>';
            }
            if($cccount !=NULL) {
              echo '<li><a href="https://grima.app.wrlc.org/Deaccession/Deaccession.php" target="_blank">Alma Deaccession<i class="material-icons left">open_in_new</i></a>';
            }
                
              echo '</ul>
              
               
                
                  <div class="left hide-on-med-and-down">
                   
                    <!-- Dropdown Trigger -->
                    <a class="dropdown-trigger btn waves-effect waves-light left red" href="#!" data-target="dropdown2">Advanced Settings<i class="material-icons right">arrow_drop_down</i></a>
                  </div>
               
         











			  <button class="btn waves-effect waves-light right green" type="submit" >Update <i class="material-icons right">send</i> </button>
           </div>';
			
			// Connection will be closed in footer.php
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
