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
</head>
<body>
<div class="container mt-5">
<h2>Refile Stats - Monthly</h2>';

// Step 1: Group data by $col4 and sum $col6
$dataByMonth = [];
$grandTotal = 0; // overall total tracker

foreach ($rows as $row) {
    $col4 = isset($row->Column4) ? htmlspecialchars($row->Column4) : 'N/A';
    $col1 = isset($row->Column1) ? htmlspecialchars($row->Column1) : 'N/A';
    $col6 = isset($row->Column6) ? floatval($row->Column6) : 0;

    // Store data under month-year
    $dataByMonth[$col4]['rows'][] = [
        'month' => $col4,
        'university' => $col1,
        'total' => $col6
    ];

    // Add to monthly subtotal
    if (!isset($dataByMonth[$col4]['subtotal'])) {
        $dataByMonth[$col4]['subtotal'] = 0;
    }
    $dataByMonth[$col4]['subtotal'] += $col6;

    // Add to grand total
    $grandTotal += $col6;
}

// Display grand total above the table
echo "<h3 class='text-primary mb-4'>Grand Total: " . number_format($grandTotal, 0) . "</h3>";

// Step 2: Build the table
echo '<table class="table table-striped">
<thead class="thead-dark">
<tr>
<th>Month - Year</th>
<th>Owning University</th>
<th>Totals</th>
</tr>
</thead>
<tbody>';

// Step 3: Display grouped data with subtotal rows
foreach ($dataByMonth as $month => $info) {
    foreach ($info['rows'] as $r) {
        echo "<tr>
                <td>{$r['month']}</td>
                <td>{$r['university']}</td>
                <td>" . number_format($r['total'], 0) . "</td>
              </tr>";
    }

    // Monthly subtotal row
    echo "<tr class='table-secondary fw-bold'>
            <td colspan='2' class='text-end'>Total for {$month}</td>
            <td>" . number_format($info['subtotal'], 0) . "</td>
          </tr>";
}

echo '</tbody>
</table>
<a class="btn btn-info" href="refile_date.php">View by Date Range</a>
</div>
</body>
</html>';

include 'include/footer.php';
?>