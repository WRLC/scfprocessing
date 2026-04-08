<script>
    let inactivityTime = 28 * 60 * 1000;
    let activityTimer;

    function resetTimer() {
        clearTimeout(activityTimer);
        activityTimer = setTimeout(function () {
            window.location.href = '../login.php';
        }, inactivityTime);
    }

    document.addEventListener('mousemove', resetTimer);
    document.addEventListener('keypress', resetTimer);
    document.addEventListener('scroll', resetTimer);
    document.addEventListener('click', resetTimer);

    resetTimer();
</script>