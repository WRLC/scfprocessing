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

include 'header.php'; // assumes $conn is created here

if (!isset($conn) || !($conn instanceof mysqli)) {
    throw new RuntimeException('Database connection is not available.');
}

$userId = (string) $_SESSION['user_id'];

$beginInput       = trim((string) filter_input(INPUT_GET, 'begin', FILTER_DEFAULT));
$endInput         = trim((string) filter_input(INPUT_GET, 'end', FILTER_DEFAULT));
$selectedLibrary  = trim((string) filter_input(INPUT_GET, 'library', FILTER_DEFAULT));

$beginFormatted = null;
$endFormatted   = null;

if ($beginInput !== '') {
    $beginTs = strtotime($beginInput);
    if ($beginTs !== false) {
        $beginFormatted = date('Y-m-d', $beginTs);
    }
}

if ($endInput !== '') {
    $endTs = strtotime($endInput);
    if ($endTs !== false) {
        $endFormatted = date('Y-m-d', $endTs);
    }
}

$hasDateRange = ($beginFormatted !== null && $endFormatted !== null);

$rateMap = [
    'volumes'     => 0.75,
    'oversized'   => 0.75,
    'boxes'       => 2.65,
    'clamshells'  => 1.50,
    'flat_boxes'  => 2.65,
    'long_boxes'  => 2.65,
    'shelf'       => 2.00,
    'deaccession' => 1.70,
];

$totals = [
    'volumes'     => 0,
    'oversized'   => 0,
    'boxes'       => 0,
    'clamshells'  => 0,
    'flat_boxes'  => 0,
    'long_boxes'  => 0,
    'shelf'       => 0,
    'deaccession' => 0,
];

$libraries = [];
$rowsByLibrary = [];

/**
 * Fetch library list
 */
$librarySql = 'SELECT university FROM LibraryLocations';
$params = [];
$types  = '';

if ($selectedLibrary !== '') {
    $librarySql .= ' WHERE university = ?';
    $params[] = $selectedLibrary;
    $types   .= 's';
}

$librarySql .= ' ORDER BY university ASC';

$stmt = $conn->prepare($librarySql);
if (!$stmt) {
    throw new RuntimeException('Prepare failed: ' . $conn->error);
}

if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $libraries[] = $row['university'];
}

$stmt->close();

/**
 * Aggregate all counts in one query
 */
$reportSql = "
    SELECT
        plibrary,
        COALESCE(SUM(CASE WHEN pcode NOT IN ('BX','SR','RB','XX','CB','GB','LB','WD') THEN cccount ELSE 0 END), 0) AS volumes,
        COALESCE(SUM(CASE WHEN pcode = 'XX' THEN cccount ELSE 0 END), 0) AS oversized,
        COALESCE(SUM(CASE WHEN pcode IN ('RB','BX') THEN cccount ELSE 0 END), 0) AS boxes,
        COALESCE(SUM(CASE WHEN pcode = 'CB' THEN cccount ELSE 0 END), 0) AS clamshells,
        COALESCE(SUM(CASE WHEN pcode = 'GB' THEN cccount ELSE 0 END), 0) AS flat_boxes,
        COALESCE(SUM(CASE WHEN pcode = 'LB' THEN cccount ELSE 0 END), 0) AS long_boxes,
        COALESCE(SUM(CASE WHEN pcode = 'SR' THEN cccount ELSE 0 END), 0) AS shelf,
        COALESCE(SUM(CASE WHEN pcode = 'WD' THEN cccount ELSE 0 END), 0) AS deaccession
    FROM ProcessingAll
    WHERE plibrary <> 'WRLC Books (OUP)'
";

$reportParams = [];
$reportTypes  = '';

if ($selectedLibrary !== '') {
    $reportSql .= " AND plibrary = ?";
    $reportParams[] = $selectedLibrary;
    $reportTypes   .= 's';
}

if ($hasDateRange) {
    $reportSql .= " AND cctimestamp BETWEEN ? AND ?";
    $reportParams[] = $beginFormatted . ' 00:00:00';
    $reportParams[] = $endFormatted . ' 23:59:59';
    $reportTypes   .= 'ss';
}

$reportSql .= " GROUP BY plibrary ORDER BY plibrary ASC";

$stmt = $conn->prepare($reportSql);
if (!$stmt) {
    throw new RuntimeException('Prepare failed: ' . $conn->error);
}

if ($reportTypes !== '') {
    $stmt->bind_param($reportTypes, ...$reportParams);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $library = $row['plibrary'];

    $rowsByLibrary[$library] = [
        'volumes'     => (int) $row['volumes'],
        'oversized'   => (int) $row['oversized'],
        'boxes'       => (int) $row['boxes'],
        'clamshells'  => (int) $row['clamshells'],
        'flat_boxes'  => (int) $row['flat_boxes'],
        'long_boxes'  => (int) $row['long_boxes'],
        'shelf'       => (int) $row['shelf'],
        'deaccession' => (int) $row['deaccession'],
    ];

    foreach ($totals as $key => $value) {
        $totals[$key] += $rowsByLibrary[$library][$key];
    }
}

$stmt->close();

$values = [];
$grandTotal = 0.00;

foreach ($totals as $key => $count) {
    $values[$key] = $count * $rateMap[$key];
    $grandTotal += $values[$key];
}

function displayCell(int $value): string
{
    return $value > 0 ? number_format($value) : '';
}
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
$(function () {
    $("#datepicker, #datepicker2").datepicker();
});
</script>

<div>
    <div class="row">
        <div class="col s12 push-m1 m10"><br><br><br>
            <div class="no-print"></div>

            <div class="card white lighten-1">
                <div class="no-print">
                    <ul class="collapsible">
                        <li>
                            <div class="collapsible-header grey-text lighten-4">
                                <i class="material-icons blue-grey-text">date_range</i>
                                <i class="material-icons blue-grey-text">business</i>
                                FILTER
                            </div>
                            <div class="collapsible-body">
                                <span>
                                    <form method="get">
                                        <div class="input-field col s6">
                                            <i class="material-icons blue-grey-text prefix">date_range</i>
                                            <input
                                                name="begin"
                                                id="datepicker"
                                                type="text"
                                                class="validate"
                                                value="<?= htmlspecialchars($beginInput, ENT_QUOTES, 'UTF-8') ?>"
                                            >
                                            <label for="datepicker">Start Date</label>
                                        </div>

                                        <div class="input-field col s6">
                                            <i class="material-icons indigo-text prefix">date_range</i>
                                            <input
                                                name="end"
                                                id="datepicker2"
                                                type="text"
                                                class="validate"
                                                value="<?= htmlspecialchars($endInput, ENT_QUOTES, 'UTF-8') ?>"
                                            >
                                            <label for="datepicker2">End Date</label>
                                        </div>

                                        <div class="row">
                                            <div class="input-field col s6">
                                                <i class="material-icons blue-grey-text prefix">business</i>
                                                <select name="library">
                                                    <option value="" <?= $selectedLibrary === '' ? 'selected' : '' ?>>All Libraries</option>
                                                    <?php foreach ($libraries as $library): ?>
                                                        <option
                                                            value="<?= htmlspecialchars($library, ENT_QUOTES, 'UTF-8') ?>"
                                                            <?= $selectedLibrary === $library ? 'selected' : '' ?>
                                                        >
                                                            <?= htmlspecialchars($library, ENT_QUOTES, 'UTF-8') ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label>Select Library</label>
                                            </div>
                                        </div>

                                        <div style="border-bottom:1px solid #eee;" class="input-field col s12">
                                            <?php if ($beginInput !== '' || $endInput !== '' || $selectedLibrary !== ''): ?>
                                                <a class="btn waves-effect waves-light left red" href="billing.php">
                                                    Clear <i class="material-icons left">clear</i>
                                                </a>
                                            <?php endif; ?>

                                            <button class="btn waves-effect waves-light right green" type="submit">
                                                Filter <i class="material-icons right">filter_list</i>
                                            </button>
                                        </div>
                                    </form>

                                    <br><br><br>
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div style="padding:24px 13px!important;" class="card-content blue-grey-text">
                    <span class="card-title center">SCF Billing Counts Report</span>

                    <?php if ($selectedLibrary !== ''): ?>
                        <span class="card-title center"><?= htmlspecialchars($selectedLibrary, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>

                    <?php if ($hasDateRange): ?>
                        <div class="print center" style="margin:20px 0;">
                            <?= htmlspecialchars($beginInput, ENT_QUOTES, 'UTF-8') ?> -
                            <?= htmlspecialchars($endInput, ENT_QUOTES, 'UTF-8') ?>
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
                                    <?php
                                    $rowTotal = array_sum($counts);
                                    if ($rowTotal === 0) {
                                        continue;
                                    }
                                    ?>
                                    <tr>
                                        <td style="border-left:1px solid #eee;"><?= htmlspecialchars($library, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?= displayCell($counts['volumes']) ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?= displayCell($counts['oversized']) ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?= displayCell($counts['boxes']) ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?= displayCell($counts['clamshells']) ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?= displayCell($counts['flat_boxes']) ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?= displayCell($counts['long_boxes']) ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?= displayCell($counts['shelf']) ?></td>
                                        <td class="center" style="border-left:1px solid #eee;"><?= displayCell($counts['deaccession']) ?></td>
                                    </tr>
                                <?php endforeach; ?>

                                <tr>
                                    <td style="border-top:2px solid #ccc; border-bottom:2px solid #ccc;" class="green black-text lighten-4">Total Count:</td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?= number_format($totals['volumes']) ?></td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?= number_format($totals['oversized']) ?></td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?= number_format($totals['boxes']) ?></td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?= number_format($totals['clamshells']) ?></td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?= number_format($totals['flat_boxes']) ?></td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?= number_format($totals['long_boxes']) ?></td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?= number_format($totals['shelf']) ?></td>
                                    <td class="green black-text lighten-4 center" style="border-left:1px solid #eee; border-top:2px solid #ccc; border-bottom:2px solid #ccc;"><?= number_format($totals['deaccession']) ?></td>
                                </tr>

                                <tr>
                                    <th class="green darken-1 white-text">Value:</th>
                                    <th class="green darken-1 white-text center">$<?= number_format($values['volumes'], 2) ?></th>
                                    <th class="green darken-1 white-text center">$<?= number_format($values['oversized'], 2) ?></th>
                                    <th class="green darken-1 white-text center">$<?= number_format($values['boxes'], 2) ?></th>
                                    <th class="green darken-1 white-text center">$<?= number_format($values['clamshells'], 2) ?></th>
                                    <th class="green darken-1 white-text center">$<?= number_format($values['flat_boxes'], 2) ?></th>
                                    <th class="green darken-1 white-text center">$<?= number_format($values['long_boxes'], 2) ?></th>
                                    <th class="green darken-1 white-text center">$<?= number_format($values['shelf'], 2) ?></th>
                                    <th class="green darken-1 white-text center">$<?= number_format($values['deaccession'], 2) ?></th>
                                </tr>
                            </tbody>
                        </table>

                        <br><br>
                        <h4 class="center">Total: $<?= number_format($grandTotal, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
mysqli_close($conn);
include 'footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.collapsible');
    M.Collapsible.init(elems);
});

$(document).ready(function() {
    $('.collapsible').collapsible();
    $('select').formSelect();
});
</script>
</body>
</html>