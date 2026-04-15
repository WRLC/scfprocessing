<script>
    let inactivityTime = 28 * 60 * 1000;
    let activityTimer;

    function resetTimer() {
        clearTimeout(activityTimer);
        activityTimer = setTimeout(function () {
            window.location.href = '/login.php';
        }, inactivityTime);
    }

    ['mousemove', 'keypress', 'scroll', 'click', 'input', 'change'].forEach(function(eventName) {
        document.addEventListener(eventName, resetTimer);
    });

    resetTimer();
</script>