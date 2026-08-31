<?php
declare(strict_types=1);

include 'include/access.php';
include 'include/admin_access.php';
include 'include/refile_data_file.php';

$file = refileEnsurePersistentNdjson();

header('Content-Type: application/x-ndjson; charset=utf-8');
header('Content-Disposition: attachment; filename="refile.ndjson"');
header('Content-Length: ' . (string)filesize($file));

readfile($file);
