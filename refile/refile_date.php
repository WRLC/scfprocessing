<?php
include 'include/access.php';
include 'include/admin_access.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Date Range Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<?php include 'include/nav.php';?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-10 offset-md-1">

                <h4 class="text-center">Refile Stats by Date Range</h4>
                <form method="get" class="bg-light border p-4 text-center"  action="">


                <div class="row offset-md-2">
                    <div class="mb-3 col-md-6" style="max-width: 300px;">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>

                    <div class="mb-3 col-md-6" style="max-width: 300px;">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                    </div>
                  </div>





                    <button type="submit" class="btn btn-primary">Submit</button> <a href="refile_date.php" class="btn btn-danger">Clear</a>
                </form>

                <?php
// Check if form is submitted
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    // Retrieve start and end dates
    $start_date = htmlspecialchars($_GET['start_date']);
    $end_date = htmlspecialchars($_GET['end_date']);

    // Display the start and end dates
    echo "<h6 class='mt-1 text-center'>Selected Dates: " . $start_date . " to " . $end_date . "</h6>";

    // API URL
    include 'include/apikey.php';

    $url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=%2Fshared%2FShared%20storage%20institution%2FReports%2FAPI%2FAPI%20-%20SCF%20Hold%20Shelf%20History&limit=1000&col_names=true&apikey=' . $api_key . '&filter=%3Csawx:expr%20xsi:type=%22sawx:list%22%20op=%22containsAny%22%20xmlns:saw=%22com.siebel.analytics.web/report/v1.1%22%20xmlns:sawx=%22com.siebel.analytics.web/expression/v1.1%22%20xmlns:xsi=%22http://www.w3.org/2001/XMLSchema-instance%22%20xmlns:xsd=%22http://www.w3.org/2001/XMLSchema%22%20%3E%3Csawx:expr%20xsi:type=%22sawx:comparison%22%20op=%22between%22%3E%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Physical%20Items%20Historical%20Events%22.%22Event%20Start%20Date%22.%22Event%20Start%20Date%22%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22xsd:date%22%3E' . $start_date . '%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22xsd:date%22%3E' . $end_date . '%3C/sawx:expr%3E%3C/sawx:expr%3E%3C/sawx:expr%3E';

    // Fetch XML data from the URL
    $xml_data = file_get_contents($url);

    // Check if data was successfully fetched
    if ($xml_data === false) {
        echo "<p class='text-danger'>Failed to retrieve data from the API.</p>";
    } else {
        // Parse the XML data
        $xml = simplexml_load_string($xml_data);

        if ($xml === false) {
            echo "<p class='text-danger'>Failed to parse XML data.</p>";
        } else {
            // Count the total number of rows
            $totalRows = count($xml->QueryResult->ResultXml->rowset->Row);

            // Display the total row count before the table
            // echo "<h5 class='mt-3'>Total: ".$totalRows."</h5>";

            // Display the data in a table
            echo "<h4 class='mt-2'>Results: " . $totalRows . "</h4>";
            echo '<div class="text-align"><a class="btn btn-sm btn-success" href="' . $url . '">XML Link</a><br /><br /></div>';
            //  echo '<a target="_blank" href="' . $url . '">Link to XML</a>';
            echo "<table class='table table-bordered table-striped table-hover'>";
            echo "<thead class='thead-dark'><tr><th>Title</th><th>Tray Barcode</th><th style='max-width:200px;'>Barcode</th><th style='max-width:200px;'>Date</th></tr></thead>";
            echo "<tbody>";
            foreach ($xml->QueryResult->ResultXml->rowset->Row as $row) {
                // Convert Column3 to an integer and add to the total
                $title = (int) $row->Column2;
                $barcode = (int) $row->Column3;
                $trayBarcode = (int) $row->Column4;
                $date = (int) $row->Column5;

                echo "<tr>";
                echo "<td>{$row->Column2}</td>";
                echo "<td>{$row->Column3}</td>";
                echo "<td>{$row->Column4}</td>";
                echo "<td>{$row->Column5}</td>";

                echo "</tr>";
            }

            echo "</tbody></table>";
        }
    }
}
?>
           <br />
       <a class="btn btn-info" href="refile_month.php">View by Month</a>

        </div> </div> </div>
  <!-- Include Bootstrap JS  -->
<?php include 'include/footer.php';?>
</body>

</html>