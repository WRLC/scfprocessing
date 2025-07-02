<?php /** @noinspection PhpUndefinedVariableInspection */
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
<?php $hoursname = $_GET['name']; ?>
<div style="margin: auto; width: 50%; padding: 10px;">
  <div class="row">
    <div class="col s12 m12">
      <div class="card white lighten-1">
        <div class="card-content blue-text"> <span class="card-title">Update Hours for <?php echo $hoursname; ?></span>
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


$formurl = 'processing_update.php'; ?>
            <!-- ****  Javascript to submit the form to Google Sheets, then return to this page if successful **** --> 
            <script type="text/javascript">var submitted=false;</script>
            <iframe name="hidden_iframe" id="hidden_iframe" style="display:none;" onload="if(submitted) {window.location='processing_edit.php?submit=true&id=<?php echo $id; ?>';}"></iframe>
            <form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="POST" target="hidden_iframe" onsubmit="submitted=true;">
              
                <input type="hidden" name="Name" value="<?php echo $name; ?>" />
                <input type="hidden" name="ProcessingKey" value="<?php echo $id; ?>" />
              <div class="row">
                <div class="input-field col s12"> <i class="material-icons prefix">account_balance</i>
                  
              <?php 
			  
		  
			  
			  
			  
$namelibrary = 'Library';
$namestaff = 'Name';
$namebarcode = 'TrayLocation';
$namecount = 'Count';
$namefull = 'Full';
$nameas = 'Checked';
$nameverify = 'Verify';

/////Display all items with Cross Checked Items	  
$sql = "SELECT * FROM ProcessingForm WHERE ProcessingKey = $id";

//$sql 	= 'SELECT * FROM scfprocessing.ProcessingForm';
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{
echo '<select name="'.$namelibrary.'" class="validate">
                    <span class="helper-text" data-error="wrong" data-success="Complete">Select Library</span>';
echo '<option value="'.$row['Library'].'" selected>'.$row['Library'].'</option>';	  
	
$sheetid = '1mHvqrZVBNce6gZPrqojkh6auZ_tEO_QL0pb_Szyk9J8';
$url = 'https://spreadsheets.google.com/feeds/list/'.$sheetid.'/1/public/values?alt=json';
$file= file_get_contents($url);

$json = json_decode($file);
$rows = $json->{'feed'}->{'entry'};

foreach ($rows as $row) {
	
	$university = $row->{'gsx$university'}->{'$t'};
	$code = $row->{'gsx$code'}->{'$t'};
	
		if((isset($university)) AND $university !='')
	
		echo '<option value="'.$code.'">'.$university.'</option>';
} 
}
echo '</select>
                </div>
              </div>
              <div class="row">';
			  //$sql 	= 'SELECT * FROM scfprocessing.ProcessingForm';
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{
                echo '<div class="input-field col s12"> <i class="material-icons prefix">line_style</i>
                  <input name="'.$namebarcode.'" value="'.$row['TrayLocation'].'" id="icon_prefix2" type="text" class="validate">
                  <label for="icon_prefix2">Tray/Shelf Barcode</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Scan in Tray/Shelf Barcode</span> </div>
              </div>
              <div class="row">
                <div class="input-field col s12"> <i class="material-icons prefix">add_shopping_cart_circle</i>';
				$sql = "SELECT * FROM ProcessingForm WHERE ProcessingKey = $id";


                  echo '<input name="'.$namecount.'" value="'.$row['Count'].'" id="icon_prefix3" type="number" class="validate">';
				  

                  echo '<label for="icon_prefix3">Tray/Shelf Count</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Numbers Only</span> </div>
              </div>
              <div class="row">
                <div class="input-field col s6"> <i class="material-icons prefix">shopping_cart_circle</i>
                  <label>
                    <input type="checkbox" value="Yes"';
					
					if(isset($row['Full']) AND $row['Full'] == 'Yes') echo ' checked ';
					
					echo ' name="'.$namefull.'" />
                    <span>Tray/Shelf Full?</span> </label>
                </div>';
				
				
             echo ' </div>
              <div class="row">
                <div class="input-field col s10"> <i class="material-icons prefix">search</i>
                  <label>
                    <input type="checkbox" value="Yes"';
					if(isset($row['Verify']) AND $row['Verify'] == 'Yes') echo ' checked ';
					
					echo' name="'.$nameverify.'" />
                    <span>Advanced Search in Alma Completed</span> </label>
                </div>
              </div>
              <div class="row">
                <div class="input-field col s10"> <i class="material-icons prefix">spellcheck</i>
                  <label>
                    <input type="checkbox" value="Yes"';
					if(isset($row['Checked']) AND $row['Checked'] == 'Yes') echo ' checked ';
					}
					
					echo ' name="'.$nameas.'" />
                    <span>Verified all information is correct</span> </label>
                </div>
              </div>
              <input type="hidden" name="submit" value="Submit" />
			  <br /><br />
			  <a class="btn waves-effect waves-light left red" href="processing_delete.php?id='.$id.'">Delete <i class="material-icons left">delete_forever</i></a>
              <button class="btn waves-effect waves-light right green" type="submit" >Update <i class="material-icons right">send</i> </button>
           ';
			
			mysqli_close($conn);
          echo ' </form></div>
        </div>
      </div>
	   <br />
    <a class="btn waves-effect waves-light right blue" href="list.php#processing'.$id.'">Return to List <i class="material-icons left">keyboard_return</i></a><br /><br />
    </div>
  </div>
</div>

<!--JavaScript at end of body for optimized loading-->';
include('footer.php'); ?>
</body>
</html>