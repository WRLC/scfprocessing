<?php
session_start();

if ( isset( $_SESSION['user_id'] ) AND ($_SESSION['user_id'] =='Tammy Hennig' OR $_SESSION['user_id'] =='Admin') ) {
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
<div style="margin: auto; width: 50%; padding: 10px;">
  <div class="row">
    <div class="col s12 m12">
      <h4 class="red-text center"><i style="position: absolute; margin-top:-10px;" class="medium material-icons">bar_chart</i><span style="margin-left:60px;">Reports</span></h4>
      <ul class="collection">
        <!--<li class="collection-item avatar"> <i class="material-icons circle blue">view_list</i> <span class="title blue-text">Daily List</span>
          <p>List of daily progress. <br>
            <i class="grey-text">Check for Cross-Check errors at a glance and correct them</i> </p>
          <a href="/scf/list.php" class="secondary-content"><i class="material-icons">arrow_forward</i></a> </li>-->
          
        <li class="collection-item avatar"> <i class="material-icons circle blue">view_list</i> <span class="title blue-text">Processing List</span>
          <p>List of daily progress in an interactive Dashboard. <br>
            <i class="grey-text">Check for Cross-Check errors at a glance and correct them</i> </p>
          <a href="https://datastudio.google.com/s/muphq56sHV8" target="_blank" class="secondary-content"><i class="material-icons">arrow_forward</i></a> </li>
           <li class="collection-item avatar"><i class="material-icons circle purple">insert_chart</i> <span class="title purple-text">Monthly Billing Counts</span>
          <p> <i>Counts emailed monthly to Tim</i><br>
            <i class="grey-text">via PDF</i> </p>
          <a href="https://datastudio.google.com/open/1TVcFgEBeUz6BTsG5kBVtua5CWQoQjBPk" target="_blank" class="secondary-content"><i class="material-icons">arrow_forward</i></a> </li>
        <li class="collection-item avatar"> <i class="material-icons circle green">timer</i> <span class="title green-text">Staff Time Cards</span>
          <p>List of daily time cards in an interactive Dashboard. <br>
          </p>
          <a href="https://datastudio.google.com/u/1/reporting/1s3fk8vW6-vesgIyMDvfTB4jscEeKaCYH/page/2T1l" target="_blank" class="secondary-content"><i class="material-icons">arrow_forward</i></a> </li>
       
          
        <li class="collection-item avatar"> <i class="material-icons circle pink">pie_chart</i> <span class="title pink-text">Processing Goals</span>
          <p>Track progress of Staff Processing<br>
          
          <i class="grey-text">
          <ul>
            <li>View Processing and Cross-Check counts.</li>
            <li>Temp Staff Work Hours.</li>
            <li>Daily, Weekly, Monthly, and YTD.</li>
            <li>Emailed daily to Tammy.</li>
          </ul>
          </i>
          </p>
          <a href="https://datastudio.google.com/s/ou6dcSn6KJM" target="_blank" class="secondary-content pulse"><i class="material-icons">arrow_forward</i></a> </li>
      </ul>
    </div>
  </div>
</div>

<!--JavaScript at end of body for optimized loading-->
<?php include('footer.php'); ?>
</body>
</html>