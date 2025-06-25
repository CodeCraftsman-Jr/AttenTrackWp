<?php
/*
Template Name: Alternative Attention Test
*/

// Get current user's IDs
$user_id = get_current_user_id();
$user_ids = get_user_ids($user_id);

// Log IDs for verification
error_log('Alternative Attention Test - User IDs:');
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
    <title>Alternative Attention Test</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

        #test-id {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 1.2em;
            color: #00e6e6;
            background-color: rgba(30, 30, 30, 0.7);
            padding: 5px;
            border-radius: 5px;
        }

        h1 {
            font-size: 2.5em;
            font-weight: 600;
            margin-top: 20px;
            color: #000000;
            text-align: center;
            width: 100%;
        }

        .container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px;
            gap: 40px;
            width: 100%;
            max-width: 2000px;
            height: 450px;
            border-radius: 12px;
        }

        .left-section {
            width: 50%;
        }

        .alphabet-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin: 20px auto;
            max-width: 600px;
            padding: 20px;
        }
        
        .grid-item {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 20px;
            text-align: center;
            font-size: 24px;
            cursor: pointer;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .grid-item:hover {
            background-color: #e0e0e0;
        }
        
        .grid-item.selected {
            background-color: #4CAF50;
            color: white;
        }

        .right-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 55%;
        }

        .timer {
            font-size: 4em;
            font-weight: 700;
            margin: 20px 0;
            color: #000000;
        }

        .current-number {
            font-size: 6em;
            margin: 10px 0;
            color: #000000;
            font-weight: bold;
        }

        input[type="text"] {
            font-size: 1.5em;
            padding: 15px;
            width: 100%;
            max-width: 300px;
            margin-top: 20px;
            text-align: center;
            border: 2px solid #40E0D0;
            border-radius: 8px;
            background-color: white;
            color: #333;
            outline: none;
        }

        input[type="text"]::placeholder {
            color: #888;
        }

        button {
            font-size: 1.2em;
            padding: 10px 20px;
            margin-top: 20px;
            cursor: pointer;
            border: none;
            background-color: #40E0D0;
            color: black;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        button:hover {
            background-color: #00b3b3;
            transform: translateY(-2px);
        }

        .hidden {
            display: none;
        }

        #realTimeLog {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1em;
            color: #00e6e6;
            background-color: rgba(30, 30, 30, 0.7);
            padding: 10px;
            border-radius: 5px;
            width: 200px;
            text-align: left;
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1000;
            max-width: 500px;
            width: 90%;
        }

        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .popup h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .popup p {
            font-size: 18px;
            margin: 10px 0;
            text-align: left;
        }

        .popup .buttons {
            margin-top: 20px;
            text-align: center;
        }

        .popup .buttons button {
            margin: 0 10px;
            padding: 10px 20px;
            font-size: 16px;
        }

        #resultMessage {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                gap: 20px;
            }

            .left-section, .right-section {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <h1>Alternative Attention Test</h1>
    
    <div id="test-info" hidden>
        <p>Profile ID: <?php echo esc_html($user_ids['profile_id']); ?></p>
        <p>Test ID: <?php echo esc_html($user_ids['test_id']); ?></p>
        <p>User Code: <?php echo esc_html($user_ids['user_code']); ?></p>
    </div>
    <div class="container">
        <div class="left-section">
            <div class="alphabet-grid">
                <?php
                $alphabet = range('A', 'Z');
                for ($i = 0; $i < 26; $i++) {
                    echo "<div class='grid-item'>{$alphabet[$i]}<br>" . ($i + 1) . "</div>";
                }
                ?>
            </div>
        </div>
        <div class="right-section">
            <div class="timer" id="timer">60</div>
            <div class="current-number" id="currentNumber"></div>
            <input type="text" id="inputField" maxlength="1" placeholder="Enter letter" class="hidden">
            <button id="startButton">Start Test</button>
        </div>
    </div>
    <div class="popup-overlay" id="popupOverlay"></div>
    <div class="popup" id="resultsPopup">
        <h2>Test Results</h2>
        <p>Correct Responses: <span id="finalCorrect"></span></p>
        <p>Incorrect Responses: <span id="finalIncorrect"></span></p>
        <p>Average Response Time: <span id="finalAvgTime"></span> ms</p>
        <p>Score: <span id="finalScore"></span></p>
        <div id="resultMessage" style="display: none;"></div>
        <div class="buttons">
            <button type="button" class="btn btn-primary" id="submitResultsBtn">Submit Results</button>
            <button type="button" class="btn btn-secondary" id="dashboardBtn">Go to Dashboard</button>
        </div>
    </div>
    <div id="realTimeLog" hidden>
        <div>Correct: <span id="correctCount">0</span></div>
        <div>Incorrect: <span id="incorrectCount">0</span></div>
        <div>Average Time: <span id="avgTime">0</span>ms</div>
    </div>

    <script>
    // Debug logging function
    function log(message, data = null) {
        const timestamp = new Date().toISOString();
        if (data) {
            console.log(`[${timestamp}] ${message}:`, data);
        } else {
            console.log(`[${timestamp}] ${message}`);
        }
    }

    // Initialize variables and event handlers
    let isTestRunning = false;
    let timer = null;
    let timeLeft = 60;
    let startTime = 0;
    let currentRandomNumber = 0;
    let correctCount = 0;
    let incorrectCount = 0;
    let totalResponseTime = 0;
    let roundCount = 0;
    let totalLettersShown = 0;

    // Initialize when document is ready
    $(document).ready(function() {
        log('Document ready, initializing...');
        
        try {
            // Initialize button handlers
            $('#submitResultsBtn').on('click', function() {
                log('Submit button clicked');
                submitResults();
            });

            $('#dashboardBtn').on('click', function() {
                log('Dashboard button clicked');
                goToDashboard();
            });
            
            log('Initialization complete');
        } catch (error) {
            console.error('Error during initialization:', error);
        }
    });

    // Pass IDs to JavaScript for logging
    const userIds = {
        testId: '<?php echo esc_js($user_ids['test_id']); ?>',
        profileId: '<?php echo esc_js($user_ids['profile_id']); ?>',
        userCode: '<?php echo esc_js($user_ids['user_code']); ?>'
    };
    
    // Log IDs in browser console for verification
    console.log('Alternative Attention Test - User IDs:', userIds);

    let alphabetMap = {};
    for (let i = 1; i <= 26; i++) {
        alphabetMap[i] = String.fromCharCode(64 + i);
    }

    function updateLog() {
        const stats = {
            correctCount,
            incorrectCount,
            avgTime: (roundCount > 0 ? Math.round(totalResponseTime / roundCount) : 0)
        };
        log('Stats updated:', stats);
        
        $('#correctCount').text(correctCount);
        $('#incorrectCount').text(incorrectCount);
        $('#avgTime').text(stats.avgTime);
    }

    function nextRound() {
        if (!isTestRunning) {
            log('Test not running, skipping next round');
            return;
        }
        
        currentRandomNumber = Math.floor(Math.random() * 26) + 1;
        log('New round started:', {
            number: currentRandomNumber,
            expectedLetter: alphabetMap[currentRandomNumber],
            totalLettersShown
        });
        
        $('#currentNumber').text(currentRandomNumber);
        $('#inputField').val('').focus();
        startTime = Date.now();
        totalLettersShown++;
    }

    function startTest() {
        log('Starting test...');
        isTestRunning = true;
        $('#startButton').hide();
        $('#inputField').removeClass('hidden').focus();
        $('#realTimeLog').show();
        nextRound();
        startTimer();
        log('Test started successfully');
    }

    function startTimer() {
        log('Timer started, 60 seconds remaining');
        timer = setInterval(() => {
            timeLeft--;
            $('#timer').text(timeLeft);
            
            if (timeLeft % 10 === 0) {
                log('Time update:', { timeLeft });
            }
            
            if (timeLeft <= 0) {
                log('Time\'s up!');
                clearInterval(timer);
                endTest();
            }
        }, 1000);
    }

    function endTest() {
        log('Test ended, preparing results...');
        
        isTestRunning = false;
        clearInterval(timer);
        $('#inputField').addClass('hidden');
        $('#realTimeLog').hide();
        
        const accuracy = correctCount / (correctCount + incorrectCount);
        const avgReactionTime = Math.round(totalResponseTime / roundCount);
        let score = Math.max(0, Math.round(accuracy * (2000 - avgReactionTime)/2000));
        
        log('Calculating final results:', {
            accuracy,
            avgReactionTime,
            score,
            totalLettersShown
        });

        // Update results popup
        $('#finalCorrect').text(correctCount);
        $('#finalIncorrect').text(incorrectCount);
        $('#finalAvgTime').text(avgReactionTime);
        $('#finalScore').text(score);
        
        // Show popup
        showPopup();
        log('Results popup shown');
    }

    function showPopup() {
        $('#popupOverlay').fadeIn();
        $('#resultsPopup').fadeIn();
    }

    function hidePopup() {
        $('#popupOverlay').fadeOut();
        $('#resultsPopup').fadeOut();
    }

    function submitResults() {
        log('Submit button clicked, preparing submission...');
        
        // Disable only the submit button
        const submitBtn = $('#submitResultsBtn');
        submitBtn.prop('disabled', true).text('Saving...');
        
        const results = {
            test_id: userIds.testId,
            profile_id: userIds.profileId,
            user_code: userIds.userCode,
            correct_responses: correctCount,
            incorrect_responses: incorrectCount,
            total_items_shown: totalLettersShown,
            reaction_time: Math.round(totalResponseTime / roundCount) / 1000
        };

        log('Submitting results:', results);
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'save_alternative_attention_results',
                nonce: '<?php echo wp_create_nonce('alternative_attention_nonce'); ?>',
                ...results
            },
            success: function(response) {
                log('Submission successful:', response);
                const msgDiv = $('#resultMessage');
                msgDiv.removeClass('alert-danger').addClass('alert-success')
                    .text('Test results saved successfully!')
                    .fadeIn();
                submitBtn.prop('disabled', false).text('Submit Results');
            },
            error: function(xhr, status, error) {
                log('Submission failed:', { status, error, response: xhr.responseText });
                const msgDiv = $('#resultMessage');
                msgDiv.removeClass('alert-success').addClass('alert-danger')
                    .html('Error saving results: ' + error)
                    .fadeIn();
                submitBtn.prop('disabled', false).text('Submit Results');
            }
        });
    }

    function goToDashboard() {
        log('Navigating to dashboard...');
        window.location.href = '/dashboard/';
    }

    // Event Listeners
    $('#startButton').on('click', function() {
        log('Start button clicked');
        startTest();
    });
    
    $('#inputField').on('input', function(e) {
        if (!isTestRunning) {
            log('Input received but test not running');
            return;
        }
        
        const userAnswer = e.target.value.toUpperCase();
        const correctAnswer = alphabetMap[currentRandomNumber];
        
        if (userAnswer.length === 1) {
            const responseTime = Date.now() - startTime;
            totalResponseTime += responseTime;
            roundCount++;
            
            const result = {
                userAnswer,
                correctAnswer,
                responseTime,
                isCorrect: userAnswer === correctAnswer,
                roundNumber: roundCount,
                totalLettersShown
            };
            
            if (userAnswer === correctAnswer) {
                correctCount++;
                log('Correct answer!', result);
            } else {
                incorrectCount++;
                log('Incorrect answer!', result);
            }
            
            updateLog();
            nextRound();
        }
    });
    </script>
</body>
</html>
