<?php
/*
Template Name: Demo Selective Attention Test
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
error_log('Demo Selective Attention Test - User IDs:');
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
    <title>Demo Selective Attention Test</title>
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
        }

        h1 {
            color: #333;
            font-size: 36px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        #timer {
            font-size: 24px;
            font-weight: bold;
            margin-top: 10px;
            color: #333;
        }

        #letter {
            font-size: 200px;
            color: #333;
            min-width: 200px;
            min-height: 240px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #inputBox {
            font-size: 28px;
            padding: 10px;
            width: 130px;
            margin-top: 20px;
            border-radius: 5px;
            border: 2px solid #ccc;
            transition: all 0.3s ease;
            text-align: center;
            display: none;
        }

        #inputBox:focus {
            border-color: #40E0D0;
            outline: none;
            box-shadow: 0 0 10px rgba(64, 224, 208, 0.3);
        }

        #startButton {
            font-size: 24px;
            padding: 15px 30px;
            margin-top: 30px;
            cursor: pointer;
            background-color: #40E0D0;
            color: white;
            border: none;
            border-radius: 5px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        #startButton:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(0, 0, 0, 0.15);
        }

        #startButton:active {
            transform: translateY(0);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }

        .results {
            display: none;
            margin-top: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .results h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .results table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .results th, .results td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .results th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .alert {
            padding: 15px;
            margin: 20px 0;
            border: 1px solid transparent;
            border-radius: 4px;
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
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
    </style>
</head>
<body>
    <h1>Demo Selective Attention Test</h1>
    
    <!-- Demo Notice -->
    <div class="alert">
        <i class="fas fa-info-circle"></i>
        This is a 20-second demo version. For full access to tests and results, please upgrade your subscription.
    </div>

    <div id="timer">Time left: 20s</div>
    <div id="letter"></div>
    <input type="text" id="inputBox" maxlength="1">
    <button id="startButton">Start Demo Test</button>

    <div id="results-modal" class="modal">
        <div class="modal-content">
            <h2>Demo Results</h2>
            <p>Correct Responses: <span id="correct-count">0</span></p>
            <p>Wrong Responses: <span id="wrong-count">0</span></p>
            <p>Average Reaction Time: <span id="avg-reaction-time">0</span> ms</p>
            <div class="alert mt-4">
                <i class="fas fa-star"></i>
                Want to see your full results and track your progress? 
                <a href="<?php echo home_url('/subscription-plans'); ?>">Upgrade your subscription</a>
            </div>
            <div class="mt-4">
                <button id="retryButton" class="btn btn-secondary">Try Again</button>
                <a href="<?php echo home_url('/subscription-plans'); ?>" class="btn btn-primary">Upgrade Now</a>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        console.log('Demo Selective Test script starting...');

        const TEST_DURATION = 20; // seconds
        const LETTER_INTERVAL = 2000; // 2 seconds

        let gameStarted = false;
        let gameEnded = false;
        let timeLeft = TEST_DURATION;
        let timerInterval;
        let letterInterval;
        let currentLetter = null;
        let lastLetterTime = 0;
        let correctCount = 0;
        let wrongCount = 0;
        let reactionTimes = [];
        let inputLocked = false;

        const elements = {
            timer: $('#timer'),
            startButton: $('#startButton'),
            letter: $('#letter'),
            inputBox: $('#inputBox'),
            resultsModal: $('#results-modal'),
            correctCount: $('#correct-count'),
            wrongCount: $('#wrong-count'),
            avgReactionTime: $('#avg-reaction-time'),
            retryButton: $('#retryButton')
        };

        function generateLetter() {
            if (gameEnded) return;

            const letters = ['b', 'd', 'q', 'r'];
            const remainingTime = timeLeft;
            
            if (remainingTime <= 0) {
                endTest();
                return;
            }

            const randomLetter = letters[Math.floor(Math.random() * letters.length)];
            currentLetter = randomLetter;
            lastLetterTime = Date.now();
            elements.letter.text(randomLetter);
            elements.inputBox.val('').prop('disabled', false).focus();
        }

        function startTest() {
            gameStarted = true;
            gameEnded = false;
            timeLeft = TEST_DURATION;
            correctCount = 0;
            wrongCount = 0;
            reactionTimes = [];
            
            elements.startButton.hide();
            elements.inputBox.show().focus();

            timerInterval = setInterval(() => {
                timeLeft--;
                elements.timer.text(`Time left: ${timeLeft}s`);

                if (timeLeft <= 0) {
                    endTest();
                }
            }, 1000);

            letterInterval = setInterval(generateLetter, LETTER_INTERVAL);
            generateLetter();
        }

        function endTest() {
            gameEnded = true;
            clearInterval(timerInterval);
            clearInterval(letterInterval);
            elements.letter.text('');
            elements.inputBox.hide();
            
            const avgReactionTime = reactionTimes.length > 0 
                ? Math.round(reactionTimes.reduce((a, b) => a + b) / reactionTimes.length) 
                : 0;

            elements.correctCount.text(correctCount);
            elements.wrongCount.text(wrongCount);
            elements.avgReactionTime.text(avgReactionTime);
            elements.resultsModal.show();
        }

        elements.startButton.on('click', startTest);
        elements.retryButton.on('click', function() {
            elements.resultsModal.hide();
            elements.startButton.show();
        });

        elements.inputBox.on('input', function(e) {
            if (!gameStarted || gameEnded || inputLocked) return;

            const userInput = e.target.value.toLowerCase();
            if (userInput.length > 0) {
                inputLocked = true;
                const reactionTime = Date.now() - lastLetterTime;

                if (userInput === currentLetter) {
                    correctCount++;
                    reactionTimes.push(reactionTime);
                } else {
                    wrongCount++;
                }

                setTimeout(() => {
                    inputLocked = false;
                    elements.inputBox.val('');
                    generateLetter();
                }, 200);
            }
        });
    });
    </script>
</body>
</html>

<?php get_footer(); ?>
