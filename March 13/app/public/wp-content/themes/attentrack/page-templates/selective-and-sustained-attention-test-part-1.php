<?php
/*
* Template Name: Selective and Sustained Attention Test Part 1
* Template Post Type: page
*/

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// Get test ID from URL
$test_id = isset($_GET['test_id']) ? sanitize_text_field($_GET['test_id']) : '';
if (empty($test_id)) {
    error_log('Test ID is empty in selective-and-sustained-attention-test-part-1.php');
    error_log('GET parameters: ' . print_r($_GET, true));
    wp_redirect(home_url('/dashboard'));
    exit;
}

// Log the test ID for debugging
error_log('Test ID received: ' . $test_id);

?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <?php
    // Localize the AJAX URL and nonce
    wp_localize_script('jquery', 'attentrack_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('attentrack_nonce')
    ));
    ?>
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
            min-width: 200px;
            min-height: 240px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
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
            display: inline-block;
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
    
    <div class="log" hidden>
        <p>Total Letters: <span id="totalCount">0</span></p>
        <p>P Letters: <span id="pCount">0</span></p>
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
        console.log('Test Phase 1 script starting...');
        
        // Get test ID
        let testID = '<?php echo $test_id; ?>';
        let totalCount = 0;
        let pCount = 0;
        const REQUIRED_P_COUNT = 25;
        const letters = ['b', 'd', 'q', 'r'];
        
        // Show test ID for reference
        document.getElementById('test-id').textContent = 'Test ID: ' + testID;

        let gameStarted = false;
        let gameEnded = false;
        let currentLetter = '';
        let startTime = 0;
        let timerInterval;
        let timeLeft = 80;
        let responses = [];
        let responseReceived = false;
        let inputLocked = false;
        let correctCount = 0;
        let incorrectCount = 0;
        let missedCount = 0;

        // Get DOM elements
        const letterElement = document.getElementById('letter');
        const inputBox = document.getElementById('inputBox');
        const startButton = document.getElementById('startButton');
        const timerElement = document.getElementById('timer');
        const modal = document.getElementById('gameOverModal');
        const modalHeader = modal.querySelector('.modal-header');
        const modalBody = modal.querySelector('.modal-body');
        const continueButton = document.getElementById('continueButton');

        // Hide input box initially
        inputBox.style.display = 'none';

        // Update timer display
        function updateTimer() {
            if (gameEnded) return;
            
            timeLeft--;
            document.getElementById('timer').textContent = `Time Remaining: ${timeLeft}s`;
            
            // Check for missed response on previous letter
            if (!responseReceived && currentLetter === 'p') {
                console.log('Missed p response');
                missedCount++;
                document.getElementById('unattemptedCount').textContent = missedCount;
                responses.push({
                    letter: currentLetter,
                    response: '',
                    correct: false,
                    reactionTime: 2000,
                    missed: true
                });
            }

            if (timeLeft <= 0 || (pCount >= REQUIRED_P_COUNT && !responseReceived)) {
                gameEnded = true;
                clearInterval(timerInterval);
                inputBox.disabled = true;
                showModal();
            }
        }

        // Generate letter
        function showLetter() {
            if (gameEnded) return;
            
            // Check if we've shown enough 'p' letters
            if (pCount < REQUIRED_P_COUNT) {
                // Increase chance of 'p' appearing as time runs out
                const remainingTime = timeLeft;
                const pProbability = Math.min(0.8, 0.3 + (0.5 * (1 - remainingTime/80)));
                const letters = ['b', 'd', 'p', 'q', 'r'];
                currentLetter = Math.random() < pProbability ? 'p' : letters[Math.floor(Math.random() * letters.length)];
                if (currentLetter === 'p') pCount++;
            } else {
                // Only show non-p letters once we've met the p count
                const nonPLetters = ['b', 'd', 'q', 'r'];
                currentLetter = nonPLetters[Math.floor(Math.random() * nonPLetters.length)];
            }

            totalCount++;
            console.log('Generated letter:', currentLetter, 'P count:', pCount, 'Total count:', totalCount);

            // Apply animation
            letterElement.textContent = '';
            letterElement.classList.remove('letter-change');
            void letterElement.offsetWidth; // Trigger reflow
            letterElement.textContent = currentLetter;
            letterElement.classList.add('letter-change');

            startTime = Date.now();
            responseReceived = false;
        }

        // Start game
        function startGame() {
            console.log('Starting game...');
            if (!gameStarted) {
                gameStarted = true;
                
                // Show input box and hide start button
                inputBox.style.display = 'block';
                startButton.style.display = 'none';
                
                inputBox.value = '';
                inputBox.focus();
                
                // Start letter generation
                showLetter();
                setInterval(showLetter, 2000);
                
                // Start timer
                timerInterval = setInterval(updateTimer, 1000);
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

        function showModal() {
            modalHeader.textContent = `Game Over!`;
            const totalResponses = responses.length;
            const correctResponses = responses.filter(r => r.correct).length;
            const accuracy = (correctResponses / totalResponses) * 100;
            const avgReactionTime = responses.reduce((sum, r) => sum + r.reactionTime, 0) / responses.length / 1000;
            let score = Math.round((accuracy * (2000 - avgReactionTime))/20);
            if (score < 0) score = 0;

            modalBody.innerHTML = `
                Time's up! Here are your results:<br><br>
                Results:<br>
                Total Letters Shown: ${totalCount}<br>
                Total 'P' Letters: ${pCount}<br>
                Total Responses: ${totalResponses}<br>
                Correct Responses: ${correctResponses}<br>
                Accuracy: ${accuracy.toFixed(1)}%<br>
                Average Reaction Time: ${avgReactionTime.toFixed(3)}s<br>
                Missed Responses: ${responses.filter(r => r.missed).length}<br>
                False Alarms: ${responses.filter(r => !r.correct && !r.missed).length}<br>
                Score: ${score}
            `;
            modal.style.display = "block";
            
            // Add event listener for continue button
            document.getElementById('continueButton').onclick = function() {
                window.location.href = '<?php echo get_permalink(get_page_by_path("selective-and-sustained-attention-test-part-2")); ?>';
            };

            jQuery.ajax({
                url: attentrack_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'save_test_results',
                    nonce: attentrack_ajax.nonce,
                    test_id: testID,
                    test_phase: 1,
                    score: score,
                    accuracy: accuracy,
                    reaction_time: avgReactionTime,
                    missed_responses: missedCount,
                    false_alarms: responses.filter(r => !r.correct && !r.missed).length,
                    responses: JSON.stringify(responses),
                    total_letters: totalCount,
                    p_letters: pCount
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Test results saved:', response);
                        // Redirect to next test with test ID
                        window.location.href = '<?php echo home_url("/selective-and-sustained-attention-test-part-2"); ?>?test_id=' + testID;
                    } else {
                        console.error('Failed to save test results:', response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }

        continueButton.addEventListener('click', function() {
            window.location.href = '<?php echo get_permalink(get_page_by_path("selective-and-sustained-attention-test-part-2")); ?>';
        });

        startButton.addEventListener('click', startGame);
    </script>

</body>
</html>
