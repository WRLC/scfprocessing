<?php
include 'include/access.php';

function almaRequest($url, $method = 'GET', $api_key = '', $body = null)
{
    $ch = curl_init();

    $headers = [
        'Accept: application/xml',
    ];

    if (!empty($api_key)) {
        $headers[] = 'Authorization: apikey ' . $api_key;
    }

    if ($method === 'POST' || $method === 'PUT') {
        $headers[] = 'Content-Type: application/xml';
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, false);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    }

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

    curl_close($ch);

    return [
        'response' => $response,
        'error' => $error,
        'errno' => $errno,
        'httpcode' => $httpcode,
        'final_url' => $final_url,
        'ok' => ($errno === 0 && $response !== false),
    ];
}

function normalizeBarcode($barcode)
{
    $barcode = trim($barcode);

    if (substr($barcode, -1) !== 'X') {
        $barcode .= 'X';
    }

    if (strtolower(substr($barcode, 0, 1)) === 'p' && strlen($barcode) === 6) {
        $barcode .= 'X';
    }

    return $barcode;
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCF Refile: Step 1: Check-in Returns and place on Hold Shelf</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <?php include 'include/refresh.php'; ?>
</head>
<body>
<?php include 'include/nav.php'; ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">

            <h2 class="text-center">Step 1: Check-in Returns and place on Hold Shelf - <?php echo htmlspecialchars($name ?? ''); ?></h2>

            <div class="card-header bg-primary text-white">
                <h4>Barcode Entry</h4>
            </div>

            <?php
            $itembarcode = isset($_GET['barcode']) ? htmlspecialchars(trim($_GET['barcode'])) : '';
            ?>

            <form action="" method="POST" id="dateForm" class="border p-4 bg-light">
                <div class="form-group">
                    <label for="barcode">Item Barcode:</label>
                    <input type="text" class="form-control" id="barcode" name="barcode" value="<?php echo $itembarcode; ?>" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Submit</button>
            </form>

            <div class="row justify-content-center text-center mt-4" id="loadingSpinner" style="display:none;">
                <div class="text-center justify-content-center">
                    <div class="spinner-border mt-1 text-primary text-center" role="status">
                        <span class="visually-hidden"></span>
                    </div>
                    <p>Loading data, please wait...</p>
                </div>
            </div>

            <?php
            if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['barcode'])) {
                $barcode = normalizeBarcode($_POST['barcode']);

                $library = 'SCF';
                $circ_desk = 'DEFAULT_CIRC_DESK';
                $prefix = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/';

                include 'include/apikey.php';

                if (!isset($api_key) || $api_key === '') {
                    echo "<div class='alert alert-danger mt-3 text-center'>API key is not configured.</div>";
                    exit;
                }

                $candidateBarcodes = [$barcode];

                $barcodeWithoutX = str_replace('X', '', $barcode);
                if ($barcodeWithoutX !== $barcode) {
                    $candidateBarcodes[] = $barcodeWithoutX;
                }

                $itemLookup = null;
                $resolvedBarcode = null;

                foreach ($candidateBarcodes as $candidate) {
                    $item_by_barc_url = $prefix . 'items?item_barcode=' . urlencode($candidate) . '&apikey=' . urlencode($api_key);
                    $result = almaRequest($item_by_barc_url, 'GET', $api_key);

                    if ($result['ok'] && $result['httpcode'] === 200 && !empty($result['response'])) {
                        $itemLookup = $result;
                        $resolvedBarcode = $candidate;
                        break;
                    }
                }

                if ($itemLookup === null) {
                    echo "<div class='alert alert-danger mt-3 text-center'>Item record does not exist or Alma lookup timed out.</div>";
                    exit;
                }

                $barcode = $resolvedBarcode;
                $response = $itemLookup['response'];
                $httpcode = $itemLookup['httpcode'];
                $final_url = $itemLookup['final_url'];

                $xml = simplexml_load_string($response);

                if ($xml === false) {
                    echo "<div class='alert alert-danger mt-3 text-center'>Failed to parse item XML response.</div>";
                    exit;
                }

                $internalNote3 = (string)($xml->item_data->internal_note_3 ?? '');
                $internalNote1 = (string)($xml->item_data->internal_note_1 ?? '');
                $acn = (string)($xml->item_data->alternative_call_number ?? '');
                $holding_id = (string)($xml->holding_data->holding_id ?? '');
                $pid = (string)($xml->item_data->pid ?? '');
                $mms_id = (string)($xml->bib_data->mms_id ?? '');

                if (!empty($internalNote3) && $internalNote3 !== 'SCF Hold Shelf') {
                    echo "<br /><div class='alert alert-danger text-center'>
                        <h4 class='mb-3'>Barcode " . htmlspecialchars($barcode) . "</h4>
                        Internal note 3 message: " . htmlspecialchars($internalNote3) . "<br /><br />
                        <span class='font-italic'>This item has not been checked in. Please give it to your supervisor.</span>
                    </div>";
                    exit;
                }

                $loan_url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/" . rawurlencode($mms_id) . "/holdings/" . rawurlencode($holding_id) . "/items/" . rawurlencode($pid) . "/loans?apikey=" . urlencode($api_key);

                $loanResult = almaRequest($loan_url, 'GET', $api_key);

                if (!$loanResult['ok']) {
                    echo "<div class='alert alert-danger mt-3'>Loan lookup failed: " . htmlspecialchars($loanResult['error']) . "</div>";
                    exit;
                }

                if ($loanResult['httpcode'] !== 200) {
                    echo "<div class='alert alert-danger mt-3'>Loan lookup returned HTTP " . (int)$loanResult['httpcode'] . ".</div>";
                    exit;
                }

                $loan_xml = simplexml_load_string($loanResult['response']);

                if ($loan_xml === false) {
                    echo "<div class='alert alert-danger mt-3'>Failed to parse loan XML response.</div>";
                    exit;
                }

                $total_record_count = (int)($loan_xml['total_record_count'] ?? 0);

                if ($total_record_count === 0) {
                    echo '<div class="alert alert-danger text-center mt-3"><strong><h3>Item Already Checked In.</h3><p>Please give it to your supervisor for review.</p></strong></div>';
                } else {
                    $item_loan = $loan_xml->item_loan;

                    if ($item_loan) {
                        $user_id = (string)($item_loan->user_id ?? '');

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

                        if (isset($deliverToMapping[$user_id])) {
                            echo "<div class='alert alert-warning text-center mt-3' style='color:#000;'><strong>Patron: Deliver To: " . htmlspecialchars($deliverToMapping[$user_id]) . "</strong></div>";
                        } else {
                            if ($user_id !== '') {
                                echo "<div class='alert alert-warning text-center mt-3' style='color:#000;'><strong>Patron: " . htmlspecialchars($user_id) . ".</strong></div>";
                            } else {
                                echo '<div class="alert alert-danger text-center font-italics mt-3" style="color:#000;"><strong>Patron field is blank. Set Item aside for Supervisor.</strong></div>';
                            }
                        }
                    }
                }

                if ($httpcode !== 200) {
                    echo "<p class='alert alert-danger'>Barcode error. Please check with Supervisor.</p>";
                    exit;
                }

                $item_url = htmlspecialchars($final_url);

                if (!$item_url) {
                    echo "<p>Failed to retrieve item URL for barcode " . htmlspecialchars($barcode) . ". Check if the barcode is valid and try again.</p>";
                    exit;
                }

                $scan_in_url = $final_url . '&op=scan&library=' . urlencode($library) . '&circ_desk=' . urlencode($circ_desk);

                $scanResult = almaRequest($scan_in_url, 'POST', $api_key, '');

                if (!$scanResult['ok']) {
                    echo "<div class='alert alert-danger mt-3'>Scan-in failed: " . htmlspecialchars($scanResult['error']) . "</div>";
                    exit;
                }

                $post_response = $scanResult['response'];
                $post_httpcode = $scanResult['httpcode'];

                if ($post_httpcode === 200) {
                    echo '<div class="alert alert-primary text-center"><h4>Barcode ' . htmlspecialchars($barcode) . ' has been checked in.</h4>';
                    $status = "Item In Place";
                } else {
                    $status = "Item Not In Place";
                    echo "<div class='alert alert-info'><h5>Scan-in API Response:</h5>";
                    echo "<pre>HTTP Status Code: " . htmlspecialchars((string)$post_httpcode) . "</pre>";
                    echo "<pre>" . htmlspecialchars((string)$post_response) . "</pre>";
                    echo "</div>";
                }

                if (empty($internalNote1) && empty($acn)) {
                    echo "<br /><div class='alert alert-warning text-center'>
                        <h4 class='mb-3'>Barcode " . htmlspecialchars($barcode) . "</h4>
                        <h5>Possible New Book - Additional Processing Needed.</h5>
                        <span class='font-italic'>This item has been checked in but do not place on hold shelf.</span>
                    </div>";
                    exit;
                }

                $xml_response = almaRequest($final_url, 'GET', $api_key);

                if (!$xml_response['ok'] || $xml_response['httpcode'] !== 200) {
                    echo "<div class='alert alert-danger mt-3'>Failed to reload item record for update.</div>";
                    exit;
                }

                $xml = simplexml_load_string($xml_response['response']);

                if ($xml === false) {
                    echo "<div class='alert alert-danger mt-3'>Failed to parse item record for update.</div>";
                    exit;
                }

                $title = (string)($xml->bib_data->title ?? '');
                $internal_note_1 = (string)($xml->item_data->internal_note_1 ?? '');
                $internal_note_3 = (string)($xml->item_data->internal_note_3 ?? '');
                $mms_id = (string)($xml->bib_data->mms_id ?? '');
                $process_type = (string)($xml->item_data->process_type ?? '');

                $xml->item_data->internal_note_3 = 'SCF Hold Shelf';
                $xml->holding_data->in_temp_location = 'true';
                $xml->holding_data->temp_library = 'SCF';
                $xml->holding_data->temp_location = 'SCF_Hold';

                $modified_xml = $xml->asXML();

                $putResult = almaRequest($final_url, 'PUT', $api_key, $modified_xml);

                if (!$putResult['ok'] || $putResult['httpcode'] >= 400) {
                    echo '<p>Exception found: Return Item to Supervisor</p>';
                    echo '<pre>' . htmlspecialchars($putResult['response'] ?: $putResult['error']) . '</pre>';
                    echo '<br /><center><a class="btn btn-danger text-center" href="">Clear Form</a></center>';
                    exit;
                }

                echo '<h4>' . htmlspecialchars($title) . '</h4>';

                $formatted_note = preg_replace_callback(
                    '/(R\d{2})|(M\d{2})|(S\d{2})|(T\d{2})/',
                    function ($matches) {
                        if (!empty($matches[1])) {
                            return '<span class="text-primary">' . htmlspecialchars($matches[1]) . '</span>';
                        } elseif (!empty($matches[2])) {
                            return '<span class="text-success">' . htmlspecialchars($matches[2]) . '</span>';
                        } elseif (!empty($matches[3])) {
                            return '<span class="text-danger">' . htmlspecialchars($matches[3]) . '</span>';
                        } elseif (!empty($matches[4])) {
                            return '<span class="text-info">' . htmlspecialchars($matches[4]) . '</span>';
                        }
                        return '';
                    },
                    $internal_note_1
                );

                echo '<h4 class="badge text-center badge-light" style="font-size:1.4em;">Tray Barcode: ' . $formatted_note . '</h4>';
                echo '<p><i>Be sure to write down and include the tray barcode with the item.</i></p>';

                if ($process_type !== '') {
                    echo "<div class='alert alert-warning'>This item has a processing type of '" . htmlspecialchars($process_type) . "'.</div><div>";
                }

                echo "<div class='alert alert-success'><strong>Item is ready to be placed on Hold Shelf</strong></div><br /><br />";
                echo "<div>";
                echo '<a class="btn btn-primary mr-1" target="_blank" href="https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/' . rawurlencode($mms_id) . '/loans?apikey=' . urlencode($api_key) . '">View Check-In XML</a> ';
                echo '<a href="' . htmlspecialchars($final_url) . '" class="btn btn-primary" target="_blank">View Record XML</a></div>';

                $file = __DIR__ . '/refile.ndjson';

                if ($process_type !== '') {
                    $process_type_full = $status . ' - ' . $process_type;
                } else {
                    $process_type_full = $status;
                }

                $jsonDate = date('Y-m-d H:i:s');
                $jsonName = $name ?? '';
                $jsonBarcode = $barcode;
                $jsonTrayBarcode = $internal_note_1;
                $jsonStatus = $process_type_full;
                $jsonStep = '1';

                $newEntry = [
                    'date' => $jsonDate,
                    'name' => $jsonName,
                    'barcode' => $jsonBarcode,
                    'tray barcode' => $jsonTrayBarcode,
                    'status' => $jsonStatus,
                    'step' => $jsonStep,
                ];

                $jsonLine = json_encode($newEntry, JSON_UNESCAPED_SLASHES);
                if ($jsonLine === false) {
                    die('Error: JSON encoding failed. ' . json_last_error_msg());
                }

                if (file_put_contents($file, $jsonLine . "\n", FILE_APPEND | LOCK_EX) === false) {
                    die('Error: Unable to write to the JSON file.');
                }

                echo '<br /><center><a class="btn btn-danger text-center" href="">Clear Form</a></center>';
                echo '</div>';
            } else {
                if ($_SERVER["REQUEST_METHOD"] === "POST") {
                    echo "<p>No barcode submitted.</p>";
                }
                echo '</div>';
            }
            ?>

        </div>
    </div>
</div>

<?php include 'include/footer.php'; ?>

<script>
document.getElementById('dateForm').onsubmit = function() {
    document.getElementById('loadingSpinner').style.display = 'block';
};
</script>
</body>
</html>
