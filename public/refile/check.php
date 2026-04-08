<?php
include 'include/access.php';

function h($value)
{
    return htmlspecialchars((string)(isset($value) ? $value : ''), ENT_QUOTES, 'UTF-8');
}

function almaGetXml($url)
{
    $ch = curl_init($url);

    if (!$ch) {
        return array(
            'ok' => false,
            'status' => 0,
            'body' => '',
            'error' => 'Unable to initialize cURL.'
        );
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    $body = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($body === false) {
        return array(
            'ok' => false,
            'status' => $status,
            'body' => '',
            'error' => $error !== '' ? $error : 'Unknown cURL error.'
        );
    }

    return array(
        'ok' => ($status >= 200 && $status < 300),
        'status' => $status,
        'body' => $body,
        'error' => ''
    );
}

$getbarcode = isset($_GET['barcode']) ? trim($_GET['barcode']) : '';
$postBarcode = isset($_POST['barcode']) ? trim($_POST['barcode']) : '';
$formBarcode = ($postBarcode !== '') ? $postBarcode : $getbarcode;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Information</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <?php include 'include/refresh.php'; ?>
</head>

<body>
    <?php include 'include/nav.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Check Item Status by Barcode</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="barcode">Enter Barcode:</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="barcode"
                                    name="barcode"
                                    placeholder="Enter barcode"
                                    value="<?php echo h($formBarcode); ?>"
                                    required>
                            </div>
                            <button type="submit" class="btn btn-primary">Search</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'include/apikey.php';

    $barcodeInput = isset($_POST['barcode']) ? trim($_POST['barcode']) : '';

    if ($barcodeInput === '') {
        echo "<div class='alert alert-danger mt-3 text-center'>Please enter a barcode.</div>";
    } else {
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

        $baseApi = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/items?item_barcode=';
        $itemResponse = null;
        $url = '';
        $xml = false;

        foreach ($candidateBarcodes as $candidateBarcode) {
            $testUrl = $baseApi . rawurlencode($candidateBarcode) . '&apikey=' . rawurlencode($api_key);
            $response = almaGetXml($testUrl);

            if ($response['ok'] && trim($response['body']) !== '') {
                $testXml = @simplexml_load_string($response['body']);

                if ($testXml !== false && isset($testXml->item_data->barcode)) {
                    $itemResponse = $response;
                    $url = $testUrl;
                    $xml = $testXml;
                    break;
                }
            }
        }

        if ($xml === false) {
            echo "<div class='alert alert-danger mt-3 text-center'>Item record does not exist</div>";
        } else {
            $title = isset($xml->bib_data->title) ? (string)$xml->bib_data->title : '';
            $item_barcode = isset($xml->item_data->barcode) ? (string)$xml->item_data->barcode : '';
            $internalNote3 = isset($xml->item_data->internal_note_3) ? (string)$xml->item_data->internal_note_3 : '';
            $internalNote1 = isset($xml->item_data->internal_note_1) ? (string)$xml->item_data->internal_note_1 : '';
            $description = isset($xml->item_data->description) ? (string)$xml->item_data->description : '';
            $in_temp_location = isset($xml->holding_data->in_temp_location) ? (string)$xml->holding_data->in_temp_location : '';
            $temp_library = isset($xml->holding_data->temp_library) ? (string)$xml->holding_data->temp_library : '';
            $temp_location = isset($xml->holding_data->temp_location) ? (string)$xml->holding_data->temp_location : '';
            $provenance_desc = isset($xml->item_data->provenance['desc']) ? (string)$xml->item_data->provenance['desc'] : '';
            $process_type = isset($xml->item_data->process_type) ? (string)$xml->item_data->process_type : '';
            $holding_id = isset($xml->holding_data->holding_id) ? (string)$xml->holding_data->holding_id : '';
            $pid = isset($xml->item_data->pid) ? (string)$xml->item_data->pid : '';
            $mms_id = isset($xml->bib_data->mms_id) ? (string)$xml->bib_data->mms_id : '';
            $modification_date = isset($xml->item_data->modification_date) ? (string)$xml->item_data->modification_date : '';
            $modification_date = str_replace('Z', '', $modification_date);

            echo "<div class='row mt-3'>
                    <div class='col-md-8 offset-md-2'>
                        <div class='card bg-light'>
                            <div class='card-header bg-dark text-white text-center'>
                                <h4>Item Details for Barcode: " . h($item_barcode) . "</h4>
                            </div>
                            <div class='card-body'>";

            echo '<h6 class="text-center alert alert-success">Item Information</h6>';
            echo "<ul class='list-group'>";
            echo "<li class='list-group-item'><strong>Title:</strong> " . h($title) . "</li>";
            echo "<li class='list-group-item'><strong>Barcode:</strong> " . h($item_barcode) . "</li>";
            echo "<li class='list-group-item'><strong>Tray/Shelf Barcode:</strong> " . h($internalNote1) . "</li>";
            echo "<li class='list-group-item'><strong>Description:</strong> " . h($description) . "</li>";
            echo "<li class='list-group-item'><strong>MMS ID:</strong> " . h($mms_id) . "</li>";
            echo "<li class='list-group-item'><strong>Owning Library:</strong> " . h($provenance_desc) . "</li>";
            echo "<li class='list-group-item'><strong>Last Modified:</strong> " . h($modification_date) . "</li>";
            echo "</ul>";

            echo '<br><h6 class="text-center alert alert-primary">Refile Information</h6>';
            echo "<ul class='list-group'>";
            echo "<li class='list-group-item'><strong>Internal Note 3:</strong> " . h($internalNote3) . "</li>";
            echo "<li class='list-group-item'><strong>In Temp Location:</strong> " . h($in_temp_location) . "</li>";
            echo "<li class='list-group-item'><strong>Temp Location:</strong> " . h($temp_location) . "</li>";
            echo "<li class='list-group-item'><strong>Temp Library:</strong> " . h($temp_library) . "</li>";
            echo "<li class='list-group-item'><strong>Process Type:</strong> <span class='text-danger'>" . h($process_type) . "</span></li>";

            $loan_url = '';

            if ($mms_id !== '' && $holding_id !== '' && $pid !== '') {
                $loan_url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/'
                    . rawurlencode($mms_id)
                    . '/holdings/' . rawurlencode($holding_id)
                    . '/items/' . rawurlencode($pid)
                    . '/loans?apikey=' . rawurlencode($api_key);

                $loanResponse = almaGetXml($loan_url);

                if (!$loanResponse['ok']) {
                    echo "<div class='alert alert-danger mt-3'>Loan API Error";
                    if ($loanResponse['status'] > 0) {
                        echo " (HTTP " . (int)$loanResponse['status'] . ")";
                    }
                    echo ".</div>";
                } else {
                    $loan_xml = @simplexml_load_string($loanResponse['body']);

                    if ($loan_xml === false) {
                        echo "<div class='alert alert-danger mt-3'>Failed to parse loan XML response.</div>";
                    } else {
                        $total_record_count = isset($loan_xml['total_record_count']) ? (int)$loan_xml['total_record_count'] : 0;

                        if ($total_record_count === 0) {
                            echo '<li class="list-group-item"><strong>Status:</strong> <span class="text-success">Item Checked In</span></li>';
                        } else {
                            $item_loan = isset($loan_xml->item_loan) ? $loan_xml->item_loan : null;

                            if ($item_loan) {
                                $due_date = isset($item_loan->due_date) ? (string)$item_loan->due_date : '';
                                $loan_status = isset($item_loan->loan_status) ? (string)$item_loan->loan_status : '';
                                $process_status = isset($item_loan->process_status) ? (string)$item_loan->process_status : '';
                                $user_id = isset($item_loan->user_id) ? (string)$item_loan->user_id : '';

                                echo "</ul>";
                                echo "<br><h6 class='text-center alert alert-info'>Loan Information</h6>";
                                echo "<ul class='list-group'>";

                                if ($loan_status === 'Active') {
                                    echo "<li class='list-group-item'><strong>Loan Status: </strong><span class='text-danger font-italic'>Checked Out</span></li>";
                                } else {
                                    echo "<li class='list-group-item'><strong>Loan Status:</strong> " . h($loan_status) . "</li>";
                                }

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
                                    echo "<li class='list-group-item'><strong>Borrower:</strong> <span class='badge badge-warning' style='font-size:1.2em;'>Deliver To: " . h($deliverToMapping[$user_id]) . "</span></li>";
                                } else {
                                    echo "<li class='list-group-item'><strong>Borrower (User ID):</strong> " . h($user_id) . "</li>";
                                }

                                echo "<li class='list-group-item'><strong>Due Date:</strong> " . h($due_date) . "</li>";
                                echo "<li class='list-group-item'><strong>Process Status:</strong> " . h($process_status) . "</li>";
                            } else {
                                echo "<li class='list-group-item text-danger'>No item_loan found in the XML response.</li>";
                            }
                        }
                    }
                }
            } else {
                echo "<li class='list-group-item text-warning'>Loan lookup unavailable: missing bib/item identifiers.</li>";
            }

            echo "</ul>";
            echo "<br>";
            echo "<div class='btn-group mb-4 offset-md-4' style='width:240px;'>";
            echo "<a class='btn btn-info btn-sm' style='width:120px;' target='_blank' href='" . h($url) . "'>XML</a>";
            if ($loan_url !== '') {
                echo "<a class='btn btn-warning btn-sm' style='width:120px;' target='_blank' href='" . h($loan_url) . "'>Loan XML</a>";
            }
            echo "</div>";
            echo "<p class='text-center'>Does everything look ok?</p>";
            echo "<a class='btn btn-danger offset-md-5 mb-2' style='width:130px;' href='check_in.php?barcode=" . rawurlencode($item_barcode) . "'>Check-In Item</a>";
            echo "</div></div></div></div>";
        }
    }
}
?>
        <p class="text-center">Example barcode: 32882012348937X</p>
    </div>

    <?php include 'include/footer.php'; ?>
</body>
</html>