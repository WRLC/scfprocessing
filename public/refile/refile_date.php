<?php
include 'include/access.php';
include 'include/admin_access.php';
include 'include/apikey.php';

/**
 * Fetch all rows from an Alma Analytics report, following the resumption token
 * so the page is not limited to the first 1000 results.
 */
function fetchAllAnalyticsRows(string $initialUrl, string $apiKey): array
{
    $rows = [];
    $url = $initialUrl;

    while (!empty($url)) {
        $xmlData = file_get_contents($url);

        if ($xmlData === false) {
            throw new Exception('Failed to retrieve data from the API.');
        }

        $xml = simplexml_load_string($xmlData);

        if ($xml === false) {
            throw new Exception('Failed to parse XML data.');
        }

        if (isset($xml->QueryResult->ResultXml->rowset->Row)) {
            foreach ($xml->QueryResult->ResultXml->rowset->Row as $row) {
                $rows[] = $row;
            }
        }

        $isFinished = strtolower(trim((string) $xml->QueryResult->IsFinished));
        $resumptionToken = trim((string) $xml->QueryResult->ResumptionToken);

        if ($isFinished === 'false' && $resumptionToken !== '') {
            $url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?token=' . urlencode($resumptionToken) . '&apikey=' . urlencode($apiKey);
        } else {
            $url = '';
        }
    }

    return $rows;
}

function safeColumn(SimpleXMLElement $row, string $column, string $fallback = 'N/A'): string
{
    if (isset($row->{$column}) && trim((string) $row->{$column}) !== '') {
        return htmlspecialchars((string) $row->{$column});
    }

    return $fallback;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refile Stats by Date Range</title>
    <?php include 'include/refresh.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .university-toggle { cursor: pointer; }
        .totals-table th,
        .totals-table td { font-size: 1.1rem; }
    </style>
</head>

<body>
<?php include 'include/nav.php'; ?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-10 offset-md-1">

                <h4 class="text-center">Refile Stats by Date Range</h4>
                <form method="get" class="bg-light border p-4 text-center" action="">
                    <div class="row offset-md-2">
                        <div class="mb-3 col-md-6" style="max-width: 300px;">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>" required>
                        </div>

                        <div class="mb-3 col-md-6" style="max-width: 300px;">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>
                    <a href="refile_month_new.php" class="btn btn-danger">Clear</a>
                </form>

                <br />
                <a class="btn btn-info" href="refile_month.php">View by Month</a>

                <?php
                if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                    $start_date = htmlspecialchars($_GET['start_date']);
                    $end_date = htmlspecialchars($_GET['end_date']);
                    $selectedDateRange = $start_date . ' to ' . $end_date;

                    $apiKey = 'l8xxae3d148d61bf44adbd5068269c2e013e';
                    $reportPath = '%2Fshared%2FShared%20storage%20institution%2FReports%2FAPI%2FAPI%20-%20Due%20Date%20Test';
                    $filter = '%3Csawx:expr%20xsi:type=%22sawx:list%22%20op=%22containsAny%22%20xmlns:saw=%22com.siebel.analytics.web/report/v1.1%22%20xmlns:sawx=%22com.siebel.analytics.web/expression/v1.1%22%20xmlns:xsi=%22http://www.w3.org/2001/XMLSchema-instance%22%20xmlns:xsd=%22http://www.w3.org/2001/XMLSchema%22%20%3E%3Csawx:expr%20xsi:type=%22sawx:comparison%22%20op=%22between%22%3E%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Physical%20Items%20Historical%20Events%22.%22Event%20Start%20Date%22.%22Event%20Start%20Date%22%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22xsd:date%22%3E' . $start_date . '%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22xsd:date%22%3E' . $end_date . '%3C/sawx:expr%3E%3C/sawx:expr%3E%3C/sawx:expr%3E';
                    $url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=' . $reportPath . '&limit=1000&col_names=false&apikey=' . $apiKey . '&filter=' . $filter;

                    try {
                        $rows = fetchAllAnalyticsRows($url, $apiKey);
                        $totalRows = count($rows);
                        $dataByUniversity = [];

                        foreach ($rows as $row) {
                            $university = safeColumn($row, 'Column1', 'N/A');
                            $subtotal = isset($row->Column6) ? floatval($row->Column6) : 0;

                            if (!isset($dataByUniversity[$university])) {
                                $dataByUniversity[$university] = 0;
                            }

                            $dataByUniversity[$university] += $subtotal;
                        }

                        $totalRows = array_sum($dataByUniversity);

                        ksort($dataByUniversity, SORT_NATURAL | SORT_FLAG_CASE);

                        echo '<div class="d-flex justify-content-between align-items-center mt-3 mb-2">';
                        echo '<h4 class="text-primary mb-0">Grand Total: ' . number_format($totalRows, 0) . '</h4>';
                        echo '<h5 class="mb-0 text-muted">' . $selectedDateRange . '</h5>';
                        echo '</div>';
                        echo '<div class="text-align"><a class="btn btn-sm btn-success" target="_blank" href="' . $url . '">XML Link</a><br /><br /></div>';

                        echo '<table class="table table-bordered table-striped table-hover align-middle totals-table">';
                        echo '<thead class="thead-dark"><tr><th>Owning University</th><th style="width:20%;">Totals</th></tr></thead>';
                        echo '<tbody>';

                        foreach ($dataByUniversity as $university => $subtotal) {
                            echo '<tr>';
                            echo '<td>' . $university . '</td>';
                            echo '<td>' . number_format($subtotal, 0) . '</td>';
                            echo '</tr>';
                        }

                        echo '<tr class="table-secondary fw-bold">';
                        echo '<td class="text-end">Total for ' . $selectedDateRange . '</td>';
                        echo '<td>' . number_format($totalRows, 0) . '</td>';
                        echo '</tr>';
                        echo '</tbody>';
                        echo '</table>';
                    } catch (Exception $e) {
                        echo "<p class='text-danger'>" . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                ?>

              
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'include/footer.php'; ?>
</body>

</html>
