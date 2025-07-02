<?php include 'include/access.php'; ?>
<?php
// Get API Keys
include 'include/apikey.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Items on Hold Shelf</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'include/nav.php'; ?>

<?php
$baseUrl = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports';
$params = '?path=%2Fshared%2FShared+storage+institution%2FReports%2FAPI%2FAPI+Tray+Check+-+SCF+Hold+Shelf&limit=1000&col_names=true&apikey=' . $api_key;
$url = $baseUrl . $params;

$allRows = [];
$columnsToDisplay = ['Column1','Column3','Column4','Column5','Column7'];

do {
    $xml = simplexml_load_file($url);
    if (!$xml) {
        die('Error: Unable to fetch XML data.');
    }
    if (isset($xml->QueryResult->ResultXml->rowset->Row)) {
        foreach ($xml->QueryResult->ResultXml->rowset->Row as $row) {
            $rowArray = [];
            foreach ($row->attributes() as $k => $v) {
                $rowArray[$k] = (string)$v;
            }
            foreach ($row->children() as $k => $v) {
                $rowArray[$k] = (string)$v;
            }
            $allRows[] = $rowArray;
        }
    }
    $isFinished = (string)$xml->QueryResult->IsFinished;
    if ($isFinished === 'false') {
        $token = (string)$xml->QueryResult->ResumptionToken;
        $url = $baseUrl . $params . '&token=' . $token;
    } else {
        $url = null;
    }
} while ($url);

$totalCount = count($allRows);
?>

<div class="container mt-4">
  <h3 class="text-center">Number of Items on Hold Shelf: <?= htmlspecialchars($totalCount) ?></h3>

  <?php if ($totalCount > 0): ?>
    <div class="list-group mt-4">
      <?php $i = 1; foreach ($allRows as $row): ?>
        <div class="list-group-item">
          <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1"><?= $i++ ?>. <?= htmlspecialchars($row['Column1'] ?? '') ?></h5>
          </div>
          <p class="mb-1">
            <strong>Date:</strong> <?= htmlspecialchars($row['Column3'] ?? '') ?><br>
            <?php


// Add the button in a new cell
echo '<strong>Barcode: </strong><a href="check.php?barcode='
   . htmlspecialchars($row['Column4'] ?? '', ENT_QUOTES, 'UTF-8')
   . '">'
   . htmlspecialchars($row['Column4'] ?? '', ENT_QUOTES, 'UTF-8')
   . '</a><br />';
            ?>
            
          
            <strong>Tray Barcode:</strong> <?= htmlspecialchars($row['Column5'] ?? '') ?><br>
            <strong>IN3:</strong> <?= htmlspecialchars($row['Column7'] ?? '') ?>
          </p>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="text-center mt-4">No rows found.</p>
  <?php endif; ?>

  <div class="mt-3">
    <a href="refile_summary.php" class="btn btn-info">Show Summary</a>
  </div>
</div>

<?php include 'include/footer.php'; ?>
</body>
</html>