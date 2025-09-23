<?php
include 'include/access.php';
include 'include/admin_access.php';

// Handle deletion before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $timestamp  = $_POST['timestamp']  ?? '';
    $identifier = $_POST['identifier'] ?? '';
    deleteEntry($timestamp, $identifier);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

function deleteEntry(string $timestamp, string $identifier)
{
    $file     = __DIR__ . '/refile.ndjson';
    $tmpFile  = $file . '.tmp';
    $in  = fopen($file, 'r');
    $out = fopen($tmpFile, 'w');
    while (($line = fgets($in)) !== false) {
        $item = json_decode(trim($line), true);
        if (!is_array($item)) continue;
        if ($item['date'] === $timestamp && $item['barcode'] === $identifier) {
            continue;
        }
        fwrite($out, json_encode($item, JSON_UNESCAPED_SLASHES) . "\n");
    }
    fclose($in);
    fclose($out);
    rename($tmpFile, $file);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>All Refile Entries</title>
  <?php include 'include/refresh.php'; ?>
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
  >
</head>
<body>
<?php include 'include/nav.php'; ?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header">
      <h3 class="mb-0">All Entries</h3>
      <a href="refile_summary.php" class="btn btn-sm btn-info float-right">Show Summary</a>
    </div>
    <ul class="list-group list-group-flush">
      <?php
      $file = __DIR__ . '/refile.ndjson';
      if (file_exists($file) && filesize($file) > 0) {
          $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
          rsort($lines, SORT_STRING);
          foreach ($lines as $line) {
              $row = json_decode($line, true);
              if (!is_array($row)) continue;
              ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong><?= htmlspecialchars($row['date'] ?? '') ?></strong><br>
                  <?= htmlspecialchars($row['name'] ?? '') ?> —
                  <a href="check.php?barcode=<?= htmlspecialchars($row['barcode'] ?? '') ?>"
                     target="_blank"><?= htmlspecialchars($row['barcode'] ?? '') ?></a><br>
                  Tray: <?= htmlspecialchars($row['tray barcode'] ?? '') ?> |
                  <span class="<?= ($row['status'] ?? '') === 'Item In Place' ? 'text-success' : 'text-danger' ?>">
                    <?= htmlspecialchars($row['status'] ?? '') ?>
                  </span> |
                  Step <?= htmlspecialchars($row['step'] ?? '') ?>
                </div>
                <form method="POST" style="margin:0;">
                  <input type="hidden" name="timestamp"
                         value="<?= htmlspecialchars($row['date'] ?? '') ?>">
                  <input type="hidden" name="identifier"
                         value="<?= htmlspecialchars($row['barcode'] ?? '') ?>">
                  <button type="submit" name="delete" class="btn btn-sm btn-danger">
                    Delete
                  </button>
                </form>
              </li>
              <?php
          }
      } else {
          echo '<li class="list-group-item text-center">No entries found.</li>';
      }
      ?>
    </ul>
  </div>
</div>

<?php include 'include/footer.php'; ?>
</body>
</html>