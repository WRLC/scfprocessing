<?php

function refileDataDir()
{
    return __DIR__ . '/../data';
}

function refileUploadsDir()
{
    return refileDataDir() . '/uploads';
}

function refileJobItemsDir()
{
    return refileDataDir() . '/job_items';
}

function refileLocksDir()
{
    return refileDataDir() . '/locks';
}

function refileJobsFile()
{
    return refileDataDir() . '/jobs.ndjson';
}

function ensureRefileStorage()
{
    $dirs = array(
        refileDataDir(),
        refileUploadsDir(),
        refileJobItemsDir(),
        refileLocksDir()
    );

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    if (!file_exists(refileJobsFile())) {
        file_put_contents(refileJobsFile(), '');
    }
}

function nowUtc()
{
    return gmdate('Y-m-d H:i:s');
}

function readNdjsonFile($file)
{
    if (!file_exists($file)) {
        return array();
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) {
        return array();
    }

    $rows = array();

    foreach ($lines as $line) {
        $decoded = json_decode($line, true);
        if (is_array($decoded)) {
            $rows[] = $decoded;
        }
    }

    return $rows;
}

function writeNdjsonFile($file, $rows)
{
    $tmp = $file . '.tmp';
    $fh = fopen($tmp, 'wb');

    if (!$fh) {
        throw new RuntimeException('Unable to write file: ' . $tmp);
    }

    foreach ($rows as $row) {
        fwrite($fh, json_encode($row, JSON_UNESCAPED_SLASHES) . "\n");
    }

    fclose($fh);

    if (!rename($tmp, $file)) {
        throw new RuntimeException('Unable to replace file: ' . $file);
    }
}

function appendNdjsonRow($file, $row)
{
    $line = json_encode($row, JSON_UNESCAPED_SLASHES);
    if ($line === false) {
        throw new RuntimeException('Unable to encode JSON row.');
    }

    file_put_contents($file, $line . "\n", FILE_APPEND | LOCK_EX);
}

function allJobs()
{
    ensureRefileStorage();
    return readNdjsonFile(refileJobsFile());
}

function saveAllJobs($jobs)
{
    ensureRefileStorage();
    writeNdjsonFile(refileJobsFile(), $jobs);
}

function nextJobId()
{
    $jobs = allJobs();
    $max = 0;

    foreach ($jobs as $job) {
        $id = isset($job['id']) ? (int)$job['id'] : 0;
        if ($id > $max) {
            $max = $id;
        }
    }

    return $max + 1;
}

function createJob($job)
{
    $jobs = allJobs();
    $job['id'] = nextJobId();
    $jobs[] = $job;
    saveAllJobs($jobs);
    return $job;
}

function findJobById($jobId)
{
    $jobs = allJobs();

    foreach ($jobs as $job) {
        if ((int)$job['id'] === (int)$jobId) {
            return $job;
        }
    }

    return null;
}

function updateJob($jobId, $updates)
{
    $jobs = allJobs();

    foreach ($jobs as $i => $job) {
        if ((int)$job['id'] === (int)$jobId) {
            $jobs[$i] = array_merge($job, $updates);
            saveAllJobs($jobs);
            return $jobs[$i];
        }
    }

    return null;
}

function replaceJob($jobId, $newJob)
{
    $jobs = allJobs();

    foreach ($jobs as $i => $job) {
        if ((int)$job['id'] === (int)$jobId) {
            $jobs[$i] = $newJob;
            saveAllJobs($jobs);
            return $newJob;
        }
    }

    return null;
}

function jobItemsFile($jobId)
{
    return refileJobItemsDir() . '/job_' . (int)$jobId . '.ndjson';
}

function readJobItems($jobId)
{
    ensureRefileStorage();
    return readNdjsonFile(jobItemsFile($jobId));
}

function writeJobItems($jobId, $items)
{
    ensureRefileStorage();
    writeNdjsonFile(jobItemsFile($jobId), $items);
}

function appendJobItem($jobId, $item)
{
    ensureRefileStorage();
    appendNdjsonRow(jobItemsFile($jobId), $item);
}

function jobLockFile($jobId)
{
    return refileLocksDir() . '/job_' . (int)$jobId . '.lock';
}

function acquireJobLock($jobId)
{
    $lockFile = jobLockFile($jobId);
    $fh = fopen($lockFile, 'c');

    if (!$fh) {
        return false;
    }

    if (!flock($fh, LOCK_EX | LOCK_NB)) {
        fclose($fh);
        return false;
    }

    return $fh;
}

function releaseJobLock($lockHandle)
{
    if (is_resource($lockHandle)) {
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
    }
}