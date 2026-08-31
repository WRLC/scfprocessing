<?php
declare(strict_types=1);

function refileBundledNdjsonPath(): string
{
    return dirname(__DIR__) . '/refile.ndjson';
}

function refilePersistentNdjsonPath(): string
{
    $configuredPath = $_ENV['REFILE_NDJSON_PATH'] ?? getenv('REFILE_NDJSON_PATH');
    if (is_string($configuredPath) && trim($configuredPath) !== '') {
        return trim($configuredPath);
    }

    if (getenv('WEBSITE_SITE_NAME') !== false) {
        return '/home/site/data/refile/refile.ndjson';
    }

    return refileBundledNdjsonPath();
}

function refileEnsurePersistentNdjson(): string
{
    $path = refilePersistentNdjsonPath();
    $directory = dirname($path);

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    if (!file_exists($path)) {
        $seedPath = refileBundledNdjsonPath();
        if (file_exists($seedPath)) {
            copy($seedPath, $path);
        } else {
            touch($path);
        }
    }

    return $path;
}
