<?php include 'include/access.php';

$getbarcode = isset($_GET['barcode']) ? htmlspecialchars(trim($_GET['barcode'])) : '';

?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Information</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <?php include 'include/refresh.php';?>
</head>

<body>
    <!-- Nav bar -->
    <?php include 'include/nav.php';?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Check Item Status by Barcode</h4>
                    </div>
                    <div class="card-body">
                        <!-- Form to submit barcode -->
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="barcode">Enter Barcode:</label>
                                <input type="text"
                                <?php if (isset($getbarcode)) {
    echo 'value="' . $getbarcode . '"';
}
?>

                                class="form-control" id="barcode" name="barcode" placeholder="Enter barcode" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Search</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if form was submitted and barcode is provided

    // Get API Keys
    include 'include/apikey.php';

    $barcode = htmlspecialchars($_POST['barcode']); // Get barcode from form input and sanitize it
    $barcode = trim($_POST['barcode']);
    if (substr($barcode, -1) !== 'X') {
        $barcode .= 'X'; // Append 'X' to the end of the barcode if it is missing
    }
// Himmelfarb check: begins with 'p' and 6 characters
if (strtolower(substr($barcode, 0, 1)) === 'p' && strlen($barcode) === 6) {
    // If it begins with 'p' (case-insensitive), always add another X
    $barcode .= 'X';
}
//// function to check the URL. Add above code.
    function getFinalRedirectUrl($url)
    {
        // Initialize cURL session
        $ch = curl_init($url);
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
        return $finalUrl !== $url ? $finalUrl : false;
    }
//// End Function

    // URL of the API with the item barcode and API key
    $url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/items?item_barcode=" . $barcode . "&apikey=" . $api_key;

//////// Check if barcode uses X, remove it, check again, error message if nothing works. Uses function above
    // Check if the URL redirects
    $redirectUrl = getFinalRedirectUrl($url);

    if ($redirectUrl) {
        // echo "Success, ".$barcode." redirects to: " . $redirectUrl;
    } else {
        // Modify the item barcode by removing the 'X' and reconstruct the URL
        $item_barcode_no_x = str_replace('X', '', $barcode);
        $urlWithoutX = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/items?item_barcode=" . $item_barcode_no_x . "&apikey=" . $api_key;

        // Check if the modified URL redirects
        $redirectUrl = getFinalRedirectUrl($urlWithoutX);
        // if redirect works, set new $url
        if ($redirectUrl) {
            // echo "Removed the X, and ".$item_barcode_no_x." works: " . $redirectUrl;
            $url = $urlWithoutX;
        } else {
            echo "<div class='alert alert-danger mt-3 text-center'>Item record does not exist</div>";
            exit;
        }
    }
///// end check barcode

    // Initialize cURL session for the first request
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
    curl_setopt($ch, CURLOPT_HEADER, false); // Exclude headers in output

    // Execute the cURL session
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        echo "<div class='alert alert-danger mt-3'>cURL Error: " . curl_error($ch) . "</div>";
    } else {
        // Load the response into a SimpleXML object for easy parsing
        $xml = simplexml_load_string($response);

        if ($xml === false) {

            echo "<div class='alert alert-danger mt-3'>Failed to parse XML response.</div>";
        } else {

            // Extract <title>, <barcode>, <internal_note_3>, and additional data
            $title = (string) $xml->bib_data->title;
            $item_barcode = (string) $xml->item_data->barcode;
            $internalNote3 = (string) $xml->item_data->internal_note_3;
            $internalNote1 = (string) $xml->item_data->internal_note_1;
            $description = (string) $xml->item_data->description;
            $in_temp_location = (string) $xml->holding_data->in_temp_location;
            $temp_library = (string) $xml->holding_data->temp_library;
            $temp_location = (string) $xml->holding_data->temp_location;
            $provenance = (string) $xml->item_data->provenance;
            $provenance_desc = (string) $xml->item_data->provenance['desc'];
            $process_type = (string) $xml->item_data->process_type;
            $holding_id = (string) $xml->holding_data->holding_id;
            $pid = (string) $xml->item_data->pid;
            $mms_id = (string) $xml->bib_data->mms_id;
            $modification_date = (string) $xml->item_data->modification_date;
            $modification_date = str_replace('Z', '', $modification_date);

            // Display the results using Bootstrap styling
            echo "<div class='row mt-3'>
                        <div class='col-md-8 offset-md-2'>
                            <div class='card bg-light'>
                                <div class='card-header bg-dark text-white text-center'>
                                    <h4>Item Details for Barcode: $item_barcode</h4>
                                </div>
                                <div class='card-body'>";

            echo '<h6 class="text-center alert alert-success" >Item Information</h6><ul class="list-group">';
            echo "<ul class='list-group'>";
            echo " <li class='list-group-item'><strong>Title:</strong> " . htmlspecialchars($title) . "</li>";
            echo "<li class='list-group-item'><strong>Barcode:</strong> " . htmlspecialchars($item_barcode) . "</li>";
            echo "<li class='list-group-item'><strong>Tray/Shelf Barcode:</strong> " . htmlspecialchars($internalNote1) . "</li>";
            echo "<li class='list-group-item'><strong>Description:</strong> " . htmlspecialchars($description) . "</li>";
            echo "<li class='list-group-item'><strong>MMS ID:</strong> " . htmlspecialchars($mms_id) . "</li>";
            echo "<li class='list-group-item'><strong>Owning Library:</strong> " . htmlspecialchars($provenance_desc) . "</li>";
            echo "<li class='list-group-item'><strong>Last Modified:</strong> " . htmlspecialchars($modification_date) . "</li>";

            echo '</ul><br /><h6 class="text-center alert alert-primary">Refile Information</h6><ul class="list-group">';
            echo "<li class='list-group-item'><strong>Internal Note 3:</strong> " . htmlspecialchars($internalNote3) . "</li>";
            echo "<li class='list-group-item'><strong>In Temp Location:</strong> " . htmlspecialchars($in_temp_location) . "</li>";
            echo "<li class='list-group-item'><strong>Temp Location:</strong> " . htmlspecialchars($temp_location) . "</li>";
            echo "<li class='list-group-item'><strong>Temp Library:</strong> " . htmlspecialchars($temp_library) . "</li>";
            echo "<li class='list-group-item'><strong>Process Type:</strong> <span class='text-danger'>" . htmlspecialchars($process_type) . "</span></li>";

            // Second API call to fetch loan details
            $loan_url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/" . $mms_id . "/holdings/" . $holding_id . "/items/" . $pid . "/loans?apikey=" . $api_key;
            curl_setopt($ch, CURLOPT_URL, $loan_url);
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
                    if ($total_record_count === 0) {
                        echo '<li class="list-group-item"><strong>Status:</strong> <span class="text-success">Item Checked In</span></li>';
                    } else {

                        // Access the single <item_loan> elements if item is still checked out
                        $item_loan = $loan_xml->item_loan;

                        if ($item_loan) {
                            // Extract the required values from the single item_loan element
                            $due_date = (string) $item_loan->due_date;
                            $loan_status = (string) $item_loan->loan_status;
                            $process_status = (string) $item_loan->process_status;
                            $user_id = (string) $item_loan->user_id;

                            // Display the extracted values
                            if ($loan_status == 'Active') {
                                echo "

                                    </ul>
                                    <br /><h6 class='text-center alert alert-info'>Loan Information</h6><ul class='list-group'>

                                    <li class='list-group-item'><strong>Loan Status: </strong><span class='text-danger font-italic'>Checked Out</span></li>";
                            } else {
                                echo "<li class='list-group-item'><strong>Loan Status:</strong> " . htmlspecialchars($loan_status) . "</li>";
                            }

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

// Sample $user_id variable
                            //$user_id = "01WRLC_AMU-UNIV_LIB";

// Check if $user_id exists in the mapping and display the corresponding "Deliver To" value
                            if (isset($deliverToMapping[$user_id])) {
                                echo "<li class='list-group-item'><strong>Borrower:</strong> <span class='badge badge-warning' style='font-size:1.2em;'>Deliver To: " . $deliverToMapping[$user_id] . "</span></li>";

                            } else {
                                echo "<li class='list-group-item'><strong>Borrower (User ID):</strong> " . htmlspecialchars($user_id) . "</li>";
                            }

                            // echo "<li class='list-group-item'><strong>User ID (Borrower):</strong> " . htmlspecialchars($user_id) . "</li>";
                            echo "<li class='list-group-item'><strong>Due Date:</strong> " . htmlspecialchars($due_date) . "</li>";
                            echo "<li class='list-group-item'><strong>Process Status:</strong> " . htmlspecialchars($process_status) . "</li>";
                        } else {
                            echo 'No item_loan found in the XML response.';
                        }
                    }
                }
            }

            echo "</ul>"; // End of the list
            echo "
                <br />
                <div class='btn-group mb-4 offset-md-4' style='width:240px;'>
                <a class='btn btn-info btn-sm' style='width:120px;'  target='_blank' href='" . $url . "'>XML</a>
                <a class='btn btn-warning btn-sm' style='width:120px;' target='_blank' href='" . $loan_url . "'>Loan XML</a></div>
                <p class='text-center'>Does everything look ok?</p>
                <a class='btn btn-danger offset-md-5 mb-2' style='width:130px;' href='check_in.php?barcode=" . htmlspecialchars($item_barcode) . "'>Check-In Item</a>
                </div>
                </div>
                </div>
                </div>";
        }
    }
    // Close the cURL session
    curl_close($ch);
}

?>
            <p class="text-center">Example barcode: 32882012348937X</p>
    </div>




    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <?php include 'include/footer.php';?>
</body>

</html>