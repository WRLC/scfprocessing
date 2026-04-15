<?php

function refileBaseDir()
{
    return realpath(__DIR__ . '/..');
}

function refileDataDir()
{
    return refileBaseDir() . '/data';
}

function refileItemsDir()
{
    return refileDataDir() . '/items';
}

function refileUploadsDir()
{
    return refileDataDir() . '/uploads';
}

function refileJobsFile()
{
    return refileDataDir() . '/jobs.ndjson';
}

function ensureRefileDirs()
{
    $dirs = [
        refileDataDir(),
        refileItemsDir(),
        refileUploadsDir()
    ];

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
        return [];
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) {
        return [];
    }

    $rows = [];
    foreach ($lines as $line) {
        $decoded = json_decode($line, true);
        if (is_array($decoded)) {
            $rows[] = $decoded;
        }
    }

    return $rows;
}

function writeNdjsonFile($file, array $rows)
{
    $tmp = $file . '.tmp';
    $out = '';

    foreach ($rows as $row) {
        $json = json_encode($row, JSON_UNESCAPED_SLASHES);
        if ($json !== false) {
            $out .= $json . "\n";
        }
    }

    file_put_contents($tmp, $out, LOCK_EX);
    rename($tmp, $file);
}

function appendNdjsonRow($file, array $row)
{
    $json = json_encode($row, JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }

    return file_put_contents($file, $json . "\n", FILE_APPEND | LOCK_EX) !== false;
}

function nextJobId()
{
    ensureRefileDirs();
    $jobs = readNdjsonFile(refileJobsFile());
    $max = 0;

    foreach ($jobs as $job) {
        $id = isset($job['id']) ? (int)$job['id'] : 0;
        if ($id > $max) {
            $max = $id;
        }
    }

    return $max + 1;
}

function getJobItemsFile($jobId)
{
    return refileItemsDir() . '/job_' . (int)$jobId . '.ndjson';
}

function createJob(array $job)
{
    ensureRefileDirs();
    return appendNdjsonRow(refileJobsFile(), $job);
}

function getAllJobs()
{
    ensureRefileDirs();
    $jobs = readNdjsonFile(refileJobsFile());

    usort($jobs, function ($a, $b) {
        return ((int)$b['id']) <=> ((int)$a['id']);
    });

    return $jobs;
}

function getJobById($jobId)
{
    $jobs = getAllJobs();

    foreach ($jobs as $job) {
        if ((int)$job['id'] === (int)$jobId) {
            return $job;
        }
    }

    return null;
}

function updateJob($jobId, callable $callback)
{
    $file = refileJobsFile();
    $jobs = readNdjsonFile($file);

    foreach ($jobs as &$job) {
        if ((int)$job['id'] === (int)$jobId) {
            $job = $callback($job);
            break;
        }
    }

    writeNdjsonFile($file, $jobs);
}

function appendJobItem($jobId, array $item)
{
    ensureRefileDirs();
    return appendNdjsonRow(getJobItemsFile($jobId), $item);
}

function getJobItems($jobId)
{
    return readNdjsonFile(getJobItemsFile($jobId));
}

function updateJobItems($jobId, callable $callback)
{
    $file = getJobItemsFile($jobId);
    $items = readNdjsonFile($file);

    foreach ($items as &$item) {
        $item = $callback($item);
    }

    writeNdjsonFile($file, $items);
}