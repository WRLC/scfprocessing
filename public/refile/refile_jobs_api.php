<?php
include 'include/access.php';
include 'include/refile_jobs_ndjson.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

function safeDeleteFile($file)
{
    if ($file !== '' && file_exists($file) && is_file($file)) {
        @unlink($file);
    }
}

function completedStatuses()
{
    return ['completed', 'completed_no_eligible_items'];
}

function deleteJobAndChildrenById($jobId)
{
    $jobsFile = refileJobsFile();
    $jobs = readNdjsonFile($jobsFile);

    $targetJob = null;
    foreach ($jobs as $job) {
        if ((int)$job['id'] === (int)$jobId) {
            $targetJob = $job;
            break;
        }
    }

    if (!$targetJob) {
        return false;
    }

    $idsToDelete = [(int)$jobId];

    if (isset($targetJob['job_type']) && $targetJob['job_type'] === 'analyze') {
        foreach ($jobs as $job) {
            $parentId = isset($job['parent_job_id']) ? (int)$job['parent_job_id'] : 0;
            if ($parentId === (int)$jobId) {
                $idsToDelete[] = (int)$job['id'];
            }
        }
    }

    $filtered = [];
    foreach ($jobs as $job) {
        if (!in_array((int)$job['id'], $idsToDelete, true)) {
            $filtered[] = $job;
        }
    }

    writeNdjsonFile($jobsFile, $filtered);

    foreach ($idsToDelete as $id) {
        $itemsFile = getJobItemsFile($id);
        safeDeleteFile($itemsFile);
    }

    if (isset($targetJob['job_type']) && $targetJob['job_type'] === 'analyze') {
        $storedFilename = isset($targetJob['stored_filename']) ? trim((string)$targetJob['stored_filename']) : '';
        if ($storedFilename !== '') {
            safeDeleteFile(refileUploadsDir() . '/' . $storedFilename);
        }
    }

    return true;
}

function deleteCompletedJobs()
{
    $jobs = getAllJobs();
    $idsToDelete = [];

    foreach ($jobs as $job) {
        $jobId = isset($job['id']) ? (int)$job['id'] : 0;
        $status = isset($job['status']) ? (string)$job['status'] : '';
        $jobType = isset($job['job_type']) ? (string)$job['job_type'] : '';
        $parentJobId = isset($job['parent_job_id']) ? (int)$job['parent_job_id'] : 0;

        if ($jobId < 1) {
            continue;
        }

        if ($jobType === 'apply' && $status === 'completed') {
            $idsToDelete[] = $jobId;

            if ($parentJobId > 0) {
                $idsToDelete[] = $parentJobId;
            }
        }

        if ($jobType === 'analyze' && in_array($status, ['completed', 'completed_no_eligible_items'], true)) {
            $idsToDelete[] = $jobId;
        }
    }

    $idsToDelete = array_values(array_unique(array_map('intval', $idsToDelete)));

    foreach ($idsToDelete as $jobId) {
        deleteJobAndChildrenById($jobId);
    }

    return count($idsToDelete);
}

if ($action === 'list') {
    echo json_encode(['jobs' => getAllJobs()]);
    exit;
}

if ($action === 'start_apply' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $parentJobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
    $parentJob = getJobById($parentJobId);

    if (!$parentJob || $parentJob['job_type'] !== 'analyze') {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Analyze job was not found.'
        ]);
        exit;
    }

    $parentStatus = isset($parentJob['status']) ? (string)$parentJob['status'] : '';
    $parentTotalPairs = isset($parentJob['total_pairs']) ? (int)$parentJob['total_pairs'] : 0;
    $parentProcessedPairs = isset($parentJob['processed_pairs']) ? (int)$parentJob['processed_pairs'] : 0;
    $checkComplete = ($parentTotalPairs > 0 && $parentProcessedPairs >= $parentTotalPairs);

    if ($parentStatus !== 'ready_to_refile' && !(in_array($parentStatus, ['running', 'queued'], true) && $checkComplete)) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Analyze job is not ready. The check phase must be complete before eligible items can be processed.'
        ]);
        exit;
    }

    $items = getJobItems($parentJobId);
    if ($checkComplete && $parentStatus !== 'ready_to_refile') {
        updateJob($parentJobId, function ($jobRow) {
            $jobRow['status'] = 'ready_to_refile';
            if (empty($jobRow['completed_at'])) {
                $jobRow['completed_at'] = nowUtc();
            }
            $jobRow['last_error'] = '';
            return $jobRow;
        });
    }

    $eligibleCount = 0;

    foreach ($items as $item) {
        $eligible = isset($item['eligible_for_apply']) ? (int)$item['eligible_for_apply'] : 0;
        if ($eligible === 1) {
            $eligibleCount++;
        }
    }

    // Prevent duplicate apply jobs for this analyze job
    $jobs = getAllJobs();
    foreach ($jobs as $existingJob) {
        if (
            ($existingJob['job_type'] ?? '') === 'apply' &&
            (int)($existingJob['parent_job_id'] ?? 0) === $parentJobId &&
            in_array(($existingJob['status'] ?? ''), ['queued', 'applying', 'completed'], true)
        ) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'error' => 'A completion job already exists for this file.'
            ]);
            exit;
        }
    }

    if ($eligibleCount < 1) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'No eligible items were found for apply.'
        ]);
        exit;
    }

    $jobId = nextJobId();

    $job = [
        'id' => $jobId,
        'job_type' => 'apply',
        'parent_job_id' => $parentJobId,
        'original_filename' => $parentJob['original_filename'],
        'stored_filename' => '',
        'uploaded_by' => $parentJob['uploaded_by'],
        'status' => 'queued',
        'created_at' => nowUtc(),
        'started_at' => '',
        'completed_at' => '',
        'total_pairs' => $eligibleCount,
        'processed_pairs' => 0,
        'mismatched_tray_errors' => isset($parentJob['mismatched_tray_errors']) ? (int)$parentJob['mismatched_tray_errors'] : 0,
        'not_yet_checked_in' => isset($parentJob['not_yet_checked_in']) ? (int)$parentJob['not_yet_checked_in'] : 0,
        'already_fully_processed' => isset($parentJob['already_fully_processed']) ? (int)$parentJob['already_fully_processed'] : 0,
        'eligible_for_apply_count' => 0,
        'success_count' => 0,
        'error_count' => 0,
        'last_error' => ''
    ];

    createJob($job);

    echo json_encode([
        'ok' => true,
        'job_id' => $jobId,
        'eligible_count' => $eligibleCount
    ]);
    exit;
}

if ($action === 'delete_job' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
    $mode = isset($_POST['mode']) ? trim((string)$_POST['mode']) : 'delete';

    if ($jobId < 1) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Invalid job id.'
        ]);
        exit;
    }

    $job = getJobById($jobId);

    if (!$job) {
        http_response_code(404);
        echo json_encode([
            'ok' => false,
            'error' => 'Job not found.'
        ]);
        exit;
    }

    $isRunningJob = isset($job['status']) && in_array($job['status'], ['queued', 'running', 'applying'], true);
    $isRetryDelete = ($mode === 'retry');

    if ($isRunningJob && !$isRetryDelete) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Cannot delete a running job. Use Delete and Retry if this job is stuck.'
        ]);
        exit;
    }

    $deleted = deleteJobAndChildrenById($jobId);

    if (!$deleted) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'error' => 'Unable to delete job.'
        ]);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'deleted_job_id' => $jobId
    ]);
    exit;
}

if ($action === 'delete_completed' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $count = deleteCompletedJobs();

    echo json_encode([
        'ok' => true,
        'deleted_count' => $count
    ]);
    exit;
}

http_response_code(400);
echo json_encode([
    'ok' => false,
    'error' => 'Invalid action.'
]);