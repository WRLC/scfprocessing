<?php include 'include/access.php';
include 'include/admin_access.php';

include 'include/apikey.php';
// URL to fetch the XML data
$api_key = $_ENV['SCF_REFILE'] ?? getenv('SCF_REFILE');
$url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=%2Fshared%2FShared%20storage%20institution%2FReports%2FAPI%2FAPI%20-%20Due%20Date%20Test&limit=1000&col_names=false&apikey=" . $api_key;

// Fetch the XML data
$xmlData = file_get_contents($url);

if ($xmlData === false) {
    die("Error: Unable to fetch XML data.");
}

// Load XML into a SimpleXMLElement object
$xml = simplexml_load_string($xmlData);

if ($xml === false) {
    die("Error: Unable to parse XML data.");
}

// Extract the rows
$rows = $xml->QueryResult->ResultXml->rowset->Row;

// Bootstrap Table Header
echo '
<!DOCTYPE html>
<html lang="en">
<head>';

include 'include/nav.php';

echo '<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <titleMonthly Refile Stats</title>
     <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Refile stats - Monthly</h2>
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Month</th>
                    <th>Year</th>
                    <th>Events</th>
                    <th>Items</th>
                </tr>
            </thead>
            <tbody>';

// Loop through each row and extract the required columns
foreach ($rows as $row) {
    $col1 = isset($row->Column1) ? htmlspecialchars($row->Column1) : 'N/A';
    $col2 = isset($row->Column2) ? htmlspecialchars($row->Column2) : 'N/A';
    $col3 = isset($row->Column3) ? htmlspecialchars($row->Column3) : 'N/A';
    $col4 = isset($row->Column4) ? htmlspecialchars($row->Column4) : 'N/A';

    echo "<tr>
            <td>{$col1}</td>
            <td>{$col2}</td>
            <td>{$col3}</td>
            <td>{$col4}</td>
          </tr>";
}

echo '        </tbody>
        </table>
        <a class="btn btn-info" href="refile_date.php">View by Date Range</a>
    </div>
</body>
</html>';

include 'include/footer.php';
