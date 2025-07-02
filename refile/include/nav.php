<?php include 'refresh.php';?>
<nav class="navbar navbar-expand-sm navbar-dark text-white" style="background-color:#2196F3;">
<a class="navbar-brand" href="#"></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
    <div class="navbar-nav text-white">
    <a class="navbar-brand mb-0 h1 nav-item nav-link text-white" href="index.php">SCF Refile Tools</a><span class="nav-link">|</span>
      <a class="nav-item nav-link text-white" href="check_in.php">Check-In</a><span class="nav-link">|</span>

      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Reshelf
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
          <a class="dropdown-item" href="refile_upload.php">Bulk</a>
          <a class="dropdown-item" href="refile_update.php">Single Item</a>
        </div>
      </li><span class="nav-link">|</span>
      <?php if (isset($_SESSION['user_id']) and $_SESSION['admin'] == 'yes') {
    echo '
     <a class="nav-item nav-link text-white" href="refile_summary.php">Summary</a><span class="nav-link">|</span>
      <a class="nav-item nav-link text-white" href="refile_errors.php">Mismatched Trays</a><span class="nav-link">|</span>
      <a class="nav-item nav-link text-white" href="hold_shelf.php">Hold Shelf List</a><span class="nav-link">|</span>
      <a class="nav-item nav-link text-white" href="refile_date.php">Alma Stats</a><span class="nav-link">|</span>';
}
;?>
<a class="nav-item nav-link text-white" href="check.php">Item Check</a>
<a class="btn float-right btn-dark my-2 my-sm-0" style="position: absolute; right:20px;" href="../index.php">SCF Home</a>
    </div>
  </div>
</nav>