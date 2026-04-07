<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id'], $_SESSION['expire'])) {
    header('Location: login.php');
    exit;
}

if (time() > (int) $_SESSION['expire']) {
    session_destroy();
    header('Location: login.php');
    exit;
}

include 'header.php'; // expects $conn from connect.php

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not available.');
}

/*
|--------------------------------------------------------------------------
| Input handling
|--------------------------------------------------------------------------
*/
$beginInput = isset($_GET['begin']) ? trim((string)$_GET['begin']) : '';
$endInput = isset($_GET['end']) ? trim((string)$_GET['end']) : '';
$selectedLibrary = isset($_GET['library']) ? trim((string)$_GET['library']) : '';

$beginFormatted = '';
$endFormatted = '';
$hasDateRange = false;

if ($beginInput !== '' && $endInput !== '') {
    $beginTs = strtotime($beginInput);
    $endTs = strtotime($endInput);

    if ($beginTs !== false && $endTs !== false) {
        $beginFormatted = date('Y-m-d', $beginTs);
        $endFormatted = date('Y-m-d', $endTs);
        $hasDateRange = true;
    }
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/
function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function displayCount(int $value): string
{
    return $value > 0 ? number_format($value) : '';
}

/*
|--------------------------------------------------------------------------
| Library list for datalist
|--------------------------------------------------------------------------
*/
$libraries = [];

$libraryListSql = "SELECT university FROM LibraryLocations ORDER BY university ASC";
$libraryListResult = mysqli_query($conn, $libraryListSql);

if ($libraryListResult instanceof mysqli_result) {
    while ($row = mysqli_fetch_assoc($libraryListResult)) {
        $libraries[] = (string)$row['university'];
    }
    mysqli_free_result($libraryListResult);
}

/*
|--------------------------------------------------------------------------
| Report data
|--------------------------------------------------------------------------
*/
$rates = [
    'volumes'      => 0.75,
    'oversized'    => 0.75,
    'boxes'        => 2.65,
    'clamshells'   => 1.50,
    'flat_boxes'   => 2.65,
    'long_boxes'   => 2.65,
    'shelf'        => 2.00,
    'deaccessioned'=> 1.70,
];

$totals = [
    'volumes'       => 0,
    'oversized'     => 0,
    'boxes'         => 0,
    'clamshells'    => 0,
    'flat_boxes'    => 0,
    'long_boxes'    => 0,
    'shelf'         => 0,
    'deaccessioned' => 0,
];

$rowsByLibrary = [];

$sql = "
    SELECT
        plibrary,
        COALESCE(SUM(CASE WHEN pcode NOT IN ('BX','SR','RB','XX','CB','GB','LB','WD') THEN cccount ELSE 0 END), 0) AS volumes,
        COALESCE(SUM(CASE WHEN pcode = 'XX' THEN cccount ELSE 0 END), 0) AS oversized,
        COALESCE(SUM(CASE WHEN pcode IN ('RB','BX') THEN cccount ELSE 0 END), 0) AS boxes,
        COALESCE(SUM(CASE WHEN pcode = 'CB' THEN cccount ELSE 0 END), 0) AS clamshells,
        COALESCE(SUM(CASE WHEN pcode = 'GB' THEN cccount ELSE 0 END), 0) AS flat_boxes,
        COALESCE(SUM(CASE WHEN pcode = 'LB' THEN cccount ELSE 0 END), 0) AS long_boxes,
        COALESCE(SUM(CASE WHEN pcode = 'SR' THEN cccount ELSE 0 END), 0) AS shelf,
        COALESCE(SUM(CASE WHEN pcode = 'WD' THEN cccount ELSE 0 END), 0) AS deaccessioned
    FROM ProcessingAll
    WHERE plibrary <> 'WRLC Books (OUP)'
";

$params = [];
$types = '';

if ($selectedLibrary !== '') {
    $sql .= " AND plibrary = ?";
    $params[] = $selectedLibrary;
    $types .= 's';
}

if ($hasDateRange) {
    $sql .= " AND cctimestamp BETWEEN ? AND ?";
    $params[] = $beginFormatted . ' 00:00:00';
    $params[] = $endFormatted . ' 23:59:59';
    $types .= 'ss';
}

$sql .= " GROUP BY plibrary ORDER BY plibrary ASC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die('Query preparation failed.');
}

if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result instanceof mysqli_result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $library = (string)$row['plibrary'];

        $rowsByLibrary[$library] = [
            'volumes'       => (int)$row['volumes'],
            'oversized'     => (int)$row['oversized'],
            'boxes'         => (int)$row['boxes'],
            'clamshells'    => (int)$row['clamshells'],
            'flat_boxes'    => (int)$row['flat_boxes'],
            'long_boxes'    => (int)$row['long_boxes'],
            'shelf'         => (int)$row['shelf'],
            'deaccessioned' => (int)$row['deaccessioned'],
        ];

        foreach ($totals as $key => $value) {
            $totals[$key] += $rowsByLibrary[$library][$key];
        }
    }

    mysqli_free_result($result);
}

mysqli_stmt_close($stmt);

$values = [];
$grandTotal = 0.00;

foreach ($totals as $key => $count) {
    $values[$key] = $count * $rates[$key];
    $grandTotal += $values[$key];
}
?>

<style>
.billing-filter-wrap {
    padding: 8px 14px 0 14px;
}

.billing-filter {
    margin: 0;
}

.billing-filter-row {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
}

.billing-filter-row input[type="date"],
.billing-filter-row input[type="text"] {
    margin: 0;
    height: 2.25rem;
    padding: 0 8px;
    font-size: 0.95rem;
    max-width: 190px;
    background: #fff;
    border: 1px solid #cfd8dc;
    border-radius: 4px;
    box-sizing: border-box;
}

.billing-filter-row .btn-small,
.billing-filter-row .btn-flat {
    margin: 0;
    height: 2.25rem;
    line-height: 2.25rem;
    padding: 0 12px;
}

.billing-filter-row .btn-flat {
    border: 1px solid #cfd8dc;
}

.billing-report-title {
    margin-bottom: 4px;
}

.billing-date-range {
    margin: 14px 0 18px 0;
    font-size: 1rem;
}

@media only screen and (max-width: 700px) {
    .billing-filter-row {
        justify-content: stretch;
    }

    .billing-filter-row input[type="date"],
    .billing-filter-row input[type="text"],
    .billing-filter-row .btn-small,
    .billing-filter-row .btn-flat {
        width: 100%;
        max-width: none;
    }
}
</style>

<div>
    <div class="row">
        <div class="col s12 push-m1 m10"><br><br><br>

            <div class="card white lighten-1">

                <div class="no-print billing-filter-wrap">
                    <form method="get" action="billing.php" class="billing-filter">
                        <div class="billing-filter-row">
                            <input
                                type="date"
                                name="begin"
                                value="<?php echo h($beginFormatted); ?>"
                                aria-label="Start date"
                            >

                            <input
                                type="date"
                                name="end"
                                value="<?php echo h($endFormatted); ?>"
                                aria-label="End date"
                            >

                            <input
                                type="text"
                                name="library"
                                list="library-list"
                                placeholder="Library"
                                value="<?php echo h($selectedLibrary); ?>"
                                aria-label="Library"
                            >

                            <datalist id="library-list">
                                <?php foreach ($libraries as $library): ?>
                                    <option value="<?php echo h($library); ?>">
                                <?php endforeach; ?>
                            </datalist>

                            <button class="btn-small waves-effect waves-light green" type="submit">
                                Filter
                            </button>

                            <?php if ($beginInput !== '' || $endInput !== '' || $selectedLibrary !== ''): ?>
                                <a class="btn-flat black-text" href="billing.php">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div style="padding:24px 13px!important;" class="card-content blue-grey-text">
                    <span class="card-title center billing-report-title">SCF Billing Counts Report</span>

                    <?php if ($selectedLibrary !== ''): ?>
                        <span class="card-title center"><?php echo h($selectedLibrary); ?></span>
                    <?php endif; ?>

                    <?php if ($hasDateRange): ?>
                        <div class="print center billing-date-range">
                            <?php echo h(date('M d, Y', strtotime($beginFormatted))); ?>
                            -
                            <?php echo h(date('M d, Y', strtotime($endFormatted))); ?>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <table class="striped" style="border:2px solid #ccc!important;">
                            <thead>
                                <tr>
                                    <th class="blue-grey white-text center">Library</th>
                                    <th class="blue-grey white-text center">Volumes</th>
                                    <th class="blue-grey white-text center">Oversized Books</th>
                                    <th class="blue-grey white-text center">Boxes</th>
                                    <th class="blue-grey white-text center">Clamshells</th>
                                    <th class="blue-grey white-text center">Flat Boxes</th>
                                    <th class="blue-grey white-text center">Long Boxes</th>
                                    <th class="blue-grey white-text center">Shelf Rentals</th>
                                    <th class="blue-grey white-text center">Deaccessioned</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rowsByLibrary as $library => $counts): ?>
                                    <?php if (array_sum($counts) === 0) { continue; } ?>
                                    <tr>
                                        <td style="border-left:1px solid #eee;"><?php echo h($library); ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?php echo displayCount($counts['volumes']); ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?php echo displayCount($counts['oversized']); ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?php echo displayCount($counts['boxes']); ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?php echo displayCount($counts['clamshells']); ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?php echo displayCount($counts['flat_boxes']); ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?php echo displayCount($counts['long_boxes']); ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?php echo displayCount($counts['shelf']); ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?php echo displayCount($counts['deaccessioned']); ?></td>
                                    </tr>
                                <?php endforeach; ?>

                                <tr>
                                    <td style="border-top:2px solid #ccc; border-bottom:2px solid #ccc;" class="green black-text lighten-4">Total Count:</td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?php echo number_format($totals['volumes']); ?></td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?php echo number_format($totals['oversized']); ?></td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?php echo number_format($totals['boxes']); ?></td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?php echo number_format($totals['clamshells']); ?></td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?php echo number_format($totals['flat_boxes']); ?></td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?php echo number_format($totals['long_boxes']); ?></td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?php echo number_format($totals['shelf']); ?></td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?php echo number_format($totals['deaccessioned']); ?></td>
                                </tr>

                                <tr>
                                    <th class="green darken-1 white-text">Value:</th>
                                    <th class="green darken-1 white-text center">$<?php echo number_format($values['volumes'], 2, '.', ','); ?></th>
                                    <th class="green darken-1 white-text center">$<?php echo number_format($values['oversized'], 2, '.', ','); ?></th>
                                    <th class="green darken-1 white-text center">$<?php echo number_format($values['boxes'], 2, '.', ','); ?></th>
                                    <th class="green darken-1 white-text center">$<?php echo number_format($values['clamshells'], 2, '.', ','); ?></th>
                                    <th class="green darken-1 white-text center">$<?php echo number_format($values['flat_boxes'], 2, '.', ','); ?></th>
                                    <th class="green darken-1 white-text center">$<?php echo number_format($values['long_boxes'], 2, '.', ','); ?></th>
                                    <th class="green darken-1 white-text center">$<?php echo number_format($values['shelf'], 2, '.', ','); ?></th>
                                    <th class="green darken-1 white-text center">$<?php echo number_format($values['deaccessioned'], 2, '.', ','); ?></th>
                                </tr>
                            </tbody>
                        </table>

                        <br><br>
                        <h4 class="center">Total: $<?php echo number_format($grandTotal, 2, '.', ','); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>