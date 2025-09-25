<?php include 'include/access.php';?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCF Refile: Step 1: Check-in Returns and place on Hold Shelf</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <?php include 'include/refresh.php';?>
</head>
<body>
 <!-- Nav bar -->
 <?php include 'include/nav.php';?>
<div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">

        <h2 class="text-center">Step 1: Check-in Returns and place on Hold Shelf - <?php echo $name; ?></h2>
        <!-- Form for barcode input -->
        <div class="card-header bg-primary text-white">
                        <h4>Barcode Entry</h4>
                    </div>
        <form action="" method="POST" id="dateForm" class="border p-4 bg-light">
            <div class="form-group">
                <label for="barcode">Item Barcode:</label>
                <?php $itembarcode = isset($_GET['barcode']) ? htmlspecialchars(trim($_GET['barcode'])) : '';
$itembarcode = isset($_GET['barcode']) ? htmlspecialchars(trim($_GET['barcode'])) : '';
?>
<input type="text" class="form-control" id="barcode" name="barcode" value="<?php echo $itembarcode; ?>" required>



            </div>
            <button type="submit" class="btn btn-primary btn-block">Submit</button>
        </form>

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
// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['barcode'])) {
    // Get the barcode from the form submission
    $barcode = trim($_POST['barcode']);

    // add X to barcode if missing
    if (substr($barcode, -1) !== 'X') {
        $barcode .= 'X'; // Append 'X' to the end of the barcode if it is missing
    }
// Himmelfarb check: begins with 'p' and 6 characters
if (strtolower(substr($barcode, 0, 1)) === 'p' && strlen($barcode) === 6) {
    // If it begins with 'p' (case-insensitive), always add another X
    $barcode .= 'X';
}
    // Define variables
    $library = 'SCF'; // Replace LIBCODE with actual library code
    $circ_desk = 'DEFAULT_CIRC_DESK'; // Replace CIRCDESKCODE with actual circ desk code
    $prefix = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/';

//Include API Keys
    include 'include/apikey.php';

//// function to check the URL. Add above code.
    function getFinalRedirectUrl($item_by_barc_url)
    {
        // Initialize cURL session
        $ch = curl_init($item_by_barc_url);
        // Set cURL options to follow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        // Execute cURL request
        curl_exec($ch);
        // Check the final URL after following redirects
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        // Close cURL session
        curl_close($ch);
        // Return final URL if it is different from the input URL, indicating a redirect
        return $finalUrl !== $item_by_barc_url ? $finalUrl : false;
    }
//// End Function

    // Construct item by barcode URL
    $item_by_barc_url = $prefix . 'items?item_barcode=' . urlencode($barcode) . '&apikey=' . $api_key;

//////// Check if barcode uses X, remove it, check again, error message if nothing works. Uses function above
    // Check if the URL redirects
    $redirectUrl = getFinalRedirectUrl($item_by_barc_url);

    if ($redirectUrl) {
        // pass along original URL
    } else {
        // Modify the item barcode by removing the 'X' and reconstruct the URL
        $item_barcode_no_x = str_replace('X', '', $barcode);
        $urlWithoutX = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/items?item_barcode=" . $item_barcode_no_x . "&apikey=" . $api_key;

        // Check if the modified URL redirects
        $redirectUrl = getFinalRedirectUrl($urlWithoutX);

        // if redirect works, set new $url
        if ($redirectUrl) {
            $item_by_barc_url = $urlWithoutX;
        } else {
            echo "<div class='alert alert-danger mt-3 text-center'>Item record does not exist</div>";
            exit;
        }
    }
///// end check barcode

    // Make the GET request to fetch item URL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $item_by_barc_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [$api_key_header]);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Set a maximum number of redirects
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // Get the final URL after redirects
    curl_close($ch);

    // The URL of the XML resource
    $xmlUrl = $final_url;
// Load the XML content
    $xml = simplexml_load_file($xmlUrl);

// Check if the XML was loaded successfully
    if ($xml === false) {
        echo "Failed to load XML";
        exit;
    }
// Check the value of <internal_note_3>,<internal_note_1>, and <alternative_call_number>
    $internalNote3 = (string) $xml->item_data->internal_note_3;
    $internalNote1 = (string) $xml->item_data->internal_note_1;
    $acn = (string) $xml->item_data->alternative_call_number;
    $holding_id = (string) $xml->holding_data->holding_id;
    $pid = (string) $xml->item_data->pid;
    $mms_id = (string) $xml->bib_data->mms_id;

// If the field is blank, proceed; otherwise, display the message
    if (empty($internalNote3) or $internalNote3 == 'SCF Hold Shelf') {

    } else {
        echo "<br /><div class='alert alert-danger text-center'>
    <h4 class='mb-3'>Barcode " . $barcode . "</h4>
    Internal note 3 message: " . $internalNote3 . "<br /><br /> <span class='font-italic'>This item has not been checked in. Please give it to your supervisor.</span></div>";

        exit;
    }

    ////// Second API call to fetch loan details //////
    $loan_url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/" . $mms_id . "/holdings/" . $holding_id . "/items/" . $pid . "/loans?apikey=" . $api_key;

    // Initialize cURL session for the second request
    $ch = curl_init();
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $loan_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
    curl_setopt($ch, CURLOPT_HEADER, false); // Exclude headers in output

    $loan_response = curl_exec($ch);

    if ($loan_response === false) {
        echo "<div class='alert alert-danger mt-3'>cURL Error: " . curl_error($ch) . "</div>";
    } else {
        // Load the response into a SimpleXML object
        $loan_xml = simplexml_load_string($loan_response);

        if ($loan_xml === false) {
            echo "<div class='alert alert-danger mt-3'>Failed to parse loan XML response.</div>";
        } else {
            // Check if the item is checked in (no loans)
            $total_record_count = (int) $loan_xml['total_record_count'];
        }
    }

    // Check if the item is checked in (no loans)
    $total_record_count = (int) $loan_xml['total_record_count'];

    if ($total_record_count === 0) {
        echo '<div class="alert alert-danger text-center mt-3"><strong><h3>Item Already Checked In.</h3> <p>Please give it to your supervisor for review.</p></strong></div>';

    } else {

        // Access the single <item_loan> elements if item is still checked out
        $item_loan = $loan_xml->item_loan;

        if ($item_loan) {
            // Extract the required values from the single item_loan element
            $due_date = (string) $item_loan->due_date;
            $loan_status = (string) $item_loan->loan_status;
            $process_status = (string) $item_loan->process_status;
            $user_id = (string) $item_loan->user_id;

// Mapping user_id to "Deliver To" values
            $deliverToMapping = [
                "01WRLC_AMU-UNIV_LIB" => "AU",
                "01WRLC_AMULAW-PLL" => "AULAW",
                "01WRLC_CAA-CUMULLEN" => "CU",
                "01WRLC_CAA-CUOLL" => "CU (for Lima)",
                "01WRLC_DOC-DCVN" => "DC",
                "01WRLC_DOCLAW-UDCLAW" => "DCLaw",
                "01WRLC_GAL-GALLAUDET" => "GA",
                "01WRLC_GML-FENWICK" => "GM",
                "01WRLC_GML-ARLINGTON" => "GMA",
                "01WRLC_GML-MERCER" => "GMP",
                "01WRLC_GUNIV-lau" => "GT",
                "01WRLC_GUNIV-qatar" => "GT",
                "01WRLC_GUNIV-kie" => "GT-Bioethics",
                "01WRLC_GUNIV-bfcsc" => "GT-Booth",
                "01WRLC_GUNIV-mccourt" => "GT-McCourt",
                "01WRLC_GUNIV-maindel" => "GT-OD",
                "01WRLC_GUNIV-scs" => "GT-SCS",
                "01WRLC_GUNIV-wdst" => "GT-WTL",
                "01WRLC_GUNIV-sci" => "GTB",
                "01WRLC_GUNIVLAW-GUL" => "GTL",
                "01WRLC_GWA-gelman" => "GW",
                "01WRLC_GWA-SCRC" => "GW-SC",
                "01WRLC_GWA-eckles" => "GWE",
                "01WRLC_GWA-vstcl" => "GWN",
                "01WRLC_GWAHLTH-GW_VSTC_Library_Delivery" => "GWN",
                "01WRLC_GWA-zOffCampus" => "GWOC",
                "01WRLC_GWAHLTH-GWAHLTH" => "HI",
                "01WRLC_SCF-SCF" => "HQ",
                "01WRLC_HOW-HUF" => "HU",
                "01WRLC_HOW-HUB" => "HU",
                "01WRLC_HOW-HUMS" => "HU",
                "01WRLC_HOW-HUS" => "HU",
                "01WRLC_HOW-HULS" => "HU-HS",
                "01WRLC_HOW-HL" => "HUWC",
                "01WRLC_GWALAW-GWALAW" => "JB",
                "01WRLC_MAR-main" => "MU",
                "01WRLC_MAR-bcle" => "MUB",
                "01WRLC_SCF-Sheehan Library" => "TR",
            ];

// Check if $user_id exists in the mapping and display the corresponding "Deliver To" value
            if (isset($deliverToMapping[$user_id])) {
                echo "<div class='alert alert-warning text-center mt-3' style='color:#000;'><strong>Patron: Deliver To: " . $deliverToMapping[$user_id] . "</strong></div>";

            } else {

                if (isset($user_id) and $user_id !== '') {
                    echo "<div class='alert alert-warning text-center mt-3' style='color:#000;'><strong>Patron: " . htmlspecialchars($user_id) . ".</strong></div>";
                } else {echo '<div class="alert alert-danger text-center font-italics mt-3" style="color:#000;"><strong>Patron field is blank. Set Item aside for Supervisor.</strong></div>';}
            }
        }
    }

    curl_close($ch);

    if ($httpcode != 200) {
        echo "<p class='alert alert-danger'>Barcode error. Please check with Supervisor.</p>";
        if ($httpcode == 302) {
            // echo "<p>Redirected to: $final_url</p>";
        }
    } else {
        // Parse the response XML to extract the item ID
        $xml = simplexml_load_string($response);
        if ($xml === false) {
            echo "<p>Error: Failed to parse XML response. Please check the API response format.</p>";
        } else {
            // Assuming the item URL is in the 'link' element
            $item_url = htmlspecialchars($final_url);
            if ($item_url) {
                // Construct scan-in URL
                $scan_in_url = $item_url . '&op=scan&library=' . urlencode($library) . '&circ_desk=' . urlencode($circ_desk);

                // Make the POST request to check in the item
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $scan_in_url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/xml',
                    'Authorization: apikey ' . $api_key, // Use API key header
                    'Accept: application/xml',
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $post_response = curl_exec($ch);
                $post_httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

// Output response
                if ($post_httpcode = '200') {echo '

<div class="alert alert-primary text-center"><h4>Barcode ' . $barcode . ' has been checked in.</h4>';
                    $status = "Item In Place";} else {
                    $status = "Item Not In Place";
                    echo "<div class='alert alert-info'><h5>Scan-in API Response:</h5>";
                    echo "<pre>HTTP Status Code: $post_httpcode</pre>";
                    echo "<pre>" . htmlspecialchars($post_response) . "</pre>";
                }

// If there is no tray barcode, check in the item but do not place on hold shelf.
                if (empty($internalNote1) and empty($acn)) {
                    echo "<br /><div class='alert alert-warning text-center'>
            <h4 class='mb-3'>Barcode " . $barcode . "</h4>
            <h5>Possible New Book - Additional Processing Needed.</h5> <span class='font-italic'>This item has been checked in but do not place on hold shelf.</span></div>";
                    exit;

                }
//// Update Internal Note 3 to SCF Hold Shelf ////

// Function to perform GET request
                function curlGet($url)
                {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FAILONERROR, true);
                    $response = curl_exec($ch);
                    $error_code = curl_errno($ch);
                    $error_msg = curl_error($ch);
                    curl_close($ch);

                    if ($error_code) {
                        echo "GET Request Error: $error_code - $error_msg\n";
                        return false;
                    }

                    return $response;
                }

// Function to perform PUT request
                function curlPut($url, $data)
                {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/xml',
                        'Content-Length: ' . strlen($data),
                    ));
                    curl_setopt($ch, CURLOPT_FAILONERROR, true);
                    $response = curl_exec($ch);
                    $error_code = curl_errno($ch);
                    $error_msg = curl_error($ch);
                    curl_close($ch);

                    if ($error_code) {
                        echo "PUT Request Error: $error_code - $error_msg\n";
                        return false;
                    }

                    return $response;
                }

// GET request to fetch the XML data
                $xml_response = curlGet($item_url);

                if ($xml_response !== false) {
                    // Load the XML response into a SimpleXMLElement object
                    $xml = new SimpleXMLElement($xml_response);

                    // Extract the variables
                    $title = (string) $xml->bib_data->title;
                    $internal_note_1 = (string) $xml->item_data->internal_note_1;
                    $internal_note_3 = (string) $xml->item_data->internal_note_3;
                    $mms_id = (string) $xml->bib_data->mms_id;
                    $process_type = (string) $xml->item_data->process_type;

                    // Modify the <internal_note_3> element
                    $custom_internal_note = 'SCF Hold Shelf';
                    $xml->item_data->internal_note_3 = $custom_internal_note;

                    //Modify Temp Location fields
                    // In temp location - true or false
                    $custom_in_temp_location = 'true';
                    $xml->holding_data->in_temp_location = $custom_in_temp_location;

                    // In temp location - true or false
                    $custom_temp_library = 'SCF';
                    $xml->holding_data->temp_library = $custom_temp_library;

                    // In temp location - true or false
                    $custom_temp_location = 'SCF_Hold';
                    $xml->holding_data->temp_location = $custom_temp_location;

                    // Convert the modified XML object back to a string
                    $modified_xml = $xml->asXML();

                    // PUT URL (same as GET URL without the API key)
                    $put_url = $item_url;

                    // PUT request to update the data
                    $put_response = curlPut($put_url, $modified_xml);

                    if ($put_response !== false) {
                        echo '<h4>' . $title . '</h4>';

                        $formatted_note = preg_replace_callback(
                            '/(R\d{2})|(M\d{2})|(S\d{2})|(T\d{2})/',
                            function ($matches) {
                                // Assign Bootstrap classes based on which group matched
                                if ($matches[1]) {
                                    return '<span class="text-primary">' . $matches[1] . '</span>'; // R group
                                } elseif ($matches[2]) {
                                    return '<span class="text-success">' . $matches[2] . '</span>'; // M group
                                } elseif ($matches[3]) {
                                    return '<span class="text-danger">' . $matches[3] . '</span>'; // S group
                                } elseif ($matches[4]) {
                                    return '<span class="text-info">' . $matches[4] . '</span>'; // T group
                                }
                            },
                            $internal_note_1
                        );

                        // Display the formatted result
                        echo '<h4 class="badge text-center badge-light" style="font-size:1.4em;">Tray Barcode: ' . $formatted_note . '</h4>';
                        echo '<p><i>Be sure to write down and include the tray barcode with the item.</i></p>';

                        if (isset($process_type) and $process_type !== '') {
                            echo "<div class='alert alert-warning'> This item has a processing type of '" . $process_type . "'.</div><div>";
                        }
                        echo "<div class='alert alert-success'> <strong>Item is ready to be placed on Hold Shelf</strong></div><br /><br />";
                        echo "<div>";
                        echo '<a class="btn btn-primary mr-1" target="_blank" href="https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/' . $mms_id . '/loans?apikey=' . $api_key . '">View Check-In XML</a><a href="' . $final_url . '" class="btn btn-primary" target="_blank" >View Record XML</a></h4></div>';

/////// ***** Record to NDJSON File ******** //////////////
                        // File path for the NDJSON file in the same directory
                        $file = __DIR__ . '/refile.ndjson';
                        
                        // Define the process type
                        if (isset($process_type) and $process_type !== '') {
                            $process_type_full = $status . ' - ' . $process_type;
                        } else {
                            $process_type_full = $status;
                        }
                        
                        // Define the variables for a new entry
                        $jsonDate = date('Y-m-d H:i:s'); // Current timestamp
                        $jsonName = $name; // Replace this with actual name variable
                        $jsonBarcode = $barcode; // Replace this with actual barcode variable
                        $jsonTrayBarcode = $internal_note_1; // Tray barcode
                        $jsonStatus = $process_type_full; // Check if item has been checked in or not
                        $jsonStep = '1'; // define the step the row is part of
                        
                        // Create a new entry
                        $newEntry = [
                            'date' => $jsonDate,
                            'name' => $jsonName,
                            'barcode' => $jsonBarcode,
                            'tray barcode' => $jsonTrayBarcode,
                            'status' => $jsonStatus,
                            'step' => $jsonStep,
                        ];
                        
                        // Encode the new entry as compact JSON
                        $jsonLine = json_encode($newEntry, JSON_UNESCAPED_SLASHES);
                        if ($jsonLine === false) {
                            die('Error: JSON encoding failed. ' . json_last_error_msg());
                        }
                        
                        // Append the JSON line to the NDJSON file
                        if (file_put_contents($file, $jsonLine . "\n", FILE_APPEND | LOCK_EX) === false) {
                            die('Error: Unable to write to the JSON file.');
                        }
                        
                      //  echo "Entry saved successfully to NDJSON file.";

/////// ***** End Record to NDJSON File ******** //////////////

                    } else {echo '<p>Execption found : Return Item to Supervisor</p><p>' . $put_response . '</p>';}
                    echo '<br /><center><a class="btn btn-danger text-center" href="">Clear Form</a></center>';
                }

            } else {
                echo "<p>Failed to retrieve item URL for barcode $barcode. Check if the barcode is valid and try again.</p>";
            }
        }
    }
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        echo "<p>No barcode submitted.</p>";
    }
    echo '</div>';

}

?>
    </div>
    </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <?php include 'include/footer.php';?>
    <script>
        document.getElementById('dateForm').onsubmit = function() {
            // Show the spinner when form is submitted
            document.getElementById('loadingSpinner').style.display = 'block';
        };

        <?php if (isset($xmlData) && $xmlData): ?>
            // Hide the spinner when the table is loaded
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('resultTable').style.display = 'block';
        <?php endif;?>
    </script>
</body>
</html>