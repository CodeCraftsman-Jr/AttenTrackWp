<?php
/**
 * Template Name: Test Break
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$next_phase = isset($_GET['next']) ? sanitize_text_field($_GET['next']) : '';
if (!$next_phase) {
    wp_redirect(home_url('/'));
    exit;
}
?>

<div class="break-container">
    <h1 class="break-title">Take a Short Break</h1>
    <div id="timer" class="break-timer">15</div>
    <p class="break-text">Please take a moment to rest your eyes and mind.</p>
</div>

<style>
body {
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
}

.break-container {
    text-align: center;
    padding: 50px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 100px);
}

.break-title {
    color: var(--secondary-color);
    font-size: 36px;
    margin-bottom: 30px;
}

.break-timer {
    font-size: 72px;
    font-weight: bold;
    color: var(--primary-color);
    margin: 30px 0;
}

.break-text {
    font-size: 24px;
    color: var(--secondary-color);
    margin-top: 20px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const timerElement = document.getElementById('timer');
    let timeLeft = 15;

    // Start the timer immediately
    const timerInterval = setInterval(() => {
        timeLeft--;
        timerElement.textContent = timeLeft;
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            window.location.href = '<?php echo esc_url(home_url('/' . $next_phase)); ?>';
        }
    }, 1000);
});
</script>
