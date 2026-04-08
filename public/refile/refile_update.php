<?php
include 'include/access.php';
include 'include/apikey.php';

function h($value)
{
    return htmlspecialchars((string)(isset($value) ? $value : ''), ENT_QUOTES, 'UTF-8');
}

function almaRequest($url, $method = 'GET', $body = null, $headers = array())
{
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    } elseif ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $responseBody = curl_exec($ch);
    $curlError = curl_error($ch);
    $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($responseBody === false) {
        return array(
            'ok' => false,
            'status' => $statusCode,
            'body' => '',
            'error' => $curlError !== '' ? $curlError : 'Unknown cURL error.'
        );
    }

    return array(
        'ok' => ($statusCode >= 200 && $statusCode < 300),
        'status' => $statusCode,
        'body' => $responseBody,
        'error' => ''
    );
}

function loadXmlFromResponse($response)
{
    if (!is_array($response) || empty($response['ok']) || trim($response['body']) === '') {
        return false;
    }

    return @simplexml_load_string($response['body']);
}

function findItemXmlByBarcode($barcodeInput, $apiKey)
{
    $candidateBarcodes = array();

    $barcodeInput = trim((string)$barcodeInput);

    if ($barcodeInput === '') {
        return array(
            'ok' => false,
            'url' => '',
            'xml' => false,
            'error' => 'Blank barcode.'
        );
    }

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
        $url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/items?item_barcode='
            . rawurlencode($candidateBarcode)
            . '&apikey=' . rawurlencode($apiKey);

        $response = almaRequest($url, 'GET');
        $xml = loadXmlFromResponse($response);

        if ($xml !== false && isset($xml->item_data->barcode)) {
            return array(
                'ok' => true,
                'url' => $url,
                'xml' => $xml,
                'error' => ''
            );
        }
    }

    return array(
        'ok' => false,
        'url' => '',
        'xml' => false,
        'error' => 'Item record does not exist.'
    );
}

$barcodeValue = isset($_POST['barcode']) ? trim($_POST['barcode']) : (isset($_GET['barcode']) ? trim($_GET['barcode']) : '');
$trayBarcodeValue = isset($_POST['trayBarcode']) ? trim($_POST['trayBarcode']) : (isset($_GET['trayBarcode']) ? trim($_GET['trayBarcode']) : '');

$rows = array();
$name = '';

if (isset($_SESSION['user_id'])) {
    $name = $_SESSION['user_id'];
} elseif (isset($GLOBALS['name'])) {
    $name = $GLOBALS['name'];
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Information</title>
    <?php include 'include/refresh.php'; ?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'include/nav.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h2 class="text-center">Step 2: Tray verification and reshelving in SCF</h2>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Barcode Entry</h4>
                    </div>
                    <div class="card-body bg-light">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="barcode">Item Barcode:</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="barcode"
                                    name="barcode"
                                    value="<?php echo h($barcodeValue); ?>"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="trayBarcode">Tray Barcode:</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="trayBarcode"
                                    name="trayBarcode"
                                    value="<?php echo h($trayBarcodeValue); ?>"
                                    required>
                            </div>
                            <button type="submit" class="btn btn-primary">Preview</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barcode']) && isset($_POST['trayBarcode']) && !isset($_POST['proceed'])) {
    $trayBarcode = trim((string)$_POST['trayBarcode']);
    $barcode = trim((string)$_POST['barcode']);
    $trayBarcode = substr($trayBarcode, 0, 12);

    echo '<div class="row mt-3">
            <div class="col-md-12">
            <h2>Item Details</h2>
            <table class="table table-striped table-bordered">
            <thead class="thead-dark"><tr>
                <th>Tray Barcode</th>
                <th>Title</th>
                <th>Item Barcode</th>
                <th>Internal Note 3</th>
                <th>Status</th>
              </tr></thead><tbody>';

    $itemLookup = findItemXmlByBarcode($barcode, $api_key);

    if (!$itemLookup['ok'] || $itemLookup['xml'] === false) {
        echo '</tbody></table></div></div>';
        echo "<div class='alert alert-danger mt-3 text-center'>Item record does not exist</div>";
    } else {
        $xml = $itemLookup['xml'];
        $url = $itemLookup['url'];

        $title = isset($xml->bib_data->title) ? (string)$xml->bib_data->title : '';
        $item_barcode = isset($xml->item_data->barcode) ? (string)$xml->item_data->barcode : '';
        $internalNote1 = isset($xml->item_data->internal_note_1) ? (string)$xml->item_data->internal_note_1 : '';
        $internalNote1 = substr($internalNote1, 0, 12);
        $internalNote3 = isset($xml->item_data->internal_note_3) ? (string)$xml->item_data->internal_note_3 : '';
        $mms_id = isset($xml->bib_data->mms_id) ? (string)$xml->bib_data->mms_id : '';
        $holding_id = isset($xml->holding_data->holding_id) ? (string)$xml->holding_data->holding_id : '';
        $pid = isset($xml->item_data->pid) ? (string)$xml->item_data->pid : '';

        $loan_url = '';
        $total_record_count = 0;
        $status = '';
        $due_date = '';
        $process_status = '';

        if ($mms_id !== '' && $holding_id !== '' && $pid !== '') {
            $loan_url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/'
                . rawurlencode($mms_id)
                . '/holdings/' . rawurlencode($holding_id)
                . '/items/' . rawurlencode($pid)
                . '/loans?apikey=' . rawurlencode($api_key);

            $loanResponse = almaRequest($loan_url, 'GET');
            $loan_xml = loadXmlFromResponse($loanResponse);

            if ($loan_xml === false) {
                $status = '<span class="text-warning">Loan status unavailable</span>';
            } else {
                $total_record_count = isset($loan_xml['total_record_count']) ? (int)$loan_xml['total_record_count'] : 0;

                if ($total_record_count === 0) {
                    $status = '<span class="text-success">Item Checked In</span>';
                } else {
                    $item_loan = isset($loan_xml->item_loan) ? $loan_xml->item_loan : null;

                    if ($item_loan) {
                        $status = '<span class="text-danger">Checked Out</span>';
                        $due_date = isset($item_loan->due_date) ? (string)$item_loan->due_date : '';
                        $process_status = isset($item_loan->process_status) ? (string)$item_loan->process_status : '';
                    } else {
                        $status = '<span class="text-warning">Loan status unavailable</span>';
                    }
                }
            }
        } else {
            $status = '<span class="text-warning">Loan status unavailable</span>';
        }

        $rows[] = array(
            'trayBarcode' => $trayBarcode,
            'title' => $title,
            'item_barcode' => $item_barcode,
            'internalNote1' => $internalNote1,
            'internalNote3' => $internalNote3,
            'mms_id' => $mms_id,
            'holding_id' => $holding_id,
            'pid' => $pid,
        );

        echo "<tr>";

        if ($trayBarcode === $internalNote1) {
            echo '<td><span class="text-success">' . h($trayBarcode) . ' - Match</span></td>';
        } else {
            if (!empty($trayBarcode) && !empty($internalNote1)) {
                $barcodeFile = $trayBarcode;
                $barcodeAlma = $internalNote1;
                $itembarcode = $item_barcode;

                $google_form_url = "https://docs.google.com/forms/u/0/d/e/1FAIpQLSfdqhD8VPq8X13niOSL-y7146PkmYtzJW0v7U-Sr94EmJOtyA/formResponse";

                $form_data = array(
                    "entry.1671538415" => $barcodeFile,
                    "entry.1478552555" => $barcodeAlma,
                    "entry.860961451" => $itembarcode,
                );

                $googleResponse = almaRequest(
                    $google_form_url,
                    'POST',
                    http_build_query($form_data)
                );

                echo '<td class="table-danger"><span class="text-danger">'
                    . h($trayBarcode) . ' (from File) - '
                    . h($internalNote1) . ' (from Alma) - Does Not Match. <br />'
                    . '<a class="btn btn-danger" href="https://docs.google.com/spreadsheets/d/1bieQ2wsjb1ptVt49QEea94mmUVcoDbnONzJi4xXXOo4/edit?resourcekey=&gid=729588841#gid=729588841">Mismatch Recorded</a>'
                    . '</span></td>';
            } else {
                $missing = array();

                if (empty($trayBarcode)) {
                    $missing[] = 'trayBarcode (from File)';
                }

                if (empty($internalNote1)) {
                    $missing[] = 'internalNote1 (from Alma)';
                }

                $missing_message = implode(' and ', $missing) . ' missing';

                $barcodeFile = !empty($trayBarcode) ? $trayBarcode : 'MISSING';
                $barcodeAlma = !empty($internalNote1) ? $internalNote1 : 'MISSING';
                $itembarcode = $item_barcode;

                $google_form_url = "https://docs.google.com/forms/u/0/d/e/1FAIpQLSfdqhD8VPq8X13niOSL-y7146PkmYtzJW0v7U-Sr94EmJOtyA/formResponse";

                $form_data = array(
                    "entry.1671538415" => $barcodeFile,
                    "entry.1478552555" => $barcodeAlma,
                    "entry.860961451" => $itembarcode,
                );

                $googleResponse = almaRequest(
                    $google_form_url,
                    'POST',
                    http_build_query($form_data)
                );

                echo '<td class="table-warning"><span class="text-warning">Error: '
                    . h($missing_message)
                    . '. <br /><a class="btn btn-warning" href="https://docs.google.com/spreadsheets/d/1bieQ2wsjb1ptVt49QEea94mmUVcoDbnONzJi4xXXOo4/edit?resourcekey=&gid=729588841#gid=729588841">Missing Data Recorded</a></span></td>';
            }
        }

        echo "<td>" . h($title) . "</td>";
        echo "<td>" . h($item_barcode) . "</td>";
        echo "<td>" . h($internalNote3) . "</td>";

        if ($total_record_count !== 0) {
            echo "<td class='table-danger'>" . $status . "<br /><small>Due: " . h($due_date) . "<br />Processing Data: " . h($process_status) . "</small></td>";
        } else {
            echo "<td>" . $status . "</td>";
        }

        echo "</tr>";
        echo '</tbody></table>';
        echo '</div></div>';

        if (!empty($rows)) {
            echo '<form method="POST" action="">';
            echo '<input type="hidden" name="mms_id" value="' . h($mms_id) . '">';
            echo '<input type="hidden" name="holding_id" value="' . h($holding_id) . '">';
            echo '<input type="hidden" name="pid" value="' . h($pid) . '">';
            echo '<input type="hidden" name="barcode" value="' . h($item_barcode) . '">';
            echo '<button type="submit" name="proceed" class="btn btn-primary">Proceed</button> <a class="btn btn-danger" href="">Clear</a>';
            echo '</form>';
        }
    }
}

if (isset($_POST['proceed'])) {
    $mms_id = isset($_POST['mms_id']) ? trim($_POST['mms_id']) : '';
    $holding_id = isset($_POST['holding_id']) ? trim($_POST['holding_id']) : '';
    $pid = isset($_POST['pid']) ? trim($_POST['pid']) : '';
    $barcode = isset($_POST['barcode']) ? trim($_POST['barcode']) : '';

    if ($mms_id === '' || $holding_id === '' || $pid === '' || $barcode === '') {
        echo "<div class='alert alert-danger mt-3 text-center'>Missing data required to proceed.</div>";
    } else {
        $getUrl = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/"
            . rawurlencode($mms_id)
            . "/holdings/" . rawurlencode($holding_id)
            . "/items/" . rawurlencode($pid)
            . "?apikey=" . rawurlencode($api_key);

        $getResponse = almaRequest($getUrl, 'GET');
        $xml = loadXmlFromResponse($getResponse);

        if ($xml === false) {
            echo "<div class='alert alert-danger mt-3 text-center'>Unable to retrieve item record for update.</div>";
        } else {
            if (isset($xml->item_data->internal_note_3)) {
                $xml->item_data->internal_note_3 = '';
            }

            if (isset($xml->holding_data->in_temp_location)) {
                $xml->holding_data->in_temp_location = 'false';
            }

            if (isset($xml->holding_data->temp_library)) {
                $xml->holding_data->temp_library = '';
            }

            if (isset($xml->holding_data->temp_location)) {
                $xml->holding_data->temp_location = '';
            }

            $xmlString = $xml->asXML();

            $putResponse = almaRequest(
                $getUrl,
                'PUT',
                $xmlString,
                array('Content-Type: application/xml')
            );

            if (!$putResponse['ok']) {
                echo "<div class='alert alert-danger mt-3 text-center'>Update failed";
                if (!empty($putResponse['status'])) {
                    echo " (HTTP " . (int)$putResponse['status'] . ")";
                }
                echo ".</div>";
            } else {
                echo '<div class="row mt-3">';
                echo '<div class="col-md-12">';
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

                $updatedLookup = findItemXmlByBarcode($barcode, $api_key);

                if (!$updatedLookup['ok'] || $updatedLookup['xml'] === false) {
                    echo "<tr><td colspan='6' class='table-danger'>Unable to reload updated item data.</td></tr>";
                } else {
                    $xml = $updatedLookup['xml'];

                    $title = isset($xml->bib_data->title) ? (string)$xml->bib_data->title : '';
                    $item_barcode = isset($xml->item_data->barcode) ? (string)$xml->item_data->barcode : '';
                    $internalNote1 = isset($xml->item_data->internal_note_1) ? (string)$xml->item_data->internal_note_1 : '';
                    $internalNote1 = substr($internalNote1, 0, 12);
                    $process_type = isset($xml->item_data->process_type) ? (string)$xml->item_data->process_type : '';
                    $internalNote3 = isset($xml->item_data->internal_note_3) ? (string)$xml->item_data->internal_note_3 : '';
                    $mms_id = isset($xml->bib_data->mms_id) ? (string)$xml->bib_data->mms_id : '';

                    $file = __DIR__ . '/refile.ndjson';

                    $jsonDate = date('Y-m-d H:i:s');
                    $jsonName = $name;
                    $jsonBarcode = $item_barcode;
                    $jsonTrayBarcode = $internalNote1;

                    if (isset($process_type) && $process_type !== '') {
                        $jsonStatus = 'Item In Place - ' . $process_type;
                    } else {
                        $jsonStatus = 'Item In Place';
                    }

                    $jsonStep = '2';

                    $newEntry = array(
                        'date' => $jsonDate,
                        'name' => $jsonName,
                        'barcode' => $jsonBarcode,
                        'tray barcode' => $jsonTrayBarcode,
                        'status' => $jsonStatus,
                        'step' => $jsonStep,
                    );

                    $jsonLine = json_encode($newEntry, JSON_UNESCAPED_SLASHES);

                    if ($jsonLine === false) {
                        echo "<div class='alert alert-danger mt-3 text-center'>JSON encoding failed: " . h(json_last_error_msg()) . "</div>";
                    } else {
                        if (file_put_contents($file, $jsonLine . "\n", FILE_APPEND | LOCK_EX) === false) {
                            echo "<div class='alert alert-danger mt-3 text-center'>Unable to write to the NDJSON file.</div>";
                        }
                    }

                    echo "<tr>";
                    echo '<td>' . h($internalNote1) . '</td>';
                    echo "<td>" . h($title) . "</td>";
                    echo "<td>" . h($item_barcode) . "</td>";
                    echo "<td>" . h($internalNote3) . "</td>";
                    echo "<td>" . h($mms_id) . "</td>";
                    echo "<td><span class='text-success'>Updated</span></td>";
                    echo "</tr>";
                }

                echo '</tbody></table>';
                echo '</div></div>';
            }
        }
    }
}
?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>