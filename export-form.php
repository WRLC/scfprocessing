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
<?php include('header.php'); 

$name = $_SESSION['user_id'];
$submit = $_GET['submit'];
$cardname = $_GET['name'];
$beginurl = $_GET['begin'];
$endurl = $_GET['end'];
$selectedlibrary = $_GET['library'];

$beginurformatted = date("Y-m-d", strtotime($beginurl));
$endurlformattted = date("Y-m-d", strtotime($endurl));

if((isset($beginurl) AND $beginurl != '') AND (isset($endurl) AND $endurl != ''))

{
$daterange = ' AND (cctimestamp BETWEEN "'.$beginurformatted.' 00:00:00" AND "'.$endurlformattted.' 23:59:59") ';
}
else { 
$daterange ='';
}

?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="/resources/demos/style.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
  $( function() {
    $( "#datepicker" ).datepicker();
  } );
  
  
   $( function() {
    $( "#datepicker2" ).datepicker();
  } );
  
  
  </script>

<div class="row">
  <div class="col s12 push-m3 m6">
    <div class="card white lighten-1 mt-5">
    
     <div class="card-content blue-text"> <span class="card-title center">Export Processing Records To CSV</span>
      <form method="post" action="export.php">
      
      
       <div class="input-field col s12"> <i class="material-icons blue-grey-text prefix">search</i>
       <input id="icon_prefix" name="search" type="text" class="validate">
          <label for="icon_prefix">Search (Any Tray Number Value)</label>
      
        </div>
      
      
      
      
        <div class="input-field col s6"> <i class="material-icons indigo-text prefix">date_range</i>
          <input name="startdate" id="datepicker"
                
                <?php
				
				if(isset($beginurl) and $beginurl !='') echo 'value="'.$beginurl.'"'; 
				//else echo 'value=""';
				
				?>
                
                 type="text" class="validate">
          <label for="icon_prefix3">Start Date</label>
        </div>
        <div class="input-field col s6"> <i class="material-icons indigo-text prefix">date_range</i>
          <input name="enddate" id="datepicker2"
                
                 <?php
				
				if(isset($endurl) and $endurl !='') echo 'value="'.$endurl.'"'; 
				//else echo 'value=""';
				
				?>
                
                
                              
                
                 type="text" class="validate">
          <label for="icon_prefix3">End Date</label>
        </div>
        <div class="input-field col s6"> <i class="material-icons blue-grey-text prefix">business</i>
          <select name="library">
            <?php
					
					if(isset($selectedlibrary) AND $selectedlibrary != '')
					
					echo ' <option value="'.$selectedlibrary.'" selected>'.$selectedlibrary.'</option>
					<option value="">All Libraries</option>';
					else
				echo ' <option value="" selected>All Libraries</option>';	
					
	  /////Get library information	  
$sql = "SELECT university from LibraryLocations";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{				

$library = $row['university'];
	echo '<option value="'.$library.'">'.$library.'</option>';
}	
		
//mysqli_close($conn);				
?>
          </select>
          <label>Select Library</label>
        </div>
        
  <div class="input-field col s6"> <i class="material-icons blue-grey-text prefix">person</i>
          <select name="name">
            <?php
					
					
				echo ' <option value="" selected>All Staff</option>';	
					
	  /////Get library information	  
$sql = "SELECT * FROM Staff ORDER by name ASC";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))

{				

$name = $row['name'];
	echo '<option value="'.$name.'">'.$name.'</option>';
}	
		
mysqli_close($conn);				
?>
          </select>
          <label>Select Staff</label>
        </div>       
        

      
        <?php
			 
			
			 
			 echo '<br />
      <br /> <br />
			 
			 <button style="margin-top:50px;" class="btn waves-effect waves-light right green" name="export" type="submit">CSV Export <i class="material-icons right">get_app</i> </button>
			 
			   <br />
      <br />
      <br /> <br />
	
			 
			';
			 
			 ?> 
      </form>
      <br />
      <br />
      <br /> <br />
      <br />
      <br /><br /> <br /><br />
     
   
   
    </div>
  </div>
</div>
</div>
<?php include('footer.php'); ?>

</body></html>