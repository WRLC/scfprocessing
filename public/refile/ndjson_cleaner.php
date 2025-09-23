<?php
// ndjson_cleaner_dashboard.php
include 'include/access.php';
include 'include/admin_access.php';

$inputFile = __DIR__ . '/refile.ndjson';
$outputFile = __DIR__ . '/refile_cleaned.ndjson';
$requiredKeys = ['date', 'name', 'barcode', 'tray barcode', 'status', 'step'];

$cleanCount = 0;
$skipCount = 0;

if (isset($_GET['clean'])) {
    $in = fopen($inputFile, 'r');
    $out = fopen($outputFile, 'w');

    if ($in && $out) {
        while (($line = fgets($in)) !== false) {
            $line = trim($line);
            if ($line === '') continue;

            $entry = json_decode($line, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($entry)) {
                $skipCount++;
                continue;
            }

            foreach ($requiredKeys as $key) {
                if (!isset($entry[$key])) {
                    $entry[$key] = '';
                }
            }

            fwrite($out, json_encode($entry, JSON_UNESCAPED_SLASHES) . "\n");
            $cleanCount++;
        }
        fclose($in);
        fclose($out);

        // Replace original file
        rename($outputFile, $inputFile);
        $status = "Clean complete: $cleanCount cleaned, $skipCount skipped.";
    } else {
        $status = "Error opening file(s).";
    }
} else {
    $status = "Click the button below to clean the NDJSON file.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDJSON Cleaner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>NDJSON Cleaner Dashboard</h2>
    <p class="alert alert-info"><?php echo htmlspecialchars($status); ?></p>
    <a href="?clean=1" class="btn btn-success">Run Cleaner</a>
    <a href="refile.ndjson" target="_blank" class="btn btn-secondary">View NDJSON</a>
</div>
</body>
</html>
