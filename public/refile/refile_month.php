<?php
include 'include/access.php';
include 'include/admin_access.php';
include 'include/apikey.php';

// Fetch the XML data
$url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=%2Fshared%2FShared%20storage%20institution%2FReports%2FAPI%2FAPI%20-%20Due%20Date%20Test&limit=1000&col_names=false&apikey=l8xxae3d148d61bf44adbd5068269c2e013e";
$xmlData = file_get_contents($url);
if ($xmlData === false) die("Error: Unable to fetch XML data.");

$xml = simplexml_load_string($xmlData);
if ($xml === false) die("Error: Unable to parse XML data.");

// Extract rows
$rows = $xml->QueryResult->ResultXml->rowset->Row;

// Bootstrap header
echo '
<!DOCTYPE html>
<html lang="en">
<head>';
include 'include/nav.php';
echo '<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Monthly Refile Stats</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  /* Optional: make the month header row look clickable */
  .month-toggle { cursor: pointer; }
</style>
</head>
<body>
<div class="container mt-5">
<h2>Refile Stats - Monthly</h2>';

// Helper: robust-ish month/year parsing to a sortable timestamp
function monthYearToTimestamp(string $label): int {
    $label = trim($label);

    // Common trick: try parsing with a day in front (helps "January 2026", "Jan 2026", etc.)
    $ts = strtotime("1 " . $label);
    if ($ts !== false) return $ts;

    // Try raw label (might already include a day)
    $ts = strtotime($label);
    if ($ts !== false) return $ts;

    // Fallback: try MM/YYYY or MM-YYYY
    if (preg_match('/^\s*(\d{1,2})[\/\-](\d{4})\s*$/', $label, $m)) {
        $month = (int)$m[1];
        $year  = (int)$m[2];
        if ($month >= 1 && $month <= 12) {
            return mktime(0, 0, 0, $month, 1, $year);
        }
    }

    // Last resort: shove unknown formats to the bottom
    return 0;
}

// Step 1: Group data by month-year (Column4) and sum Column6
$dataByMonth = [];
$grandTotal = 0;

foreach ($rows as $row) {
    $col4 = isset($row->Column4) ? htmlspecialchars((string)$row->Column4) : 'N/A';
    $col1 = isset($row->Column1) ? htmlspecialchars((string)$row->Column1) : 'N/A';
    $col6 = isset($row->Column6) ? floatval($row->Column6) : 0;

    if (!isset($dataByMonth[$col4])) {
        $dataByMonth[$col4] = [
            'ts' => monthYearToTimestamp($col4),
            'rows' => [],
            'subtotal' => 0
        ];
    }

    $dataByMonth[$col4]['rows'][] = [
        'month' => $col4,
        'university' => $col1,
        'total' => $col6
    ];

    $dataByMonth[$col4]['subtotal'] += $col6;
    $grandTotal += $col6;
}

// Sort months descending by timestamp (most recent first)
uasort($dataByMonth, function($a, $b) {
    return ($b['ts'] <=> $a['ts']);
});

// Display grand total above the table
echo "<h3 class='text-primary mb-4'>Grand Total: " . number_format($grandTotal, 0) . "</h3>";

// Step 2: Build the table
echo '<table class="table table-striped align-middle">
<thead class="thead-dark">
<tr>
<th style="width: 25%;">Month - Year</th>
<th>Owning University</th>
<th style="width: 15%;">Totals</th>
</tr>
</thead>';

// Step 3: Collapsible month groups (Bootstrap collapse)
// Most recent month open by default
$monthIndex = 0;

foreach ($dataByMonth as $month => $info) {
    $collapseId = "monthCollapse_" . $monthIndex;
    $isFirst = ($monthIndex === 0);
    $showClass = $isFirst ? "show" : "";

    // Month header row (toggle)
    echo '
    <tbody>
      <tr class="table-primary month-toggle" data-bs-toggle="collapse" data-bs-target="#' . $collapseId . '" aria-expanded="' . ($isFirst ? "true" : "false") . '" aria-controls="' . $collapseId . '">
        <td colspan="3">
          <div class="d-flex justify-content-between align-items-center">
            <span class="fw-bold">' . htmlspecialchars($month) . '</span>
            <span class="small text-muted">Click to expand/collapse</span>
          </div>
        </td>
      </tr>
    </tbody>';

    // Collapsible section for that month’s detail rows + subtotal
    echo '<tbody id="' . $collapseId . '" class="collapse ' . $showClass . '">';

    foreach ($info['rows'] as $r) {
        echo "<tr>
                <td></td>
                <td>{$r['university']}</td>
                <td>" . number_format($r['total'], 0) . "</td>
              </tr>";
    }

    // Monthly subtotal row
    echo "<tr class='table-secondary fw-bold'>
            <td colspan='2' class='text-end'>Total for " . htmlspecialchars($month) . "</td>
            <td>" . number_format($info['subtotal'], 0) . "</td>
          </tr>";

    echo '</tbody>';

    $monthIndex++;
}

echo '</table>
<a class="btn btn-info" href="refile_date.php">View by Date Range</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';

include 'include/footer.php';
?>