<?php
/**
 * Template Name: Test Phase 2
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

get_header();
?>

<div class="test-container">
    <h1 class="test-title">Selective And Sustained Attention Test</h1>
    
    <div id="test-area" class="position-relative">
        <div id="letter" class="test-letter"></div>
        <input type="text" id="inputBox" class="test-input" maxlength="1" autocomplete="off">
        <button id="startButton" class="test-button">Start Test</button>
        <div id="timer" class="test-timer"></div>
    </div>

    <!-- Modal for game over -->
    <div class="modal fade" id="gameOverModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Test Complete</h5>
                </div>
                <div class="modal-body">
                    <div class="results-summary">
                        <p>Total Letters: <span id="totalLetters">0</span></p>
                        <p>Correct Responses: <span id="correctResponses">0</span></p>
                        <p>Accuracy: <span id="accuracy">0</span>%</p>
                        <p>Average Response Time: <span id="avgResponseTime">0</span>ms</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="continueButton" class="btn btn-primary">Continue to Break</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
body {
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
}

.test-container {
    text-align: center;
    padding: 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: calc(100vh - 100px);
}

.test-title {
    color: var(--secondary-color);
    font-size: 36px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.test-letter {
    font-size: 200px;
    color: var(--secondary-color);
    min-width: 100px;
    min-height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    margin: 30px 0;
}

.test-input {
    font-size: 28px;
    padding: 10px;
    width: 130px;
    margin-top: 20px;
    border-radius: 5px;
    border: 2px solid #ccc;
    transition: all 0.3s ease;
    text-align: center;
}

.test-input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 10px rgba(132, 203, 212, 0.3);
}

.test-button {
    font-size: 24px;
    padding: 15px 30px;
    margin-top: 30px;
    cursor: pointer;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 5px;
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.test-button:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: 0 7px 14px rgba(0, 0, 0, 0.15);
}

.test-timer {
    font-size: 24px;
    font-weight: bold;
    margin-top: 20px;
    color: var(--secondary-color);
}

.results-summary {
    text-align: left;
    margin: 20px 0;
}

.results-summary p {
    margin: 10px 0;
    font-size: 16px;
}

.fade-out {
    animation: fadeOut 0.5s ease forwards;
}

.fade-in {
    animation: fadeIn 0.5s ease forwards;
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

<script>
jQuery(document).ready(function($) {
    let testId = null;
    let gameStarted = false;
    let gameEnded = false;
    let inputLocked = false;
    let currentLetter = '';
    let startTime = 0;
    let timerInterval;
    let timeLeft = 300; // 5 minutes
    let responses = [];
    let letterElement = $('#letter');
    let inputBox = $('#inputBox');
    let startButton = $('#startButton');
    let timerElement = $('#timer');
    let modal = $('#gameOverModal');
    let continueButton = $('#continueButton');

    // Fetch unique test ID
    function fetchUniqueTestID() {
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'get_unique_test_id',
                nonce: '<?php echo wp_create_nonce('get_unique_test_id'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    testId = response.data.test_id;
                }
            }
        });
    }

    // Update timer display
    function updateTimer() {
        let minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;
        timerElement.text(`${minutes}:${seconds < 10 ? '0' : ''}${seconds}`);
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            gameEnded = true;
            showModal();
        }
        timeLeft--;
    }

    // Animate letter change
    function animateLetterChange(newLetter) {
        letterElement.addClass('fade-out');
        setTimeout(() => {
            letterElement.text(newLetter);
            letterElement.removeClass('fade-out').addClass('fade-in');
            setTimeout(() => letterElement.removeClass('fade-in'), 500);
        }, 500);
    }

    // Generate letter
    function generateLetter() {
        if (gameEnded) return;

        let letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        let newLetter;
        
        // 20% chance of generating 'O'
        if (Math.random() < 0.2) {
            newLetter = 'O';
        } else {
            do {
                newLetter = letters[Math.floor(Math.random() * letters.length)];
            } while (newLetter === 'O');
        }

        currentLetter = newLetter;
        animateLetterChange(newLetter);
        inputLocked = false;
        startTime = Date.now();
    }

    // Input handler
    inputBox.on('input', function() {
        if (!gameStarted || gameEnded || inputLocked) return;

        let input = $(this).val().toUpperCase();
        if (input.length === 1) {
            inputLocked = true;
            let endTime = Date.now();
            let responseTime = endTime - startTime;

            responses.push({
                letter: currentLetter,
                response: input,
                responseTime: responseTime,
                correct: (currentLetter === 'O' && input === 'O') || (currentLetter !== 'O' && input !== 'O')
            });

            $(this).val('');
            setTimeout(generateLetter, 500);
        }
    });

    // Start game
    function startGame() {
        if (gameStarted) return;
        
        gameStarted = true;
        startButton.hide();
        inputBox.show().focus();
        
        generateLetter();
        timerInterval = setInterval(updateTimer, 1000);
    }

    // Show modal with results
    function showModal() {
        let totalLetters = responses.length;
        let correctResponses = responses.filter(r => r.correct).length;
        let accuracy = ((correctResponses / totalLetters) * 100).toFixed(2);
        let avgResponseTime = (responses.reduce((sum, r) => sum + r.responseTime, 0) / totalLetters).toFixed(2);

        $('#totalLetters').text(totalLetters);
        $('#correctResponses').text(correctResponses);
        $('#accuracy').text(accuracy);
        $('#avgResponseTime').text(avgResponseTime);

        modal.modal('show');
    }

    // Submit results
    function submitResults() {
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'save_test_results',
                nonce: '<?php echo wp_create_nonce('save_test_results'); ?>',
                test_id: testId,
                phase: 2,
                responses: responses
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = '<?php echo esc_url(home_url('/test-break-2')); ?>';
                }
            }
        });
    }

    // Initialize
    inputBox.hide();
    fetchUniqueTestID();
    
    // Event listeners
    startButton.click(startGame);
    continueButton.click(submitResults);
});
</script>

<?php get_footer(); ?>
