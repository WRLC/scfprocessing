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
<div style="margin: auto;
  width: 50%;
  
  padding: 10px;
  ">
  <div class="row">
    <div class="col s12 m12">
      <div class="card white lighten-1">
        <div class="card-content blue-text"> <span class="card-title">Cross Check Form: <?php echo $_SESSION['user_id']; ?></span>
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

$formurl = 'https://docs.google.com/forms/d/e/1FAIpQLSeevtguf8HYqfIfEDic4ZC1SgH3fwlyfKUcmTm_E5hfabnt-g/formResponse'; ?>
            <!-- ****  Javascript to submit the form to Google Sheets, then return to this page if successful **** --> 
            <script type="text/javascript">var submitted=false;</script>
            <iframe name="hidden_iframe" id="hidden_iframe" style="display:none;" onload="if(submitted) {window.location='crosscheck.php?submit=true';}"></iframe>
            <form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="GET" target="hidden_iframe" onsubmit="submitted=true;">
              
              <!--- <form action="https://docs.google.com/forms/d/e/1FAIpQLSeevtguf8HYqfIfEDic4ZC1SgH3fwlyfKUcmTm_E5hfabnt-g/formResponse" method="GET">  -->
              
              <?php 
$namestaff = 'entry.2051445591';
$namebarcode = 'entry.405061146';
$namecount = 'entry.498270155';

$nameas = 'entry.922993077';
$nameverify = 'entry.2092785568';
?>
              <input type="hidden" name="usp" value="pp_url" />
              <input type="hidden" name="<?php echo $namestaff; ?>" value="<?php echo $name; ?>" />
              <div class="row">
                <div class="input-field col s12"> <i class="material-icons prefix">line_style</i>
                  <select name="<?php echo $namebarcode; ?>">
                    <option value="" disabled selected>Select Tray/Shelf Barcode</option>
                    <?php
	  
	 $i = 0;
$sheetid = '1exbCuvsm63PILUAwjZZk_h6DEgs0IqxQ3_PUGSN4cdc';
$url = 'https://spreadsheets.google.com/feeds/list/'.$sheetid.'/1/public/values?alt=json';
$file= file_get_contents($url);

$json = json_decode($file);
$rows = $json->{'feed'}->{'entry'};

foreach ($rows as $row) {
	
	$dropdown = $row->{'gsx$dropdown'}->{'$t'};
	
		if((isset($dropdown)) AND $dropdown !='')
	
		echo '<option value="'.$dropdown.'">'.$dropdown.'</option>';
		
		
	$i++;
	
	
	
}

 


?>
                  </select>
                  <?php
	if($i > 0) echo '<label>Only unfinished cross checked tray/shelf barcodes will display</label>';
	else echo '<label>All Cross Check Items Have Been Processed.</label>';
	?>
                </div>
              </div>
              <div class="row">
                <div class="input-field col s12"> <i class="material-icons prefix">add_shopping_cart_circle</i>
                  <input name="<?php echo $namecount; ?>" id="icon_prefix3" type="number" class="validate">
                  <label for="icon_prefix3">Tray/Shelf Count</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Numbers Only</span> </div>
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
</div>

<!--JavaScript at end of body for optimized loading-->

<?php include('footer.php'); ?>
</body>
</html>