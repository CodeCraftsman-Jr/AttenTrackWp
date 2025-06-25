<?php
/*
Template Name: Selective Attention Test
*/

if (!session_id()) {
    session_start();
}

// Get current user's IDs
$user_id = get_current_user_id();
$user_ids = get_user_ids($user_id);

// Get test settings from session
$time_limit = isset($_SESSION['test_time_limit']) ? $_SESSION['test_time_limit'] : 60; // Default 60 seconds

// For paid users, ensure it's 60 seconds
if (!isset($_SESSION['disable_db_storage']) || !$_SESSION['disable_db_storage']) {
    $time_limit = 60;
}

// For free users, always use 15 seconds
$disable_db_storage = isset($_SESSION['disable_db_storage']) ? $_SESSION['disable_db_storage'] : false;
if ($disable_db_storage) {
    $time_limit = 15;
}

// Log IDs for verification
error_log('Selective Attention Test - User IDs:');
error_log('User ID: ' . $user_id);
error_log('Profile ID: ' . $user_ids['profile_id']);
error_log('Test ID: ' . $user_ids['test_id']);
error_log('User Code: ' . $user_ids['user_code']);
error_log('Time Limit: ' . $time_limit . 's');
error_log('DB Storage: ' . ($disable_db_storage ? 'Disabled' : 'Enabled'));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Selective Attention Test</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            text-align: center;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 30px;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f5f5f5;
        }

        h1 {
            color: black;
            font-size: 36px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 20px;
        }

        #timer {
            font-size: 24px;
            font-weight: bold;
            margin-top: 10px;
            color: black;
        }

        #letter {
            font-size: 200px;
            color: black;
            min-width: 200px;
            min-height: 240px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            margin: 20px auto;
        }

        #inputBox {
            font-size: 28px;
            padding: 10px;
            width: 130px;
            margin-top: 20px;
            border-radius: 5px;
            border: 2px solid #ccc;
            transition: border 0.3s ease;
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
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        #startButton:hover {
            background-color: #40E0D0;
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(0, 0, 0, 0.15);
        }

        .log {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 16px;
            background: rgba(0, 0, 0, 0.8);
            padding: 15px;
            border-radius: 5px;
            max-width: 250px;
            display: none;
        }

        #test-id {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 18px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            overflow: auto;
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 30px;
            border-radius: 10px;
            width: 80%;
            max-width: 500px;
            text-align: center;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .modal-body {
            font-size: 18px;
            margin-bottom: 20px;
            color: black;
        }

        .modal-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .modal-button:hover {
            background-color: #45a049;
        }

        @keyframes letterChange {
            0% {
                transform: translateX(-100%) scale(0.5);
                opacity: 0;
            }
            50% {
                transform: translateX(0) scale(1.2);
                opacity: 0.8;
            }
            100% {
                transform: translateX(0) scale(1);
                opacity: 1;
            }
        }

        .letter-change {
            animation: letterChange 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
    </style>
</head>
<body>
    <h1>Selective Attention Test</h1>
    
    <div id="timer">Time Remaining: <?php echo $time_limit; ?>s</div>
    <div id="test-info">
        <p>Profile ID: <?php echo esc_html($user_ids['profile_id']); ?></p>
        <p>Test ID: <?php echo esc_html($user_ids['test_id']); ?></p>
        <p>User Code: <?php echo esc_html($user_ids['user_code']); ?></p>
    </div>
    <?php if ($time_limit === 15) { ?>
        <div class="alert alert-info">This is a trial test limited to 15 seconds. Subscribe to get full access.</div>
    <?php } ?>
    <div id="letter"></div>

    <input type="text" id="inputBox" maxlength="1" autocomplete="off" placeholder="Type Here" autofocus>
    <button id="startButton">Start Test</button>
    
    <div class="log" hidden>
        <p>Total Letters: <span id="totalLetters">0</span></p>
        <p>P Letters: <span id="pLetters">0</span></p>
        <p>Correct: <span id="correctCount">0</span></p>
        <p>Unattempted: <span id="unattemptedCount">0</span></p>
        <p>Wrong: <span id="wrongCount">0</span></p>
    </div>

    <!-- Modal -->
    <div id="gameOverModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" id="modalHeader"></div>
            <div class="modal-body" id="modalBody"></div>
            <button class="modal-button" id="continueButton">Continue</button>
        </div>
    </div>

    <script>
        console.log('Test Phase 0 script starting...');
        
        // Get user data from localStorage
        const userData = JSON.parse(localStorage.getItem('userData') || '{}');
        const testId = userData.testId || '';

        // Pass IDs to JavaScript for logging
        const userIds = {
            userId: '<?php echo esc_js($user_id); ?>',
            profileId: '<?php echo esc_js($user_ids['profile_id']); ?>',
            testId: '<?php echo esc_js($user_ids['test_id']); ?>',
            userCode: '<?php echo esc_js($user_ids['user_code']); ?>'
        };
        
        // Log IDs in browser console for verification
        console.log('Selective Attention Test - User IDs:', userIds);

        // Get DOM elements
        const timerElement = document.getElementById("timer");
        const letterElement = document.getElementById("letter");
        const inputBox = document.getElementById("inputBox");
        const totalLettersElement = document.getElementById("totalLetters");
        const pLettersElement = document.getElementById("pLetters");
        const correctCountElement = document.getElementById("correctCount");
        const unattemptedCountElement = document.getElementById("unattemptedCount");
        const wrongCountElement = document.getElementById("wrongCount");
        const startButton = document.getElementById("startButton");
        const gameOverModal = document.getElementById("gameOverModal");
        const modalHeader = document.getElementById("modalHeader");
        const modalBody = document.getElementById("modalBody");
        const continueButton = document.getElementById("continueButton");

        // Initialize game state
        let gameStarted = false;
        let gameEnded = false;
        let timer = <?php echo $time_limit; ?>; // Use exact time from PHP
        let displayTimer = <?php echo $time_limit; ?>;
        let pCount = 0;
        let totalCount = 0;
        let correctCount = 0;
        let incorrectCount = 0;
        let missedCount = 0;
        let responses = [];
        let letterTimer;
        let letterInterval = 1200; // Show each letter for 1.2 seconds
        let currentLetter = '';
        let lastLetter = '';
        let responseReceived = false;
        let inputLocked = false;
        let startTime;
        let disableDbStorage = <?php echo $disable_db_storage ? 'true' : 'false'; ?>;

        // Game logic functions
        function updateTimer() {
            if (gameStarted && !gameEnded) {
                timer--;
                displayTimer = timer;
                timerElement.textContent = `Time Remaining: ${displayTimer}s`;
                
                if (timer <= 0) {
                    endGame();
                }
            }
        }

        // Function to generate a new letter
        function generateLetter() {
            console.log('Generating new letter...');
            if (gameEnded) {
                console.log('Game ended, not generating more letters');
                return;
            }

            // Check if previous letter was 'p' and no response was received
            if (currentLetter === 'p' && !responseReceived) {
                console.log('Missed "p" response');
                missedCount++;
                responses.push({
                    letter: currentLetter,
                    correct: false,
                    missed: true,
                    reactionTime: 2000
                });
            }

            // Reset response flag
            responseReceived = false;

            // Introduce a wider variety of letters including 'p'
            let letters = ['b', 'd', 'q', 'r'];
            let newLetter;

            // Ensure we achieve exactly 25 'p's
            if (pCount < 25) {
                // Increase probability of 'p' appearing as time runs out
                const remainingTime = timer;
                const pProbability = Math.min(0.8, 0.3 + (0.5 * (1 - remainingTime/<?php echo $time_limit; ?>)));
                newLetter = Math.random() < pProbability ? 'p' : letters[Math.floor(Math.random() * letters.length)];
                if (newLetter === 'p') pCount++;
            } else {
                // Only show non-p letters once we've met the p count
                newLetter = letters[Math.floor(Math.random() * letters.length)];
            }

            console.log('Generated letter:', newLetter, 'P count:', pCount);

            lastLetter = newLetter;
            currentLetter = newLetter;

            // Apply animation
            letterElement.textContent = '';
            letterElement.classList.remove('letter-change');
            void letterElement.offsetWidth; // Trigger reflow
            letterElement.textContent = newLetter;
            letterElement.classList.add('letter-change');

            totalCount++;
            startTime = Date.now();
            inputBox.value = '';
            inputLocked = false;
            inputBox.focus();

            if (!gameEnded && timer > 0) {
                clearTimeout(letterTimer);
                letterTimer = setTimeout(generateLetter, letterInterval);
            }
        }

        // Handle input
        inputBox.addEventListener('input', function(e) {
            if (!gameStarted || gameEnded || inputLocked) {
                console.log('Input ignored - game not in correct state');
                return;
            }

            const userInput = e.target.value.toLowerCase();
            console.log('Input received:', userInput);
            
            if (userInput.length > 0) {
                console.log('Processing input:', userInput, 'Current letter:', currentLetter);
                responseReceived = true;
                inputLocked = true;

                const reactionTime = Date.now() - startTime;
                console.log('Reaction time:', reactionTime, 'ms');

                if (currentLetter === 'p') {
                    if (userInput === 'p') {
                        console.log('Correct response to p');
                        correctCount++;
                    } else {
                        console.log('Incorrect response to p');
                        incorrectCount++;
                    }
                } else if (userInput === 'p') {
                    console.log('False alarm - p pressed when not shown');
                    incorrectCount++;
                }

                responses.push({
                    letter: currentLetter,
                    response: userInput,
                    correct: (currentLetter === 'p' && userInput === 'p') || 
                            (currentLetter !== 'p' && userInput !== 'p'),
                    reactionTime: reactionTime,
                    missed: false
                });

                // Clear input and unlock for next letter
                inputBox.value = '';
                inputLocked = false;
            }
        });

        // Start the game
        startButton.addEventListener('click', function() {
            if (!gameStarted) {
                gameStarted = true;
                startTime = Date.now();
                this.style.display = 'none';
                inputBox.disabled = false;
                inputBox.focus();
                
                // Start timers
                setInterval(updateTimer, 1000);
                generateLetter();
                
                // Start letter display interval
                setInterval(() => {
                    if (gameStarted && !gameEnded) {
                        generateLetter();
                    }
                }, letterInterval);
            }
        });

        function endGame() {
            console.log('Game ending...');
            gameEnded = true;
            showModal();
        }

        function showModal() {
            console.log('Showing results modal');
            
            // Calculate final results
            const totalResponses = responses.length;
            const correctResponses = responses.filter(r => r.correct).length;
            const missedPs = responses.filter(r => r.letter === 'p' && r.missed).length;
            const falseAlarms = responses.filter(r => r.letter !== 'p' && r.response === 'p').length;
            const accuracy = ((correctResponses / totalResponses) * 100).toFixed(1);
            const avgReactionTime = (responses.reduce((sum, r) => sum + r.reactionTime, 0) / totalResponses / 1000).toFixed(3);
            const score = Math.round((parseFloat(accuracy) * (2000 - parseFloat(avgReactionTime) * 1000))/20);
            
            // Save results to database
            if (!disableDbStorage) {
                const ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                const nonce = '<?php echo wp_create_nonce('selective_attention_nonce'); ?>';
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'save_selective_attention_results',
                        nonce: nonce,
                        test_id: userIds.testId,
                        profile_id: userIds.profileId,
                        user_code: userIds.userCode,
                        total_letters: totalCount,
                        p_letters: pCount,
                        correct_responses: correctResponses,
                        incorrect_responses: incorrectCount + falseAlarms,
                        reaction_time: avgReactionTime
                    },
                    success: function(response) {
                        console.log('Test results saved:', response);
                        if (response.success) {
                            window.location.href = '/dashboard/';
                        } else {
                            alert('Error saving results: ' + response.data);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error saving test results:', error);
                    }
                });
            } else {
                console.log('Database storage disabled for trial test');
            }
            
            modalBody.innerHTML = `
                Time's up! Here are your results:<br><br>
                Results:<br>
                Total Letters Shown: ${totalCount}<br>
                Total 'P' Letters: ${pCount}<br>
                Total Responses: ${totalResponses}<br>
                Correct Responses: ${correctResponses}<br>
                Missed 'P's: ${missedPs}<br>
                False Alarms: ${falseAlarms}<br>
                Accuracy: ${accuracy}%<br>
                Average Reaction Time: ${avgReactionTime}s<br>
                Score: ${score}
            `;
            gameOverModal.style.display = "block";
        }

        continueButton.addEventListener('click', function() {
            console.log('Continue button clicked, redirecting to dashboard');
            window.location.href = '<?php echo home_url('/dashboard'); ?>';
        });

        inputBox.addEventListener('focus', function() {
            startTime = Date.now();
        });
    </script>
</body>
</html>
