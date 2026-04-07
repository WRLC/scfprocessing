<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id'], $_SESSION['expire'])) {
    header('Location: login.php');
    exit;
}

if (time() > (int)$_SESSION['expire']) {
    session_destroy();
    header('Location: login.php');
    exit;
}

include 'header.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not available.');
}

$userId = (string)($_SESSION['user_id'] ?? '');
$working = isset($working) ? (string)$working : 'false';
$account = isset($account) ? (string)$account : 'false';

$pendingCrossChecks = 0;

$sql = "SELECT COUNT(*) AS pending_count FROM ProcessingAll WHERE ccname IS NULL OR ccname = ''";
$result = mysqli_query($conn, $sql);

if ($result instanceof mysqli_result) {
    $row = mysqli_fetch_assoc($result);
    $pendingCrossChecks = isset($row['pending_count']) ? (int)$row['pending_count'] : 0;
    mysqli_free_result($result);
}
?>
<style>
  .no-shadow {
    box-shadow: none !important;
  }

  .btn-large .badge {
  position: static;          /* remove floating behavior */
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-left: 10px;
  vertical-align: middle;
  height: 22px;
  min-width: 22px;
  line-height: 22px;
  padding: 0 6px;
  font-size: 12px;
  border-radius:4px;
  color: #fff;
  margin-top: 16px;
}


</style>

<div>
  <div class="row center">
    <h4 class="white-text blue darken-2 mt-0 p-3">
      <i style="position: absolute; margin-top:-10px;" class="medium material-icons">settings_applications</i>
      <span style="margin-left:70px;">SCF Tools</span>
    </h4>

    <div class="col s12 push-m3 m3">
      <?php if ($working !== 'true' && $account !== 'true'): ?>
        <div class="card white lighten-1">
          <div class="card-content black-text">
            <span class="card-title">Time Card</span>
            <p>Be sure to clock in to your time card before beginning work.</p>
          </div>
          <a href="timecard.php" class="waves-effect green waves-light btn-large">
            <i class="material-icons left">timer</i>Time Card
          </a>
          <br><br>
        </div>
      <?php endif; ?>

      <div class="card white lighten-1 mb-5">
        <div class="card-content black-text">
          <span class="card-title blue-text">SCF Processing Utilities</span>
        </div>

        <a href="altcall.php" class="waves-effect blue waves-light btn-large mr-3">
          <i class="material-icons left">library_add</i> Add Item Call Number
        </a>

        <a href="in1.php" class="waves-effect blue waves-light btn-large">
          <i class="material-icons left">speaker_notes</i> Add Internal Note 1
        </a>

        <a href="notecall.php" class="waves-effect green waves-light btn-large">
          <i class="material-icons left">speaker_notes</i> Add ICN/IN1
        </a>
        <br><br><br>
      </div>

      <div class="card white lighten-1">
        <div class="card-content black-text">
          <span class="card-title pink-text">Refile Processing</span>
          <p>Tools for Processing of Refile Items</p>
        </div>
        <a href="refile/index.php" class="waves-effect pink waves-light btn-large">
          Home <i class="material-icons right">arrow_forward</i>
        </a>
        <br><br>
      </div>
    </div>

    <div class="col s12 push-m3 m3">
      <div class="card white lighten-1 mb-5">
        <div class="card-content black-text">
          <span class="card-title teal-text">SCF Processing Forms</span>
        </div>

        <a href="processing.php" class="waves-effect waves-light btn-large">
          <i class="material-icons left">move_to_inbox</i> Tray/Shelf Location
        </a>

        <a href="crosscheck.php" class="waves-effect waves-light btn-large">
          <i class="material-icons left">check_circle</i> Cross Check
          <?php if ($pendingCrossChecks > 0): ?>
            <span class="badge red">
  <?php echo $pendingCrossChecks; ?>
</span>
          <?php endif; ?>
        </a>

        <br><br>
      </div>

      <div class="card white lighten-1 pb-5">
        <div class="card-content black-text">
          <span class="card-title purple-text">Projects Tracker</span>
          <p>Manage ongoing special project and track time spent working on Deaccessions.</p>
        </div>

        <a href="project.php" class="waves-effect purple waves-light btn-large">
          <i class="material-icons left">developer_board</i>Projects
        </a>
        <br>

        <a href="deaccessionin.php" class="waves-effect purple waves-light btn-large">
          <i class="material-icons left">timer</i>Track Time
        </a>
        <br>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>