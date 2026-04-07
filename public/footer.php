<?php
if (isset($conn) && $conn instanceof mysqli) {
    mysqli_close($conn);
}
?>
<!-- JavaScript at end of body for optimized loading -->
<script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<script src="js/materialize.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    M.FormSelect.init(document.querySelectorAll('select'));
    M.Sidenav.init(document.querySelectorAll('.sidenav'));
    M.Dropdown.init(document.querySelectorAll('.dropdown-trigger'));
    M.Collapsible.init(document.querySelectorAll('.collapsible'));
});
</script>