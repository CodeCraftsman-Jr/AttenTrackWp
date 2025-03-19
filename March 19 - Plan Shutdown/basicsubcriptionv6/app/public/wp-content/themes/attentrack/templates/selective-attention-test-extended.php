<?php
/**
 * Template Name: Selective Attention Test Extended
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// Get and validate test ID
$test_id = isset($_GET['test_id']) ? sanitize_text_field($_GET['test_id']) : '';
if (empty($test_id)) {
    wp_redirect(home_url('/selectionpage2'));
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
            color: #333;
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
    <div id="test-id">Test ID: <?php echo esc_html($test_id); ?></div>
    
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
            timeLeft--;
            elements.timer.textContent = `Time Remaining: ${timeLeft}s`;
            console.log(`Time remaining: ${timeLeft}s, P count: ${pCount}`);

            if (!responseReceived && currentLetter === 'p') {
                console.log('Missed p response');
                responses.push({
                    phase: currentPhase,
                    letter: currentLetter,
                    response: '',
                    correct: false,
                    reactionTime: CONFIG.LETTER_INTERVAL,
                    missed: true
                });
            }

            if (timeLeft <= 0 || pCount >= CONFIG.LETTERS_PER_PHASE) {
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
            const accuracy = (correctResponses / phaseResponses.length) * 100;
            const avgReactionTime = phaseResponses.reduce((sum, r) => sum + r.reactionTime, 0) / phaseResponses.length / 1000;
            const missedCount = phaseResponses.filter(r => r.missed).length;
            const falseAlarms = phaseResponses.filter(r => !r.correct && !r.missed).length;
            
            return {
                phase: currentPhase,
                totalLetters: totalCount,
                pLetters: pCount,
                correctResponses,
                accuracy,
                avgReactionTime,
                missedCount,
                falseAlarms,
                score: Math.round((accuracy * (2000 - avgReactionTime))/20)
            };
        }

        function showBreakScreen() {
            const results = calculatePhaseResults();
            phaseResults.push(results);

            elements.breakModal.style.display = 'block';
            elements.phaseResults.innerHTML = `
                Phase ${currentPhase} Results:<br>
                Accuracy: ${results.accuracy.toFixed(1)}%<br>
                Average Reaction Time: ${results.avgReactionTime.toFixed(3)}s<br>
                Missed Responses: ${results.missedCount}<br>
                False Alarms: ${results.falseAlarms}<br>
                Score: ${results.score}
            `;

            let breakTimeLeft = CONFIG.BREAK_TIME;
            elements.breakTimer.textContent = breakTimeLeft;

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

        function endPhase() {
            console.log(`Ending phase ${currentPhase}`);
            gameEnded = true;
            clearInterval(timerInterval);
            elements.inputBox.disabled = true;

            if (currentPhase < CONFIG.PHASES) {
                showBreakScreen();
            } else {
                showFinalResults();
            }
        }

        function startNextPhase() {
            currentPhase++;
            elements.phaseIndicator.textContent = `Phase ${currentPhase} of ${CONFIG.PHASES}`;
            updateProgress();
            
            // Reset phase variables
            timeLeft = CONFIG.TEST_TIME;
            pCount = 0;
            totalCount = 0;
            gameEnded = false;
            elements.inputBox.disabled = false;
            elements.inputBox.value = '';
            elements.inputBox.focus();

            // Start new phase
            generateLetter();
            timerInterval = setInterval(updateTimer, 1000);
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

            // Save individual phase results
            phaseResults.forEach((phaseResult, index) => {
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'save_test_results',
                        test_id: '<?php echo esc_js($test_id); ?>',
                        test_type: 'selective_attention_extended',
                        test_phase: index + 1,
                        score: phaseResult.score,
                        accuracy: phaseResult.accuracy,
                        reaction_time: phaseResult.avgReactionTime,
                        missed_responses: phaseResult.missedCount,
                        false_alarms: phaseResult.falseAlarms,
                        responses: JSON.stringify(responses.filter(r => r.phase === index + 1)),
                        total_letters: phaseResult.totalLetters,
                        p_letters: phaseResult.pLetters,
                        is_phase_result: true,
                        nonce: '<?php echo wp_create_nonce('attentrack_test_nonce'); ?>'
                    },
                    success: function(response) {
                        if (!response.success) {
                            console.error(`Failed to save phase ${index + 1} results:`, response);
                        } else {
                            console.log(`Phase ${index + 1} results saved successfully`);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(`AJAX error saving phase ${index + 1}:`, error);
                    }
                });
            });

            // Save combined results
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'save_test_results',
                    test_id: '<?php echo esc_js($test_id); ?>',
                    test_type: 'selective_attention_extended',
                    test_phase: 0, // 0 indicates combined results
                    score: finalResults.totalScore,
                    accuracy: finalResults.overallAccuracy,
                    reaction_time: finalResults.overallReactionTime,
                    missed_responses: phaseResults.reduce((sum, r) => sum + r.missedCount, 0),
                    false_alarms: phaseResults.reduce((sum, r) => sum + r.falseAlarms, 0),
                    responses: JSON.stringify(responses),
                    total_letters: phaseResults.reduce((sum, r) => sum + r.totalLetters, 0),
                    p_letters: phaseResults.reduce((sum, r) => sum + r.pLetters, 0),
                    is_combined_result: true,
                    improvement: finalResults.improvement,
                    nonce: '<?php echo wp_create_nonce('attentrack_test_nonce'); ?>'
                },
                success: function(response) {
                    if (!response.success) {
                        console.error('Failed to save combined results:', response);
                        alert('Error saving results. Please try again.');
                    } else {
                        console.log('Combined results saved successfully');
                        // Show success message in the final modal
                        elements.finalModal.querySelector('.modal-body').innerHTML += `
                            <div class="success-message" style="margin-top: 20px; color: #28a745;">
                                Results saved successfully! Redirecting to dashboard...
                            </div>
                        `;
                        // Redirect to dashboard after 2 seconds
                        setTimeout(() => {
                            window.location.href = '<?php echo esc_js(home_url("/dashboard")); ?>';
                        }, 2000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    alert('Error saving results. Please try again.');
                }
            });
        }

        function calculateImprovement() {
            if (phaseResults.length < 2) return 0;
            const firstPhase = phaseResults[0].score;
            const lastPhase = phaseResults[phaseResults.length - 1].score;
            return ((lastPhase - firstPhase) / firstPhase) * 100;
        }

        // Event Listeners
        elements.startButton.addEventListener('click', function() {
            if (!gameStarted) {
                console.log('Starting game - Phase 1');
                gameStarted = true;
                elements.inputBox.style.display = 'block';
                elements.startButton.style.display = 'none';
                elements.inputBox.focus();

                generateLetter();
                timerInterval = setInterval(updateTimer, 1000);
                updateProgress();
            }
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
            window.location.href = '<?php echo esc_js(home_url("/selectionpage2")); ?>';
        });

        // Initialize
        elements.inputBox.style.display = 'none';
        updateProgress();
    });
    </script>

    <?php wp_footer(); ?>
</body>
</html>
