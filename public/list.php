<?php
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['admin']) || $_SESSION['admin'] !== 'yes') {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['expire']) || time() > (int)$_SESSION['expire']) {
    session_destroy();
    header('Location: login.php');
    exit;
}

include 'header.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not available.');
}

date_default_timezone_set('America/New_York');

function h($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$beginurl = isset($_GET['begin']) ? trim((string)$_GET['begin']) : '';
$endurl   = isset($_GET['end']) ? trim((string)$_GET['end']) : '';
$search   = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$order    = isset($_GET['order']) ? trim((string)$_GET['order']) : 'ptraylocation';
$sort     = isset($_GET['sort']) ? strtoupper(trim((string)$_GET['sort'])) : 'ASC';
$date     = isset($_GET['date']) ? strtoupper(trim((string)$_GET['date'])) : '';

$sort = ($sort === 'DESC') ? 'DESC' : 'ASC';
$sort2 = ($sort === 'ASC') ? 'DESC' : 'ASC';

$allowedOrder = array(
    'ptraylocation',
    'plibrary',
    'pname',
    'ptimestamp',
    'pcount',
    'pfull',
    'ccname',
    'cctimestamp',
    'cccount'
);

if (!in_array($order, $allowedOrder, true)) {
    $order = 'ptraylocation';
}

$allowedDateRanges = array('DAY', 'WEEK', 'MONTH', 'YEAR');
if (!in_array($date, $allowedDateRanges, true)) {
    $date = '';
}

$queryParams = array();

if ($beginurl !== '') {
    $queryParams['begin'] = $beginurl;
}
if ($endurl !== '') {
    $queryParams['end'] = $endurl;
}
if ($search !== '') {
    $queryParams['search'] = $search;
}
if ($date !== '') {
    $queryParams['date'] = $date;
}

function sortLink($column, $sortDirection, $existingParams)
{
    $params = $existingParams;
    $params['order'] = $column;
    $params['sort'] = $sortDirection;
    return '?' . http_build_query($params);
}

$sql = "SELECT * FROM ProcessingAll WHERE 1=1";
$params = array();
$types = '';

if ($search !== '') {
    $sql .= " AND ptraylocation LIKE ?";
    $params[] = '%' . $search . '%';
    $types .= 's';
}

if ($beginurl !== '' && $endurl !== '') {
    $beginTs = strtotime($beginurl);
    $endTs = strtotime($endurl);

    if ($beginTs !== false && $endTs !== false) {
        $b = date('Y-m-d', $beginTs) . ' 00:00:00';
        $e = date('Y-m-d', $endTs) . ' 23:59:59';
        $sql .= " AND ptimestamp BETWEEN ? AND ?";
        $params[] = $b;
        $params[] = $e;
        $types .= 'ss';
    }
} elseif ($date !== '') {
    switch ($date) {
        case 'DAY':
            $sql .= " AND ptimestamp >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)";
            break;
        case 'WEEK':
            $sql .= " AND ptimestamp >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 WEEK)";
            break;
        case 'MONTH':
            $sql .= " AND ptimestamp >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 MONTH)";
            break;
        case 'YEAR':
            $sql .= " AND ptimestamp >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 YEAR)";
            break;
    }
}

$sql .= " ORDER BY {$order} {$sort}";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die('Query preparation failed: ' . h($conn->error));
}

if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row_cnt = ($result instanceof mysqli_result) ? mysqli_num_rows($result) : 0;
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
.clip {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
}
.clip:hover {
    overflow: visible;
    white-space: normal;
    max-width: none;
}
</style>

<script>
$(function() {
    $("#datepicker, #datepicker2").datepicker({ dateFormat: "yy-mm-dd" });
});
</script>

<div class="container">
    <h3 class="purple-text center-align">SCF Tray Processing List</h3>

    <div class="row">
        <div class="col s12 m6">
            <div>
                <ul class="collapsible popout">
                    <li>
                        <div class="collapsible-header green lighten-5 pb-4">
                            <i class="material-icons">date_range</i>Date Filter
                        </div>
                        <div class="collapsible-body white">
                            <form method="get">
                                <div class="input-field">
                                    <i class="material-icons prefix indigo-text">date_range</i>
                                    <input id="datepicker" name="begin" type="text" class="validate" value="<?php echo h($beginurl); ?>">
                                    <label for="datepicker">Start Date</label>
                                </div>
                                <div class="input-field">
                                    <i class="material-icons prefix blue-grey-text">date_range</i>
                                    <input id="datepicker2" name="end" type="text" class="validate" value="<?php echo h($endurl); ?>">
                                    <label for="datepicker2">End Date</label>
                                </div>
                                <?php if ($search !== ''): ?>
                                    <input type="hidden" name="search" value="<?php echo h($search); ?>">
                                <?php endif; ?>
                                <input type="hidden" name="order" value="<?php echo h($order); ?>">
                                <input type="hidden" name="sort" value="<?php echo h($sort); ?>">
                                <div class="right-align" style="margin-top:16px;">
                                    <?php if ($beginurl !== '' || $endurl !== ''): ?>
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

        <div class="col s12 m6">
            <div>
                <ul class="collapsible popout">
                    <li>
                        <div class="collapsible-header blue lighten-5 pb-4">
                            <i class="material-icons">search</i>Search Tray/Shelf
                        </div>
                        <div class="collapsible-body white">
                            <form method="get">
                                <div class="input-field">
                                    <i class="material-icons prefix">search</i>
                                    <input id="search" name="search" type="text" class="validate" value="<?php echo h($search); ?>">
                                    <label for="search">Tray/Shelf Number</label>
                                </div>
                                <?php if ($beginurl !== ''): ?>
                                    <input type="hidden" name="begin" value="<?php echo h($beginurl); ?>">
                                <?php endif; ?>
                                <?php if ($endurl !== ''): ?>
                                    <input type="hidden" name="end" value="<?php echo h($endurl); ?>">
                                <?php endif; ?>
                                <input type="hidden" name="order" value="<?php echo h($order); ?>">
                                <input type="hidden" name="sort" value="<?php echo h($sort); ?>">
                                <div class="right-align" style="margin-top:16px;">
                                    <?php if ($search !== ''): ?>
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

    <div class="row">
        <div class="col s12">
            <div>
                <div class="card-content center-align">
                    <?php foreach (array('DAY', 'WEEK', 'MONTH', 'YEAR') as $d): ?>
                        <a class="btn btn-large waves-effect waves-light mr-1" href="?<?php echo http_build_query(array('order' => 'ptimestamp', 'sort' => 'DESC', 'date' => $d)); ?>">
                            <i class="material-icons left">date_range</i><?php echo ucfirst(strtolower($d)); ?>
                        </a>
                    <?php endforeach; ?>
                    <a class="btn btn-large waves-effect waves-light orange mr-1" href="list.php">
                        <i class="material-icons left">star</i>All
                    </a>
                    <a class="btn btn-large waves-effect waves-light red" href="match.php">
                        <i class="material-icons left">warning</i>Mis-Match Only
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <?php if ($row_cnt > 0): ?>
                        <h5 class="right-align grey-text text-darken-1 mb-5 mt-0">
                            <?php
                            switch ($date) {
                                case 'WEEK':
                                    echo 'Last 7 Days: ';
                                    break;
                                case 'DAY':
                                    echo 'Last 24 Hours: ';
                                    break;
                                case 'MONTH':
                                    echo 'Last 30 Days: ';
                                    break;
                                case 'YEAR':
                                    echo 'This Year: ';
                                    break;
                                default:
                                    echo 'All Records: ';
                                    break;
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
                                    <a href="<?php echo h(sortLink('ptraylocation', $sort2, $queryParams)); ?>">Tray Number</a>
                                </th>
                                <th class="purple-text purple lighten-5 center-align">
                                    <a href="<?php echo h(sortLink('plibrary', $sort2, $queryParams)); ?>">Library</a>
                                </th>
                                <th class="purple-text purple lighten-5 center-align">
                                    <a href="<?php echo h(sortLink('pname', $sort2, $queryParams)); ?>">Name</a>
                                </th>
                                <th class="purple-text purple lighten-5 center-align">
                                    <a href="<?php echo h(sortLink('ptimestamp', $sort2, $queryParams)); ?>">Time</a>
                                </th>
                                <th class="purple-text purple lighten-5 center-align">
                                    <a href="<?php echo h(sortLink('pcount', $sort2, $queryParams)); ?>">Count</a>
                                </th>
                                <th class="purple-text purple lighten-5 center-align">
                                    <a href="<?php echo h(sortLink('pfull', $sort2, $queryParams)); ?>">Full</a>
                                </th>
                                <th class="blue-text blue lighten-5 center-align">
                                    <a href="<?php echo h(sortLink('ccname', $sort2, $queryParams)); ?>">Name</a>
                                </th>
                                <th class="blue-text blue lighten-5 center-align">
                                    <a href="<?php echo h(sortLink('cctimestamp', $sort2, $queryParams)); ?>">Time</a>
                                </th>
                                <th class="blue-text blue lighten-5 center-align">
                                    <a href="<?php echo h(sortLink('cccount', $sort2, $queryParams)); ?>">Count</a>
                                </th>
                                <th class="red-text red lighten-5 center-align">
                                    <a href="match.php">Match</a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result instanceof mysqli_result): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <?php
                                    $pcount = isset($row['pcount']) && $row['pcount'] !== null && $row['pcount'] !== '' ? (int)$row['pcount'] : 0;
                                    $cccount = isset($row['cccount']) && $row['cccount'] !== null && $row['cccount'] !== '' ? (int)$row['cccount'] : null;

                                    $difference = ($cccount !== null && $pcount !== $cccount)
                                        ? abs($pcount - $cccount)
                                        : 0;

                                    $processing_date = '';
                                    if (!empty($row['ptimestamp'])) {
                                        $pt = strtotime($row['ptimestamp']);
                                        if ($pt !== false) {
                                            $processing_date = date('m/d/Y g:ia', $pt);
                                        }
                                    }

                                    $cc_date = '';
                                    if (!empty($row['cctimestamp'])) {
                                        $ct = strtotime($row['cctimestamp']);
                                        if ($ct !== false) {
                                            $cc_date = date('m/d/Y g:ia', $ct);
                                        }
                                    }
                                    ?>
                                    <tr<?php echo $difference > 0 ? ' class="red lighten-4"' : ''; ?>>
                                        <td style="min-width:200px;">
                                            <a class="btn-small purple lighten-1 bold no-shadow" href="edit.php?id=<?php echo (int)$row['ProcessingKey']; ?>">
                                                <?php echo h($row['ptraylocation']); ?>
                                            </a>
                                        </td>
                                        <td class="center-align clip"><?php echo h($row['plibrary']); ?></td>
                                        <td class="clip"><?php echo h($row['pname']); ?></td>
                                        <td class="clip"><?php echo h($processing_date); ?></td>
                                        <td class="center-align" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <a href="almacount.php?id=<?php echo (int)$row['ProcessingKey']; ?>">
                                                <?php echo $pcount; ?>
                                            </a>
                                        </td>
                                        <td class="center-align" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo h($row['pfull']); ?></td>
                                        <td class="clip"><?php echo h($row['ccname']); ?></td>
                                        <td class="clip"><?php echo h($cc_date); ?></td>
                                        <td class="center-align" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?php echo $cccount !== null ? $cccount : ''; ?>
                                        </td>
                                        <td class="center-align" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?php if ($difference > 0): ?>
                                                <span class="new badge red" data-badge-caption=""><?php echo $difference; ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php mysqli_free_result($result); ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
mysqli_stmt_close($stmt);
include 'footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    M.Collapsible.init(document.querySelectorAll('.collapsible'));
    M.FloatingActionButton.init(document.querySelectorAll('.fixed-action-btn'));
    M.Modal.init(document.querySelectorAll('.modal'));
});
</script>
</body>
</html> 