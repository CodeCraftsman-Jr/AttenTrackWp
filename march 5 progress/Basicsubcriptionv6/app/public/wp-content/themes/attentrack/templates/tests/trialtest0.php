<?php
/**
 * Template Name: Trial Test 0
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

get_header();
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
        color: #333;
    }

    h1 {
        font-size: 36px;
        margin-bottom: 20px;
        color: #333;
    }

    #timer {
        font-size: 24px;
        font-weight: bold;
        margin-top: 10px;
        color: #333;
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

    .button {
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

    .button:hover {
        background-color: #40E0D0;
        transform: translateY(-2px);
        box-shadow: 0 7px 14px rgba(0, 0, 0, 0.15);
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

    .letter-change {
        animation: letterChange 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
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

    .popup {
        display: none;
        position: fixed;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        padding: 30px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        z-index: 1000;
        max-width: 500px;
        width: 90%;
    }

    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    .popup h2 {
        margin-top: 0;
        color: #333;
    }

    .popup p {
        color: #666;
        line-height: 1.6;
    }
</style>

<div class="test-container">
    <h1>Selective Attention Test - Trial</h1>
    <div id="test-id" hidden></div>
    
    <div id="letter"></div>
    <div id="timer">Time Remaining: 60s</div>
    <button id="startButton" class="button">Start Test</button>
</div>

<div id="overlay" class="overlay"></div>

<div id="startPopup" class="popup">
    <h2>Start Demo</h2>
    <p>This is a trial test to help you understand how the actual test works.</p>
    <p>Click on the spacebar whenever you see the letter 'P' appear on the screen.</p>
    <button id="startTestButton" class="button">Start Trial</button>
</div>

<div id="endPopup" class="popup">
    <h2>Demo Complete</h2>
    <p>You have completed the trial test. Are you ready to start the actual test?</p>
    <button id="startActualButton" class="button">Start Actual Test</button>
</div>

<!-- Modal for results -->
<div class="modal fade" id="resultsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Trial Test Complete</h5>
            </div>
            <div class="modal-body">
                <div class="results-summary">
                    <p>Correct Clicks: <span id="correctClicks">0</span></p>
                    <p>Incorrect Clicks: <span id="incorrectClicks">0</span></p>
                    <p>Missed P's: <span id="missedPs">0</span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="continueButton" class="btn btn-primary">Continue to Selection Page</button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let testId = null;
    let gameStarted = false;
    let gameEnded = false;
    let inputLocked = false;
    let currentLetter = '';
    let timer;
    let timeLeft = 60;
    let correctClicks = 0;
    let incorrectClicks = 0;
    let missedPs = 0;
    let totalPs = 0;

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

    function updateTimer() {
        if (timeLeft <= 0) {
            clearInterval(timer);
            gameEnded = true;
            showModal();
            return;
        }
        timeLeft--;
        $('#timer').text(`Time Remaining: ${timeLeft}s`);
    }

    function animateLetterChange(newLetter) {
        const letterElement = $('#letter');
        letterElement.removeClass('letter-change');
        void letterElement[0].offsetWidth;
        letterElement.addClass('letter-change');
        letterElement.text(newLetter);
    }

    function generateLetter() {
        if (!gameStarted || gameEnded) return;

        const letters = ['b', 'd', 'p', 'q', 'P'];
        const randomIndex = Math.floor(Math.random() * letters.length);
        const newLetter = letters[randomIndex];

        if (newLetter === 'P') {
            totalPs++;
        }

        currentLetter = newLetter;
        animateLetterChange(newLetter);

        setTimeout(() => {
            if (currentLetter === 'P' && !inputLocked) {
                missedPs++;
            }
            generateLetter();
        }, 1000);
    }

    function startGame() {
        if (gameStarted) return;
        
        gameStarted = true;
        $('#startButton').hide();
        
        timer = setInterval(updateTimer, 1000);
        generateLetter();
    }

    function showStartPopup() {
        $('#overlay').show();
        $('#startPopup').show();
    }

    function startDemo() {
        $('#overlay').hide();
        $('#startPopup').hide();
        startGame();
    }

    function showEndPopup() {
        $('#overlay').show();
        $('#endPopup').show();
    }

    async function submitResults() {
        const results = {
            test_id: testId,
            correct_clicks: correctClicks,
            incorrect_clicks: incorrectClicks,
            missed_ps: missedPs,
            total_ps: totalPs,
            completion_time: 60 - timeLeft
        };

        try {
            const response = await fetch('<?php echo esc_url(rest_url('attentrack/v1/save-trial-results')); ?>', {
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

            window.location.href = '<?php echo esc_url(home_url('/selection-page-2')); ?>';
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Event Listeners
    $(document).on('keydown', function(event) {
        if (!gameStarted || gameEnded || inputLocked) return;

        if (event.key === ' ' || event.key === 'Spacebar') {
            event.preventDefault();
            
            if (currentLetter === 'P') {
                correctClicks++;
                missedPs--;
            } else {
                incorrectClicks++;
            }
        }
    });

    $('#startButton').on('click', showStartPopup);
    $('#startTestButton').on('click', startDemo);
    $('#startActualButton').on('click', function() {
        window.location.href = '<?php echo esc_url(home_url('/test-0')); ?>';
    });
    $('#continueButton').on('click', submitResults);

    // Initialize
    fetchTestId();
});
</script>

<?php get_footer(); ?>
