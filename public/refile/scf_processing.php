<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tray/Shelf Location Form</title>
    <!-- Import Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col s12 m6">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Tray/Shelf Location Form</span>

                    <!-- Form -->
                    <form method="POST" action="">
                        <div class="input-field">
                            <input id="trayBarcode" name="trayBarcode" type="text" class="validate" required>
                            <label for="trayBarcode">Tray Barcode</label>
                        </div>
                        <div class="input-field">
                            <input id="barcode" name="barcode" type="text" class="validate" required>
                            <label for="barcode">Item Barcode</label>
                        </div>
                        <button type="submit" class="btn waves-effect waves-light">Submit</button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture the form data
    $trayBarcode = htmlspecialchars($_POST['trayBarcode']);
    $barcode = htmlspecialchars($_POST['barcode']);

    // API URL with the barcode and API key
    $api_key = $_ENV['SCF_REFILE'] ?? getenv('SCF_REFILE');
    $api_url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/items?item_barcode=" . $barcode . "&apikey=" . $api_key;

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects

    // Execute cURL request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        echo '<p>Error: ' . curl_error($ch) . '</p>';
    } else {
        // Close cURL
        curl_close($ch);

        // Parse the XML response
        $xml = simplexml_load_string($response);

        if ($xml) {
            // Capture <title>, <barcode>, and <internal_note_3>
            $title = $xml->title ?? 'N/A';
            $item_barcode = $xml->barcode ?? 'N/A';
            $internal_note_3 = $xml->internal_note_3 ?? 'N/A';

            // Display results
            echo "
                <div class='card-panel teal'>
                    <span class='white-text'>
                        <strong>Title:</strong> {$title}<br>
                        <strong>Barcode:</strong> {$item_barcode}<br>
                        <strong>Internal Note 3:</strong> {$internal_note_3}
                    </span>
                </div>
                ";
        } else {
            echo '<p>Error parsing XML response.</p>';
        }
    }
}
?>
</div>

<!-- Import Materialize JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>