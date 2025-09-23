<?php
include 'include/access.php';
include 'include/admin_access.php';

// File path for the NDJSON file
$file = __DIR__ . '/refile.ndjson';

// Load the existing data from NDJSON file
$data = [];
if (file_exists($file) && filesize($file) > 0) {
    $handle = fopen($file, "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line === '') continue;
            $item = json_decode($line, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data[] = $item;
            }
        }
        fclose($handle);
    } else {
        die('Error: Unable to open the NDJSON file.');
    }
}

// Initialize date components
$currentDate    = new DateTime();
$currentDay     = $currentDate->format('Y-m-d');
$currentWeek    = $currentDate->format('W');
$currentMonth   = $currentDate->format('m');
$currentYear    = $currentDate->format('Y');

// Calculate the date for yesterday
$yesterdayDate  = new DateTime('yesterday');
$yesterdayDay   = $yesterdayDate->format('Y-m-d');

// Function to export data to CSV
function exportToCSV($filteredData, $filename)
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'Name', 'Barcode', 'Tray Barcode', 'Status', 'Step']);

    foreach ($filteredData as $row) {
        fputcsv($output, [
            $row['date']         ?? '',
            $row['name']         ?? '',
            $row['barcode']      ?? '',
            $row['tray barcode'] ?? '',
            $row['status']       ?? '',
            $row['step']         ?? '',
        ]);
    }

    fclose($output);
    exit();
}

// Handle CSV export request
if (isset($_GET['export'])) {
    $timeFrame    = $_GET['export'];
    $filteredData = [];

    foreach ($data as $entry) {
        $entryDate  = new DateTime($entry['date']);
        $entryDay   = $entryDate->format('Y-m-d');
        $entryWeek  = $entryDate->format('W');
        $entryMonth = $entryDate->format('m');
        $entryYear  = $entryDate->format('Y');

        if (
            ($timeFrame == 'today'     && $entryDay   == $currentDay)    ||
            ($timeFrame == 'yesterday' && $entryDay   == $yesterdayDay)  ||
            ($timeFrame == 'week'      && $entryWeek  == $currentWeek
                                      && $entryYear == $currentYear)  ||
            ($timeFrame == 'month'     && $entryMonth == $currentMonth
                                      && $entryYear == $currentYear)  ||
            ($timeFrame == 'year'      && $entryYear  == $currentYear)   ||
            ($timeFrame == 'total')
        ) {
            $filteredData[] = $entry;
        }
    }

    exportToCSV($filteredData, $timeFrame . '_data.csv');
}

// Initialize counters
$totalStep1    = $totalStep2 = 0;
$todayStep1    = $todayStep2 = 0;
$yesterdayStep1= $yesterdayStep2 = 0;
$thisWeekStep1 = $thisWeekStep2 = 0;
$thisMonthStep1= $thisMonthStep2 = 0;
$thisYearStep1 = $thisYearStep2 = 0;

foreach ($data as $entry) {
    $entryDate  = new DateTime($entry['date']);
    $entryDay   = $entryDate->format('Y-m-d');
    $entryWeek  = $entryDate->format('W');
    $entryMonth = $entryDate->format('m');
    $entryYear  = $entryDate->format('Y');

    if ($entry['step'] == 1) {
        $totalStep1++;
        if ($entryDay    == $currentDay)   $todayStep1++;
        if ($entryDay    == $yesterdayDay) $yesterdayStep1++;
        if ($entryWeek   == $currentWeek
         && $entryYear   == $currentYear)   $thisWeekStep1++;
        if ($entryMonth  == $currentMonth
         && $entryYear   == $currentYear)   $thisMonthStep1++;
        if ($entryYear   == $currentYear)   $thisYearStep1++;
    }
    elseif ($entry['step'] == 2) {
        $totalStep2++;
        if ($entryDay    == $currentDay)   $todayStep2++;
        if ($entryDay    == $yesterdayDay) $yesterdayStep2++;
        if ($entryWeek   == $currentWeek
         && $entryYear   == $currentYear)   $thisWeekStep2++;
        if ($entryMonth  == $currentMonth
         && $entryYear   == $currentYear)   $thisMonthStep2++;
        if ($entryYear   == $currentYear)   $thisYearStep2++;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refile Summary</title>
    <?php include 'include/refresh.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'include/nav.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4">Refile Summary:</h2>
    <h6 class="font-italic">(Step 1 / Step 2)</h6>
    <div class="row">
        <?php
        $cards = [
            ['Today',     $todayStep1,     $todayStep2,     'success',   'today'],
            ['Yesterday', $yesterdayStep1, $yesterdayStep2, 'warning',   'yesterday'],
            ['This Week', $thisWeekStep1,  $thisWeekStep2,  'info',      'week'],
            ['This Month',$thisMonthStep1, $thisMonthStep2, 'primary',   'month'],
            ['This Year', $thisYearStep1,  $thisYearStep2,  'secondary', 'year'],
            ['Total',     $totalStep1,     $totalStep2,     'danger',    'total'],
        ];

        foreach ($cards as [$title, $step1, $step2, $class, $export]) {
            echo '<div class="col-md-4 mb-4"><div class="card text-center">';
            echo '<div class="card-body alert-' . $class . '"><h5 class="card-title">' . $title . '</h5>';
            echo '<p class="card-text">' . $step1 . ' / ' . $step2 . '</p></div>';
            echo '<div class="card-footer"><a href="?export=' . $export . '" class="btn btn-sm btn-secondary">Download ' . $title . ' CSV</a></div>';
            echo '</div></div>';
        }
        ?>

<a class="btn btn-info mb-3 mr-3" href="refile_summary2.php">Show All Records</a> <a class="btn btn-warning mb-3" href="refile.ndjson">View NDJSON</a>
    </div>
</div>

<?php
// NDJSON Delete function
function deleteEntry($timestamp, $identifier)
{
    $file = __DIR__ . '/refile.ndjson';
    if (!file_exists($file)) return;

    $tempFile = $file . '.tmp';
    $in = fopen($file, 'r');
    $out = fopen($tempFile, 'w');
    if (!$in || !$out) {
        die('Error opening file(s) for deletion.');
    }

    while (($line = fgets($in)) !== false) {
        $item = json_decode($line, true);
        if (json_last_error() !== JSON_ERROR_NONE) continue;
        if ($item['date'] === $timestamp && $item['barcode'] === $identifier) {
            continue;
        }
        fwrite($out, json_encode($item, JSON_UNESCAPED_SLASHES) . "\n");
    }

    fclose($in);
    fclose($out);
    rename($tempFile, $file);
}

// Check if delete was requested
if (isset($_POST['delete'])) {
    $timestamp  = $_POST['timestamp'];
    $identifier = $_POST['identifier'];
    deleteEntry($timestamp, $identifier);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>


<?php include 'include/footer.php'; ?>
</body>
</html>