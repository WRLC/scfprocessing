<?php include 'include/access.php'; ?>
<?php
function h($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function almaRequest($url, $method = 'GET', $body = null, $headers = array(), $timeout = 12, $connectTimeout = 5, $retries = 2, $retryDelayMs = 400)
{
    $attempt = 0;
    $lastError = '';
    $lastStatus = 0;
    $lastBody = '';

    while ($attempt <= $retries) {
        $ch = curl_init();

        if (!$ch) {
            return array(
                'ok' => false,
                'status' => 0,
                'body' => '',
                'error' => 'Unable to initialize cURL.'
            );
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
        }

        if (!empty($headers)) {
            $cleanHeaders = array();
            foreach ($headers as $header) {
                if (is_string($header) && $header !== '') {
                    $cleanHeaders[] = $header;
                }
            }
            if (!empty($cleanHeaders)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $cleanHeaders);
            }
        }

        $responseBody = curl_exec($ch);
        $lastError = curl_error($ch);
        $lastStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $lastBody = ($responseBody !== false) ? $responseBody : '';

        curl_close($ch);

        if ($responseBody !== false && $lastStatus >= 200 && $lastStatus < 300) {
            return array(
                'ok' => true,
                'status' => $lastStatus,
                'body' => $responseBody,
                'error' => ''
            );
        }

        $shouldRetry = false;

        if ($responseBody === false) {
            $shouldRetry = true;
        } elseif (in_array($lastStatus, array(408, 429, 500, 502, 503, 504), true)) {
            $shouldRetry = true;
        }

        if (!$shouldRetry || $attempt === $retries) {
            break;
        }

        usleep($retryDelayMs * 1000);
        $retryDelayMs *= 2;
        $attempt++;
    }

    return array(
        'ok' => false,
        'status' => $lastStatus,
        'body' => $lastBody,
        'error' => $lastError !== '' ? $lastError : 'Request failed.'
    );
}

function loadXmlFromResponse($response)
{
    if (!is_array($response) || empty($response['ok']) || trim((string) $response['body']) === '') {
        return false;
    }

    return @simplexml_load_string($response['body']);
}

function findItemXmlByBarcode($barcodeInput, $apiKey, $prefix)
{
    $barcodeInput = trim((string) $barcodeInput);

    if ($barcodeInput === '') {
        return array(
            'ok' => false,
            'lookup_url' => '',
            'xml' => false,
            'response' => null,
            'error' => 'Blank barcode.'
        );
    }

    $candidateBarcodes = array();

    $barcodeWithX = $barcodeInput;
    if (substr($barcodeWithX, -1) !== 'X') {
        $barcodeWithX .= 'X';
    }
    $candidateBarcodes[] = $barcodeWithX;

    if (strtolower(substr($barcodeInput, 0, 1)) === 'p' && strlen($barcodeInput) === 6) {
        $candidateBarcodes[] = $barcodeInput . 'X';
    }

    $barcodeWithoutX = str_replace('X', '', $barcodeWithX);
    if ($barcodeWithoutX !== '') {
        $candidateBarcodes[] = $barcodeWithoutX;
    }

    $candidateBarcodes = array_values(array_unique($candidateBarcodes));

    foreach ($candidateBarcodes as $candidateBarcode) {
        $lookupUrl = $prefix . 'items?item_barcode=' . rawurlencode($candidateBarcode) . '&apikey=' . rawurlencode($apiKey);

        $response = almaRequest(
            $lookupUrl,
            'GET',
            null,
            array('Accept: application/xml'),
            12,
            5,
            2,
            400
        );

        $xml = loadXmlFromResponse($response);

        if ($xml !== false && isset($xml->item_data->barcode)) {
            return array(
                'ok' => true,
                'lookup_url' => $lookupUrl,
                'xml' => $xml,
                'response' => $response,
                'error' => ''
            );
        }
    }

    return array(
        'ok' => false,
        'lookup_url' => '',
        'xml' => false,
        'response' => null,
        'error' => 'Item record does not exist.'
    );
}

function buildItemResourceUrl($prefix, $mmsId, $holdingId, $pid, $apiKey)
{
    return $prefix
        . 'bibs/' . rawurlencode($mmsId)
        . '/holdings/' . rawurlencode($holdingId)
        . '/items/' . rawurlencode($pid)
        . '?apikey=' . rawurlencode($apiKey);
}
?>
<!DOCTYPE html>
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

            <h2 class="text-center">Step 1: Check-in Returns and place on Hold Shelf - <?php echo h(isset($name) ? $name : ''); ?></h2>

            <div class="card-header bg-primary text-white">
                <h4>Barcode Entry</h4>
            </div>

            <form action="" method="POST" id="dateForm" class="border p-4 bg-light">
                <div class="form-group">
                    <label for="barcode">Item Barcode:</label>
                    <?php $itembarcode = isset($_GET['barcode']) ? trim((string) $_GET['barcode']) : ''; ?>
                    <input type="text" class="form-control" id="barcode" name="barcode" value="<?php echo h($itembarcode); ?>" required>
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['barcode'])) {
    $barcode = trim((string) $_POST['barcode']);

    if (substr($barcode, -1) !== 'X') {
        $barcode .= 'X';
    }

    if (strtolower(substr($barcode, 0, 1)) === 'p' && strlen($barcode) === 6) {
        $barcode .= 'X';
    }

    $library = 'SCF';
    $circ_desk = 'DEFAULT_CIRC_DESK';
    $prefix = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/';

    include 'include/apikey.php';

    $itemLookup = findItemXmlByBarcode($barcode, $api_key, $prefix);

    if (!$itemLookup['ok'] || $itemLookup['xml'] === false) {
        echo "<div class='alert alert-danger mt-3 text-center'>Item record does not exist</div>";
    } else {
        $lookup_url = $itemLookup['lookup_url'];
        $httpcode = (int) $itemLookup['response']['status'];
        $xml = $itemLookup['xml'];

        $title = isset($xml->bib_data->title) ? (string) $xml->bib_data->title : '';
        $internalNote3 = isset($xml->item_data->internal_note_3) ? (string) $xml->item_data->internal_note_3 : '';
        $internalNote1 = isset($xml->item_data->internal_note_1) ? (string) $xml->item_data->internal_note_1 : '';
        $acn = isset($xml->item_data->alternative_call_number) ? (string) $xml->item_data->alternative_call_number : '';
        $holding_id = isset($xml->holding_data->holding_id) ? (string) $xml->holding_data->holding_id : '';
        $pid = isset($xml->item_data->pid) ? (string) $xml->item_data->pid : '';
        $mms_id = isset($xml->bib_data->mms_id) ? (string) $xml->bib_data->mms_id : '';
        $process_type = isset($xml->item_data->process_type) ? (string) $xml->item_data->process_type : '';
        $item_url = buildItemResourceUrl($prefix, $mms_id, $holding_id, $pid, $api_key);

        if (!empty($internalNote3) && $internalNote3 !== 'SCF Hold Shelf') {
            echo "<br><div class='alert alert-danger text-center'>
                <h4 class='mb-3'>Barcode " . h($barcode) . "</h4>
                Internal note 3 message: " . h($internalNote3) . "<br><br>
                <span class='font-italic'>This item has not been checked in. Please give it to your supervisor.</span>
            </div>";
        } else {
            $loan_url = $prefix . 'bibs/' . rawurlencode($mms_id) . '/holdings/' . rawurlencode($holding_id) . '/items/' . rawurlencode($pid) . '/loans?apikey=' . rawurlencode($api_key);

            $loanResponse = almaRequest(
                $loan_url,
                'GET',
                null,
                array('Accept: application/xml'),
                15,
                5,
                3,
                700
            );

            $loan_xml = loadXmlFromResponse($loanResponse);
            $total_record_count = 0;
            $due_date = '';
            $process_status = '';
            $user_id = '';
            $alreadyCheckedIn = false;
            $scanSucceeded = false;
            $status = "Item Not In Place";

            if ($loan_xml === false) {
                echo "<div class='alert alert-danger mt-3'>Loan lookup failed: " . h($loanResponse['error']) . "</div>";
            } else {
                $total_record_count = isset($loan_xml['total_record_count']) ? (int) $loan_xml['total_record_count'] : 0;

                if ($total_record_count === 0) {
                    $alreadyCheckedIn = true;
                    echo '<div class="alert alert-danger text-center mt-3"><strong><h3>Item Already Checked In.</h3><p>Please give it to your supervisor for review.</p></strong></div>';
                } else {
                    $item_loan = isset($loan_xml->item_loan) ? $loan_xml->item_loan : null;

                    if ($item_loan) {
                        $due_date = isset($item_loan->due_date) ? (string) $item_loan->due_date : '';
                        $process_status = isset($item_loan->process_status) ? (string) $item_loan->process_status : '';
                        $user_id = isset($item_loan->user_id) ? (string) $item_loan->user_id : '';

                        $deliverToMapping = array(
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
                            "01WRLC_SCF-Sheehan Library" => "TR"
                        );

                        if (isset($deliverToMapping[$user_id])) {
                            echo "<div class='alert alert-warning text-center mt-3' style='color:#000;'><strong>Patron: Deliver To: " . h($deliverToMapping[$user_id]) . "</strong></div>";
                        } else {
                            if ($user_id !== '') {
                                echo "<div class='alert alert-warning text-center mt-3' style='color:#000;'><strong>Patron: " . h($user_id) . ".</strong></div>";
                            } else {
                                echo '<div class="alert alert-danger text-center font-italics mt-3" style="color:#000;"><strong>Patron field is blank. Set Item aside for Supervisor.</strong></div>';
                            }
                        }
                    }
                }

                if ($httpcode !== 200) {
                    echo "<p class='alert alert-danger'>Barcode error. Please check with Supervisor.</p>";
                } else {
                    if (!$alreadyCheckedIn) {
                        $scan_in_url = $item_url
                            . '&op=scan'
                            . '&library=' . urlencode($library)
                            . '&circ_desk=' . urlencode($circ_desk);

                        $scanResponse = almaRequest(
                            $scan_in_url,
                            'POST',
                            '',
                            array(
                                'Content-Type: application/xml',
                                'Authorization: apikey ' . $api_key,
                                'Accept: application/xml'
                            ),
                            15,
                            5,
                            2,
                            500
                        );

                        $post_response = $scanResponse['body'];
                        $post_httpcode = (int) $scanResponse['status'];

                        if ($post_httpcode === 200) {
                            $status = "Item In Place";
                            $scanSucceeded = true;
                        } else {
                            $status = "Item Not In Place";
                            echo "<div class='alert alert-info'><h5>Scan-in API Response:</h5>";
                            echo "<pre>HTTP Status Code: " . h($post_httpcode) . "</pre>";
                            echo "<pre>" . h($post_response) . "</pre>";
                        }
                    }

                    if (empty($internalNote1) && empty($acn)) {
                        echo "<br><div class='alert alert-warning text-center'>
                            <h4 class='mb-3'>Barcode " . h($barcode) . "</h4>
                            <h5>Possible New Book - Additional Processing Needed.</h5>
                            <span class='font-italic'>This item has been checked in but do not place on hold shelf.</span>
                        </div>";
                    } else {
                        $getItemResponse = almaRequest(
                            $item_url,
                            'GET',
                            null,
                            array('Accept: application/xml'),
                            12,
                            5,
                            2,
                            400
                        );

                        $itemXml = loadXmlFromResponse($getItemResponse);

                        if ($itemXml === false) {
                            echo '<p>Exception found: Return Item to Supervisor</p>';
                        } else {
                            $displayTitle = isset($itemXml->bib_data->title) ? (string) $itemXml->bib_data->title : $title;
                            $displayInternalNote1 = isset($itemXml->item_data->internal_note_1) ? (string) $itemXml->item_data->internal_note_1 : $internalNote1;
                            $displayMmsId = isset($itemXml->bib_data->mms_id) ? (string) $itemXml->bib_data->mms_id : $mms_id;
                            $displayProcessType = isset($itemXml->item_data->process_type) ? (string) $itemXml->item_data->process_type : $process_type;

                            if (!$alreadyCheckedIn && $scanSucceeded) {
                                $itemXml->item_data->internal_note_3 = 'SCF Hold Shelf';
                                $itemXml->holding_data->in_temp_location = 'true';
                                $itemXml->holding_data->temp_library = 'SCF';
                                $itemXml->holding_data->temp_location = 'SCF_Hold';

                                $modified_xml = $itemXml->asXML();

                                $putResponse = almaRequest(
                                    $item_url,
                                    'PUT',
                                    $modified_xml,
                                    array(
                                        'Content-Type: application/xml',
                                        'Authorization: apikey ' . $api_key,
                                        'Accept: application/xml'
                                    ),
                                    15,
                                    5,
                                    2,
                                    500
                                );

                                if (!$putResponse['ok']) {
                                    echo '<p>Exception found: Return Item to Supervisor</p><p>' . h($putResponse['body']) . '</p>';
                                }
                            }

                            echo '<div class="alert alert-primary text-center">';

                            if ($alreadyCheckedIn) {
                                echo '<h4>Barcode ' . h($barcode) . ' was already checked in.</h4>';
                            } elseif ($scanSucceeded) {
                                echo '<h4>Barcode ' . h($barcode) . ' has been checked in.</h4>';
                            }

                            echo '<h4>' . h($displayTitle) . '</h4>';

                            $formatted_note = preg_replace_callback(
                                '/(R\d{2})|(M\d{2})|(S\d{2})|(T\d{2})/',
                                function ($matches) {
                                    if (!empty($matches[1])) {
                                        return '<span class="text-primary">' . $matches[1] . '</span>';
                                    } elseif (!empty($matches[2])) {
                                        return '<span class="text-success">' . $matches[2] . '</span>';
                                    } elseif (!empty($matches[3])) {
                                        return '<span class="text-danger">' . $matches[3] . '</span>';
                                    } elseif (!empty($matches[4])) {
                                        return '<span class="text-info">' . $matches[4] . '</span>';
                                    }
                                    return '';
                                },
                                $displayInternalNote1
                            );

                            echo '<h4 class="badge text-center badge-light" style="font-size:1.4em;">Tray Barcode: ' . $formatted_note . '</h4>';
                            echo '<p><i>Be sure to write down and include the tray barcode with the item.</i></p>';

                            if ($displayProcessType !== '') {
                                echo "<div class='alert alert-warning'>This item has a processing type of '" . h($displayProcessType) . "'.</div>";
                            }

                            if ($alreadyCheckedIn) {
                                echo "<div class='alert alert-warning'><strong>Item was already checked in. Review before placing on Hold Shelf.</strong></div>";
                            } else {
                                echo "<div class='alert alert-success'><strong>Item is ready to be placed on Hold Shelf</strong></div>";
                            }

                            echo "<br><br>";
                            echo '<a class="btn btn-primary mr-1" target="_blank" href="https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/' . rawurlencode($displayMmsId) . '/loans?apikey=' . rawurlencode($api_key) . '">View Check-In XML</a>';
                            echo '<a href="' . h($item_url) . '" class="btn btn-primary" target="_blank">View Record XML</a>';
                            echo '<br><br><center><a class="btn btn-danger text-center" href="">Clear Form</a></center>';
                            echo '</div>';

                            if (!$alreadyCheckedIn && $scanSucceeded) {
                                $file = __DIR__ . '/refile.ndjson';

                                if ($displayProcessType !== '') {
                                    $process_type_full = $status . ' - ' . $displayProcessType;
                                } else {
                                    $process_type_full = $status;
                                }

                                $jsonDate = date('Y-m-d H:i:s');
                                $jsonName = isset($name) ? $name : '';
                                $jsonBarcode = $barcode;
                                $jsonTrayBarcode = $displayInternalNote1;
                                $jsonStatus = $process_type_full;
                                $jsonStep = '1';

                                $newEntry = array(
                                    'date' => $jsonDate,
                                    'name' => $jsonName,
                                    'barcode' => $jsonBarcode,
                                    'tray barcode' => $jsonTrayBarcode,
                                    'status' => $jsonStatus,
                                    'step' => $jsonStep
                                );

                                $jsonLine = json_encode($newEntry, JSON_UNESCAPED_SLASHES);
                                if ($jsonLine === false) {
                                    die('Error: JSON encoding failed. ' . json_last_error_msg());
                                }

                                if (file_put_contents($file, $jsonLine . "\n", FILE_APPEND | LOCK_EX) === false) {
                                    die('Error: Unable to write to the JSON file.');
                                }
                            }
                        }
                    }

                    echo '</div>';
                }
            }
        }
    }
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
var dateForm = document.getElementById('dateForm');
if (dateForm) {
    dateForm.onsubmit = function() {
        document.getElementById('loadingSpinner').style.display = 'block';
    };
}
</script>
</body>
</html>