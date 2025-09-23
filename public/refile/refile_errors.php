<?php include 'include/access.php';
include 'include/admin_access.php';

// Google Sheets API URL
$json_key = $_ENV['GOOGLE_SHEET'] ?? getenv('GOOGLE_SHEET');
$json_url = "https://sheets.googleapis.com/v4/spreadsheets/1bieQ2wsjb1ptVt49QEea94mmUVcoDbnONzJi4xXXOo4/values/Sheet1?alt=json&key=" . $json_key;

// Fetch the JSON data
$json_data = file_get_contents($json_url);

// Decode the JSON data into a PHP array
$data = json_decode($json_data, true);

// Check if the data is valid
if (isset($data['values']) && count($data['values']) > 0) {
    $rows = $data['values'];
    // Row count excluding the header row
    $row_count = count($rows) - 1;
} else {
    echo "No data found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refile Errors</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'include/nav.php';?>
<div class="container mt-5">
    <h2 class="mb-4">Refile Errors - Mismatched Trays</h2>
    <h6 class="mb-4 alert alert-info">Once you have verified the item is in the correct tray location, click the <span class="btn btn-primary">Refile</span> button to verify the item's IN3 has been cleared and it is ready to be reshelved in the SCF. If not, click "Proceed" to clear it. Otherwise, click "Clear" to clear the form. Delete the row from the spreadsheet when done.</h6>

    <!-- Display the row count -->
    <p><strong>Item Count:</strong> <?php echo $row_count; ?></p>

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <?php
// Output the headers (first row)
$headers = $rows[0];
foreach ($headers as $header) {
    echo "<th>{$header}</th>";
}
echo "<th>Action</th>"; // Adding an extra header for the button
?>
            </tr>
        </thead>
        <tbody>
            <?php
// Output the data (remaining rows)
for ($i = 1; $i < count($rows); $i++) {
    echo "<tr>";
    for ($j = 0; $j < count($headers); $j++) {
        $cell = isset($rows[$i][$j]) ? $rows[$i][$j] : '';
        echo "<td>" . htmlspecialchars($cell) . "</td>";
    }

    // Get the values from the second and third columns
    $trayBarcode = isset($rows[$i][2]) ? $rows[$i][2] : '';
    $barcode = isset($rows[$i][3]) ? $rows[$i][3] : '';

    // Construct the URL
    $buttonUrl = "refile_update.php?barcode={$barcode}&trayBarcode={$trayBarcode}";

    // Add the button in a new cell
    echo "<td><a class='btn btn-primary' href='{$buttonUrl}'>Refile</a></td>";

    echo "</tr>";
}
?>
        </tbody>
    </table>

    <a class="btn btn-primary" target="_blank" href="https://docs.google.com/spreadsheets/d/1bieQ2wsjb1ptVt49QEea94mmUVcoDbnONzJi4xXXOo4/edit">Link to Spreadsheet</a>
</div>

<!-- Include Bootstrap JS  -->
<?php include 'include/footer.php';?>
</body>
</html>