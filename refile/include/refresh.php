<script>
        let inactivityTime = 30 * 60 * 1000; // 30 minutes in milliseconds
        let activityTimer;

        function resetTimer() {
            clearTimeout(activityTimer);
            activityTimer = setTimeout(() => {
                location.reload(); // Refresh page after 30 minutes of inactivity
            }, inactivityTime);
        }

        // Detect user activity
        document.addEventListener("mousemove", resetTimer);
        document.addEventListener("keypress", resetTimer);
        document.addEventListener("scroll", resetTimer);
        document.addEventListener("click", resetTimer);

        // Initialize timer on page load
        resetTimer();
    </script>