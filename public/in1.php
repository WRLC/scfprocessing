<?php
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['expire'])) {
    header('Location: login.php');
    exit;
}

if (time() > (int)$_SESSION['expire']) {
    session_destroy();
    header('Location: login.php');
    exit;
}

include 'header.php';

function h($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function almaRequest($url, $method = 'GET', $body = null, $headers = array())
{
    $ch = curl_init();

    if (!$ch) {
        return array('ok' => false, 'status' => 0, 'body' => '', 'error' => 'Unable to initialize cURL.');
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    if ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $responseBody = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($responseBody === false) {
        return array('ok' => false, 'status' => $status, 'body' => '', 'error' => $error);
    }

    return array(
        'ok' => ($status >= 200 && $status < 300),
        'status' => $status,
        'body' => $responseBody,
        'error' => ''
    );
}

function loadXmlFromResponse($response)
{
    if (!is_array($response) || empty($response['ok']) || trim((string)$response['body']) === '') {
        return false;
    }

    return @simplexml_load_string($response['body']);
}

function findItemXmlByBarcode($barcodeInput, $apiKey, $prefix)
{
    $barcodeInput = trim((string)$barcodeInput);

    if ($barcodeInput === '') {
        return array('ok' => false, 'url' => '', 'xml' => false);
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
        $url = $prefix . 'items?item_barcode=' . rawurlencode($candidateBarcode) . '&apikey=' . rawurlencode($apiKey);

        $response = almaRequest($url, 'GET', null, array('Accept: application/xml'));
        $xml = loadXmlFromResponse($response);

        if ($xml !== false && isset($xml->item_data->barcode)) {
            return array(
                'ok' => true,
                'url' => $url,
                'xml' => $xml
            );
        }
    }

    return array('ok' => false, 'url' => '', 'xml' => false);
}

function buildItemResourceUrl($prefix, $mmsId, $holdingId, $pid, $apiKey)
{
    return $prefix
        . 'bibs/' . rawurlencode($mmsId)
        . '/holdings/' . rawurlencode($holdingId)
        . '/items/' . rawurlencode($pid)
        . '?apikey=' . rawurlencode($apiKey);
}

$name = $_SESSION['user_id'] ?? '';
$submit = $_GET['submit'] ?? '';

$postedBarcode = trim((string)($_POST['barcode'] ?? ''));
$postedTrayBarcode = trim((string)($_POST['trayBarcode'] ?? ''));
?>

<div class="row">
    <div class="col s12 push-m3 m6">
        <div class="card white lighten-1" style="margin-top:20px;">
            <div class="card-content blue-text">
                <span class="card-title blue lighten-5 bold center">
                    Add Internal Note 1: <?php echo h($name); ?>
                </span>

                <p class="center">Add Internal Note 1 field with tray barcode</p>

                <div class="row">
                    <style>
                        #hideMe {
                            -moz-animation: cssAnimation 0s ease-in 3s forwards;
                            -webkit-animation: cssAnimation 0s ease-in 3s forwards;
                            -o-animation: cssAnimation 0s ease-in 3s forwards;
                            animation: cssAnimation 0s ease-in 3s forwards;
                            -webkit-animation-fill-mode: forwards;
                            animation-fill-mode: forwards;
                        }
                        @keyframes cssAnimation {
                            to {
                                width: 0;
                                height: 0;
                                overflow: hidden;
                            }
                        }
                        @-webkit-keyframes cssAnimation {
                            to {
                                width: 0;
                                height: 0;
                                visibility: hidden;
                            }
                        }
                    </style>

                    <?php if ($submit === 'true'): ?>
                        <div id="hideMe" class="card-title" style="color:#4CAF50;">Success!</div>
                    <?php elseif ($submit === 'false'): ?>
                        <div id="hideMe" class="card-title red-text">There was a problem updating the item.</div>
                    <?php endif; ?>

                    <form action="" method="POST" class="border p-4 bg-light">
                        <div class="form-group">
                            <label for="barcode">Enter Barcode:</label>
                            <input
                                type="text"
                                class="form-control"
                                id="barcode"
                                name="barcode"
                                value="<?php echo h($postedBarcode); ?>"
                                required
                            >
                            <span class="new badge white red-text right" data-badge-caption="Required"></span>
                        </div>

                        <div class="form-group">
                            <label for="trayBarcode">Enter Tray Barcode:</label>
                            <input
                                type="text"
                                class="form-control"
                                id="trayBarcode"
                                name="trayBarcode"
                                value="<?php echo h($postedTrayBarcode); ?>"
                                required
                            >
                            <span class="new badge white red-text right" data-badge-caption="Required"></span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Submit</button>
                    </form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barcode = trim((string)($_POST['barcode'] ?? ''));
    $trayBarcode = trim((string)($_POST['trayBarcode'] ?? ''));

    if ($barcode === '' || $trayBarcode === '') {
        echo '<p>No barcode submitted.</p>';
    } else {
        $prefix = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/';
        include 'include/apikey.php';

        $itemLookup = findItemXmlByBarcode($barcode, $api_key, $prefix);

        if (!$itemLookup['ok'] || $itemLookup['xml'] === false) {
            echo '<p class="red-text">Failed to retrieve item record for barcode ' . h($barcode) . '.</p>';
        } else {
            $xml = $itemLookup['xml'];

            $mms_id = isset($xml->bib_data->mms_id) ? (string)$xml->bib_data->mms_id : '';
            $holding_id = isset($xml->holding_data->holding_id) ? (string)$xml->holding_data->holding_id : '';
            $pid = isset($xml->item_data->pid) ? (string)$xml->item_data->pid : '';

            if ($mms_id === '' || $holding_id === '' || $pid === '') {
                echo '<p class="red-text">Item record is missing MMS ID, holding ID, or PID.</p>';
            } else {
                $item_url = buildItemResourceUrl($prefix, $mms_id, $holding_id, $pid, $api_key);

                $getResponse = almaRequest($item_url, 'GET', null, array('Accept: application/xml'));
                $itemXml = loadXmlFromResponse($getResponse);

                if ($itemXml === false) {
                    echo '<p class="red-text">Failed to load item XML.</p>';
                } else {
                    $itemXml->item_data->internal_note_1 = $trayBarcode;

                    $modifiedXml = $itemXml->asXML();

                    $putResponse = almaRequest(
                        $item_url,
                        'PUT',
                        $modifiedXml,
                        array(
                            'Content-Type: application/xml',
                            'Authorization: apikey ' . $api_key,
                            'Accept: application/xml'
                        )
                    );

                    if (!$putResponse['ok']) {
                        echo '<p>Exception found: Return Item to Supervisor</p>';
                        echo '<pre>' . h($putResponse['body'] ?: $putResponse['error']) . '</pre>';
                    } else {
                        $updatedLookup = findItemXmlByBarcode($barcode, $api_key, $prefix);

                        if (!$updatedLookup['ok'] || $updatedLookup['xml'] === false) {
                            echo '<div class="alert alert-danger mt-3">Item updated, but failed to reload updated XML.</div>';
                        } else {
                            $xml = $updatedLookup['xml'];

                            $title = isset($xml->bib_data->title) ? (string)$xml->bib_data->title : '';
                            $item_barcode = isset($xml->item_data->barcode) ? (string)$xml->item_data->barcode : '';
                            $internalNote3 = isset($xml->item_data->internal_note_3) ? (string)$xml->item_data->internal_note_3 : '';
                            $internalNote1 = isset($xml->item_data->internal_note_1) ? (string)$xml->item_data->internal_note_1 : '';
                            $alternative_call_number = isset($xml->item_data->alternative_call_number) ? (string)$xml->item_data->alternative_call_number : '';
                            $in_temp_location = isset($xml->holding_data->in_temp_location) ? (string)$xml->holding_data->in_temp_location : '';
                            $temp_library = isset($xml->holding_data->temp_library) ? (string)$xml->holding_data->temp_library : '';
                            $temp_location = isset($xml->holding_data->temp_location) ? (string)$xml->holding_data->temp_location : '';
                            $provenance = isset($xml->item_data->provenance) ? (string)$xml->item_data->provenance : '';
                            $provenance_desc = isset($xml->item_data->provenance['desc']) ? (string)$xml->item_data->provenance['desc'] : '';
                            $mms_id = isset($xml->bib_data->mms_id) ? (string)$xml->bib_data->mms_id : '';

                            echo "<br>
                                <div class='card black-text green lighten-5'>
                                    <div class='card-title card-content text-white center'>
                                        Details for Barcode: " . h($item_barcode) . "
                                        <p><span class='center'>Item has been processed.</span></p>
                                    </div>
                                    <div class='card-content'>";

                            echo '<h6 class="center teal lighten-2 white-text" style="padding:10px;">Item Information</h6>';
                            echo "<ul class='list-group'>";
                            echo "<li class='list-group-item'><strong>Title:</strong> " . h($title) . "</li>";
                            echo "<li class='list-group-item'><strong>Barcode:</strong> " . h($item_barcode) . "</li>";
                            echo "<li class='list-group-item'><strong>Tray Barcode (ICN):</strong> " . h($alternative_call_number) . "</li>";
                            echo "<li class='list-group-item'><strong>Tray Barcode (IN1):</strong> " . h($internalNote1) . "</li>";
                            echo "<li class='list-group-item'><strong>Owning Library:</strong> " . h($provenance_desc) . " (" . h($provenance) . ")</li>";
                            echo "<li class='list-group-item'><strong>MMS ID:</strong> " . h($mms_id) . "</li>";
                            echo "</ul>";

                            echo '<br><h6 class="center teal lighten-2 white-text" style="padding:10px;">Loan Information</h6>';
                            echo "<ul class='list-group'>";
                            echo "<li class='list-group-item'><strong>Internal Note 3:</strong> " . h($internalNote3) . "</li>";
                            echo "<li class='list-group-item'><strong>In Temp Location:</strong> " . h($in_temp_location) . "</li>";
                            echo "<li class='list-group-item'><strong>Temp Location:</strong> " . h($temp_location) . "</li>";
                            echo "<li class='list-group-item'><strong>Temp Library:</strong> " . h($temp_library) . "</li>";

                            $loan_url = $prefix . 'bibs/' . rawurlencode($mms_id) . '/loans?apikey=' . rawurlencode($api_key);
                            $loanResponse = almaRequest($loan_url, 'GET', null, array('Accept: application/xml'));
                            $loanXml = loadXmlFromResponse($loanResponse);

                            if ($loanXml === false) {
                                echo "<li class='list-group-item text-danger'>Failed to parse loan XML response.</li>";
                            } else {
                                $total_record_count = isset($loanXml['total_record_count']) ? (int)$loanXml['total_record_count'] : 0;

                                if ($total_record_count === 0) {
                                    echo '<li class="list-group-item"><strong>Status:</strong> <span class="text-success">Item Checked In</span></li>';
                                } else {
                                    $item_loan = isset($loanXml->item_loan) ? $loanXml->item_loan : null;

                                    if ($item_loan) {
                                        $due_date = isset($item_loan->due_date) ? (string)$item_loan->due_date : '';
                                        $loan_status = isset($item_loan->loan_status) ? (string)$item_loan->loan_status : '';
                                        $process_status = isset($item_loan->process_status) ? (string)$item_loan->process_status : '';
                                        $user_id = isset($item_loan->user_id) ? (string)$item_loan->user_id : '';

                                        echo "<li class='list-group-item'><strong>Loan Status:</strong> " . h($loan_status) . "</li>";
                                        echo "<li class='list-group-item'><strong>User ID:</strong> " . h($user_id) . "</li>";
                                        echo "<li class='list-group-item'><strong>Due Date:</strong> " . h($due_date) . "</li>";
                                        echo "<li class='list-group-item'><strong>Process Status:</strong> " . h($process_status) . "</li>";
                                    } else {
                                        echo "<li class='list-group-item'>No item_loan found in the XML response.</li>";
                                    }
                                }
                            }

                            echo "</ul>";
                            echo "<br>";
                            echo "<a class='btn btn-info btn-sm' style='width:60px;' target='_blank' href='" . h($item_url) . "'>XML</a>";
                            echo "</div></div>";
                        }

                        echo '<br><center><a class="btn btn-danger red center" href="">Clear Form</a></center><br><br>';
                    }
                }
            }
        }
    }
}
?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>