<?php
include 'include/access.php';
include 'include/apikey.php';
include 'include/refile_jobs_store.php';

function h($value)
{
    return htmlspecialchars((string)(isset($value) ? $value : ''), ENT_QUOTES, 'UTF-8');
}

$name = '';

if (isset($_SESSION['user_id'])) {
    $name = $_SESSION['user_id'];
} elseif (isset($GLOBALS['name'])) {
    $name = $GLOBALS['name'];
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    ensureRefileStorage();

    if (!isset($_FILES['file']['tmp_name']) || $_FILES['file']['tmp_name'] === '') {
        $message = 'No file uploaded.';
    } else {
        $uploadedFile = $_FILES['file']['tmp_name'];

        if (!is_uploaded_file($uploadedFile)) {
            $message = 'Upload failed.';
        } else {
            $originalName = isset($_FILES['file']['name']) ? basename((string)$_FILES['file']['name']) : 'upload.txt';
            $extension = strtolower((string)pathinfo($originalName, PATHINFO_EXTENSION));

            if ($extension !== 'txt') {
                $message = 'Invalid file type. Please upload a .txt file.';
            } else {
                $lines = file($uploadedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

                if (!$lines || count($lines) === 0) {
                    $message = 'The file is empty or could not be read.';
                } else {
                    $storedFilename = uniqid('refile_', true) . '.txt';
                    $storedPath = refileUploadsDir() . '/' . $storedFilename;

                    if (!move_uploaded_file($uploadedFile, $storedPath)) {
                        $message = 'Unable to save uploaded file.';
                    } else {
                        $pairCount = (int) ceil(count($lines) / 2);

                        $job = createJob(array(
                            'job_type' => 'analyze',
                            'parent_job_id' => null,
                            'original_filename' => $originalName,
                            'stored_filename' => $storedFilename,
                            'uploaded_by' => $name,
                            'status' => 'queued',
                            'created_at' => nowUtc(),
                            'started_at' => '',
                            'completed_at' => '',
                            'total_pairs' => $pairCount,
                            'processed_pairs' => 0,
                            'mismatched_tray_errors' => 0,
                            'not_yet_checked_in' => 0,
                            'already_fully_processed' => 0,
                            'success_count' => 0,
                            'error_count' => 0,
                            'eligible_for_apply_count' => 0,
                            'last_error' => ''
                        ));

                        header('Location: refile_jobs.php?created=' . (int)$job['id']);
                        exit;
                    }
                }
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refile Upload (Background)</title>
    <?php include 'include/refresh.php'; ?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'include/nav.php'; ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2 class="text-center">Step 2: Tray verification and reshelving in SCF</h2>

            <?php if ($message !== ''): ?>
                <div class="alert alert-danger mt-3"><?php echo h($message); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Upload Barcode File for Background Processing</h4>
                </div>
                <div class="card-body bg-light">
                    <form method="POST" action="" id="uploadForm" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="file">Choose .txt file:</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".txt" required>
                        </div>

                        <div class="text-center font-italic mt-4">
                            <button type="submit" class="btn btn-success">Start Background Analysis</button>
                            <a href="refile_jobs.php" class="btn btn-secondary">View Jobs</a>
                        </div>

                        <div class="small text-center font-italic mt-4">
                            The upload creates a background job. Monitor progress on the jobs screen.
                        </div>
                    </form>
                </div>
            </div>

            <p class="text-center mt-4">Format should be:<br>
                Tray Barcode<br>Item Barcode<br>
                Tray Barcode<br>Item Barcode
            </p>
        </div>
    </div>

    <div class="row justify-content-center text-center mt-4" id="loadingSpinner" style="display:none;">
        <div class="text-center justify-content-center">
            <div class="spinner-border mt-1 text-primary text-center" role="status"></div>
            <p>Creating job, please wait...</p>
        </div>
    </div>
</div>

<?php include 'include/footer.php'; ?>

<script>
document.getElementById('uploadForm').onsubmit = function() {
    document.getElementById('loadingSpinner').style.display = 'block';
};
</script>
</body>
</html>