<?php
include 'include/access.php';
include 'include/refile_jobs_ndjson.php';

function h($value)
{
    return htmlspecialchars((string)(isset($value) ? $value : ''), ENT_QUOTES, 'UTF-8');
}

function itemStatusBadgeClass($status)
{
    switch ($status) {
        case 'applied':
            return 'success';
        case 'analyzed':
            return 'primary';
        case 'failed':
            return 'danger';
        case 'pending':
            return 'secondary';
        default:
            return 'secondary';
    }
}

function loanStatusBadgeClass($status)
{
    switch ($status) {
        case 'checked_in':
            return 'success';
        case 'checked_out':
            return 'danger';
        default:
            return 'secondary';
    }
}

$jobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
$view = isset($_GET['view']) ? trim((string)$_GET['view']) : 'check';

$job = getJobById($jobId);
$items = [];
$pageTitle = 'Job Items';
$pageSubtitle = '';

if ($job) {
    $allItems = getJobItems($jobId);

    if ($view === 'completed') {
        $pageTitle = 'Completed Items';
        $pageSubtitle = 'Items successfully applied during the refile step.';
        foreach ($allItems as $item) {
            if (($item['item_status'] ?? '') === 'applied') {
                $items[] = $item;
            }
        }
    } else {
        $pageTitle = 'Checked Items';
        $pageSubtitle = 'All items analyzed during tray verification.';
        $items = $allItems;
    }
}

$totalItems = count($items);
$failedCount = 0;
$appliedCount = 0;
$mismatchCount = 0;
$eligibleCount = 0;
$checkedOutCount = 0;

foreach ($items as $item) {
    if (($item['item_status'] ?? '') === 'failed') {
        $failedCount++;
    }

    if (($item['item_status'] ?? '') === 'applied') {
        $appliedCount++;
    }

    if ((int)($item['mismatch_flag'] ?? 0) === 1) {
        $mismatchCount++;
    }

    if ((int)($item['eligible_for_apply'] ?? 0) === 1) {
        $eligibleCount++;
    }

    if (($item['loan_status'] ?? '') === 'checked_out') {
        $checkedOutCount++;
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?></title>
    <?php include 'include/refresh.php'; ?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .summary-card {
            border-radius: 12px;
            box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,.06);
            height: 100%;
        }
        .summary-card .card-body {
            padding: 1rem 1.25rem;
        }
        .mono {
            font-family: Menlo, Consolas, monospace;
            font-size: 0.95rem;
            word-break: break-word;
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .row-failed {
            background-color: #fff5f5;
        }
        .row-mismatch {
            background-color: #fff8e1;
        }
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .job-meta {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            border: 1px solid #e9ecef;
        }
        .small-label {
            font-size: .8rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        .big-number {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1.1;
        }
        .sticky-toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            background: white;
            padding: .75rem 0;
        }
    </style>
</head>
<body>
<?php include 'include/nav.php'; ?>

<div class="container mt-5">
    <div class="section-header mb-3">
        <div>
            <h2 class="mb-1"><?php echo h($pageTitle); ?></h2>
            <div class="text-muted"><?php echo h($pageSubtitle); ?></div>
        </div>
        <div>
            <a href="refile_jobs.php" class="btn btn-secondary">Back to Jobs</a>
        </div>
    </div>

    <?php if (!$job): ?>
        <div class="alert alert-danger">Job not found.</div>
    <?php else: ?>
        <div class="job-meta mb-4">
            <div class="row">
                <div class="col-md-2 mb-3 mb-md-0">
                    <div class="small-label">Job ID</div>
                    <div><strong><?php echo h($job['id'] ?? ''); ?></strong></div>
                </div>
                <div class="col-md-2 mb-3 mb-md-0">
                    <div class="small-label">Type</div>
                    <div><strong><?php echo h($job['job_type'] ?? ''); ?></strong></div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="small-label">File</div>
                    <div><strong><?php echo h($job['original_filename'] ?? ''); ?></strong></div>
                </div>
                <div class="col-md-2 mb-3 mb-md-0">
                    <div class="small-label">Status</div>
                    <div><strong><?php echo h($job['status'] ?? ''); ?></strong></div>
                </div>
                <div class="col-md-2">
                    <div class="small-label">Started</div>
                    <div><strong><?php echo h(($job['started_at'] ?? '') !== '' ? $job['started_at'] : ($job['created_at'] ?? '')); ?></strong></div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card summary-card border-primary">
                    <div class="card-body">
                        <div class="small-label">Items Shown</div>
                        <div class="big-number"><?php echo h($totalItems); ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card summary-card border-danger">
                    <div class="card-body">
                        <div class="small-label">Failed</div>
                        <div class="big-number"><?php echo h($failedCount); ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card summary-card border-warning">
                    <div class="card-body">
                        <div class="small-label">Mismatches</div>
                        <div class="big-number"><?php echo h($mismatchCount); ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card summary-card border-info">
                    <div class="card-body">
                        <div class="small-label">Checked Out</div>
                        <div class="big-number"><?php echo h($checkedOutCount); ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card summary-card border-success">
                    <div class="card-body">
                        <div class="small-label"><?php echo $view === 'completed' ? 'Applied' : 'Eligible'; ?></div>
                        <div class="big-number"><?php echo h($view === 'completed' ? $appliedCount : $eligibleCount); ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card summary-card border-secondary">
                    <div class="card-body">
                        <div class="small-label">View</div>
                        <div class="big-number" style="font-size:1.15rem;">
                            <?php echo h($view === 'completed' ? 'Completed' : 'Check'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sticky-toolbar mb-3">
            <div class="btn-group" role="group" aria-label="View Switcher">
                <a href="refile_job_view.php?job_id=<?php echo h($jobId); ?>&view=check"
                   class="btn btn-outline-primary <?php echo $view !== 'completed' ? 'active' : ''; ?>">
                    View Checked Items
                </a>
                <a href="refile_job_view.php?job_id=<?php echo h($jobId); ?>&view=completed"
                   class="btn btn-outline-success <?php echo $view === 'completed' ? 'active' : ''; ?>">
                    View Completed Items
                </a>
            </div>
        </div>

        <?php if (empty($items)): ?>
            <div class="alert alert-info">No items found for this view.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Line</th>
                            <th>Tray Barcode</th>
                            <th>File Item Barcode</th>
                            <th>Resolved Item Barcode</th>
                            <th>Title</th>
                            <th>Internal Note 1</th>
                            <th>Internal Note 3</th>
                            <th>Loan Status</th>
                            <th>Item Status</th>
                            <th>Eligible</th>
                            <th>Error / Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <?php
                            $rowClass = '';
                            if (($item['item_status'] ?? '') === 'failed') {
                                $rowClass = 'row-failed';
                            } elseif ((int)($item['mismatch_flag'] ?? 0) === 1) {
                                $rowClass = 'row-mismatch';
                            }

                            $internalNote1 = $item['internal_note_1'] ?? '';
                            $trayBarcode = $item['tray_barcode'] ?? '';
                            $hasMismatch = ((int)($item['mismatch_flag'] ?? 0) === 1);
                            ?>
                            <tr class="<?php echo h($rowClass); ?>">
                                <td><?php echo h($item['line_no'] ?? ''); ?></td>
                                <td class="mono"><?php echo h($trayBarcode); ?></td>
                                <td class="mono"><a href="check.php?barcode=<?php echo h($item['file_item_barcode'] ?? ''); ?>" target="_blank"><?php echo h($item['file_item_barcode'] ?? ''); ?></a></td>
                                <td class="mono"><?php echo h($item['resolved_item_barcode'] ?? ''); ?></td>
                                <td><?php echo h($item['title'] ?? ''); ?></td>
                                <td class="mono">
                                    <?php if ($hasMismatch): ?>
                                        <span class="text-danger font-weight-bold"><?php echo h($internalNote1); ?></span>
                                    <?php else: ?>
                                        <?php echo h($internalNote1); ?>
                                    <?php endif; ?>
                                </td>
                                <td class="mono">
    <?php
    if ($view === 'completed') {
        // Do not display Internal Note 3 for completed items
        echo '';
    } else {
        echo h($item['internal_note_3'] ?? '');
    }
    ?>
</td>
                                <td>
                                    <span class="badge badge-<?php echo h(loanStatusBadgeClass($item['loan_status'] ?? 'unknown')); ?>">
                                        <?php echo h($item['loan_status'] ?? 'unknown'); ?>
                                    </span>
                                    <?php if (($item['due_date'] ?? '') !== ''): ?>
                                        <div class="small text-muted mt-1">Due: <?php echo h($item['due_date']); ?></div>
                                    <?php endif; ?>
                                    <?php if (($item['process_status'] ?? '') !== ''): ?>
                                        <div class="small text-muted"><?php echo h($item['process_status']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo h(itemStatusBadgeClass($item['item_status'] ?? '')); ?>">
                                        <?php echo h($item['item_status'] ?? ''); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ((int)($item['eligible_for_apply'] ?? 0) === 1): ?>
                                        <span class="badge badge-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $notes = [];

                                    if ($hasMismatch) {
                                        $notes[] = 'Tray barcode does not match Alma Internal Note 1.';
                                    }

                                    if ((int)($item['already_processed_flag'] ?? 0) === 1) {
                                        $notes[] = 'Already fully processed.';
                                    }

                                    if (($item['item_error'] ?? '') !== '') {
                                        $notes[] = $item['item_error'];
                                    }

                                    echo h(implode(' ', $notes));
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'include/footer.php'; ?>
</body>
</html>