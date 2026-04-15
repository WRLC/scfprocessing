<?php
include 'include/access.php';
include 'include/apikey.php';
include 'include/refile_jobs_ndjson.php';
include 'include/refile_alma.php';

header('Content-Type: application/json');

ensureRefileDirs();

function getNextRunnableJob()
{
    $jobs = getAllJobs();

    usort($jobs, function ($a, $b) {
        return ((int)$a['id']) <=> ((int)$b['id']);
    });

    foreach ($jobs as $job) {
        if ($job['status'] === 'queued' || $job['status'] === 'running' || $job['status'] === 'applying') {
            return $job;
        }
    }

    return null;
}

function saveJob(array $job)
{
    updateJob($job['id'], function () use ($job) {
        return $job;
    });
}

function summaryFilePath()
{
    return dirname(__FILE__) . '/refile.ndjson';
}

function appendSummaryEntry(array $entry)
{
    $json = json_encode($entry, JSON_UNESCAPED_SLASHES);
    if ($json !== false) {
        file_put_contents(summaryFilePath(), $json . "\n", FILE_APPEND | LOCK_EX);
    }
}

function postMismatchToGoogle($barcodeFile, $barcodeAlma, $itemBarcode)
{
    $googleFormUrl = 'https://docs.google.com/forms/u/0/d/e/1FAIpQLSfdqhD8VPq8X13niOSL-y7146PkmYtzJW0v7U-Sr94EmJOtyA/formResponse';
    $formData = array(
        'entry.1671538415' => $barcodeFile !== '' ? $barcodeFile : 'MISSING',
        'entry.1478552555' => $barcodeAlma !== '' ? $barcodeAlma : 'MISSING',
        'entry.860961451' => $itemBarcode
    );

    almaRequest(
        $googleFormUrl,
        'POST',
        http_build_query($formData)
    );
}

function processAnalyzeJob(array $job, $apiKey)
{
    $filePath = refileUploadsDir() . '/' . $job['stored_filename'];

    if (!file_exists($filePath)) {
        $job['status'] = 'failed';
        $job['last_error'] = 'Uploaded file not found: ' . $filePath;
        saveJob($job);
        return;
    }

    if ($job['started_at'] === '') {
        $job['started_at'] = nowUtc();
    }

    $job['status'] = 'running';

    if (!isset($job['eligible_for_apply_count'])) {
        $job['eligible_for_apply_count'] = 0;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) {
        $job['status'] = 'failed';
        $job['last_error'] = 'Uploaded file is empty.';
        saveJob($job);
        return;
    }

    $existingItems = getJobItems($job['id']);
    $alreadyProcessed = count($existingItems);
    $chunkSize = 10;
    $processedThisTick = 0;

    for ($pairIndex = $alreadyProcessed; $pairIndex < $job['total_pairs'] && $processedThisTick < $chunkSize; $pairIndex++) {
        $lineIndex = $pairIndex * 2;

        $trayBarcode = isset($lines[$lineIndex]) ? substr(trim($lines[$lineIndex]), 0, 12) : '';
        $barcode = isset($lines[$lineIndex + 1]) ? trim($lines[$lineIndex + 1]) : '';

        $item = [
            'job_id' => $job['id'],
            'line_no' => $lineIndex + 1,
            'tray_barcode' => $trayBarcode,
            'file_item_barcode' => $barcode,
            'resolved_item_barcode' => '',
            'title' => '',
            'internal_note_1' => '',
            'internal_note_3' => '',
            'mms_id' => '',
            'holding_id' => '',
            'pid' => '',
            'loan_status' => 'unknown',
            'due_date' => '',
            'process_status' => '',
            'mismatch_flag' => 0,
            'already_processed_flag' => 0,
            'eligible_for_apply' => 0,
            'item_status' => 'pending',
            'item_error' => '',
            'created_at' => nowUtc()
        ];

        if ($barcode === '') {
            $item['item_status'] = 'failed';
            $item['item_error'] = 'Missing item barcode.';
            $job['error_count']++;
        } else {
            $lookup = findItemXmlByBarcode($barcode, $apiKey);

            if (!$lookup['ok'] || $lookup['xml'] === false) {
                $item['item_status'] = 'failed';
                $item['item_error'] = 'Item record does not exist.';
                $job['error_count']++;
            } else {
                $xml = $lookup['xml'];

                $item['title'] = isset($xml->bib_data->title) ? (string)$xml->bib_data->title : '';
                $item['resolved_item_barcode'] = isset($xml->item_data->barcode) ? (string)$xml->item_data->barcode : '';
                $item['internal_note_1'] = isset($xml->item_data->internal_note_1) ? substr((string)$xml->item_data->internal_note_1, 0, 12) : '';
                $item['internal_note_3'] = isset($xml->item_data->internal_note_3) ? (string)$xml->item_data->internal_note_3 : '';
                $item['mms_id'] = isset($xml->bib_data->mms_id) ? (string)$xml->bib_data->mms_id : '';
                $item['holding_id'] = isset($xml->holding_data->holding_id) ? (string)$xml->holding_data->holding_id : '';
                $item['pid'] = isset($xml->item_data->pid) ? (string)$xml->item_data->pid : '';

                $hasMismatch = false;
                if ($trayBarcode === '' || $item['internal_note_1'] === '' || $trayBarcode !== $item['internal_note_1']) {
                    $item['mismatch_flag'] = 1;
                    $job['mismatched_tray_errors']++;
                    $hasMismatch = true;
                }

                if ($hasMismatch) {
                    postMismatchToGoogle(
                        $trayBarcode,
                        $item['internal_note_1'],
                        $item['resolved_item_barcode']
                    );
                }

                $loanStatus = 'unknown';

                if ($item['mms_id'] !== '' && $item['holding_id'] !== '' && $item['pid'] !== '') {
                    $loanUrl = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/'
                        . rawurlencode($item['mms_id'])
                        . '/holdings/' . rawurlencode($item['holding_id'])
                        . '/items/' . rawurlencode($item['pid'])
                        . '/loans?apikey=' . rawurlencode($apiKey);

                    $loanResponse = almaRequest($loanUrl, 'GET');
                    $loanXml = loadXmlFromResponse($loanResponse);

                    if ($loanXml !== false) {
                        $totalRecordCount = isset($loanXml['total_record_count']) ? (int)$loanXml['total_record_count'] : 0;

                        if ($totalRecordCount === 0) {
                            $loanStatus = 'checked_in';
                        } else {
                            $loanStatus = 'checked_out';
                            $job['not_yet_checked_in']++;

                            $itemLoan = isset($loanXml->item_loan) ? $loanXml->item_loan : null;
                            if ($itemLoan) {
                                $item['due_date'] = isset($itemLoan->due_date) ? (string)$itemLoan->due_date : '';
                                $item['process_status'] = isset($itemLoan->process_status) ? (string)$itemLoan->process_status : '';
                            }
                        }
                    }
                }

                $item['loan_status'] = $loanStatus;

                $alreadyProcessed = (
                    $item['internal_note_3'] === '' &&
                    (
                        !isset($xml->holding_data->in_temp_location) ||
                        strtolower((string)$xml->holding_data->in_temp_location) !== 'true'
                    ) &&
                    (!isset($xml->holding_data->temp_library) || trim((string)$xml->holding_data->temp_library) === '') &&
                    (!isset($xml->holding_data->temp_location) || trim((string)$xml->holding_data->temp_location) === '')
                );

                if ($alreadyProcessed) {
                    $item['already_processed_flag'] = 1;
                    $job['already_fully_processed']++;
                }

                if ($item['mismatch_flag'] === 0 && $loanStatus === 'checked_in' && $item['already_processed_flag'] === 0) {
                    $item['eligible_for_apply'] = 1;
                    $job['eligible_for_apply_count']++;
                }

                $item['item_status'] = 'analyzed';
                $job['success_count']++;
            }
        }

        appendJobItem($job['id'], $item);
        $job['processed_pairs']++;
        $processedThisTick++;
    }

    if ($job['processed_pairs'] >= $job['total_pairs']) {
        $eligibleCount = isset($job['eligible_for_apply_count']) ? (int)$job['eligible_for_apply_count'] : 0;

        if ($eligibleCount > 0) {
            $job['status'] = 'ready_to_refile';
            $job['last_error'] = '';
        } else {
            $job['status'] = 'completed_no_eligible_items';
            $job['last_error'] = 'No eligible items found for refile.';
        }

        $job['completed_at'] = nowUtc();
    }

    saveJob($job);
}

function processApplyJob(array $job, $apiKey)
{
    if ($job['started_at'] === '') {
        $job['started_at'] = nowUtc();
    }

    $job['status'] = 'applying';

    $parentJobId = isset($job['parent_job_id']) ? (int)$job['parent_job_id'] : 0;
    $items = getJobItems($parentJobId);
    $chunkSize = 10;
    $processedThisTick = 0;

    foreach ($items as &$item) {
        if ($processedThisTick >= $chunkSize) {
            break;
        }

        if ((int)($item['eligible_for_apply'] ?? 0) !== 1) {
            continue;
        }

        if (($item['item_status'] ?? '') === 'applied') {
            continue;
        }

        if (($item['mms_id'] ?? '') === '' || ($item['holding_id'] ?? '') === '' || ($item['pid'] ?? '') === '') {
            continue;
        }

        $getUrl = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/'
            . rawurlencode($item['mms_id'])
            . '/holdings/' . rawurlencode($item['holding_id'])
            . '/items/' . rawurlencode($item['pid'])
            . '?apikey=' . rawurlencode($apiKey);

        $getResponse = almaRequest($getUrl, 'GET');
        $xml = loadXmlFromResponse($getResponse);

        if ($xml === false) {
            $item['item_status'] = 'failed';
            $item['item_error'] = 'Unable to reload item before update.';
            $job['error_count']++;
            $job['processed_pairs']++;
            $processedThisTick++;
            continue;
        }

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

        $putResponse = almaRequest(
            $getUrl,
            'PUT',
            $xml->asXML(),
            array('Content-Type: application/xml')
        );

        if (!$putResponse['ok']) {
            $item['item_status'] = 'failed';
            $item['item_error'] = 'PUT failed. HTTP ' . (int)$putResponse['status'];
            $job['error_count']++;
        } else {
            $item['item_status'] = 'applied';
            $item['item_error'] = '';
            $job['success_count']++;

            $processType = isset($xml->item_data->process_type) ? (string)$xml->item_data->process_type : '';
            $statusText = $processType !== '' ? 'Item In Place - ' . $processType : 'Item In Place';

            $summaryEntry = array(
                'date' => date('Y-m-d H:i:s'),
                'name' => isset($job['uploaded_by']) ? $job['uploaded_by'] : '',
                'barcode' => isset($item['resolved_item_barcode']) ? $item['resolved_item_barcode'] : '',
                'tray barcode' => isset($item['internal_note_1']) ? $item['internal_note_1'] : '',
                'status' => $statusText,
                'step' => '2'
            );

            appendSummaryEntry($summaryEntry);
        }

        $job['processed_pairs']++;
        $processedThisTick++;
    }

    writeNdjsonFile(getJobItemsFile($parentJobId), $items);

    if ($job['processed_pairs'] >= $job['total_pairs']) {
        $job['status'] = 'completed';
        $job['completed_at'] = nowUtc();
        $job['last_error'] = '';
    }

    saveJob($job);
}

$job = getNextRunnableJob();

if (!$job) {
    echo json_encode(['ok' => true, 'message' => 'No jobs pending.']);
    exit;
}

try {
    if ($job['job_type'] === 'analyze') {
        processAnalyzeJob($job, $api_key);
    } elseif ($job['job_type'] === 'apply') {
        processApplyJob($job, $api_key);
    }

    echo json_encode(['ok' => true, 'job_id' => $job['id']]);
} catch (Throwable $e) {
    updateJob($job['id'], function ($jobRow) use ($e) {
        $jobRow['status'] = 'failed';
        $jobRow['last_error'] = $e->getMessage();
        return $jobRow;
    });

    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}