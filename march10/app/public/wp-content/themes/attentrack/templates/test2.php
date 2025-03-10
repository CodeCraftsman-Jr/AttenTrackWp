<?php
/**
 * Template Name: Test 2 - Alternate Attention
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// get_header();
?>

<style>
    body {
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        color: #f0f0f0;
        font-family: 'Roboto', sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        position: relative;
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
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
    }

    .alphabet-grid div {
        background-color: #f0f0f0;
        color: #333;
        font-size: 1.5em;
        margin: 10px;
        padding: 20px;
        width: 20px;
        height: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
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

    @media (max-width: 768px) {
        .container {
            flex-direction: column;
            gap: 20px;
        }

        .left-section, .right-section {
            width: 100%;
        }
    }

    .modal {
        display: none;
    }

    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
</style>

<div>
    <h1>Alternate Attention Test</h1>
    <div id="test-id" hidden></div>
    <div class="container">
        <div class="left-section">
            <div class="alphabet-grid">
                <?php
                $alphabet = range('A', 'Z');
                for ($i = 0; $i < 26; $i++) {
                    echo "<div>{$alphabet[$i]}<br>" . ($i + 1) . "</div>";
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
    <div id="realTimeLog" hidden>
        <div>Correct: <span id="correctCount">0</span></div>
        <div>Incorrect: <span id="incorrectCount">0</span></div>
        <div>Average Time: <span id="avgTime">0</span>ms</div>
    </div>
</div>

<!-- Modal for results -->
<div class="modal fade" id="resultsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test Results</h5>
            </div>
            <div class="modal-body">
                <div class="results-summary">
                    <p>Correct Answers: <span id="finalCorrect">0</span></p>
                    <p>Incorrect Answers: <span id="finalIncorrect">0</span></p>
                    <p>Average Response Time: <span id="finalAvgTime">0</span>ms</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="submitButton" class="btn btn-primary">Submit Results</button>
                <button type="button" id="nextTestButton" class="btn btn-secondary">Next Test</button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let testId = null;
    let timer;
    let timeLeft = 60;
    let currentRandomNumber;
    let correctCount = 0;
    let incorrectCount = 0;
    let totalResponseTime = 0;
    let roundCount = 0;
    let startTime;
    let isTestRunning = false;

    const alphabetMap = {};
    for (let i = 1; i <= 26; i++) {
        alphabetMap[i] = String.fromCharCode(64 + i);
    }

    async function fetchTestId() {
        try {
            const response = await fetch('<?php echo esc_url(rest_url('attentrack/v1/generate-test-id')); ?>', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            });
            const data = await response.json();
            testId = data.test_id;
            $('#test-id').text(`Test ID: ${testId}`).show();
        } catch (error) {
            console.error('Error fetching test ID:', error);
        }
    }

    function updateLog() {
        $('#correctCount').text(correctCount);
        $('#incorrectCount').text(incorrectCount);
        $('#avgTime').text((roundCount > 0 ? Math.round(totalResponseTime / roundCount) : 0));
    }

    function nextRound() {
        if (!isTestRunning) return;
        
        currentRandomNumber = Math.floor(Math.random() * 26) + 1;
        $('#currentNumber').text(currentRandomNumber);
        $('#inputField').val('').focus();
        startTime = Date.now();
    }

    function startTest() {
        isTestRunning = true;
        $('#startButton').hide();
        $('#inputField').removeClass('hidden').focus();
        $('#realTimeLog').show();
        nextRound();
        startTimer();
    }

    function startTimer() {
        timer = setInterval(() => {
            timeLeft--;
            $('#timer').text(timeLeft);
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                endTest();
            }
        }, 1000);
    }

    function endTest() {
        isTestRunning = false;
        $('#inputField').addClass('hidden');
        $('#finalCorrect').text(correctCount);
        $('#finalIncorrect').text(incorrectCount);
        $('#finalAvgTime').text(Math.round(totalResponseTime / roundCount));
        $('#resultsModal').modal('show');
    }

    async function submitResults() {
        const results = {
            test_id: testId,
            correct_count: correctCount,
            incorrect_count: incorrectCount,
            avg_response_time: Math.round(totalResponseTime / roundCount),
            completion_time: 60 - timeLeft
        };

        try {
            const response = await fetch('<?php echo esc_url(rest_url('attentrack/v1/save-test-results')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify(results)
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            window.location.href = '<?php echo esc_url(home_url('/test-3')); ?>';
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Event Listeners
    $('#startButton').on('click', startTest);
    
    $('#inputField').on('input', function(e) {
        if (!isTestRunning) return;
        
        const userAnswer = e.target.value.toUpperCase();
        const correctAnswer = alphabetMap[currentRandomNumber];
        
        if (userAnswer.length === 1) {
            const responseTime = Date.now() - startTime;
            totalResponseTime += responseTime;
            roundCount++;
            
            if (userAnswer === correctAnswer) {
                correctCount++;
            } else {
                incorrectCount++;
            }
            
            updateLog();
            nextRound();
        }
    });

    $('#submitButton').on('click', submitResults);
    
    $('#nextTestButton').on('click', function() {
        window.location.href = '<?php echo esc_url(home_url('/test-3')); ?>';
    });

    // Initialize
    fetchTestId();
});
</script>

<!-- <?php get_footer(); ?> -->
