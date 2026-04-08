<?php
require_once 'connect.php';

$login = isset($_GET['login']) ? trim($_GET['login']) : '';

$staffNames = array();

$sql = "SELECT name FROM Staff ORDER BY name ASC";
$query = mysqli_query($conn, $sql);

if ($query instanceof mysqli_result) {
    while ($row = mysqli_fetch_assoc($query)) {
        $staffNames[] = isset($row['name']) ? $row['name'] : '';
    }
    mysqli_free_result($query);
}

if (isset($conn) && $conn instanceof mysqli) {
    mysqli_close($conn);
}

function h($value)
{
    return htmlspecialchars((string)(isset($value) ? $value : ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SCF Processing Login</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css" media="screen,projection"/>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body class="grey lighten-3">
<nav>
    <div class="nav-wrapper blue">
        <img src="images/wrlc-logo-white.png" height="50" style="margin:5px 0 0 20px; position:absolute;" alt="WRLC logo">
        <a href="#" class="brand-logo" style="margin-left:90px;">SCF Processing</a>
    </div>
</nav>

<div class="row">
    <div class="col s12 push-m3 m6">
        <div class="card white lighten-1 mt-6">
            <div class="card-content blue-text">
                <span class="card-title">Staff Login</span>

                <?php if ($login === 'false'): ?>
                    <h3 class="card-title" style="color:#ee6e73;">Login failed. Please try again.</h3>
                <?php endif; ?>

                <div class="row">
                    <form action="create_session.php" class="col s12" method="post">
                        <div class="row">
                            <div class="input-field col s10">
                                <i class="material-icons prefix">account_circle</i>
                                <select name="username" required>
                                    <option value="" disabled selected>Select Name</option>
                                    <?php foreach ($staffNames as $staffName): ?>
                                        <option value="<?php echo h($staffName); ?>">
                                            <?php echo h($staffName); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label>Select Name</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s10">
                                <i class="material-icons prefix">lock</i>
                                <input id="password" name="password" type="password" class="validate" required>
                                <label for="password">Password</label>
                            </div>
                        </div>

                        <button class="btn waves-effect waves-light right" type="submit">
                            Login <i class="material-icons right">exit_to_app</i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<script src="js/materialize.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    M.FormSelect.init(document.querySelectorAll('select'));
});
</script>
</body>
</html>