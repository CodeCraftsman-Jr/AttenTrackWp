<?php
/*
Template Name: Demo Divided Test
*/

// Start session if not already started
if (!session_id()) {
    session_start();
}

// Set demo test parameters
$_SESSION['test_time_limit'] = 20;
$_SESSION['disable_db_storage'] = true;
$_SESSION['is_demo'] = true;

// Get current user's IDs
$user_id = get_current_user_id();
$user_ids = get_user_ids($user_id);

// Log IDs for verification
error_log('Demo Divided Attention Test - User IDs:');
error_log('User ID: ' . $user_id);
error_log('Profile ID: ' . $user_ids['profile_id']);
error_log('Test ID: ' . $user_ids['test_id']);
error_log('User Code: ' . $user_ids['user_code']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo Divided Attention Test</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            text-align: center;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        #grid {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .color-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 20px;
            max-width: 800px;
            width: 100%;
            aspect-ratio: 3/2;
            margin: 0 auto;
        }

        .color-box {
            border-radius: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: transparent;
            font-weight: bold;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            aspect-ratio: 1;
            width: 100%;
            height: 100%;
            min-height: 100px;
        }

        .color-box:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        .clicked {
            transform: scale(0.95);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        #timer {
            font-size: 48px;
            margin: 20px 0;
            color: #2c3e50;
            font-weight: bold;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 90%;
        }

        .modal-header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        #start-button {
            padding: 15px 30px;
            font-size: 24px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin: 20px 0;
            transition: all 0.3s ease;
        }

        #start-button:hover {
            background-color: #2980b9;
            transform: scale(1.05);
        }

        #test-container {
            display: none;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <?php
    // Define ajaxurl for frontend
    echo '<script type="text/javascript">
        var ajaxurl = "' . admin_url('admin-ajax.php') . '";
    </script>';
    ?>
    <div id="grid">
        <h1>Demo Divided Attention Test</h1>

        <!-- Demo Notice -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            This is a 20-second demo version. For full access to tests and results, please upgrade your subscription.
        </div>

        <div id="timer">Time left: 20s</div>
        <button id="start-button">Start Demo Test</button>
        <div class="color-container">
            <div class="color-box" data-color="red"></div>
            <div class="color-box" data-color="blue"></div>
            <div class="color-box" data-color="green"></div>
            <div class="color-box" data-color="yellow"></div>
            <div class="color-box" data-color="purple"></div>
            <div class="color-box" data-color="orange"></div>
        </div>
    </div>

    <div id="results-modal" class="modal">
        <div class="modal-content">
            <h2>Demo Results</h2>
            <p>Correct Responses: <span id="correct-count">0</span></p>
            <p>Wrong Responses: <span id="wrong-count">0</span></p>
            <p>Missed Responses: <span id="missed-count">0</span></p>
            <p>Average Reaction Time: <span id="avg-reaction-time">0</span> ms</p>
            <div class="alert alert-info mt-4">
                <i class="fas fa-star me-2"></i>
                Want to see your full results and track your progress? 
                <a href="<?php echo home_url('/subscription-plans'); ?>" class="alert-link">Upgrade your subscription</a>
            </div>
            <div class="mt-4">
                <button onclick="location.reload()" class="btn btn-secondary">Try Again</button>
                <a href="<?php echo home_url('/subscription-plans'); ?>" class="btn btn-primary">Upgrade Now</a>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        console.log('Demo Divided Test script starting...');
        
        const colors = ['red', 'blue', 'green', 'yellow', 'purple', 'orange'];
        const colorCodes = {
            red: '#FF0000',
            blue: '#0000FF',
            green: '#00FF00',
            yellow: '#FFFF00',
            purple: '#800080',
            orange: '#FFA500'
        };

        let gameStarted = false;
        let gameEnded = false;
        let timerInterval;
        let timeLeft = 20;
        let currentAudioColor = null;
        let lastAudioPlayTime = 0;
        let correctCount = 0;
        let wrongCount = 0;
        let missedCount = 0;
        let totalColorsShown = 0;
        let reactionTimes = [];
        let audioTimeout = null;

        function shuffleColors() {
            const boxes = $('.color-box').toArray();
            for (let i = boxes.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                boxes[i].style.backgroundColor = colorCodes[$(boxes[j]).data('color')];
                boxes[j].style.backgroundColor = colorCodes[$(boxes[i]).data('color')];
                const tempColor = $(boxes[i]).data('color');
                $(boxes[i]).data('color', $(boxes[j]).data('color'));
                $(boxes[j]).data('color', tempColor);
            }
        }

        function playRandomColor() {
            if (gameEnded) return;

            shuffleColors(); // Shuffle colors before playing new audio
            const randomColor = colors[Math.floor(Math.random() * colors.length)];
            currentAudioColor = randomColor;
            totalColorsShown++;
            
            // Use ResponsiveVoice to speak the color
            if (window.responsiveVoice) {
                responsiveVoice.speak(randomColor, "UK English Female", {
                    pitch: 1,
                    rate: 1,
                    volume: 1
                });
            }

            lastAudioPlayTime = Date.now();
            
            if (audioTimeout) {
                clearTimeout(audioTimeout);
            }
            audioTimeout = setTimeout(() => {
                if (currentAudioColor) {
                    missedCount++;
                    currentAudioColor = null;
                }
                if (!gameEnded) {
                    playRandomColor(); // Only play next if game hasn't ended
                }
            }, 2000);
        }

        $('.color-box').on('click', function() {
            if (!gameStarted || gameEnded || !currentAudioColor) return;

            const clickedColor = $(this).data('color');
            const reactionTime = Date.now() - lastAudioPlayTime;

            if (clickedColor === currentAudioColor) {
                correctCount++;
                reactionTimes.push(reactionTime);
            } else {
                wrongCount++;
            }

            clearTimeout(audioTimeout);
            currentAudioColor = null;
            
            // Start next color after a short delay
            setTimeout(() => {
                if (!gameEnded) {
                    playRandomColor();
                }
            }, 500); // Half second delay before next color
        });

        $('#start-button').on('click', function() {
            if (gameStarted) return;
            
            gameStarted = true;
            $(this).hide();
            
            shuffleColors();
            playRandomColor();

            timerInterval = setInterval(() => {
                timeLeft--;
                $('#timer').text(`Time left: ${timeLeft}s`);

                if (timeLeft <= 0) {
                    endGame();
                }
            }, 1000);
        });

        function endGame() {
            gameEnded = true;
            clearInterval(timerInterval);
            if (audioTimeout) {
                clearTimeout(audioTimeout);
            }
            
            const avgReactionTime = reactionTimes.length > 0 
                ? Math.round(reactionTimes.reduce((a, b) => a + b) / reactionTimes.length) 
                : 0;

            $('#correct-count').text(correctCount);
            $('#wrong-count').text(wrongCount);
            $('#missed-count').text(missedCount);
            $('#avg-reaction-time').text(avgReactionTime);
            $('#results-modal').show();
        }
    });
    </script>
    <script src="https://code.responsivevoice.org/responsivevoice.js?key=RMQ1WzP6"></script>
</body>
</html>

<?php get_footer(); ?>
