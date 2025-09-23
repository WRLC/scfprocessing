<?php
session_start();

if ( isset( $_SESSION['user_id'] ) ) {
    // Grab user data from the database using the user_id
    // Let them access the "logged in only" pages
	$now = time(); // Checking the time now when home page starts.
	

        if ($now > $_SESSION['expire']) {
            session_destroy();
            header("Location: login.php");
        }
	
	
} else {
    // Redirect them to the login page
    header("Location: login.php");
}
?>
<?php include('header.php'); ?>

  <div class="row">
     <div class="col s12 push-m3 m6">
      <div class="card white lighten-1" style="margin-top:20px;">
        <div class="card-content blue-text"> <span class="card-title bold center">Add Item Call Number and Internal Note 1: 
         <?php echo $_SESSION['user_id']; ?></span><p class="center">Add Item Call Number and Internal Note 1 fields with shelf barcode at the same time.</p>
         <p class="center red-text">For Archival Materials Only</p>
          <div class="row">
            <style>
#hideMe {
    -moz-animation: cssAnimation 0s ease-in 3s forwards;
    /* Firefox */
    -webkit-animation: cssAnimation 0s ease-in 3s forwards;
    /* Safari and Chrome */
    -o-animation: cssAnimation 0s ease-in 3s forwards;
    /* Opera */
    animation: cssAnimation 0s ease-in 3s forwards;
    -webkit-animation-fill-mode: forwards;
    animation-fill-mode: forwards;
}
@keyframes cssAnimation {
    to {
        width:0;
        height:0;
        overflow:hidden;
    }
}
@-webkit-keyframes cssAnimation {
    to {
        width:0;
        height:0;
        visibility:hidden;
    }
}
</style>
            <?php

$name = $_SESSION['user_id'];
$submit = $_GET['submit'];
?>

<!-- Form for barcode input -->



<form action="" method="POST" class="border p-4 bg-light">
    <div class="form-group">
        <label for="barcode">Enter Barcode:</label>
        <input type="text" class="form-control" id="barcode" name="barcode" required>
        <span class="new badge white red-text right" data-badge-caption="Required"></span>
    </div>

    <div class="form-group">
        <label for="trayBarcode">Enter Shelf Barcode:</label>
        <input type="text" class="form-control" id="trayBarcode" name="trayBarcode"
        
        
        <?php

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['barcode'])) {
// Get the barcode from the form submission
$barcode = trim($_POST['barcode']);
$trayBarcode = trim($_POST['trayBarcode']);

echo 'value="'.$trayBarcode.'"';



}
?>
        
        
        
        required>
        <span class="new badge white red-text right" data-badge-caption="Required"></span>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Submit</button>
</form>

<?php
// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['barcode'])) {
    // Get the barcode from the form submission
    $barcode = trim($_POST['barcode']);
    $trayBarcode = trim($_POST['trayBarcode']);

    // Define variables
$library = 'SCF';  // Replace LIBCODE with actual library code
$circ_desk = 'DEFAULT_CIRC_DESK';  // Replace CIRCDESKCODE with actual circ desk code
$prefix = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/';

//Include API Keys
include('include/apikey.php');

  //  echo "<p>Processing barcode: ".$barcode." </p>";

    // Construct item by barcode URL
    $item_by_barc_url = $prefix . 'items?item_barcode=' . urlencode($barcode).'&apikey='.$api_key;
  //  echo "<p>__ Calling GET \"$item_by_barc_url\" ...</p>";

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

    // Output the full response for debugging
   // echo "<h5>Debug Info:</h5>";
   // echo "<pre>HTTP Status Code: $httpcode</pre>";
   // echo "<pre>Response:\n" . htmlspecialchars($response) . "</pre>";
   // echo "<pre>Final URL After Redirects:\n" . htmlspecialchars($final_url) . "</pre>";

    if ($httpcode != 200) {
        echo "<p>Error: Received HTTP status code $httpcode. Please check the API request.</p>";
        if ($httpcode == 302) {
           // echo "<p>Redirected to: $final_url</p>";
        }
    } else {
        // Parse the response XML to extract the item ID
        $xml = simplexml_load_string($response);
        if ($xml === false) {
            echo "<p>Error: Failed to parse XML response. Please check the API response format.</p>";
        } else {

           $item_url = htmlspecialchars($final_url);

            if ($item_url) {
                // Construct scan-in URL
                $scan_in_url = $item_url . '&op=scan&library=' . urlencode($library) . '&circ_desk=' . urlencode($circ_desk);
         
              
//////////// Begin GET/PUT Update Internal Note 1 and item call number

// Function to perform GET request
function curlGet($url) {
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
function curlPut($url, $data) {
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'Content-Type: application/xml',
'Content-Length: ' . strlen($data)
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
$title = (string)$xml->bib_data->title;
$internal_note_1 = (string)$xml->item_data->internal_note_1;
$internal_note_3 = (string)$xml->item_data->internal_note_3;
$mms_id = (string)$xml->bib_data->mms_id;


/////Write the variables

// Modify the <internal_note_1> element
$custom_internal_note_1 = $trayBarcode;
$xml->item_data->internal_note_1 = $custom_internal_note_1;

// Modify the <alternative_call_number> element
$custom_alternative_call_number = $trayBarcode;
$xml->item_data->alternative_call_number = $custom_alternative_call_number;

// Convert the modified XML object back to a string
$modified_xml = $xml->asXML();

// PUT URL (same as GET URL without the API key)
$put_url = $item_url;

// PUT request to update the data
$put_response = curlPut($put_url, $modified_xml);

if ($put_response !== false) {

//////// Get the updated fields and display them



// URL of the API with the item barcode and API key
$url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/items?item_barcode=" . $barcode . "&apikey=" . $api_key;

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
    $title = (string)$xml->bib_data->title;
    $item_barcode = (string)$xml->item_data->barcode;
    $internalNote3 = (string)$xml->item_data->internal_note_3;
    $internalNote1 = (string)$xml->item_data->internal_note_1;
    $alternative_call_number = (string)$xml->item_data->alternative_call_number;

    $in_temp_location = (string)$xml->holding_data->in_temp_location;
    $temp_library = (string)$xml->holding_data->temp_library;
    $temp_location = (string)$xml->holding_data->temp_location;
    $provenance = (string)$xml->item_data->provenance;
    $provenance_desc = (string)$xml->item_data->provenance['desc'];
    $mms_id = (string)$xml->bib_data->mms_id;

    // Display the results using Bootstrap styling
    echo "<br />
            <div class='card black-text green lighten-5'>
                <div class='card-title card-content  text-white center'>
                    Details for Barcode: ".$item_barcode."
                    <p><span class='center'>Item has been processed.</span></p>
                    </div>
                    

                <div class='card-content'>";

    echo '<h6 class="center teal lighten-2 white-text" style="padding:10px;">Item Information</h6><ul class="list-group">';
    echo "<ul class='list-group'>";
    echo " <li class='list-group-item'><strong>Title:</strong> " . htmlspecialchars($title) . "</li>";
    echo "<li class='list-group-item'><strong>Barcode:</strong> " . htmlspecialchars($item_barcode) . "</li>";
    echo "<li class='list-group-item'><strong>Tray Barcode (ICN):</strong> " . htmlspecialchars($alternative_call_number) . "</li>";
    echo "<li class='list-group-item'><strong>Tray Barcode (IN1):</strong> " . htmlspecialchars($internalNote1) . "</li>";
    echo "<li class='list-group-item'><strong>Owning Library:</strong> ".htmlspecialchars($provenance_desc)." (" . htmlspecialchars($provenance) . ")</li>";
    echo "<li class='list-group-item'><strong>MMS ID:</strong> " . htmlspecialchars($mms_id) . "</li>";

    echo '</ul><br /><h6 class="center teal lighten-2 white-text" style="padding:10px;">Loan Information</h6><ul class="list-group">';
    echo "<li class='list-group-item'><strong>Internal Note 3:</strong> " . htmlspecialchars($internalNote3) . "</li>";
    echo "<li class='list-group-item'><strong>In Temp Location:</strong> " . htmlspecialchars($in_temp_location) . "</li>";
    echo "<li class='list-group-item'><strong>Temp Location:</strong> " . htmlspecialchars($temp_location) . "</li>";
    echo "<li class='list-group-item'><strong>Temp Library:</strong> " . htmlspecialchars($temp_library) . "</li>";


    // Second API call to fetch loan details
    $loan_url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/" . $mms_id . "/loans?apikey=" . $api_key;
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
            $total_record_count = (int)$loan_xml['total_record_count'];
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
                    if ($loan_status == 'Active') echo "
                    
                    </ul>
                    <br /><h6 class='center alert alert-info'>Loan Information</h6><ul class='list-group'>
                    
                    <li class='list-group-item'><strong>Loan Status: </strong><span class='text-danger font-italic'>Checked Out</span></li>";
                    else
                        echo "<li class='list-group-item'><strong>Loan Status:</strong> " . htmlspecialchars($loan_status) . "</li>";
                        echo "<li class='list-group-item'><strong>User ID:</strong> " . htmlspecialchars($user_id) . "</li>";
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
<a class='btn btn-info btn-sm' style='width:60px;' target='_blank' href='" . $url . "'>XML</a>
</div>
</div>
";
}
}
// Close the cURL session
curl_close($ch);

////////// end display item details




//////////// End GET/PUT Update Internal Note 1


/////////// Begin display tray contents in a table

// Retrieve Tray Barcode input
// Retrieve Tray Barcode input. Limit to 12 characters
//$ItemCall = htmlspecialchars($trayBarcode);
$ItemCall = substr($trayBarcode, 0, 12);


/////////// End display tray contents in a table


}
else {echo '<p>Execption found : Return Item to Supervisor</p><p>'.$put_response.'</p>';}
echo '<br /><center><a class="btn btn-danger red center" href="">Clear Form</a></center><br /><br />'; 
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
        </div>
      </div>
    </div>
  </div>
  
<!--JavaScript at end of body for optimized loading-->
<?php include('footer.php'); ?>
</body>
</html>