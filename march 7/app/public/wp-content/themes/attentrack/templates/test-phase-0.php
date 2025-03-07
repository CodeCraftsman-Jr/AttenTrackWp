<?php
/**
 * Template Name: Test Phase Zero
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}
?>

<div class="test-container">
    <h1 style="color:black">Selective And Sustained Attention Test</h1>
    
    <div id="test-area" class="position-relative">
        <div id="timer" class="test-timer" style="color:black;">Time Remaining: 80s</div>
        <div id="test-id"></div>
        <div id="letter" class="test-letter"></div>
        <input type="text" id="inputBox" class="test-input" maxlength="1" autocomplete="off">
        <button id="startButton" class="test-button">Start Test</button>
    </div>

    <!-- Modal for game over -->
    <div class="modal fade" id="gameOverModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Test Complete</h5>
                </div>
                <div class="modal-body">
                    <p>Your results have been recorded.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="continueButton" class="btn btn-primary">Continue to Next Phase</button>
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
    let testId = '<?php 
        $current_user = wp_get_current_user();
        $patient_id = get_user_meta($current_user->ID, 'patient_id', true);
        $test_phase = '0';
        $test_id = $patient_id . '_phase' . $test_phase;
        echo esc_js($test_id);
    ?>';
    document.getElementById('test-id').textContent = 'Test ID: ' + testId;

    let gameStarted = false;
    let gameEnded = false;
    let inputLocked = false;
    let currentLetter = '';
    let startTime = 0;
    let timerInterval;
    let timeLeft = 80; // 80 seconds
    let responses = [];
    let pPositions = [];
    let letterElement = $('#letter');
    let inputBox = $('#inputBox');
    let startButton = $('#startButton');
    let timerElement = $('#timer');
    let modal = $('#gameOverModal');
    let continueButton = $('#continueButton');

    // Update timer display
    function updateTimer() {
        let seconds = timeLeft;
        timerElement.text(`Time Remaining: ${seconds}s`);
        
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

    // Generate P positions
    function generatePPositions() {
        pPositions = [];
        let numPs = Math.floor(Math.random() * 3) + 3; // 3-5 P's
        for (let i = 0; i < numPs; i++) {
            pPositions.push(Math.floor(Math.random() * 26));
        }
        return pPositions;
    }

    // Generate letter
    function generateLetter() {
        if (gameEnded) return;

        let letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        let newLetter;
        
        if (pPositions.length === 0) {
            pPositions = generatePPositions();
        }

        if (pPositions.includes(responses.length)) {
            newLetter = 'P';
        } else {
            do {
                newLetter = letters[Math.floor(Math.random() * letters.length)];
            } while (newLetter === 'P');
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
                correct: (currentLetter === 'P' && input === 'P') || (currentLetter !== 'P' && input !== 'P')
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
        
        generatePPositions();
        generateLetter();
        
        timerInterval = setInterval(updateTimer, 1000);
    }

    // Show modal
    function showModal() {
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
                phase: 0,
                responses: responses
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = '<?php echo esc_url(home_url('/test-phase-1')); ?>';
                }
            }
        });
    }

    // Initialize
    inputBox.hide();
    
    // Event listeners
    startButton.click(startGame);
    continueButton.click(submitResults);
});
</script>

<?php ?>
