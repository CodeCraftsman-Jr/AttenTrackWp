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

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .break-container {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        .break-title {
            color: #333;
            font-size: 32px;
            margin-bottom: 30px;
        }

        .break-timer {
            font-size: 48px;
            color: #007bff;
            margin: 30px 0;
            font-weight: bold;
        }

        .break-text {
            font-size: 18px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .break-tip {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-style: italic;
            color: #495057;
        }
    </style>
</head>
<body>
<div class="break-container">
    <h1 class="break-title">Take a Short Break</h1>
    <div id="timer" class="break-timer">15</div>
    <p class="break-text">Please take a moment to rest your eyes and mind.</p>
</div>

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
</body>
</html>
