<?php include 'include/access.php';

// Define the helper function once, outside the loop.
function getFinalRedirectUrl($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "<div class='alert alert-danger'>cURL Error: " . curl_error($ch) . "</div>";
        curl_close($ch);
        return false;
    }

    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return ($finalUrl !== $url) ? $finalUrl : false;
}


?><!DOCTYPE html>



<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Information</title>
    <?php include 'include/refresh.php';?>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-control {
    display: block;
    width: 100%;
    height: 44px;
    padding: .375rem .75rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: .25rem;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
}
        </style>
</head>
<body>
    <!-- Nav bar -->
    <?php include 'include/nav.php';?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h2 class="text-center">Step 2: Tray verification and reshelving in SCF</h2>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Upload Barcode File</h4>
                    </div>
                    <div class="card-body bg-light">
                        <!-- Form to upload .txt file -->
                        <form method="POST" action="" id="dateForm" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="file">Choose .txt file:</label>
                                <input type="file" class="form-control" id="file" name="file" accept=".txt" required>
                            </div>
                            <div class="text-center font-italic mt-4"><button type="submit" class="btn btn-success">Preview Records from File</button></div>
                            <div class="small text-center font-italic mt-4"> Be patient. It can take time to load a large list of item records.</div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

         <!-- Spinner section, initially hidden -->
   <div class="row justify-content-center text-center mt-4" id="loadingSpinner" style="display:none;">
            <div class=" text-center justify-content-center">

                <div class="spinner-border mt-1  text-primary text-center" role="status">
                    <span class="visually-hidden"></span>
                </div>
                <p>Loading data, please wait...</p>
            </div>
        </div>
 <!-- End Spinner section, initially hidden -->

        <?php

$rows = []; // To store item details for further processing



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'])) {

    // Ensure the file is a .txt
    $file = $_FILES['file']['tmp_name'];
    if (mime_content_type($file) !== 'text/plain') {
        echo "<div class='alert alert-danger mt-3'>Invalid file type. Please upload a .txt file.</div>";
        exit;
    }

    // Read the file content line by line
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);



    if (!$lines) {
        echo "<div class='alert alert-danger mt-3'>The file is empty or could not be read.</div>";
        exit;
    }

   

    // Get API Keys
    include 'include/apikey.php';

    echo '<div class="row mt-3">
            <div class="col-md-12">';
    // Display the table header and row count
    $row_count = count($rows); // Count rows
    echo '<h2>Item Details</h2>';

    echo '<table class="table table-striped table-bordered">
            <thead class="thead-dark"><tr>
                <th>Tray Barcode</th>
                <th>Title</th>
                <th>Item Barcode</th>
                <th>Internal Note 3</th>
                <th>Status</th>
              </tr></thead><tbody>';

    // Process lines in pairs
    for ($i = 0; $i < count($lines); $i += 2) {
        $trayBarcode = $lines[$i]; // Odd row
        $barcode = isset($lines[$i + 1]) ? $lines[$i + 1] : null; // Even row (barcode)

        if (substr($barcode, -1) !== 'X') {
            $barcode .= 'X'; // Append 'X' to the end of the barcode if it is missing
        }
// Himmelfarb check: begins with 'p' and 6 characters
if (strtolower(substr($barcode, 0, 1)) === 'p' && strlen($barcode) === 6) {
    // If it begins with 'p' (case-insensitive), always add another X
    $barcode .= 'X';
}
        $trayBarcode = substr($trayBarcode, 0, 12);




        if ($barcode) {

// API URL to get item data
            $url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/items?item_barcode=" . $barcode . "&apikey=" . $api_key;

//////// Check if barcode uses X, remove it, check again, error message if nothing works. Uses function above
    // Check if the URL redirects
    $redirectUrl = getFinalRedirectUrl($url);

    // Check for redirect; if not found, try removing the 'X'
                    // Check for redirect; if not found, try removing the 'X'
                    $redirectUrl = getFinalRedirectUrl($url);
                    if (!$redirectUrl) {
                        $item_barcode_no_x = str_replace('X', '', $barcode);
                        $urlWithoutX = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/items?item_barcode=" . $item_barcode_no_x . "&apikey=" . $api_key;
                        $redirectUrl = getFinalRedirectUrl($urlWithoutX);
                        if ($redirectUrl) {
                            $url = $urlWithoutX;
                        } else {
                            echo "<div class='alert alert-danger mt-3 text-center'>Item record for barcode $barcode does not exist</div>";
                            continue; // Skip this iteration instead of exiting
                        }
    }
///// end check barcode





// Initialize cURL session for the first request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HEADER, false);

// Execute the cURL session and fetch the response
            $response = curl_exec($ch);

            if ($response === false) {
                echo "<div class='alert alert-danger mt-3'>cURL Error: " . curl_error($ch) . "</div>";
                curl_close($ch); // Ensure cURL is closed before continue
                continue;
            }
// Load the response into a SimpleXML object for easy parsing
            $xml = simplexml_load_string($response);
            if ($xml === false) {
                echo "<div class='alert alert-danger mt-3'>Failed to parse XML response for item information.</div>";
                curl_close($ch); // Ensure cURL is closed before continue
                continue;
            }
// Extract relevant fields
            $title = (string) $xml->bib_data->title;
            $item_barcode = (string) $xml->item_data->barcode;
            $internalNote1 = (string) $xml->item_data->internal_note_1;
            $internalNote1 = substr($internalNote1, 0, 12);
            $internalNote3 = (string) $xml->item_data->internal_note_3;
            $mms_id = (string) $xml->bib_data->mms_id;
            $holding_id = (string) $xml->holding_data->holding_id;
            $pid = (string) $xml->item_data->pid;

// Second API call to get loan details
            $loan_url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/" . $mms_id . "/holdings/" . $holding_id . "/items/" . $pid . "/loans?apikey=" . $api_key;
            curl_setopt($ch, CURLOPT_URL, $loan_url);
            $loan_response = curl_exec($ch);
            if ($loan_response === false) {
                echo "<div class='alert alert-danger mt-3'>cURL Error: " . curl_error($ch) . "</div>";
                continue;
            }
// Load the loan response into a SimpleXML object
            $loan_xml = simplexml_load_string($loan_response);
            if ($loan_xml === false) {
                echo "<div class='alert alert-danger mt-3'>Failed to parse loan XML response for loan details. " . $url . "</div>";
                continue;
            }
// Check if the item is checked in (no loans)
            $total_record_count = (int) $loan_xml['total_record_count'];
            $status = '';
            $due_date = '';
            $process_status = '';
            if ($total_record_count === 0) {
                $status = '<span class="text-success">Item Checked In</span>';
            } else {
                // Extract loan details if item is checked out
                $item_loan = $loan_xml->item_loan;
                if ($item_loan) {
                    $status = '<span class="text-danger">Checked Out</span>';
                    $due_date = (string) $item_loan->due_date;
                    $process_status = (string) $item_loan->process_status;
                }
            }

// Store the row data for later use
            $rows[] = [
                'trayBarcode' => $trayBarcode,
                'title' => $title,
                'item_barcode' => $item_barcode,
                'internalNote1' => $internalNote1,
                'internalNote3' => $internalNote3,
                'mms_id' => $mms_id,
                'holding_id' => $holding_id,
                'pid' => $pid,
            ];

            $row_count = count($rows);
// Display the results in table rows
            echo "<tr>";






if (!empty($trayBarcode) && !empty($internalNote1)) {

    if ($trayBarcode == $internalNote1) {
        // Both available and match
        echo '<td><span class="text-success">' . htmlspecialchars($trayBarcode) . ' - Match</span></td>';
    } else {
        // Both available but do not match
        $barcode = $trayBarcode;
        $barcodeAlma = $internalNote1;
        $itembarcode = $item_barcode;

        // Google Form URL
        $google_form_url = "https://docs.google.com/forms/u/0/d/e/1FAIpQLSfdqhD8VPq8X13niOSL-y7146PkmYtzJW0v7U-Sr94EmJOtyA/formResponse";

        // Google Form fields
        $form_data = [
            "entry.1671538415" => $barcode,
            "entry.1478552555" => $barcodeAlma,
            "entry.860961451" => $itembarcode,
        ];

        // Initialize cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $google_form_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Execute cURL session
        $response = curl_exec($ch);
        curl_close($ch);

        echo '<td class="table-danger"><span class="text-danger">' . htmlspecialchars($trayBarcode) . ' (from File) - ' . htmlspecialchars($internalNote1) . ' (from Alma) - Does Not Match. <br /><a class="btn btn-danger" href="https://docs.google.com/spreadsheets/d/1bieQ2wsjb1ptVt49QEea94mmUVcoDbnONzJi4xXXOo4/edit?resourcekey=&gid=729588841#gid=729588841">Mismatch Recorded</a></span></td>';
    }

} else {

    // One or both variables are missing
    $missing = [];

    if (empty($trayBarcode)) {
        $missing[] = 'trayBarcode (from File)';
    }

    if (empty($internalNote1)) {
        $missing[] = 'internalNote1 (from Alma)';
    }

    $missing_message = implode(' and ', $missing) . ' missing';

    $barcode = !empty($trayBarcode) ? $trayBarcode : 'MISSING';
    $barcodeAlma = !empty($internalNote1) ? $internalNote1 : 'MISSING';
    $itembarcode = $item_barcode;

    // Google Form URL
    $google_form_url = "https://docs.google.com/forms/u/0/d/e/1FAIpQLSfdqhD8VPq8X13niOSL-y7146PkmYtzJW0v7U-Sr94EmJOtyA/formResponse";

    // Google Form fields
    $form_data = [
        "entry.1671538415" => $barcode,
        "entry.1478552555" => $barcodeAlma,
        "entry.860961451" => $itembarcode,
    ];

    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $google_form_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // Execute cURL session
    $response = curl_exec($ch);
    curl_close($ch);

    echo '<td class="table-warning"><span class="text-warning">Error: ' . htmlspecialchars($missing_message) . '. <br /><a class="btn btn-warning" href="https://docs.google.com/spreadsheets/d/1bieQ2wsjb1ptVt49QEea94mmUVcoDbnONzJi4xXXOo4/edit?resourcekey=&gid=729588841#gid=729588841">Missing Data Recorded</a></span></td>';
}

            echo "<td>" . htmlspecialchars($title) . "</td>";
            echo "<td>" . htmlspecialchars($item_barcode) . "</td>";
            echo "<td>" . htmlspecialchars($internalNote3) . "</td>";
            //    echo "<td>" . htmlspecialchars($mms_id) ."</td>";

            if ($total_record_count !== 0) {
                echo "<td class='table-danger'>" . $status . "<br /><small>Due: " . htmlspecialchars($due_date) . "<br />Processing Data: " . htmlspecialchars($process_status) . "</small></td>";
            } else {
                echo "<td>" . $status . "</td>";
            }

            echo "</tr>";
        }
    }

    echo '</tbody></table><div><h2>Item Count: ' . $row_count . '</h2></div>';
    echo '</div></div>';

    // Close the cURL session
    curl_close($ch);

    // Show Proceed button if rows were fetched
    if (!empty($rows)) {
        echo '<form method="POST" id="dateForm" action="">';
        echo '<input type="hidden" name="rows" value="' . htmlspecialchars(serialize($rows)) . '">';
        echo '<button type="submit" name="proceed" class="btn btn-primary">Proceed</button> <a class="btn btn-danger" href="">Clear</a>';
        echo '</form>';
    }
}

// Handle the Proceed action
if (isset($_POST['proceed'])) {
    $rows = unserialize($_POST['rows']);
    // Get API Keys again so they work with the loop
    include 'include/apikey.php';?>

              <!-- Spinner section, initially hidden -->
              <div class="row justify-content-center text-center mt-4" id="loadingSpinner" style="display:none;">
                         <div class=" text-center justify-content-center">

                             <div class="spinner-border mt-1  text-primary text-center" role="status">
                                 <span class="visually-hidden"></span>
                             </div>
                             <p>Loading data, please wait...</p>
                         </div>
                     </div>
              <!-- End Spinner section, initially hidden -->

              <?php

    foreach ($rows as $row) {
        // Construct the URL for GET request
        $getUrl = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/" . $row['mms_id'] . "/holdings/" . $row['holding_id'] . "/items/" . $row['pid'] . "?apikey=" . $api_key . "";
        // Initialize cURL for the GET request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $getUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $getResponse = curl_exec($ch);
      //  curl_close($ch);

        // Load the response into a SimpleXML object
        $xml = simplexml_load_string($getResponse);

        // Update the <internal_note_3> field to blank
        $internalNote3 = $xml->item_data->internal_note_3;
        $internalNote3[0] = ''; // Update the value to blank

        //Modify Temp Location fields to reset
        // In temp location - true or false
        $custom_in_temp_location = 'false';
        $xml->holding_data->in_temp_location = $custom_in_temp_location;

        // In temp location - true or false
        $custom_temp_library = '';
        $xml->holding_data->temp_library = $custom_temp_library;

        // In temp location - true or false
        $custom_temp_location = '';
        $xml->holding_data->temp_location = $custom_temp_location;

        // Convert XML back to string for the PUT request
        $xmlString = $xml->asXML();

        // Construct the URL for PUT request
        $putUrl = $getUrl; // Same URL for PUT request

        // Initialize cURL for the PUT request
      //  $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $putUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/xml']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlString);
        $putResponse = curl_exec($ch);
        curl_close($ch). PHP_EOL;

        

        // Pause for 1 second
      //  sleep(1);
    }

    // Redisplay the updated data
    // We can reuse the same code as above to fetch and display updated item details
    echo '<div class="row mt-3">';
    echo '<div class="col-md-12">';
    // Count the rows in $rows and display next to "Updated Item Details"
    $updated_row_count = count($rows);
    echo '<h2>Updated Item Details <a href="" class="btn btn-danger">Clear</a></h2>';

    echo '<table class="table table-striped table-bordered">';
    echo '<thead class="thead-dark"><tr>
                <th>Tray Barcode</th>
                <th>Title</th>
                <th>Item Barcode</th>
                <th>Internal Note 3</th>
                <th>MMS ID</th>
                <th>Status</th>
              </tr></thead><tbody>';

    foreach ($rows as $row) {
        $url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/items?item_barcode=" . $row['item_barcode'] . "&apikey=" . $api_key;

        // Initialize cURL session for GET request to fetch updated data
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Parse the response XML
        $xml = simplexml_load_string($response);

        // Extract updated fields
        $title = (string) $xml->bib_data->title;
        $item_barcode = (string) $xml->item_data->barcode;
        $internalNote3 = (string) $xml->item_data->internal_note_3;
        $internalNote1 = (string) $xml->item_data->internal_note_1;
        $mms_id = (string) $xml->bib_data->mms_id;
        $process_type = (string) $xml->item_data->process_type;

        // Display updated details in table
        echo "<tr>";
        echo '<td>' . htmlspecialchars($row['trayBarcode']) . '</td>';
        echo "<td>" . htmlspecialchars($title) . "</td>";
        echo "<td>" . htmlspecialchars($item_barcode) . "</td>";
        echo "<td>" . htmlspecialchars($internalNote3) . "</td>";
        echo "<td>" . htmlspecialchars($mms_id) . "</td>";
        echo "<td><span class='text-success'>Updated</span></td>";
        echo "</tr>";

/////// ***** Record to NDJSON File ******** //////////////
// File path for the NDJSON file
$file = __DIR__ . '/refile.ndjson';

// Optional: ensure $process_type is defined before this line
$process_type_full = ' - ' . $process_type;

// Define the variables for a new entry
$jsonDate = date('Y-m-d H:i:s'); // Current timestamp
$jsonName = $name;               // Replace this with actual name variable
$jsonBarcode = $item_barcode;   // Replace this with actual barcode variable
$jsonTrayBarcode = $internalNote1; // Tray barcode

if (isset($process_type) && $process_type !== '') {
    $jsonStatus = 'Item In Place - ' . $process_type;
} else {
    $jsonStatus = 'Item In Place';
}

$jsonStep = '2'; // Define the step the row is part of

// Create a new entry
$newEntry = [
    'date' => $jsonDate,
    'name' => $jsonName,
    'barcode' => $jsonBarcode,
    'tray barcode' => $jsonTrayBarcode,
    'status' => $jsonStatus,
    'step' => $jsonStep,
];

// Encode as JSON (compact) and append to file
$jsonLine = json_encode($newEntry, JSON_UNESCAPED_SLASHES);
if ($jsonLine === false) {
    die('Error: JSON encoding failed. ' . json_last_error_msg());
}

if (file_put_contents($file, $jsonLine . "\n", FILE_APPEND | LOCK_EX) === false) {
    die('Error: Unable to write to the NDJSON file.');
} }

/////// ***** End Record to NDJSON File ******** //////////////

    echo '</tbody></table>';
    echo '</div></div>';
}
?>
            <p class="text-center">Upload a .txt file with barcodes</p>
            <p class="text-center">Format should be:<br />
            <span class="text-success">Tray Barcode<br />
            Item Barcode<br /></span>
           <span class="text-info"> Tray Barcode<br />
            Item Barcode<br /></span>
            Etc...
            </p>
    </div>
    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <?php include 'include/footer.php';?>
    <script>
        document.getElementById('dateForm').onsubmit = function() {
            // Show the spinner when form is submitted
            document.getElementById('loadingSpinner').style.display = 'block';
        };
        <?php if ($xmlData): ?>
            // Hide the spinner when the table is loaded
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('resultTable').style.display = 'block';
        <?php endif;?>
    </script>
</body>
</html>