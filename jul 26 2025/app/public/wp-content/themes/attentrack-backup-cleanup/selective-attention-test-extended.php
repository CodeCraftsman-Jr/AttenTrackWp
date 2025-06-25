<?php
/*
Template Name: Selective Attention Test Extended
*/

// Get current user's IDs
$user_id = get_current_user_id();
$user_ids = get_user_ids($user_id);

// Log IDs for verification
error_log('Selective Attention Test Extended - User IDs:');
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
    <title>Selective Attention Test Extended</title>
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
            background-color: #3BC0C0;
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(0, 0, 0, 0.15);
        }

        #test-id {
            position: fixed;
            top: 20px;
            right: 20px;
            font-size: 18px;
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            padding: 10px 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 30px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            text-align: center;
            position: relative;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from { transform: translateY(-100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .modal-body {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        #continueButton {
            background-color: #4CAF50;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #continueButton:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }

        .break-timer {
            font-size: 36px;
            color: #333;
            margin: 20px 0;
        }

        .phase-results {
            text-align: left;
            margin: 10px 0;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }

        @keyframes letterChange {
            0% { opacity: 0; transform: scale(0.8); }
            20% { opacity: 1; transform: scale(1.1); }
            100% { opacity: 1; transform: scale(1); }
        }

        .letter-change {
            animation: letterChange 0.5s ease-out;
        }

        #progress-container {
            width: 80%;
            max-width: 600px;
            margin: 20px auto;
            background: #f0f0f0;
            border-radius: 10px;
            padding: 15px;
        }

        .progress-phase {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin: 0 10px;
            border-radius: 50%;
            background: #ddd;
        }

        .progress-phase.active {
            background: #40E0D0;
            animation: pulse 1s infinite;
        }

        .progress-phase.completed {
            background: #4CAF50;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <h1>Selective Attention Test</h1>
    <div id="phase-indicator">Phase 1 of 4</div>
    
    <div id="progress-container">
        <div class="progress-phase active" data-phase="1"></div>
        <div class="progress-phase" data-phase="2"></div>
        <div class="progress-phase" data-phase="3"></div>
        <div class="progress-phase" data-phase="4"></div>
    </div>

    <div id="timer">Time Remaining: 80s</div>
    
    <div id="letter"></div>
    <input type="text" id="inputBox" maxlength="1" autocomplete="off">
    <button id="startButton">Start Test</button>

    <!-- Score tracking element -->
    <div id="unattemptedCount" style="display: none;">0</div>

    <!-- Break Modal -->
    <div id="breakModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">Take a Break</div>
            <div class="modal-body">
                <div class="break-timer">30</div>
                <p>Rest your eyes and relax. Next phase will start automatically.</p>
                <div class="phase-results"></div>
            </div>
        </div>
    </div>

    <!-- Final Results Modal -->
    <div id="finalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">Test Complete!</div>
            <div class="modal-body"></div>
            <button id="finishButton">Return to Selection Page</button>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Extended Test script starting...');

        // Get profile_id from URL
        const urlParams = new URLSearchParams(window.location.search);
        let profileId = urlParams.get('profile_id') || '1'; // Default to 1 if not provided
        let testId = 'ext_' + Date.now(); // Generate unique test ID

        // Pass IDs to JavaScript for logging
        const userIds = {
            userId: '<?php echo esc_js($user_id); ?>',
            profileId: '<?php echo esc_js($user_ids['profile_id']); ?>',
            testId: '<?php echo esc_js($user_ids['test_id']); ?>',
            userCode: '<?php echo esc_js($user_ids['user_code']); ?>'
        };
        
        // Log IDs in browser console for verification
        console.log('Selective Attention Test Extended - User IDs:', userIds);

        // Test configuration
        const CONFIG = {
            PHASES: 4,
            LETTERS_PER_PHASE: 25,
            BREAK_TIME: 30,
            TEST_TIME: 80,
            LETTER_INTERVAL: 2000
        };

        // State variables
        let currentPhase = 1;
        let phaseResults = [];
        let gameStarted = false;
        let gameEnded = false;
        let currentLetter = '';
        let startTime = 0;
        let timerInterval;
        let timeLeft = CONFIG.TEST_TIME;
        let responses = [];
        let responseReceived = false;
        let inputLocked = false;
        let pCount = 0;
        let totalCount = 0;
        let breakTimer;

        // Get DOM elements
        const elements = {
            letter: document.getElementById('letter'),
            inputBox: document.getElementById('inputBox'),
            startButton: document.getElementById('startButton'),
            timer: document.getElementById('timer'),
            phaseIndicator: document.getElementById('phase-indicator'),
            breakModal: document.getElementById('breakModal'),
            finalModal: document.getElementById('finalModal'),
            breakTimer: document.querySelector('.break-timer'),
            phaseResults: document.querySelector('.phase-results'),
            progressPhases: document.querySelectorAll('.progress-phase')
        };

        // Function to fetch user IDs
        function fetchUserIds() {
            return new Promise((resolve, reject) => {
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'get_user_ids'
                    },
                    success: function(response) {
                        if (response.success) {
                            testId = response.data.test_id;
                            profileId = response.data.profile_id;
                            console.log('Retrieved IDs:', response.data);
                            resolve(response.data);
                        } else {
                            console.error('Error fetching user IDs:', response.data);
                            reject(response.data);
                        }
                    },
                    error: function(error) {
                        console.error('AJAX error fetching user IDs:', error);
                        reject(error);
                    }
                });
            });
        }

        // Update progress indicators
        function updateProgress() {
            elements.progressPhases.forEach((phase, index) => {
                if (index + 1 < currentPhase) {
                    phase.classList.add('completed');
                    phase.classList.remove('active');
                } else if (index + 1 === currentPhase) {
                    phase.classList.add('active');
                    phase.classList.remove('completed');
                } else {
                    phase.classList.remove('active', 'completed');
                }
            });
        }

        function updateTimer() {
            if (timeLeft > 0) {
                timeLeft--;
                elements.timer.textContent = timeLeft + 's';
            }
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                endPhase();
            }
        }

        function generateLetter() {
            if (gameEnded) return;

            const letters = ['b', 'd', 'q', 'r'];
            const remainingTime = timeLeft;
            const pProbability = Math.min(0.8, 0.3 + (0.5 * (1 - remainingTime/CONFIG.TEST_TIME)));
            
            currentLetter = Math.random() < pProbability ? 'p' : letters[Math.floor(Math.random() * letters.length)];
            if (currentLetter === 'p') pCount++;

            totalCount++;
            console.log(`Phase ${currentPhase}: Generated letter: ${currentLetter}, P count: ${pCount}, Total: ${totalCount}`);

            // Clear and trigger animation
            elements.letter.textContent = currentLetter;
            elements.letter.classList.remove('letter-change');
            void elements.letter.offsetWidth; // Force reflow
            elements.letter.classList.add('letter-change');

            startTime = Date.now();
            responseReceived = false;

            // Schedule next letter
            setTimeout(generateLetter, CONFIG.LETTER_INTERVAL);
        }

        function calculatePhaseResults() {
            const phaseResponses = responses.filter(r => r.phase === currentPhase);
            const correctResponses = phaseResponses.filter(r => r.correct).length;
            const incorrectResponses = phaseResponses.filter(r => !r.correct).length;
            const accuracy = (correctResponses / phaseResponses.length) * 100 || 0;
            const avgReactionTime = phaseResponses.length > 0 ? 
                phaseResponses.reduce((sum, r) => sum + r.reactionTime, 0) / phaseResponses.length / 1000 : 0;
            const missedCount = phaseResponses.filter(r => r.missed).length;
            const falseAlarms = phaseResponses.filter(r => !r.correct && !r.missed).length;
            
            console.log(`Phase ${currentPhase} results:`, {
                phase: currentPhase,
                totalLetters: totalCount,
                pLetters: pCount,
                correctResponses,
                incorrectResponses,
                accuracy,
                avgReactionTime
            });
            
            return {
                phase: currentPhase,
                totalLetters: totalCount,
                pLetters: pCount,
                correctResponses,
                incorrectResponses,
                accuracy,
                avgReactionTime,
                missedCount,
                falseAlarms,
                score: Math.round((accuracy * (2000 - avgReactionTime * 1000))/20)
            };
        }

        function endPhase() {
            if (gameEnded) return; // Prevent multiple calls
            
            console.log(`Ending phase ${currentPhase}`);
            gameEnded = true;
            clearInterval(timerInterval);
            elements.inputBox.disabled = true;

            // Calculate and store results for the current phase
            const results = calculatePhaseResults();
            phaseResults.push(results);
            console.log(`Phase ${currentPhase} results stored:`, results);
            console.log('All phases so far:', phaseResults);

            // Save results to database via AJAX
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'save_selective_attention_extended_results',
                    nonce: '<?php echo wp_create_nonce('selective_attention_extended_nonce'); ?>',
                    test_id: userIds.testId,
                    profile_id: userIds.profileId,
                    user_code: userIds.userCode,
                    total_letters: results.totalLetters,
                    p_letters: results.pLetters,
                    correct_responses: results.correctResponses,
                    incorrect_responses: results.incorrectResponses,
                    reaction_time: results.avgReactionTime,
                    phase: currentPhase
                },
                success: function(response) {
                    console.log('Results saved:', response);
                    
                    if (currentPhase < CONFIG.PHASES) {
                        showBreakScreen();
                    } else {
                        showFinalResults();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error saving results:', error);
                    alert('Error saving results. Please try again.');
                }
            });
        }

        function startNextPhase() {
            if (currentPhase >= CONFIG.PHASES) {
                showFinalResults();
                return;
            }
            
            currentPhase++;
            if (currentPhase > CONFIG.PHASES) {
                showFinalResults();
                return;
            }
            
            elements.phaseIndicator.textContent = `Phase ${currentPhase} of ${CONFIG.PHASES}`;
            updateProgress();
            
            // Reset state variables for new phase
            gameStarted = false;
            gameEnded = false;
            timeLeft = CONFIG.TEST_TIME;
            pCount = 0;
            totalCount = 0;
            
            // Show start button for next phase
            elements.startButton.style.display = 'block';
            elements.inputBox.style.display = 'none';
            elements.inputBox.disabled = false;
            elements.letter.textContent = '';
            elements.timer.textContent = timeLeft + 's';
        }

        function showBreakScreen() {
            elements.breakModal.style.display = 'block';
            
            let breakTimeLeft = CONFIG.BREAK_TIME;
            elements.breakTimer.textContent = breakTimeLeft;

            if (breakTimer) clearInterval(breakTimer);
            
            breakTimer = setInterval(() => {
                breakTimeLeft--;
                elements.breakTimer.textContent = breakTimeLeft;
                
                if (breakTimeLeft <= 0) {
                    clearInterval(breakTimer);
                    elements.breakModal.style.display = 'none';
                    startNextPhase();
                }
            }, 1000);
        }

        function showFinalResults() {
            const finalResults = {
                phases: phaseResults,
                overallAccuracy: phaseResults.reduce((sum, r) => sum + r.accuracy, 0) / CONFIG.PHASES,
                overallReactionTime: phaseResults.reduce((sum, r) => sum + r.avgReactionTime, 0) / CONFIG.PHASES,
                totalScore: phaseResults.reduce((sum, r) => sum + r.score, 0),
                improvement: calculateImprovement()
            };

            console.log('Final results:', finalResults);

            elements.finalModal.querySelector('.modal-body').innerHTML = `
                Overall Results:<br><br>
                ${phaseResults.map(r => `
                    Phase ${r.phase}:<br>
                    - Accuracy: ${r.accuracy.toFixed(1)}%<br>
                    - Reaction Time: ${r.avgReactionTime.toFixed(3)}s<br>
                    - Score: ${r.score}<br><br>
                `).join('')}
                Overall Statistics:<br>
                - Average Accuracy: ${finalResults.overallAccuracy.toFixed(1)}%<br>
                - Average Reaction Time: ${finalResults.overallReactionTime.toFixed(3)}s<br>
                - Total Score: ${finalResults.totalScore}<br>
                - Improvement: ${finalResults.improvement.toFixed(1)}%
            `;

            elements.finalModal.style.display = 'block';

            // Save results to localStorage
            localStorage.setItem('selectiveAttentionExtendedResults', JSON.stringify(finalResults));
        }

        function calculateImprovement() {
            if (phaseResults.length < 2) return 0;
            const firstPhase = phaseResults[0].score;
            const lastPhase = phaseResults[phaseResults.length - 1].score;
            return ((lastPhase - firstPhase) / firstPhase) * 100;
        }

        // Event Listeners
        elements.startButton.addEventListener('click', function() {
            gameStarted = true;
            elements.startButton.style.display = 'none';
            elements.inputBox.style.display = 'block';
            elements.inputBox.focus();

            fetchUserIds().then(() => {
                generateLetter();
                timerInterval = setInterval(updateTimer, 1000);
                updateProgress();
            }).catch(error => {
                console.error('Failed to fetch user IDs:', error);
                // Continue with the test even if ID fetch fails
                generateLetter();
                timerInterval = setInterval(updateTimer, 1000);
                updateProgress();
            });
        });

        elements.inputBox.addEventListener('input', function(e) {
            if (!gameStarted || gameEnded || inputLocked) return;

            const userInput = e.target.value.toLowerCase();
            if (userInput.length > 0) {
                responseReceived = true;
                inputLocked = true;

                const reactionTime = Date.now() - startTime;
                console.log(`Input received: ${userInput}, Current letter: ${currentLetter}, RT: ${reactionTime}ms`);

                responses.push({
                    phase: currentPhase,
                    letter: currentLetter,
                    response: userInput,
                    correct: (currentLetter === 'p' && userInput === 'p') || 
                            (currentLetter !== 'p' && userInput !== 'p'),
                    reactionTime: reactionTime,
                    missed: false
                });

                elements.inputBox.value = '';
                inputLocked = false;
            }
        });

        document.getElementById('finishButton').addEventListener('click', function() {
            window.location.href = '<?php echo home_url('/selectionpage2'); ?>';
        });

        // Initialize
        elements.inputBox.style.display = 'none';
        updateProgress();
    });
    </script>

</body>
</html>
