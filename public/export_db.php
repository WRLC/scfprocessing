<?php
// ---- Database connection ----
$servername = $_ENV['DB_SERVERNAME'] ?? getenv('DB_SERVERNAME');
	$username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME');
	$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');
	$dbname = $_ENV['DB_DBNAME'] ?? getenv('DB_DBNAME');

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ---- Retrieve all table names from the database ----
$tables = [];
$res = $conn->query("SHOW TABLES");
if ($res) {
    while ($row = $res->fetch_array(MYSQLI_NUM)) {
        $tables[] = $row[0];
    }
    $res->free();
}

// ---- Handle export ----
if (isset($_POST['export']) && isset($_POST['table_name'])) {
    $raw   = $_POST['table_name'];
    $table = trim($raw, " \t\n\r\0\x0B`'\"");

    // Validate table name exists in the database
    if (!in_array($table, $tables, true)) {
        die("Invalid table selection: " . htmlspecialchars($table));
    }

    // Retrieve all rows from selected table
    $sql = "SELECT * FROM `" . $conn->real_escape_string($table) . "`";
    $result = $conn->query($sql);
    if (!$result) {
        die("Error retrieving data: " . $conn->error);
    }

    // ---- Prepare CSV download ----
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename={$table}_export.csv");

    $out = fopen('php://output', 'w');
    if ($result->num_rows > 0) {
        $first = $result->fetch_assoc();
        fputcsv($out, array_keys($first)); // column headers
        fputcsv($out, $first);             // first row
        while ($row = $result->fetch_assoc()) {
            fputcsv($out, $row);
        }
    } else {
        fputcsv($out, ["No records found in '{$table}'"]);
    }

    fclose($out);
    $result->free();
    $conn->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Export MySQL Table to CSV</title>
<style>
  body { font-family: system-ui, Arial, sans-serif; margin: 48px; background: #fafafa; }
  form { display:inline-block; background:#fff; padding:24px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.06); }
  select, button { padding:10px; margin-top:10px; width:100%; font-size:16px; }
  button { background:#1f6feb; color:#fff; border:none; border-radius:6px; cursor:pointer; }
  button:hover { filter:brightness(.95); }
  .note { margin-top:20px; color:#555; }
</style>
</head>
<body>
  <h2>Export Table Data to CSV</h2>
  <p>Database: <strong><?php echo htmlspecialchars($dbname); ?></strong></p>

  <?php if (!empty($tables)): ?>
    <form method="post" action="">
      <label for="table_name">Select Table:</label><br>
      <select name="table_name" id="table_name" required>
        <option value="">-- Choose a table --</option>
        <?php foreach ($tables as $tbl): ?>
          <option value="<?php echo htmlspecialchars($tbl); ?>"><?php echo htmlspecialchars($tbl); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" name="export">Export to CSV</button>
    </form>
  <?php else: ?>
    <p style="color:#b00020;">No tables found in the database <strong><?php echo htmlspecialchars($dbname); ?></strong>.</p>
  <?php endif; ?>

  <p class="note">Choose a table and click “Export to CSV” to download its contents.</p>
</body>
</html>