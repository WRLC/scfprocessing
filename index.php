<?php
session_start();

if (isset($_SESSION['user_id'])) {
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
<style>
  /*.card {min-height:300px;} */
  </style>
<?php include 'header.php';?>

<div>
  <div class="row center">
  <h4 class="white-text blue darken-2 mt-0 p-3"><i style="position: absolute; margin-top:-10px;" class="medium material-icons">settings_applications</i><span style="margin-left:70px;">SCF Tools</span></h4>
  
  <!------ Left Column -------->
  <div class="col s12 push-m3 m3">
      
      <?php if ($working !== 'true' && $account !== 'true') {
    echo '
    <div class="card white lighten-1">
        <div class="card-content black-text">
            <span class="card-title">Time Card</span>
            <p>Be sure to clock in to your time card before beginning work.</p>
        </div>
        <a href="timecard.php" class="waves-effect green waves-light btn-large">
            <i class="material-icons left">timer</i>Time Card
        </a>
        <br /><br />
    </div>';
} ?>


<!------ Processing Utilites (formerly Grima) Card -------->
<div class="card white lighten-1 mb-5">
        <div class="card-content black-text"> <span class="card-title blue-text">SCF Processing Utilities</span> </div>

        <a href="altcall.php" class="waves-effect blue waves-light btn-large mr-3"><i class="material-icons left">library_add</i> Add Item Call Number</a>
       <a href="in1.php" class="waves-effect blue waves-light btn-large"><i class="material-icons left">speaker_notes</i> Add Internal Note 1</a>
       <a href="notecall.php" class="waves-effect green waves-light btn-large"><i class="material-icons left">speaker_notes</i> Add ICN/IN1</a>
       <br />
<!----- <a href="processing_api.php" class="waves-effect waves-light btn-large">Add Alt Call Number and Internal Note 1</a>
       <br /> ---->
        <br /> <br />
      </div>
<!------ End Processing Utilites (formerly Grima) Card -------->



<!------ Refile Card -------->
<div class="card white lighten-1">
    <div class="card-content black-text">
      <span class="card-title pink-text">Refile Processing</span>
      <p>Tools for Processing of Refile Items</p>
    </div>
    <a href="refile/index.php" class="waves-effect pink waves-light btn-large">Home <i class="material-icons right">arrow_forward</i></a>
  <!---- <a href="deaccessionin.php" class="waves-effect purple waves-light btn-large"><i class="material-icons left">timer</i>Track Time</a> ---->
<br /><br />
  </div>
<!------ End Refile Card -------->






</div>
<!------ End Left Column -------->



<!------ Right Column -------->
<div class="col s12 push-m3 m3">

<!------Processing Forms Card -------->
      <div class="card white lighten-1 mb-5">
        <div class="card-content black-text"> <span class="card-title teal-text">SCF Processing Forms</span> </div>
        <a href="processing.php" class="waves-effect waves-light btn-large"><i class="material-icons left">move_to_inbox</i> Tray/Shelf Location</a> <a href="crosscheck.php" class="waves-effect waves-light btn-large"><i class="material-icons left">check_circle</i> Cross Check

<?php $i = 0;

/////Display all items with Cross Checked Items
$sql = "SELECT ccname FROM ProcessingAll";
$query = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query)) {

    if ($row['ccname'] == null) {

        $i = $i + 1;
    }

}
// mysqli_close($conn);
if ($i > 0) {
    echo '<span style="background-color:#F44336; color:#fff; border:0px solid #fff; border-radius:2px; margin-left:10px; padding:5px; height:30px; width:30px;">' . $i . '</span>';
}
?>
      </a>
         <br />
        <br />
      </div>
<!------ End Processing Forms Card -------->

<!------ Projects Tracker -------->
<div class="card white lighten-1 pb-5">
    <div class="card-content black-text">
      <span class="card-title purple-text">Projects Tracker</span>
      <p>Manage ongoing special project and track time spent working on Deaccessions.</p>
    </div>
    <a href="project.php" class="waves-effect purple waves-light btn-large"><i class="material-icons left">developer_board</i>Projects</a><br />
  <a href="deaccessionin.php" class="waves-effect purple waves-light btn-large"><i class="material-icons left">timer</i>Track Time</a>
<br /></div>
<!------ End Projects Tracker -------->


  </div>
  </div>
</div>
<!--JavaScript at end of body for optimized loading-->
<?php include 'footer.php';?>
</body>
</html>