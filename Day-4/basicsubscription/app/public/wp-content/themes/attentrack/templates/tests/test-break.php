<?php
/**
 * Template Name: Test Break
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

get_header();
?>

<div class="break-container">
    <div class="break-content">
        <h1 class="break-title">Take a Break</h1>
        
        <div class="break-timer">
            <div class="timer-circle">
                <div class="timer-value">02:00</div>
            </div>
        </div>

        <div class="break-message">
            <p>Please take a moment to rest your eyes and mind.</p>
            <ul class="break-tips">
                <li>Close your eyes for a few seconds</li>
                <li>Take deep breaths</li>
                <li>Stretch your arms and neck</li>
                <li>Look at something in the distance</li>
            </ul>
        </div>

        <button id="skipBreak" class="btn btn-primary break-button" style="display: none;">
            Continue to Next Phase
        </button>
    </div>
</div>

<style>
.break-container {
    min-height: calc(100vh - 100px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background-color: var(--light-bg);
}

.break-content {
    max-width: 600px;
    width: 100%;
    text-align: center;
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.break-title {
    color: var(--secondary-color);
    font-size: 2.5rem;
    margin-bottom: 2rem;
}

.break-timer {
    margin: 2rem 0;
}

.timer-circle {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    border: 8px solid var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    position: relative;
    background: white;
}

.timer-circle::before {
    content: '';
    position: absolute;
    top: -8px;
    left: -8px;
    right: -8px;
    bottom: -8px;
    border-radius: 50%;
    border: 8px solid var(--accent-color);
    opacity: 0.3;
}

.timer-value {
    font-size: 3rem;
    font-weight: bold;
    color: var(--secondary-color);
}

.break-message {
    margin: 2rem 0;
    color: var(--text-color);
}

.break-tips {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
    text-align: left;
    display: inline-block;
}

.break-tips li {
    margin: 0.5rem 0;
    padding-left: 1.5rem;
    position: relative;
}

.break-tips li::before {
    content: '•';
    color: var(--primary-color);
    position: absolute;
    left: 0;
    font-weight: bold;
}

.break-button {
    font-size: 1.2rem;
    padding: 0.75rem 2rem;
    background-color: var(--primary-color);
    border: none;
    border-radius: 25px;
    color: white;
    transition: all 0.3s ease;
}

.break-button:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .break-container {
        padding: 1rem;
    }

    .timer-circle {
        width: 150px;
        height: 150px;
    }

    .timer-value {
        font-size: 2rem;
    }

    .break-title {
        font-size: 2rem;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    let timeLeft = 120; // 2 minutes
    let timerInterval;
    let skipButton = $('#skipBreak');

    function updateTimer() {
        let minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;
        $('.timer-value').text(
            (minutes < 10 ? '0' : '') + minutes + ':' +
            (seconds < 10 ? '0' : '') + seconds
        );

        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            skipButton.show();
        }
        timeLeft--;
    }

    // Start the timer
    timerInterval = setInterval(updateTimer, 1000);

    // Skip break button click handler
    skipButton.click(function() {
        window.location.href = '<?php echo esc_url(home_url('/test-phase-2')); ?>';
    });
});
</script>

<?php get_footer(); ?>
