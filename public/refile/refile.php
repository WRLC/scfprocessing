<?php include 'include/access.php';?>
<?php
// Define the API URL with the apikey parameter
$api_key = $_ENV['CANNED_REPORTS'] ?? getenv('CANNED_REPORTS');
$api_url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=%2Fshared%2FShared+storage+institution%2FReports%2FAPI%2FAPI+Tray+Check+-+SCF+Hold+Shelf&limit=1000&col_names=true&apikey=' . $api_key;

// Initialize variables to store results and errors
$resultMessage = '';
$error = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $barcode = $_POST['barcode'];
    $trayBarcode = $_POST['trayBarcode'];

    // Load the XML from the API
    $xml_data = simplexml_load_file($api_url);

    if ($xml_data === false) {
        $error = "Failed to load XML data.";
    } else {
        // Initialize a variable to check if a match was found
        $matchFound = false;

        // Loop through the rows in the XML data
        foreach ($xml_data->QueryResult->ResultXml->rowset->Row as $row) {
            $column1 = (string) $row->Column1;
            $column4 = (string) $row->Column4;
            $column7 = (string) $row->Column7;
            $column8 = (string) $row->Column8;
            $column8 = substr($column8, 0, 12);

            // Check if the Barcode from the form matches Column4 in the XML
            if ($column4 == $barcode) {

// Check if Column8 and TrayBarcode match
                if ($column8 == $trayBarcode) {
                    $resultMessage .= "<p class='alert alert-success'>Success! Tray Barcode matches with Alma. Ready to Reshelf.</p>";
                } else {
                    $resultMessage .= "<p class='text-danger'>Error: Tray Barcode does not match with Alma.</p>";
                }

                $matchFound = true;
                $resultMessage = "
                    <div class='alert alert-info'>
                        <h4 class='alert-heading'>Item Found!</h4>
                        <p><strong>Title:</strong> " . htmlspecialchars($column1) . "</p>
                        <p><strong>Barcode Verified in Alma:</strong> " . htmlspecialchars($column4) . "</p>
                        <p><strong>Alma Tray Barcode:</strong> " . htmlspecialchars($column8) . "</p>
                        <p><strong>Form Tray Barcode:</strong> " . htmlspecialchars($trayBarcode) . "</p>
                        <p><strong>Status:</strong> " . htmlspecialchars($column7) . "</p>";

                // Check if item is on Hold Shelf

                // if (htmlspecialchars($column7) == 'SCF Hold Shelf') {
                //     echo '<div class="alert alert-info"><p>Item is on Hold Shelf</p></div>';
                // }

                // Check if Column8 and TrayBarcode match
                if ($column8 == $trayBarcode) {
                    $resultMessage .= "<center><h5 class='alert alert-success'>Success! Tray Barcode matches with Alma.<br />Item Ready to Reshelf.</h5><p class='mx-auto'><button type='button' class='btn btn-success'>Reshelf Item</button></p></center>";
                } else {
                    $resultMessage .= "<h4 class='text-danger'>Error: Tray Barcode does not match with Alma.</h4>";
                }

                $resultMessage .= "</div>";
                break; // Stop the loop once a match is found
            }
        }

        // If no match is found
        if (!$matchFound) {
            $error = "No match found for the given Barcode.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barcode and Tray Barcode Form</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <h2 class="text-center">Enter Item Barcode and Tray Barcode</h2>
                <form method="POST" class="border p-4 bg-light">
                    <div class="form-group">
                        <label for="barcode">Item Barcode:</label>
                        <input type="text" class="form-control" id="barcode" name="barcode" required>
                    </div>
                    <div class="form-group">
                        <label for="trayBarcode">Tray Barcode:</label>
                        <input type="text" class="form-control" id="trayBarcode" name="trayBarcode" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Submit</button>
                </form>
                <div class="mt-3">
                    <?php
// Display error message if any
if ($error) {
    echo "<div class='alert alert-danger'>$error</div>";
}
// Display result message if any
if ($resultMessage) {
    echo $resultMessage;
}
?>
                </div>
                <div class="mt-4">
                    <h5>Examples:</h5>
                    <ul>
                        <li><strong>Item Barcode:</strong> 32882012348937X</li>
                        <li><strong>Tray Barcode:</strong> R01M06S16T01</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>