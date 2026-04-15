<?php include 'include/access.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refile Jobs</title>
    <?php include 'include/refresh.php'; ?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table td, .table th {
            vertical-align: middle;
        }
        .action-buttons .btn,
        .action-buttons a,
        .action-buttons span {
            margin-bottom: .25rem;
            margin-right: .25rem;
        }
        .status-badge {
            font-size: 0.9rem;
        }
        .job-meta-small {
            font-size: 0.85rem;
            color: #6c757d;
            display: block;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
<?php include 'include/nav.php'; ?>

<div class="mt-5 ml-5 mr-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Refile Jobs</h2>
        <div>
            <a href="refile_upload_bg.php" class="btn btn-primary">Upload New File</a>
            <button class="btn btn-outline-danger ml-2" onclick="deleteCompleted()">Delete Completed</button>
        </div>
    </div>

    <table class="table table-striped table-bordered" id="jobsTable">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>File</th>
                <th>Date Began</th>
                <th>Status</th>
                <th>Check Progress</th>
                <th>Apply Progress</th>
                <th>Mismatched Tray Errors</th>
                <th>Items Not Yet Checked In</th>
                <th>Already Fully Processed</th>
                <th>Last Error</th>
                <th style="min-width: 320px;">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<?php include 'include/footer.php'; ?>

<script>
let workerBusy = false;
let jobsBusy = false;

function statusBadge(status) {
    const map = {
        queued: 'secondary',
        running: 'primary',
        ready_to_refile: 'success',
        completed_no_eligible_items: 'warning',
        applying: 'info',
        completed: 'success',
        failed: 'danger',
        refile_completed: 'success'
    };

    const labelMap = {
        queued: 'Queued',
        running: 'Checking',
        ready_to_refile: 'Ready to Refile',
        completed_no_eligible_items: 'Completed - No Eligible Items',
        applying: 'Applying Refile',
        completed: 'Completed',
        failed: 'Failed',
        refile_completed: 'Refile Completed'
    };

    const cls = map[status] || 'secondary';
    const label = labelMap[status] || status || '';
    return `<span class="badge badge-${cls} status-badge">${escapeHtml(label)}</span>`;
}

function prettyDate(value) {
    return escapeHtml(value || '');
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function normalizeJobsForDisplay(jobs) {
    const analyzeJobs = [];
    const applyJobsByParent = {};

    jobs.forEach(job => {
        if ((job.job_type || '') === 'apply' && job.parent_job_id) {
            applyJobsByParent[String(job.parent_job_id)] = job;
        } else if ((job.job_type || '') === 'analyze') {
            analyzeJobs.push(job);
        }
    });

    return analyzeJobs.map(analyzeJob => {
        const applyJob = applyJobsByParent[String(analyzeJob.id)] || null;

        let displayStatus = analyzeJob.status || '';

        if (applyJob) {
            if (applyJob.status === 'completed') {
                displayStatus = 'refile_completed';
            } else if (applyJob.status === 'applying' || applyJob.status === 'queued') {
                displayStatus = 'applying';
            } else if (applyJob.status === 'failed') {
                displayStatus = 'failed';
            }
        }

        let lastError = analyzeJob.last_error || '';
        if (applyJob && applyJob.last_error) {
            lastError = applyJob.last_error;
        }

        return {
            analyzeJob,
            applyJob,
            displayStatus,
            lastError
        };
    });
}

function buildActionHtml(groupedJob) {
    const analyzeJob = groupedJob.analyzeJob;
    const applyJob = groupedJob.applyJob;
    const displayStatus = groupedJob.displayStatus;

    let html = '<div class="action-buttons">';

    html += `<a class="btn btn-sm btn-outline-primary" href="refile_job_view.php?job_id=${analyzeJob.id}&view=check">View Checked Items</a>`;

    if (applyJob && applyJob.status === 'completed') {
        html += `<a class="btn btn-sm btn-outline-success" href="refile_job_view.php?job_id=${analyzeJob.id}&view=completed">View Completed Items</a>`;
        html += `<span class="btn btn-sm btn-success disabled" aria-disabled="true">Refile Already Completed</span>`;
    } else if (displayStatus === 'applying') {
        html += `<span class="btn btn-sm btn-info disabled" aria-disabled="true">Refile In Progress</span>`;
    } else if (analyzeJob.status === 'ready_to_refile') {
        html += `<button class="btn btn-sm btn-success" onclick="startApply(${analyzeJob.id})">Complete Refile</button>`;
    }

    const lockedStatuses = ['running', 'applying', 'queued'];
    const analyzeLocked = lockedStatuses.includes(analyzeJob.status || '');
    const applyLocked = applyJob ? lockedStatuses.includes(applyJob.status || '') : false;
    const deleteDisabled = (analyzeLocked || applyLocked)
        ? 'disabled title="Cannot delete a running job."'
        : '';

    html += `<button class="btn btn-sm btn-danger" onclick="deleteJob(${analyzeJob.id})" ${deleteDisabled}>Delete</button>`;
    html += `</div>`;

    return html;
}

function checkProgressText(analyzeJob) {
    return `${Number(analyzeJob.processed_pairs || 0)}/${Number(analyzeJob.total_pairs || 0)}`;
}

function applyProgressText(applyJob) {
    if (!applyJob) {
        return '—';
    }
    return `${Number(applyJob.processed_pairs || 0)}/${Number(applyJob.total_pairs || 0)}`;
}

function buildStatusHtml(groupedJob) {
    const analyzeJob = groupedJob.analyzeJob;
    const applyJob = groupedJob.applyJob;
    const displayStatus = groupedJob.displayStatus;

    let html = statusBadge(displayStatus);

    if (applyJob) {
        html += `<span class="job-meta-small">Apply job #${escapeHtml(applyJob.id)}</span>`;
    } else {
        html += `<span class="job-meta-small">Check job #${escapeHtml(analyzeJob.id)}</span>`;
    }

    return html;
}

async function loadJobs() {
    if (jobsBusy) {
        return;
    }

    jobsBusy = true;

    try {
        const response = await fetch('refile_jobs_api.php?action=list&_=' + Date.now(), {
            cache: 'no-store'
        });

        const text = await response.text();
        const data = JSON.parse(text);
        const jobs = Array.isArray(data.jobs) ? data.jobs : [];

        const groupedJobs = normalizeJobsForDisplay(jobs);
        const tbody = document.querySelector('#jobsTable tbody');
        tbody.innerHTML = '';

        groupedJobs.forEach(groupedJob => {
            const analyzeJob = groupedJob.analyzeJob;
            const applyJob = groupedJob.applyJob;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${analyzeJob.id}</td>
                <td>
                    ${escapeHtml(analyzeJob.original_filename || '')}
                    ${applyJob ? `<span class="job-meta-small">Items have been previewed and Updates Applied. Ready to reshelf.</span>` : ''}
                </td>
                <td>${prettyDate(analyzeJob.started_at || analyzeJob.created_at || '')}</td>
                <td>${buildStatusHtml(groupedJob)}</td>
                <td>${checkProgressText(analyzeJob)}</td>
                <td>${applyProgressText(applyJob)}</td>
                <td>${Number(analyzeJob.mismatched_tray_errors || 0)}</td>
                <td>${Number(analyzeJob.not_yet_checked_in || 0)}</td>
                <td>${Number(analyzeJob.already_fully_processed || 0)}</td>
                <td>${escapeHtml(groupedJob.lastError || '')}</td>
                <td>${buildActionHtml(groupedJob)}</td>
            `;
            tbody.appendChild(tr);
        });
    } catch (e) {
        console.error('loadJobs failed:', e);
        alert('Job loading failed. Open browser console.');
    } finally {
        jobsBusy = false;
    }
}

async function tickWorker() {
    if (workerBusy) {
        return;
    }

    workerBusy = true;

    try {
        await fetch('refile_worker_tick.php?_=' + Date.now(), {
            cache: 'no-store'
        });
    } catch (e) {
        console.error('tickWorker failed:', e);
    } finally {
        workerBusy = false;
    }
}

async function startApply(jobId) {
    const body = new URLSearchParams();
    body.append('job_id', jobId);

    try {
        const response = await fetch('refile_jobs_api.php?action=start_apply', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        });

        const text = await response.text();
        console.log('startApply:', text);
        await loadJobs();
    } catch (e) {
        console.error('startApply failed:', e);
        alert('Unable to start apply job.');
    }
}

async function deleteJob(jobId) {
    if (!confirm('Delete this job and any related files?')) {
        return;
    }

    const body = new URLSearchParams();
    body.append('job_id', jobId);

    try {
        const response = await fetch('refile_jobs_api.php?action=delete_job', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        });

        const text = await response.text();
        console.log('deleteJob:', text);
        await loadJobs();
    } catch (e) {
        console.error('deleteJob failed:', e);
        alert('Unable to delete job.');
    }
}

async function deleteCompleted() {
    if (!confirm('Delete all completed jobs and their related files?')) {
        return;
    }

    try {
        const response = await fetch('refile_jobs_api.php?action=delete_completed', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: ''
        });

        const text = await response.text();
        console.log('deleteCompleted:', text);
        await loadJobs();
    } catch (e) {
        console.error('deleteCompleted failed:', e);
        alert('Unable to delete completed jobs.');
    }
}

loadJobs();
tickWorker();

setInterval(loadJobs, 10000);    // every 10 seconds
setInterval(tickWorker, 15000);  // every 15 seconds
</script>
</body>
</html>