<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id'], $_SESSION['expire'])) {
    header('Location: login.php');
    exit;
}

if (time() > (int) $_SESSION['expire']) {
    session_destroy();
    header('Location: login.php');
    exit;
}

include 'header.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not available.');
}

$userId = (string) ($_SESSION['user_id'] ?? '');
$submit = (string) ($_GET['submit'] ?? '');

$formurl = 'all_processing_submit.php';

/*
|--------------------------------------------------------------------------
| Optional POST defaults to avoid undefined index warnings
|--------------------------------------------------------------------------
*/
$Name = (string) ($_POST['Name'] ?? '');
$TrayLocation = (string) ($_POST['TrayLocation'] ?? '');
$Count = (string) ($_POST['Count'] ?? '');
$Full = (string) ($_POST['Full'] ?? '');
$Verify = (string) ($_POST['Verify'] ?? '');
$Checked = (string) ($_POST['Checked'] ?? '');
$Library = (string) ($_POST['Library'] ?? '');

/*
|--------------------------------------------------------------------------
| Form field names
|--------------------------------------------------------------------------
*/
$namelibrary = 'Library';
$namestaff = 'Name';
$namebarcode = 'TrayLocation';
$namecount = 'Count';
$namefull = 'Full';
$nameas = 'Checked';
$nameverify = 'Verify';

/*
|--------------------------------------------------------------------------
| Load libraries
|--------------------------------------------------------------------------
*/
$libraries = [];
$sql = "SELECT university FROM LibraryLocations ORDER BY university ASC";
$query = mysqli_query($conn, $sql);

if ($query instanceof mysqli_result) {
    while ($row = mysqli_fetch_assoc($query)) {
        $libraries[] = (string) ($row['university'] ?? '');
    }
    mysqli_free_result($query);
}
?>
<div class="row">
    <div class="col s12 push-m4 m4">
        <div class="card white lighten-1 mt-5">
            <div class="card-content teal-text">
                <span class="card-title teal lighten-5 bold center">
                    Tray/Shelf Location Form: <?php echo htmlspecialchars($userId, ENT_QUOTES, 'UTF-8'); ?>
                </span>

                <div class="row">
                    <style>
                        #hideMe {
                            -moz-animation: cssAnimation 0s ease-in 3s forwards;
                            -webkit-animation: cssAnimation 0s ease-in 3s forwards;
                            -o-animation: cssAnimation 0s ease-in 3s forwards;
                            animation: cssAnimation 0s ease-in 3s forwards;
                            -webkit-animation-fill-mode: forwards;
                            animation-fill-mode: forwards;
                        }
                        @keyframes cssAnimation {
                            to {
                                width: 0;
                                height: 0;
                                overflow: hidden;
                            }
                        }
                        @-webkit-keyframes cssAnimation {
                            to {
                                width: 0;
                                height: 0;
                                visibility: hidden;
                            }
                        }
                    </style>

                    <?php if ($submit === 'true'): ?>
                        <div id="hideMe" class="card-title center" style="color:#4CAF50;">Success!</div>
                    <?php elseif ($submit === 'false'): ?>
                        <div id="hideMe" class="card-title red-text center">
                            This Tray/Shelf Barcode has already been processed. Please try another.
                        </div>
                    <?php elseif ($submit === 'blank'): ?>
                        <div id="hideMe" class="card-title red-text center">
                            Form not submitted. Please fill in all required fields.
                        </div>
                    <?php endif; ?>

                    <form autocomplete="off" action="<?php echo htmlspecialchars($formurl, ENT_QUOTES, 'UTF-8'); ?>" class="col s12" method="POST">
                        <input
                            type="hidden"
                            name="<?php echo htmlspecialchars($namestaff, ENT_QUOTES, 'UTF-8'); ?>"
                            value="<?php echo htmlspecialchars($userId, ENT_QUOTES, 'UTF-8'); ?>"
                        />

                        <div class="row">
                            <div class="input-field col s12">
                                <i class="material-icons prefix">account_balance</i>
                                <select name="<?php echo htmlspecialchars($namelibrary, ENT_QUOTES, 'UTF-8'); ?>" class="validate">
                                    <option value="" disabled selected>Select Library</option>
                                    <?php foreach ($libraries as $libraryOption): ?>
                                        <option value="<?php echo htmlspecialchars($libraryOption, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($libraryOption, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="helper-text" data-error="wrong" data-success="Complete">Select Library</span>
                                <span class="new badge white red-text right" data-badge-caption="Required"></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s12">
                                <i class="material-icons prefix">line_style</i>
                                <input
                                    name="<?php echo htmlspecialchars($namebarcode, ENT_QUOTES, 'UTF-8'); ?>"
                                    id="icon_prefix2"
                                    type="text"
                                    class="validate"
                                    value="<?php echo htmlspecialchars($TrayLocation, ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                <label for="icon_prefix2">Tray/Shelf Barcode</label>
                                <span class="new badge white red-text right" data-badge-caption="Required"></span>
                                <span class="helper-text" data-error="wrong" data-success="Complete">
                                    Scan in Tray/Shelf Barcode
                                </span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s12">
                                <i class="material-icons prefix">add_shopping_cart</i>
                                <input
                                    name="<?php echo htmlspecialchars($namecount, ENT_QUOTES, 'UTF-8'); ?>"
                                    id="icon_prefix3"
                                    type="text"
                                    class="validate"
                                    value="<?php echo htmlspecialchars($Count, ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                <label for="icon_prefix3">Tray/Shelf Count</label>
                                <span class="new badge white red-text right" data-badge-caption="Required"></span>
                                <span class="helper-text" data-error="wrong" data-success="Complete">Numbers Only</span>
                            </div>
                        </div>

                        <div class="row">
                            <span class="new badge white red-text left" data-badge-caption="All Checkboxes required except 'Tray/Shelf Full?'"></span>
                        </div>

                        <div class="row">
                            <div class="input-field col s6">
                                <i class="material-icons prefix">shopping_cart</i>
                                <label>
                                    <input
                                        type="checkbox"
                                        value="Yes"
                                        name="<?php echo htmlspecialchars($namefull, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?php echo $Full === 'Yes' ? 'checked' : ''; ?>
                                    />
                                    <span>Tray/Shelf Full?</span>
                                </label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s10">
                                <i class="material-icons prefix">search</i>
                                <label>
                                    <input
                                        type="checkbox"
                                        value="Yes"
                                        name="<?php echo htmlspecialchars($nameas, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?php echo $Checked === 'Yes' ? 'checked' : ''; ?>
                                    />
                                    <span>Advanced Search in Alma Completed</span>
                                </label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s10">
                                <i class="material-icons prefix">spellcheck</i>
                                <label>
                                    <input
                                        type="checkbox"
                                        value="Yes"
                                        name="<?php echo htmlspecialchars($nameverify, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?php echo $Verify === 'Yes' ? 'checked' : ''; ?>
                                    />
                                    <span>Verified all information is correct</span>
                                </label>
                            </div>
                        </div>

                        <input type="hidden" name="submit" value="Submit" />
                        <br><br>

                        <button class="btn waves-effect waves-light right green" type="submit">
                            Submit <i class="material-icons right">send</i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>