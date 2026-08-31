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
include 'refile/include/apikey.php';
include 'refile/include/refile_data_file.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not available.');
}

function h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function n($value): string
{
    return number_format((float)$value, 0);
}

function money(float $value): string
{
    return '$' . number_format($value, 2);
}

function scalarQuery(mysqli $conn, string $sql): int
{
    $result = mysqli_query($conn, $sql);
    if (!$result instanceof mysqli_result) {
        return 0;
    }

    $row = mysqli_fetch_row($result);
    mysqli_free_result($result);

    return (int)($row[0] ?? 0);
}

function billingTotals(mysqli $conn, string $begin, string $end): array
{
    $rates = [
        'volumes' => 0.75,
        'oversized' => 0.75,
        'boxes' => 2.65,
        'clamshells' => 1.50,
        'flat_boxes' => 2.65,
        'long_boxes' => 2.65,
        'shelf' => 2.00,
        'deaccessioned' => 1.70,
    ];

    $totals = array_fill_keys(array_keys($rates), 0);

    $sql = "
        SELECT
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
          AND cctimestamp BETWEEN ? AND ?
    ";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return ['counts' => $totals, 'items' => 0, 'value' => 0.0];
    }

    mysqli_stmt_bind_param($stmt, 'ss', $begin, $end);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result instanceof mysqli_result && ($row = mysqli_fetch_assoc($result))) {
        foreach ($totals as $key => $value) {
            $totals[$key] = (int)$row[$key];
        }
        mysqli_free_result($result);
    }

    mysqli_stmt_close($stmt);

    $items = array_sum($totals);
    $value = 0.0;
    foreach ($totals as $key => $count) {
        $value += $count * $rates[$key];
    }

    return ['counts' => $totals, 'items' => $items, 'value' => $value];
}

function readRefileNdjson(): array
{
    $file = refileEnsurePersistentNdjson();
    $data = [];

    if (!file_exists($file) || filesize($file) === 0) {
        return $data;
    }

    $handle = fopen($file, 'r');
    if (!$handle) {
        return $data;
    }

    while (($line = fgets($handle)) !== false) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $entry = json_decode($line, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($entry)) {
            $data[] = $entry;
        }
    }

    fclose($handle);

    return $data;
}

function refileSummaryCounts(array $data): array
{
    $currentDate = new DateTime();
    $currentDay = $currentDate->format('Y-m-d');
    $currentWeek = $currentDate->format('W');
    $currentMonth = $currentDate->format('m');
    $currentYear = $currentDate->format('Y');
    $yesterdayDay = (new DateTime('yesterday'))->format('Y-m-d');

    $counts = [
        'Today' => [1 => 0, 2 => 0],
        'Yesterday' => [1 => 0, 2 => 0],
        'This Week' => [1 => 0, 2 => 0],
        'This Month' => [1 => 0, 2 => 0],
        'This Year' => [1 => 0, 2 => 0],
        'Total' => [1 => 0, 2 => 0],
    ];

    foreach ($data as $entry) {
        if (empty($entry['date']) || !isset($entry['step'])) {
            continue;
        }

        try {
            $entryDate = new DateTime((string)$entry['date']);
        } catch (Exception $e) {
            continue;
        }

        $step = (int)$entry['step'];
        if ($step !== 1 && $step !== 2) {
            continue;
        }

        $entryDay = $entryDate->format('Y-m-d');
        $entryWeek = $entryDate->format('W');
        $entryMonth = $entryDate->format('m');
        $entryYear = $entryDate->format('Y');

        $counts['Total'][$step]++;
        if ($entryDay === $currentDay) $counts['Today'][$step]++;
        if ($entryDay === $yesterdayDay) $counts['Yesterday'][$step]++;
        if ($entryWeek === $currentWeek && $entryYear === $currentYear) $counts['This Week'][$step]++;
        if ($entryMonth === $currentMonth && $entryYear === $currentYear) $counts['This Month'][$step]++;
        if ($entryYear === $currentYear) $counts['This Year'][$step]++;
    }

    return $counts;
}

function fetchXmlRows(string $url): ?array
{
    $rows = [];
    $baseUrl = $url;

    do {
        $xml = @simplexml_load_file($url);
        if (!$xml) {
            return null;
        }

        if (isset($xml->QueryResult->ResultXml->rowset->Row)) {
            foreach ($xml->QueryResult->ResultXml->rowset->Row as $row) {
                $rowArray = [];
                foreach ($row->attributes() as $key => $value) {
                    $rowArray[$key] = (string)$value;
                }
                foreach ($row->children() as $key => $value) {
                    $rowArray[$key] = (string)$value;
                }
                $rows[] = $rowArray;
            }
        }

        if ((string)$xml->QueryResult->IsFinished === 'false') {
            $token = (string)$xml->QueryResult->ResumptionToken;
            $url = $baseUrl . '&token=' . urlencode($token);
        } else {
            $url = '';
        }
    } while ($url !== '');

    return $rows;
}

function monthYearToTimestamp(string $label): int
{
    $ts = strtotime('1 ' . trim($label));
    if ($ts !== false) return $ts;

    $ts = strtotime(trim($label));
    if ($ts !== false) return $ts;

    if (preg_match('/^\s*(\d{1,2})[\/\-](\d{4})\s*$/', $label, $matches)) {
        return mktime(0, 0, 0, (int)$matches[1], 1, (int)$matches[2]);
    }

    return 0;
}

function fetchRefileErrorsWithoutNotes(): ?array
{
    $jsonKey = $_ENV['GOOGLE_SHEET'] ?? getenv('GOOGLE_SHEET');
    if (!$jsonKey) {
        return null;
    }

    $url = 'https://sheets.googleapis.com/v4/spreadsheets/1bieQ2wsjb1ptVt49QEea94mmUVcoDbnONzJi4xXXOo4/values/Sheet1?alt=json&key=' . urlencode((string)$jsonKey);
    $json = @file_get_contents($url);
    if ($json === false) {
        return null;
    }

    $data = json_decode($json, true);
    if (!isset($data['values']) || !is_array($data['values']) || count($data['values']) === 0) {
        return [];
    }

    $headers = array_map('trim', $data['values'][0]);
    $notesIndex = null;
    foreach ($headers as $index => $header) {
        if (strtolower((string)$header) === 'notes') {
            $notesIndex = $index;
            break;
        }
    }

    if ($notesIndex === null) {
        return [];
    }

    $rows = [];
    for ($i = 1; $i < count($data['values']); $i++) {
        $row = $data['values'][$i];
        $notes = trim((string)($row[$notesIndex] ?? ''));
        if ($notes !== '') {
            continue;
        }

        $rows[] = [
            'values' => $row,
            'tray' => (string)($row[2] ?? ''),
            'barcode' => (string)($row[3] ?? ''),
        ];
    }

    return ['headers' => $headers, 'rows' => $rows];
}

function fetchUsageSummaryByCubicFeet(): ?array
{
    $jsonKey = $_ENV['GOOGLE_SHEET'] ?? getenv('GOOGLE_SHEET');
    if (!$jsonKey) {
        return null;
    }

    $spreadsheetId = '1NBWussFHVyPFBbNJf_sWofqzjlOWQjdFgt2AYGXs21k';
    $range = rawurlencode('totals!A1:G100');
    $url = 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheetId
        . '/values/' . $range
        . '?valueRenderOption=UNFORMATTED_VALUE&key=' . urlencode((string)$jsonKey);

    $json = @file_get_contents($url);
    if ($json === false) {
        return null;
    }

    $data = json_decode($json, true);
    if (!isset($data['values']) || !is_array($data['values']) || count($data['values']) < 2) {
        return null;
    }

    $rows = [];
    $total = null;

    for ($i = 1; $i < count($data['values']); $i++) {
        $row = $data['values'][$i];
        $university = trim((string)($row[0] ?? ''));
        if ($university === '') {
            continue;
        }

        $entry = [
            'university' => $university,
            'shelves' => (float)($row[1] ?? 0),
            'used' => (float)($row[2] ?? 0),
            'remaining' => is_numeric($row[3] ?? null) ? (float)$row[3] : null,
            'remaining_note' => !is_numeric($row[3] ?? null) ? (string)($row[3] ?? '') : '',
            'percent_used' => (float)($row[4] ?? 0),
            'reserved' => (float)($row[5] ?? 0),
            'total_cu_ft' => (float)($row[6] ?? 0),
        ];

        if (strtoupper($university) === 'TOTAL') {
            $total = $entry;
        } else {
            $rows[] = $entry;
        }
    }

    usort($rows, function (array $a, array $b): int {
        return $b['used'] <=> $a['used'];
    });

    return ['rows' => $rows, 'total' => $total];
}

$today = new DateTimeImmutable('today');
$previousMonthStartDate = $today->modify('first day of previous month');

$billingMonths = [];
for ($i = 0; $i < 6; $i++) {
    $monthDate = $today->modify('first day of this month')->modify("-{$i} months");
    $monthStart = $monthDate->format('Y-m-d 00:00:00');
    $monthEnd = $monthDate->modify('last day of this month')->format('Y-m-d 23:59:59');

    $billingMonths[] = [
        'label' => $monthDate->format('F Y'),
        'totals' => billingTotals($conn, $monthStart, $monthEnd),
        'url' => 'billing.php?begin=' . urlencode($monthDate->format('M 01, Y'))
            . '&end=' . urlencode($monthDate->modify('last day of this month')->format('M d, Y')),
    ];
}

$crosscheckCount = scalarQuery($conn, "SELECT COUNT(*) FROM ProcessingAll WHERE ccname IS NULL OR ccname = ''");
$processedSevenDays = scalarQuery($conn, "SELECT COUNT(*) FROM ProcessingAll WHERE ptimestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)");

$refileSummary = refileSummaryCounts(readRefileNdjson());
$refileErrors = fetchRefileErrorsWithoutNotes();
$usageSummary = fetchUsageSummaryByCubicFeet();

$holdShelfRows = null;
if (!empty($api_key)) {
    $holdShelfUrl = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports'
        . '?path=%2Fshared%2FShared+storage+institution%2FReports%2FAPI%2FAPI+Tray+Check+-+SCF+Hold+Shelf'
        . '&limit=1000&col_names=true&apikey=' . urlencode((string)$api_key);
    $holdShelfRows = fetchXmlRows($holdShelfUrl);
}

$almaStatsRows = null;
if (!empty($api_key)) {
    $almaStatsUrl = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports'
        . '?path=%2Fshared%2FShared%20storage%20institution%2FReports%2FAPI%2FAPI%20-%20Due%20Date%20Test'
        . '&limit=1000&col_names=false&apikey=' . urlencode((string)$api_key);
    $almaStatsRows = fetchXmlRows($almaStatsUrl);
}

$currentMonthKey = $today->format('F Y');
$previousMonthKey = $previousMonthStartDate->format('F Y');
$almaByUniversity = [];
$almaGrandTotal = 0;

if (is_array($almaStatsRows)) {
    foreach ($almaStatsRows as $row) {
        $university = trim((string)($row['Column1'] ?? 'Unknown'));
        $monthLabel = trim((string)($row['Column4'] ?? ''));
        $count = (float)($row['Column6'] ?? 0);
        $monthKey = monthYearToTimestamp($monthLabel) > 0 ? date('F Y', monthYearToTimestamp($monthLabel)) : $monthLabel;

        if (!isset($almaByUniversity[$university])) {
            $almaByUniversity[$university] = ['overall' => 0, 'current' => 0, 'previous' => 0];
        }

        $almaByUniversity[$university]['overall'] += $count;
        $almaGrandTotal += $count;
        if ($monthKey === $currentMonthKey) {
            $almaByUniversity[$university]['current'] += $count;
        }
        if ($monthKey === $previousMonthKey) {
            $almaByUniversity[$university]['previous'] += $count;
        }
    }
    ksort($almaByUniversity, SORT_NATURAL | SORT_FLAG_CASE);
}

$usageChartRows = is_array($usageSummary) ? $usageSummary['rows'] : [];
$usageChartData = [
    'labels' => array_map(fn($row) => $row['university'], $usageChartRows),
    'total' => array_map(fn($row) => (float)$row['total_cu_ft'], $usageChartRows),
    'used' => array_map(fn($row) => (float)$row['used'], $usageChartRows),
    'reserved' => array_map(fn($row) => (float)$row['reserved'], $usageChartRows),
    'remaining' => array_map(fn($row) => max(0, (float)($row['remaining'] ?? 0)), $usageChartRows),
    'percent' => array_map(fn($row) => round((float)$row['percent_used'] * 100, 1), $usageChartRows),
];
?>

<style>
.dashboard-shell {
    max-width: 1400px;
    margin: 0 auto;
    padding: 34px 20px 56px;
}

.dashboard-hero {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
}

.dashboard-hero h1 {
    margin: 0 0 6px;
    color: #1f2937;
    font-size: 2.55rem;
    font-weight: 800;
}

.dashboard-hero p {
    margin: 0;
    color: #607d8b;
    font-size: 1.06rem;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 18px;
}

.dash-card {
    background: #fff;
    border: 1px solid #e5edf2;
    border-radius: 8px;
    box-shadow: 0 14px 35px rgba(31, 41, 55, 0.07);
    overflow: hidden;
}

.dash-card .card-content {
    padding: 22px;
}

.span-3 { grid-column: span 3; }
.span-4 { grid-column: span 4; }
.span-6 { grid-column: span 6; }
.span-8 { grid-column: span 8; }
.span-12 { grid-column: span 12; }

.metric-label {
    color: #607d8b;
    font-size: .82rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
}

.metric-value {
    margin: 10px 0 8px;
    color: #111827;
    font-size: 2.8rem;
    font-weight: 800;
    line-height: 1;
}

.metric-sub {
    color: #6b7280;
    min-height: 22px;
}

.card-topline {
    align-items: center;
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
}

.card-topline h2 {
    color: #263238;
    font-size: 1.25rem;
    font-weight: 800;
    margin: 0;
}

.pill-link {
    align-items: center;
    border: 1px solid #cfd8dc;
    border-radius: 999px;
    color: #1565c0;
    display: inline-flex;
    font-size: .9rem;
    font-weight: 700;
    gap: 5px;
    padding: 7px 12px;
}

.pill-link:hover {
    background: #e3f2fd;
}

.mini-table {
    border-collapse: collapse;
    width: 100%;
}

.mini-table th {
    color: #607d8b;
    font-size: .78rem;
    font-weight: 800;
    padding: 10px 8px;
    text-transform: uppercase;
}

.mini-table td {
    border-top: 1px solid #edf2f7;
    color: #263238;
    padding: 12px 8px;
}

.mini-table td:last-child,
.mini-table th:last-child {
    text-align: right;
}

.summary-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.summary-tile {
    background: #f8fafc;
    border: 1px solid #edf2f7;
    border-radius: 8px;
    padding: 14px;
}

.summary-tile strong {
    color: #111827;
    display: block;
    font-size: 1.45rem;
    margin-top: 6px;
}

.chart-wrap {
    height: 230px;
    margin-top: 16px;
    position: relative;
}

.chart-wrap.compact {
    height: 150px;
}

.chart-wrap.tall {
    height: 330px;
}

.usage-metrics {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}

.usage-stat {
    background: #f8fafc;
    border: 1px solid #edf2f7;
    border-radius: 8px;
    padding: 14px;
}

.usage-stat span {
    color: #607d8b;
    display: block;
    font-size: .78rem;
    font-weight: 800;
    text-transform: uppercase;
}

.usage-stat strong {
    color: #111827;
    display: block;
    font-size: 1.55rem;
    margin-top: 4px;
}

.error-list {
    margin: 0;
}

.error-list li {
    border-top: 1px solid #edf2f7;
    padding: 12px 0;
}

.error-list li:first-child {
    border-top: 0;
}

.muted {
    color: #78909c;
}

.status-note {
    background: #fff8e1;
    border-radius: 8px;
    color: #7a5200;
    padding: 12px 14px;
}

@media only screen and (max-width: 1000px) {
    .span-3,
    .span-4,
    .span-6,
    .span-8 {
        grid-column: span 12;
    }

    .dashboard-hero {
        align-items: flex-start;
        flex-direction: column;
    }

    .summary-row {
        grid-template-columns: 1fr;
    }

    .usage-metrics {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

<main class="dashboard-shell">
    <section class="dashboard-hero">
        <div>
            <h1>SCF Processing Dashboard</h1>
            <p>At-a-glance processing, billing, crosscheck, and refile activity.</p>
        </div>
        <span class="chip blue-grey lighten-4 blue-grey-text text-darken-3">Updated <?php echo h(date('M j, Y g:ia')); ?></span>
    </section>

    <section class="dashboard-grid">
        <article class="dash-card span-3">
            <div class="card-content">
                <div class="metric-label">Unprocessed Crosscheck</div>
                <div class="metric-value"><?php echo n($crosscheckCount); ?></div>
                <div class="metric-sub">Items waiting for crosscheck</div>
                <a class="pill-link" href="crosscheck.php"><i class="material-icons tiny">open_in_new</i>Open crosscheck</a>
            </div>
        </article>

        <article class="dash-card span-3">
            <div class="card-content">
                <div class="metric-label">Processed Last 7 Days</div>
                <div class="metric-value"><?php echo n($processedSevenDays); ?></div>
                <div class="metric-sub">Items from the processing list</div>
                <a class="pill-link" href="list.php?order=ptimestamp&sort=DESC&date=WEEK"><i class="material-icons tiny">open_in_new</i>Open list</a>
            </div>
        </article>

        <article class="dash-card span-3">
            <div class="card-content">
                <div class="metric-label">Hold Shelf</div>
                <div class="metric-value"><?php echo is_array($holdShelfRows) ? n(count($holdShelfRows)) : '-'; ?></div>
                <div class="metric-sub"><?php echo is_array($holdShelfRows) ? 'Items currently on hold shelf' : 'Alma feed unavailable'; ?></div>
                <a class="pill-link" href="refile/hold_shelf.php"><i class="material-icons tiny">open_in_new</i>Open hold shelf</a>
            </div>
        </article>

        <article class="dash-card span-3">
            <div class="card-content">
                <div class="metric-label">Alma Refile Total</div>
                <div class="metric-value"><?php echo is_array($almaStatsRows) ? n($almaGrandTotal) : '-'; ?></div>
                <div class="metric-sub"><?php echo is_array($almaStatsRows) ? 'Overall count from Alma stats' : 'Alma feed unavailable'; ?></div>
                <a class="pill-link" href="refile/refile_month.php"><i class="material-icons tiny">open_in_new</i>Open Alma stats</a>
            </div>
        </article>

        <article class="dash-card span-12">
            <div class="card-content">
                <div class="card-topline">
                    <h2>SCF Total Usage Summary by Cubic Feet</h2>
                    <a class="pill-link" target="_blank" href="https://docs.google.com/spreadsheets/d/1NBWussFHVyPFBbNJf_sWofqzjlOWQjdFgt2AYGXs21k/edit?gid=1128396276#gid=1128396276"><i class="material-icons tiny">open_in_new</i>Open sheet</a>
                </div>
                <?php if (!is_array($usageSummary) || !is_array($usageSummary['total'])): ?>
                    <div class="status-note">Storage usage Sheet feed unavailable.</div>
                <?php else: ?>
                    <div class="usage-metrics">
                        <div class="usage-stat">
                            <span>Total Cu Ft</span>
                            <strong><?php echo n($usageSummary['total']['total_cu_ft']); ?></strong>
                        </div>
                        <div class="usage-stat">
                            <span>Cu Ft Used</span>
                            <strong><?php echo n($usageSummary['total']['used']); ?></strong>
                        </div>
                        <div class="usage-stat">
                            <span>Cu Ft Reserved</span>
                            <strong><?php echo n($usageSummary['total']['reserved']); ?></strong>
                        </div>
                        <div class="usage-stat">
                            <span>Percent Used</span>
                            <strong><?php echo number_format((float)$usageSummary['total']['percent_used'] * 100, 1); ?>%</strong>
                        </div>
                    </div>
                    <div class="chart-wrap tall"><canvas id="usageCubicFeetChart"></canvas></div>
                    <div class="chart-wrap"><canvas id="usagePercentChart"></canvas></div>
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th>University</th>
                                <th>Total Cu Ft</th>
                                <th>Cu Ft Used</th>
                                <th>Cu Ft Reserved</th>
                                <th>Cu Ft Remaining</th>
                                <th>% Used</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usageSummary['rows'] as $row): ?>
                                <tr>
                                    <td><?php echo h($row['university']); ?></td>
                                    <td><?php echo n($row['total_cu_ft']); ?></td>
                                    <td><?php echo n($row['used']); ?></td>
                                    <td><?php echo n($row['reserved']); ?></td>
                                    <td>
                                        <?php echo $row['remaining'] === null ? h($row['remaining_note']) : n($row['remaining']); ?>
                                    </td>
                                    <td><?php echo number_format((float)$row['percent_used'] * 100, 1); ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </article>

        <article class="dash-card span-6">
            <div class="card-content">
                <div class="card-topline">
                    <h2>Billing Counts</h2>
                    <a class="pill-link" href="<?php echo h($billingMonths[0]['url']); ?>"><i class="material-icons tiny">open_in_new</i>Open current month</a>
                </div>
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Items</th>
                            <th>Estimated Billing</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($billingMonths as $billingMonth): ?>
                            <tr>
                                <td><?php echo h($billingMonth['label']); ?></td>
                                <td><?php echo n($billingMonth['totals']['items']); ?></td>
                                <td><?php echo money((float)$billingMonth['totals']['value']); ?></td>
                                <td><a href="<?php echo h($billingMonth['url']); ?>">Open</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="dash-card span-6">
            <div class="card-content">
                <div class="card-topline">
                    <h2>Refile Summary</h2>
                    <a class="pill-link" href="refile/refile_summary.php"><i class="material-icons tiny">open_in_new</i>Open summary</a>
                </div>
                <div class="summary-row">
                    <?php foreach ($refileSummary as $label => $steps): ?>
                        <div class="summary-tile">
                            <span class="muted"><?php echo h($label); ?></span>
                            <strong><?php echo n($steps[1]); ?> / <?php echo n($steps[2]); ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </article>

        <article class="dash-card span-4">
            <div class="card-content">
                <div class="card-topline">
                    <h2>Refile Errors Missing Notes</h2>
                    <a class="pill-link" href="refile/refile_errors.php"><i class="material-icons tiny">open_in_new</i>Open errors</a>
                </div>
                <?php if ($refileErrors === null): ?>
                    <div class="status-note">Google Sheet feed unavailable.</div>
                <?php else: ?>
                    <div class="metric-value" style="font-size:2.15rem;"><?php echo n(count($refileErrors['rows'])); ?></div>
                    <ul class="error-list">
                        <?php foreach (array_slice($refileErrors['rows'], 0, 6) as $errorRow): ?>
                            <li>
                                <strong><?php echo h($errorRow['barcode'] ?: 'No barcode'); ?></strong><br>
                                <span class="muted">Tray <?php echo h($errorRow['tray'] ?: 'N/A'); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (count($refileErrors['rows']) > 6): ?>
                        <p class="muted">Showing 6 of <?php echo n(count($refileErrors['rows'])); ?>.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </article>

        <article class="dash-card span-8">
            <div class="card-content">
                <div class="card-topline">
                    <h2>Alma Refile Stats by Owning University</h2>
                    <a class="pill-link" href="refile/refile_month.php"><i class="material-icons tiny">open_in_new</i>Open monthly stats</a>
                </div>
                <?php if (!is_array($almaStatsRows)): ?>
                    <div class="status-note">Alma stats feed unavailable.</div>
                <?php else: ?>
                    <div class="usage-metrics" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="usage-stat">
                            <span>Total Count</span>
                            <strong><?php echo n($almaGrandTotal); ?></strong>
                        </div>
                        <div class="usage-stat">
                            <span><?php echo h($currentMonthKey); ?></span>
                            <strong><?php echo n(array_sum(array_column($almaByUniversity, 'current'))); ?></strong>
                        </div>
                        <div class="usage-stat">
                            <span><?php echo h($previousMonthKey); ?></span>
                            <strong><?php echo n(array_sum(array_column($almaByUniversity, 'previous'))); ?></strong>
                        </div>
                    </div>
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th>Owning University</th>
                                <th>Overall</th>
                                <th><?php echo h($currentMonthKey); ?></th>
                                <th><?php echo h($previousMonthKey); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($almaByUniversity as $university => $counts): ?>
                                <tr>
                                    <td><?php echo h($university); ?></td>
                                    <td><?php echo n($counts['overall']); ?></td>
                                    <td><?php echo n($counts['current']); ?></td>
                                    <td><?php echo n($counts['previous']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </article>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const chartColors = {
    blue: '#2563eb',
    teal: '#0f766e',
    amber: '#d97706',
    lightBlue: 'rgba(37, 99, 235, 0.16)',
    lightAmber: 'rgba(217, 119, 6, 0.18)',
    lightGreen: 'rgba(22, 163, 74, 0.16)'
};

Chart.defaults.font.family = 'Arial, Helvetica, sans-serif';
Chart.defaults.color = '#607d8b';

function makeChart(id, config) {
    const canvas = document.getElementById(id);
    if (!canvas || typeof Chart === 'undefined') return;
    new Chart(canvas, config);
}

const usageChartData = <?php echo json_encode($usageChartData, JSON_NUMERIC_CHECK); ?>;

makeChart('usageCubicFeetChart', {
    type: 'bar',
    data: {
        labels: usageChartData.labels,
        datasets: [
            { label: 'University Total Cu Ft', data: usageChartData.total, backgroundColor: chartColors.lightBlue, borderRadius: 5 },
            { label: 'Cu Ft Used (SCF 1,2,3)', data: usageChartData.used, backgroundColor: chartColors.blue, borderRadius: 5 },
            { label: 'Cu Ft Reserved (SCF 2,3)', data: usageChartData.reserved, backgroundColor: chartColors.amber, borderRadius: 5 },
            { label: 'Cu Ft Remaining', data: usageChartData.remaining, backgroundColor: chartColors.lightGreen, borderRadius: 5 }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { callback: value => Number(value).toLocaleString() } }
        },
        plugins: { legend: { position: 'bottom' } }
    }
});

makeChart('usagePercentChart', {
    type: 'bar',
    data: {
        labels: usageChartData.labels,
        datasets: [{
            label: 'Percent Used',
            data: usageChartData.percent,
            backgroundColor: chartColors.teal,
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: { grid: { display: false } },
            y: {
                beginAtZero: true,
                ticks: { callback: value => Number(value).toLocaleString() + '%' }
            }
        },
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>
