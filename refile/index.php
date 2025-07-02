<?php include 'include/access.php';?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refile Links</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
 <!-- Nav bar -->
<?php include 'include/nav.php';?>
    <!-- Main container -->
    <div class="container mt-5">
<h4 class="alert text-white alert-light" style="background-color: #3C6586;">Refile Processing</h4>
<div class="row text-dark">
<div class="col-sm-6">
<div class="card">
  <div class="card-body">
    <h5 class="card-title">Step 1: Check-in Returns and place on Hold Shelf</h5>
    <p class="card-text">Using the barcode:
        <ul><li>check in the item</li>
        <li>set Internal Note 3 to "SCF Hold Shelf"</li>
    <li>In temp location - "true"</li>
    <li>temp library - "SCF"</li>
    <li>temp location - "SCF_Hold"</li>
        </ul>
   </p>
   <a href="check.php" class="btn btn-primary">Preview Item</a> <a href="check_in.php" class="btn btn-primary">Check-In</a>
    </div>

  </div>
  </div>
  <div class="col-sm-6">
  <div class="card">
  <div class="card-body">
    <h5 class="card-title">Step 2: Tray verification and
    reshelving in SCF</h5>
    <p class="card-text">Upload a .txt file from the mini-scanner, verify the information, and update:
        <ul>
        <li>Internal Note 3 - ""</li>
    <li>In temp location - "false"</li>
    <li>temp library - ""</li>
    <li>temp location - ""</li>
        </ul>
    </p>
     <a href="refile_upload.php" class="btn btn-primary">Bulk</a> <a href="refile_update.php" class="btn btn-primary">Single</a> </div>

  </div>
</div>
</div>
<br />

<?php if (isset($_SESSION['user_id']) and $_SESSION['admin'] == 'yes') {?>


<h4 class="alert text-white alert-light" style="background-color: #DB8F18;">Tools</h4>

<div class="row text-dark">

<div class="col-sm-6">
<div class="card">
  <div class="card-body">
    <h5 class="card-title">Mismatched Tray Barcodes</h5>
    <p class="card-text">View a list of items with mismatched tray barcodes. Update their status and view a spreadsheet of results to edit.</p>
    <a href="refile_errors.php"   class="btn btn-primary">View</a> </div>
  </div>
  <br />
  </div>

  <div class="col-sm-6">
<div class="card">
    <div class="card-body">
      <h5 class="card-title">Check Item Status</h5>
      <p class="card-text">Check the status of any item.  Includes things like title, checked in status, tray barcode, and more.</p>
     <a href="check.php"   class="btn btn-primary">View</a>
    </div>
  </div>
</div>
<br />

<div class="col-sm-6">
<div class="card">
    <div class="card-body">
      <h5 class="card-title">Hold Shelf List</h5>
      <p class="card-text">See a list of current items on the hold shelf.</p>
     <a href="hold_shelf.php"   class="btn btn-primary">View</a>
    </div>

  </div>
</div>
<br />

<div class="col-sm-6">
<div class="card">
    <div class="card-body">
      <h5 class="card-title">Stats</h5>
      <p class="card-text">View Refile counts. Select by date range or view monthly reports.</p>
     <a href="refile_date.php"   class="btn btn-primary">View</a>
    </div>
    </div>
  </div>
</div>
<?php }?>
<br />

    <!-- Bootstrap JS  -->
    <?php include 'include/footer.php';?>
</body>
</html>