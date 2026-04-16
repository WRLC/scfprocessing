<?php
declare(strict_types=1);

session_start();

function h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$now = time();

if (!isset($_SESSION['expire']) || $now > (int)$_SESSION['expire']) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

include 'header.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('Database connection not available in crosscheck.php');
    header('Location: login.php');
    exit;
}

$name = (string)$_SESSION['user_id'];
$submit = $_GET['submit'] ?? '';
$formurl = 'all_crosscheck_submit.php';

$unfinishedRows = [];
$unfinishedCount = 0;

$sql = "
    SELECT ProcessingKey, ptraylocation
    FROM ProcessingAll
    WHERE ccname IS NULL OR ccname = ''
    ORDER BY ptimestamp DESC
";

$result = mysqli_query($conn, $sql);

if ($result instanceof mysqli_result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $unfinishedRows[] = $row;
    }
    $unfinishedCount = count($unfinishedRows);
    mysqli_free_result($result);
}
?>
<?php include('header.php'); ?>
<div class="row">
    <div class="col s12 push-m4 m4">
        <div class="card white lighten-1 mt-5">
            <div class="card-content teal-text">
                <span class="card-title teal lighten-5 teal-text bold center">
                    Cross Check Form: <?php echo h($_SESSION['user_id']); ?>
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
                        <div id="hideMe" class="card-title green-text center">Success!</div>
                    <?php endif; ?>

                    <?php if ($submit === 'blank'): ?>
                        <div id="hideMe" class="card-title red-text center">
                            Form not submitted. Please fill in all required fields.
                        </div>
                    <?php endif; ?>

                    <?php
                    $namestaff = 'ccname';
                    $namebarcode = 'cctraylocation';
                    $namescan = 'ccscan';
                    $namecount = 'cccount';
                    $nameas = 'ccchecked';
                    $nameverify = 'ccverify';
                    ?>

                    <form autocomplete="off" action="<?php echo h($formurl); ?>" class="col s12" method="POST">
                        <input type="hidden" name="usp" value="pp_url" />
                        <input type="hidden" name="<?php echo h($namestaff); ?>" value="<?php echo h($name); ?>" />

                        <div class="row">
                            <div class="input-field col s12">
                                <i class="material-icons prefix">line_style</i>
                                <select name="ProcessingKey">
                                    <option value="" disabled selected>Select Tray/Shelf Barcode</option>
                                    <?php foreach ($unfinishedRows as $row): ?>
                                        <option value="<?php echo (int)$row['ProcessingKey']; ?>">
                                            <?php echo h($row['ptraylocation']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <?php if ($unfinishedCount > 0): ?>
                                    <span class="red-text center" style="position:absolute; font-size:12px; margin:-5px 0 0 44px;">
                                        There are currently <?php echo (int)$unfinishedCount; ?> unfinished cross checked trays/shelves
                                    </span>
                                <?php else: ?>
                                    <label class="green-text">All Cross Check Items Have Been Processed.</label>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s12">
                                <i class="material-icons prefix">add_shopping_cart</i>
                                <input name="<?php echo h($namecount); ?>" id="icon_prefix3" type="text" class="validate">
                                <label for="icon_prefix3">Tray/Shelf Count</label>
                                <span class="helper-text" data-error="wrong" data-success="Complete">Numbers Only</span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s10">
                                <i class="material-icons prefix">library_books</i>
                                <label>
                                    <input type="checkbox" value="Yes" name="<?php echo h($namescan); ?>" />
                                    <span>Scan in Items in Alma</span>
                                </label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s10">
                                <i class="material-icons prefix">search</i>
                                <label>
                                    <input type="checkbox" value="Yes" name="<?php echo h($nameas); ?>" />
                                    <span>Advanced Search in Alma Completed</span>
                                </label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s10">
                                <i class="material-icons prefix">spellcheck</i>
                                <label>
                                    <input type="checkbox" value="Yes" name="<?php echo h($nameverify); ?>" />
                                    <span>Verified all information is correct</span>
                                </label>
                            </div>
                        </div>

                        <br />
                        <span class="new badge white red-text left" data-badge-caption="All Fields Required"></span>

                        <input type="hidden" name="submit" value="Submit" />
                        <br />
                        <br />

                        <button class="btn waves-effect waves-light right green" type="submit">
                            Submit <i class="material-icons right">send</i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
</body>
</html>