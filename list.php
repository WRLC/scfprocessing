<?php
session_start();
// Authentication check
if (isset($_SESSION['user_id'], $_SESSION['admin']) && $_SESSION['admin'] === 'yes') {
    if (time() > $_SESSION['expire']) {
        session_destroy();
        header("Location: login.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}

include 'header.php';

// Get & sanitize inputs
$beginurl = $_GET['begin'] ?? '';
$endurl   = $_GET['end']   ?? '';
$search   = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : '';
$order    = $_GET['order']  ?? 'ptraylocation';
$sort     = $_GET['sort']   ?? 'ASC';
$date     = $_GET['date']   ?? '';
$sort2    = $sort === 'ASC' ? 'DESC' : 'ASC';
$searchurl= $search ? "&search={$search}" : '';

// Date-range SQL
if ($beginurl && $endurl) {
    $b = date("Y-m-d", strtotime($beginurl)) . ' 00:00:00';
    $e = date("Y-m-d", strtotime($endurl))   . ' 23:59:59';
    $daterange = " AND (ptimestamp BETWEEN \"$b\" AND \"$e\") ";
} else {
    $daterange = '';
}

// WHERE-clause
if ($search) {
    $searchstring = "WHERE ptraylocation LIKE '%{$search}%' ";
} elseif ($date) {
    $searchstring = "WHERE ptimestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 1 {$date}) ";
} else {
    $searchstring = '';
}

// Run query
$sql   = "SELECT * FROM ProcessingAll {$searchstring} {$daterange} ORDER BY {$order} {$sort}";
$query = mysqli_query($conn, $sql);
$row_cnt = mysqli_num_rows($query);
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
    .clip {
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width:150px;
    }

    .clip:hover{
    overflow: visible; 
    white-space: normal; 
    max-width: auto;

    }
    </style>


<script>
  $(function(){
    $("#datepicker, #datepicker2").datepicker({ dateFormat: "yy-mm-dd" });
  });
</script>

<div class="container">
  <h3 class="purple-text center-align">SCF Tray Processing List</h3>

  <!-- Row: each collapsible in its own card -->
  <div class="row">
    <!-- Date Filter card -->
    <div class="col s12 m6">
      <div class="">
        <ul class="collapsible popout">
          <li>
            <div class="collapsible-header green lighten-5 pb-4">
              <i class="material-icons">date_range</i>Date Filter
            </div>
            <div class="collapsible-body white">
              <form method="get">
                <div class="input-field">
                  <i class="material-icons prefix indigo-text">date_range</i>
                  <input id="datepicker" name="begin" type="text" class="validate"
                         value="<?php echo htmlspecialchars($beginurl); ?>">
                  <label for="datepicker">Start Date</label>
                </div>
                <div class="input-field">
                  <i class="material-icons prefix blue-grey-text">date_range</i>
                  <input id="datepicker2" name="end" type="text" class="validate"
                         value="<?php echo htmlspecialchars($endurl); ?>">
                  <label for="datepicker2">End Date</label>
                </div>
                <input type="hidden" name="order" value="<?php echo $order; ?>">
                <input type="hidden" name="sort"  value="<?php echo $sort; ?>">
                <div class="right-align" style="margin-top:16px;">
                  <?php if ($beginurl && $endurl): ?>
                    <a class="btn-flat red-text" href="list.php">
                      <i class="material-icons left">clear</i>Clear
                    </a>
                  <?php endif; ?>
                  <button type="submit" class="btn waves-effect waves-light green">
                    <i class="material-icons left">filter_list</i>Filter
                  </button>
                </div>
              </form>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <!-- Search Tray card -->
    <div class="col s12 m6">
      <div class="">
        <ul class="collapsible popout">
          <li>
            <div class="collapsible-header blue lighten-5 pb-4">
              <i class="material-icons">search</i>Search Tray/Shelf
            </div>
            <div class="collapsible-body white">
              <form method="get">
                <div class="input-field">
                  <i class="material-icons prefix">search</i>
                  <input id="search" name="search" type="text" class="validate"
                         value="<?php echo $search; ?>">
                  <label for="search">Tray/Shelf Number</label>
                </div>
                <input type="hidden" name="order" value="<?php echo $order; ?>">
                <input type="hidden" name="sort"  value="<?php echo $sort; ?>">
                <div class="right-align" style="margin-top:16px;">
                  <?php if ($search): ?>
                    <a class="btn-flat red-text" href="list.php">
                      <i class="material-icons">clear</i>
                    </a>
                  <?php endif; ?>
                  <button type="submit" class="btn waves-effect waves-light blue">
                    <i class="material-icons left">search</i>Go
                  </button>
                </div>
              </form>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Quick-date buttons -->
  <div class="row">
    <div class="col s12">
      <div class="">
        <div class="card-content center-align">
          <?php foreach (['DAY','WEEK','MONTH','YEAR'] as $d): ?>
            <a class="btn btn-large waves-effect waves-light mr-1" href="?order=ptimestamp&sort=DESC&date=<?php echo $d; ?>">
              <i class="material-icons left">date_range</i><?php echo ucfirst(strtolower($d)); ?>
            </a>
          <?php endforeach; ?>
          <a class="btn btn-large waves-effect waves-light orange  mr-1" href="list.php">
            <i class="material-icons left">star</i>All
          </a>
          <a class="btn btn-large waves-effect waves-light red" href="match.php">
            <i class="material-icons left">warning</i>Mis-Match Only
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="row">
    <div class="col s12">
      <div class="card">
        <div class="card-content">
          <?php if ($row_cnt > 0): ?>
            <h5 class="right-align grey-text text-darken-1 mb-5 mt-0">
              <?php
                switch ($date) {
                  case 'WEEK':  echo 'Last 7 Days: ';   break;
                  case 'DAY':   echo 'Last 24 Hours: '; break;
                  case 'MONTH': echo 'Last 30 Days: ';  break;
                  case 'YEAR':  echo 'This Year: ';     break;
                  default:      echo 'All Records: ';   break;
                }
                echo $row_cnt . ' Results';
              ?>
            </h5>
          <?php endif; ?>

          <table class="striped highlight responsive-table">
            <thead>
              <tr class="purple darken-2 white-text">
                <th colspan="6" class="center-align">Processing</th>
                <th colspan="3" class="center-align blue darken-2 white-text">Cross Check</th>
                <th class="center-align red white-text"><i class="material-icons">warning</i></th>
              </tr>
              <tr>
                <th class="purple-text purple lighten-5 center-align">
                  <a href="?order=ptraylocation&sort=<?php echo $sort2.$searchurl; ?>">Tray Number</a>
                </th>
                <th class="purple-text purple lighten-5 center-align">
                  <a href="?order=plibrary&sort=<?php echo $sort2.$searchurl; ?>">Library</a>
                </th>
                <th class="purple-text purple lighten-5 center-align">
                  <a href="?order=pname&sort=<?php echo $sort2.$searchurl; ?>">Name</a>
                </th>
                <th class="purple-text purple lighten-5 center-align">
                  <a href="?order=ptimestamp&sort=<?php echo $sort2; ?>">Time</a>
                </th>
                <th class="purple-text purple lighten-5 center-align">
                  <a href="?order=pcount&sort=<?php echo $sort2.$searchurl; ?>">Count</a>
                </th>
                <th class="purple-text purple lighten-5 center-align">
                  <a href="?order=pfull&sort=<?php echo $sort2; ?>">Full</a>
                </th>
                <th class="blue-text blue lighten-5 center-align">
                  <a href="?order=ccname&sort=<?php echo $sort2.$searchurl; ?>">Name</a>
                </th>
                <th class="blue-text blue lighten-5 center-align">
                  <a href="?order=cctimestamp&sort=<?php echo $sort2.$searchurl; ?>">Time</a>
                </th>
                <th class="blue-text blue lighten-5 center-align">
                  <a href="?order=cccount&sort=<?php echo $sort2.$searchurl; ?>">Count</a>
                </th>
                <th class="red-text red lighten-5 center-align">
                  <a href="match.php">Match</a>
                </th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = mysqli_fetch_array($query)): ?>
                <?php
                  $difference = (isset($row['cccount']) && $row['pcount'] != $row['cccount'])
                                ? abs($row['pcount'] - $row['cccount']) : 0;
                  $processing_date = date('m/d/Y g:ia', strtotime($row['ptimestamp']));
                  $cc_date = $row['cctimestamp']
                             ? date('m/d/Y g:ia', strtotime($row['cctimestamp']))
                             : '';
                             
                ?>
                <?php if ($difference > 0) echo '<tr class="red lighten-4">'; else echo '<tr>'; ?>
              
                  <td style="min-width:200px;"><a class="btn-small purple lighten-1 bold no-shadow" href="edit.php?id=<?php echo $row['ProcessingKey']; ?>"><?php echo htmlspecialchars($row['ptraylocation']); ?></a></td>
                  <td class="center-align clip"><?php echo htmlspecialchars($row['plibrary']); ?></td>
                  <td class="clip"><?php echo htmlspecialchars($row['pname']); ?></td>
                  <td class="clip"><?php echo $processing_date; ?></td>
                  <td class="center-align" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><a href="almacount.php?id=<?php echo $row['ProcessingKey']; ?>"><?php echo $row['pcount']; ?></a></td>
                  <td class="center-align" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['pfull']); ?></td>
                  <td class="clip"><?php echo htmlspecialchars($row['ccname']); ?></td>
                  <td class="clip"><?php echo $cc_date; ?></td>
                  <td class="center-align" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo $row['cccount']; ?></td>
                  <td class="center-align" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php if ($difference > 0): ?><span class="new badge red" data-badge-caption=""><?php echo $difference; ?></span><?php endif; ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>

        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    M.Collapsible.init(document.querySelectorAll('.collapsible'));
    M.FloatingActionButton.init(document.querySelectorAll('.fixed-action-btn'));
    M.Modal.init(document.querySelectorAll('.modal'));
  });
</script>