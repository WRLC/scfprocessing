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
        <div class="card-content blue-text"> <span class="card-title center">Edit Project</span>
        <span class="new badge white red-text right" data-badge-caption="All Fields Required"></span>
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



            <?php

$name = $_SESSION['user_id'];
$submit = $_GET['submit'];

$id = $_GET['id'];

if($submit == 'true') echo '<div id="hideMe" class="card-title" style="color:#4CAF50;">Success!</div>';







$formurl = 'project_update.php'; ?>
            <!-- ****  Javascript to submit the form to Google Sheets, then return to this page if successful **** --> 
          <script type="text/javascript">var submitted=false;</script>
           <iframe name="hidden_iframe" id="hidden_iframe" style="display:none;" onload="if(submitted) {window.location='project_edit.php?submit=true&id=<?php echo $id; ?>';}"></iframe> 
            <form autocomplete="off" action="<?php echo $formurl; ?>" class="col s12" method="POST" target="hidden_iframe" onsubmit="submitted=true;">
              
               
                
              <div class="row">
               
                  
              <?php 

$id = $_GET['id'];


$sql2 = "SELECT * FROM project WHERE id = $id";


$query2 	= mysqli_query($conn, $sql2);
while ($row2 = mysqli_fetch_array($query2))

{
    $title = $row2['title'];
    $university = $row2['university'];
    $startDate = $row2['startDate'];
    $endDate = $row2['endDate'];
    $goal = $row2['goal'];
    $archive = $row2['archive'];

  

}
			  
              echo '
              </div>
              ';
			
	
                echo '<div class="row"><div class="input-field col s12"> <i class="material-icons prefix">developer_board</i>
                  <input name="title" value="'.$title.'" id="icon_prefix2" type="text" class="validate">
                  <label for="icon_prefix2">Project Name</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Enter Full Name of Project</span> 
                  </div>
              </div>



              <div class="row">
              <div class="input-field col s12"> <i class="material-icons prefix">account_balance</i> 
                <select name="university" class="validate">
                  <span class="helper-text" data-error="wrong" data-success="Complete">Select Library</span>
                 ';
                 

                  $sql3 = "SELECT librarykey, university FROM LibraryLocations WHERE librarykey = '$university'";
                  $query3 	= mysqli_query($conn, $sql3);
                  while ($row3 = mysqli_fetch_array($query3))
                  {
                  echo '<option value="'.$row3['librarykey'].'" selected>'.$row3['university'].'</option>';
                  }



                  
/////Display Library Locations  
$sql = "SELECT librarykey, university FROM LibraryLocations ORDER by university ASC";
$query 	= mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query))
{
echo '<option value="'.$row['librarykey'].'">'.$row['university'].'</option>';
}
mysqli_close($conn);					

                echo '</select>
               
              </div>
            </div>'; ?>





<div class="row">



      
     <form method="get">
	<div class="input-field col s6"> <i class="material-icons blue-grey-text prefix">date_range</i>
				<input name="startDate" id="datepicker"
                
                <?php
				
				if(isset($startDate) and $startDate !='') echo 'value="'.$startDate.'"'; 
				//else echo 'value=""';
				
				?>
                
                 type="text" class="validate">
				<label for="icon_prefix3">Start Date</label>
                   </div>
			
				<div class="input-field col s6"> <i class="material-icons indigo-text prefix">date_range</i>
				<input name="endDate" id="datepicker2"
                
                 <?php
				
				if(isset($endDate) and $endDate !='') echo 'value="'.$endDate.'"'; 
				//else echo 'value=""';
				
				?>
                
                
                              
                
                 type="text" class="validate">
                 <label for="icon_prefix3">End Date</label>
   
                  </div></div>
                  
                  




			  
			  
		<?php	  echo '<div class="row"> <div class="input-field col s12"><i class="material-icons prefix">stars</i>
                  <input name="goal" id="icon_prefix2" value="'.$goal.'" type="text" class="validate">
                  <label for="icon_prefix2">Goal</label>
                  <span class="helper-text" data-error="wrong" data-success="Complete">Enter the number of items to be processed</span> 
                  
                  
                  </div></div>



                  <div class="row">
                  <div class="input-field col s12"> <i class="material-icons prefix">archive</i>
                    <label>
                      <input type="checkbox" value="yes"';
                      
                      if(isset($archive) AND $archive == 'yes') echo ' checked ';
                      
                      echo ' name="archive" />
                      <span>Project Completed? (Archive Project)</span> </label>
                  </div>




              </div>';
			  
			  
             
              
           
					
					
					echo '
                    <input type="hidden" name="id" value="'.$id.'" />
              <input type="hidden" name="submit" value="Submit" />
			<br /><br />
              <a class="btn waves-effect waves-light left red" type="submit" href="project_delete.php?id='.$id.'" >Delete Project <i class="material-icons left">delete_forever</i> </a>
              <button class="btn waves-effect waves-light right green" type="submit" >Update Project <i class="material-icons right">send</i> </button>
           ';

			mysqli_close($conn);
          echo ' </form></div>
        </div></div>
     
	   
    <a class="btn waves-effect waves-light right blue" href="project_details.php?id='.$id.'">Return to Project <i class="material-icons left">keyboard_return</i></a><br /><br />
    </div>
  </div>
</div>

<!--JavaScript at end of body for optimized loading-->';
include('footer.php'); ?>
</body>
</html>