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


<script src="dist/quagga.min.js"></script>


<div class="row">
 <div class="col s12 push-m3 m6">
	<div class="col-lg-6">
		<div class="input-group">
			<input id="scanner_input" class="form-control" placeholder="Click the button to scan an EAN..." type="text" /> 
			<span class="input-group-btn"> 
				<button class="btn btn-default" type="button" data-toggle="modal" data-target="#livestream_scanner">
					<i class="fa fa-barcode"></i>
				</button> 
			</span>
		</div><!-- /input-group -->
	</div><!-- /.col-lg-6 -->
</div><!-- /.row -->
<div class="modal" id="livestream_scanner">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">Barcode Scanner</h4>
			</div>
			<div class="modal-body" style="position: static">
				<div id="interactive" class="viewport"></div>
				<div class="error"></div>
			</div>
			<div class="modal-footer">
				<label class="btn btn-default pull-left">
					<i class="fa fa-camera"></i> Use camera app
					<input type="file" accept="image/*;capture=camera" capture="camera" class="hidden" />
				</label>
				<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</div>




<?php include('header.php'); ?>

  <div class="row">
     <div class="col s12 push-m3 m6">
      <div class="card white lighten-1">
        <div class="card-content blue-text"> <span class="card-title">Tray/Shelf Location Form: <?php echo $_SESSION['user_id']; ?></span>
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

if($submit == 'true') echo '<div id="hideMe" class="card-title" style="color:#4CAF50;">Success!</div>';


$formurl = 'processing_submit.php'; ?>
            <!-- ****  Javascript to submit the form to Google Sheets, then return to this page if successful **** --> 
            <script type="text/javascript">var submitted=false;</script>
            <iframe name="hidden_iframe" id="hidden_iframe" style="display:none;" onload="if(submitted) {window.location='processing.php?submit=true';}"></iframe>
            <form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="POST" target="hidden_iframe" onsubmit="submitted=true;">
              
              
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

?>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="input-field col s12"> <i class="material-icons prefix">line_style</i>
                  <input name="<?php echo $namebarcode; ?>" id="icon_prefix2" type="text" class="validate">
                  <label for="icon_prefix2">Tray/Shelf Barcode</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Scan in Tray/Shelf Barcode</span> </div>
              </div>
              <div class="row">
                <div class="input-field col s12"> <i class="material-icons prefix">add_shopping_cart_circle</i>
                  <input name="<?php echo $namecount; ?>" id="icon_prefix3" type="number" class="validate">
                  <label for="icon_prefix3">Tray/Shelf Count</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Numbers Only</span> </div>
              </div>
              <div class="row">
                <div class="input-field col s6"> <i class="material-icons prefix">shopping_cart_circle</i>
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
              <button class="btn waves-effect waves-light right red" type="submit" >Submit <i class="material-icons right">send</i> </button>
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