<?php
/**
 * Template Name: Test Phase One
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
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
            text-align: center;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 30px;
            margin: 0;
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            color: #fff;
        }

        h1 {
            color: #fff;
            font-size: 36px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 20px;
        }

        #timer {
            font-size: 24px;
            font-weight: bold;
            margin-top: 10px;
        }

        #letter {
            font-size: 200px;
            color: black;
            min-width: 100px;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
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

    <h1 style="color:black">Selective And Sustained Attention Test</h1>
    
    <div style="color:black;" id="timer">Time Remaining: 80s</div>

    <div id="test-id"></div>
    <div id="letter"></div>

    <input type="text" id="inputBox" maxlength="1" autocomplete="off" placeholder="Type Here" autofocus>

    <button id="startButton">Start Test</button>
    
    <div class="log" hidden >
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
        // Get test ID from patient ID
        const testID = '<?php 
             $current_user = wp_get_current_user();
             $patient_id = get_user_meta($current_user->ID, 'patient_id', true);
             $test_phase = '1';
             $test_id = $patient_id . '_phase' . $test_phase;
             echo esc_js($test_id);
         ?>';
         document.getElementById('test-id').textContent = 'Test ID: ' + testID;

        let timer = 78; // Actual game time
        let displayTimer = 80; // Display time
        let gameStarted = false;
        let gameEnded = false;
        let inputLocked = false;
        let pCount = 0;
        let totalCount = 0;
        let correctCount = 0;
        let incorrectCount = 0;
        let missedCount = 0;
        let pTarget = 25;
        let lastLetter = '';
        let letterInterval = 1200;
        let letterTimer = null;
        let currentLetter = '';
        let responseReceived = false;
        let userResponses = [];
        let testIdentifier = 'Test 1'; // Identifier for this test
        let responses = [];

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

        // Timer countdown function
        function updateTimer() {
            if (gameStarted && displayTimer > 0 && !gameEnded) {
                if (displayTimer > timer) {
                    // First 2 seconds only update display timer
                    displayTimer--;
                } else {
                    // After 2 seconds, update both timers
                    displayTimer--;
                    timer--;
                }
                timerElement.textContent = `Time Remaining: ${displayTimer}s`;
                if (timer === 0) {
                    gameEnded = true;
                    showModal();
                }
            }
        }

        // Function to generate a new letter
        function animateLetterChange(newLetter) {
            const letterElement = document.getElementById('letter');
            letterElement.textContent = ''; // Clear current letter
            
            // Remove old animation class
            letterElement.classList.remove('letter-change');
            
            // Force a reflow to restart animation
            void letterElement.offsetWidth;
            
            // Set new letter and add animation
            letterElement.textContent = newLetter;
            letterElement.classList.add('letter-change');
        }

        function generateLetter() {
            if (gameEnded) return;

            // Check if previous letter was 'p' and no response was received
            if (currentLetter === 'p' && !responseReceived) {
                missedCount++;
                document.getElementById('unattemptedCount').textContent = missedCount;
            }

            // Reset response flag
            responseReceived = false;

            let newLetter;
            const currentPosition = totalCount;

            // Introduce a wider variety of letters including 'p'
            let letters = ['b', 'd', 'q', 'r', 's'];

            // Ensure we achieve exactly 25 'p's in 80 seconds
            if (pCount < 25 && Math.random() < 0.5) {
                newLetter = 'p';
                pCount++;
            } else {
                newLetter = letters[Math.floor(Math.random() * letters.length)];
            }

            lastLetter = newLetter;
            currentLetter = newLetter;
            animateLetterChange(newLetter);
            totalCount++;

            document.getElementById('totalLetters').textContent = totalCount;
            document.getElementById('pLetters').textContent = pCount;

            inputBox.value = '';
            inputLocked = false;
            inputBox.focus();

            if (!gameEnded && timer > 0) {
                clearTimeout(letterTimer);
                letterTimer = setTimeout(generateLetter, letterInterval);
            }
        }

        // Input listener for the user input
        inputBox.addEventListener('input', function() {
            if (!gameStarted || gameEnded || inputLocked) return;

            const userInput = inputBox.value.toLowerCase();
            
            if (userInput.length > 0) {
                inputLocked = true;
                responseReceived = true;
                userResponses.push(userInput);

                if (currentLetter === 'p') {
                    if (userInput === 'p') {
                        correctCount++;
                        document.getElementById('correctCount').textContent = correctCount;
                    } else {
                        incorrectCount++;
                        document.getElementById('wrongCount').textContent = incorrectCount;
                    }
                } else if (userInput === 'p') {
                    // If user pressed 'p' when it wasn't shown
                    incorrectCount++;
                    document.getElementById('wrongCount').textContent = incorrectCount;
                }

                const response = {
                    letter: currentLetter,
                    response: userInput,
                    correct: currentLetter === userInput,
                    reactionTime: 0, // Not implemented
                    missed: false
                };

                responses.push(response);
            }
        });

        // Start the game
        function startGame() {
            if (!gameStarted) {
                gameStarted = true;
                inputBox.value = '';
                inputBox.focus();
                
                // Hide start button
                document.getElementById('startButton').style.display = 'none';
                
                // Reset all counters and timers
                timer = 78;
                displayTimer = 80;
                pCount = 0;
                totalCount = 0;
                correctCount = 0;
                incorrectCount = 0;
                missedCount = 0;
                
                // Update display
                document.getElementById('totalLetters').textContent = '0';
                document.getElementById('pLetters').textContent = '0';
                document.getElementById('correctCount').textContent = '0';
                document.getElementById('unattemptedCount').textContent = '0';
                document.getElementById('wrongCount').textContent = '0';
                timerElement.textContent = `Time Remaining: ${displayTimer}s`;
                
                // Start the timer
                setInterval(updateTimer, 1000);
                
                // Start generating letters
                generateLetter();
            }
        }

        function saveResults() {
            let totalResponses = responses.length;
            let correctResponses = responses.filter(r => r.correct).length;
            let accuracy = (correctResponses / totalResponses) * 100;
            let avgReactionTime = responses.reduce((sum, r) => sum + r.reactionTime, 0) / totalResponses;
            let missedCount = responses.filter(r => r.missed).length;
            let falseAlarms = responses.filter(r => !r.correct && !r.missed).length;

            // Calculate score based on accuracy and reaction time
            let score = Math.round(accuracy * (1 - avgReactionTime/2000));

            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'save_test_results',
                    test_id: testID,
                    test_phase: 1,
                    score: score,
                    accuracy: accuracy,
                    reaction_time: avgReactionTime / 1000,
                    missed_responses: missedCount,
                    false_alarms: falseAlarms,
                    raw_data: JSON.stringify(responses),
                    _ajax_nonce: attentrack_ajax.test_nonce
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Results saved successfully');
                    } else {
                        console.error('Failed to save results:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error saving results:', error);
                }
            });
        }

        function showModal() {
            saveResults();
            
            modalHeader.textContent = `Game Over!`;
            modalBody.innerHTML = `
                Time's up! Here are your results:<br><br>
                Results:<br>
                Total Responses: ${responses.length}<br>
                Correct Responses: ${responses.filter(r => r.correct).length}<br>
                Accuracy: ${((responses.filter(r => r.correct).length / responses.length) * 100).toFixed(1)}%<br>
                Average Reaction Time: ${(responses.reduce((sum, r) => sum + r.reactionTime, 0) / responses.length / 1000).toFixed(3)}s<br>
                Missed Responses: ${responses.filter(r => r.missed).length}<br>
                False Alarms: ${responses.filter(r => !r.correct && !r.missed).length}
            `;
            gameOverModal.style.display = "block";
            
            // Add event listener for continue button
            document.getElementById('continueButton').onclick = function() {
                window.location.href = '<?php echo get_permalink(get_page_by_path('test-phase-2')); ?>';
            };
        }

        continueButton.addEventListener('click', function() {
            window.location.href = '<?php echo get_permalink(get_page_by_path('test-phase-2')); ?>';
        });

        startButton.addEventListener('click', startGame);
    </script>

</body>
</html>
