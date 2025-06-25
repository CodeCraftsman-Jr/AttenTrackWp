<?php
/*
Template Name: Demo Extended Test
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
error_log('Demo Extended Attention Test - User IDs:');
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
    <title>Demo Extended Attention Test</title>
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

        #phase-indicator {
            font-size: 24px;
            color: #666;
            margin-bottom: 20px;
        }

        #timer {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 20px 0;
        }

        #game-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin: 20px 0;
        }

        #letter-display {
            font-size: 48px;
            font-weight: bold;
            margin: 20px 0;
            min-height: 60px;
        }

        #input-box {
            font-size: 24px;
            padding: 10px;
            border: 2px solid #3498db;
            border-radius: 8px;
            width: 200px;
            text-align: center;
            margin: 20px 0;
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

        #progress-container {
            margin: 20px 0;
            text-align: center;
        }

        .progress-phase {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin: 0 10px;
            border-radius: 50%;
            background-color: #e0e0e0;
        }

        .progress-phase.active {
            background-color: #3498db;
        }

        .progress-phase.completed {
            background-color: #2ecc71;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <h1>Demo Extended Attention Test</h1>
    
    <!-- Demo Notice -->
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        This is a 20-second demo version. For full access to tests and results, please upgrade your subscription.
    </div>

    <div id="phase-indicator">Phase 1 of 4</div>
    
    <div id="progress-container">
        <div class="progress-phase active" data-phase="1"></div>
        <div class="progress-phase" data-phase="2"></div>
        <div class="progress-phase" data-phase="3"></div>
        <div class="progress-phase" data-phase="4"></div>
    </div>

    <div id="timer">Time left: 20s</div>
    <div id="game-container">
        <button id="start-button">Start Demo Test</button>
        <div id="letter-display"></div>
        <input type="text" id="input-box" class="hidden" maxlength="1">
    </div>

    <div id="results-modal" class="modal">
        <div class="modal-content">
            <h2>Demo Results</h2>
            <p>Phase <span id="current-phase">1</span> Results:</p>
            <p>Correct Responses: <span id="correct-count">0</span></p>
            <p>Wrong Responses: <span id="wrong-count">0</span></p>
            <p>Average Reaction Time: <span id="avg-reaction-time">0</span> ms</p>
            <div class="alert alert-info mt-4">
                <i class="fas fa-star me-2"></i>
                Want to see your full results and track your progress? 
                <a href="<?php echo home_url('/subscription-plans'); ?>" class="alert-link">Upgrade your subscription</a>
            </div>
            <div class="mt-4">
                <button id="continue-button" class="btn btn-primary">Continue to Next Phase</button>
                <a href="<?php echo home_url('/subscription-plans'); ?>" class="btn btn-primary">Upgrade Now</a>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        console.log('Demo Extended Test script starting...');

        const CONFIG = {
            PHASES: 4,
            TIME_PER_PHASE: 20,
            LETTER_INTERVAL: 2000
        };

        let gameStarted = false;
        let gameEnded = false;
        let currentPhase = 1;
        let timerInterval;
        let timeLeft = CONFIG.TIME_PER_PHASE;
        let letterInterval;
        let currentLetter = null;
        let lastLetterTime = 0;
        let correctCount = 0;
        let wrongCount = 0;
        let reactionTimes = [];
        let inputLocked = false;

        const elements = {
            timer: $('#timer'),
            startButton: $('#start-button'),
            letterDisplay: $('#letter-display'),
            inputBox: $('#input-box'),
            resultsModal: $('#results-modal'),
            phaseIndicator: $('#phase-indicator'),
            continueButton: $('#continue-button'),
            correctCount: $('#correct-count'),
            wrongCount: $('#wrong-count'),
            avgReactionTime: $('#avg-reaction-time'),
            currentPhase: $('#current-phase')
        };

        function updateProgressDots() {
            $('.progress-phase').each(function() {
                const phase = $(this).data('phase');
                if (phase < currentPhase) {
                    $(this).removeClass('active').addClass('completed');
                } else if (phase === currentPhase) {
                    $(this).addClass('active').removeClass('completed');
                } else {
                    $(this).removeClass('active completed');
                }
            });
        }

        function generateLetter() {
            if (gameEnded) return;

            const letters = ['b', 'd', 'q', 'r'];
            const remainingTime = timeLeft;
            
            if (remainingTime <= 0) {
                endPhase();
                return;
            }

            const randomLetter = letters[Math.floor(Math.random() * letters.length)];
            currentLetter = randomLetter;
            lastLetterTime = Date.now();
            elements.letterDisplay.text(randomLetter);
            elements.inputBox.val('').prop('disabled', false).focus();
        }

        function startPhase() {
            gameStarted = true;
            gameEnded = false;
            timeLeft = CONFIG.TIME_PER_PHASE;
            correctCount = 0;
            wrongCount = 0;
            reactionTimes = [];
            
            elements.startButton.hide();
            elements.inputBox.removeClass('hidden').focus();
            elements.phaseIndicator.text(`Phase ${currentPhase} of ${CONFIG.PHASES}`);
            updateProgressDots();

            timerInterval = setInterval(() => {
                timeLeft--;
                elements.timer.text(`Time left: ${timeLeft}s`);

                if (timeLeft <= 0) {
                    endPhase();
                }
            }, 1000);

            letterInterval = setInterval(generateLetter, CONFIG.LETTER_INTERVAL);
            generateLetter();
        }

        function endPhase() {
            clearInterval(timerInterval);
            clearInterval(letterInterval);
            elements.letterDisplay.text('');
            elements.inputBox.addClass('hidden');
            
            const avgReactionTime = reactionTimes.length > 0 
                ? Math.round(reactionTimes.reduce((a, b) => a + b) / reactionTimes.length) 
                : 0;

            elements.correctCount.text(correctCount);
            elements.wrongCount.text(wrongCount);
            elements.avgReactionTime.text(avgReactionTime);
            elements.currentPhase.text(currentPhase);
            elements.resultsModal.show();

            if (currentPhase >= CONFIG.PHASES) {
                elements.continueButton.text('Finish Test');
            }
        }

        function startNextPhase() {
            currentPhase++;
            elements.resultsModal.hide();

            if (currentPhase > CONFIG.PHASES) {
                showFinalResults();
                return;
            }

            startPhase();
        }

        function showFinalResults() {
            elements.resultsModal.show();
            elements.continueButton.hide();
            elements.phaseIndicator.text('Test Complete');
        }

        elements.startButton.on('click', startPhase);
        elements.continueButton.on('click', startNextPhase);

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
