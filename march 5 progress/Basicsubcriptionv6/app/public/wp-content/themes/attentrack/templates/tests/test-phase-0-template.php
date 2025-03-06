<?php
/*
Template Name: Test Phase 0 Template
*/

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
        color: #fff;
    }

    h1 {
        color: #000;
        font-size: 36px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        margin-bottom: 20px;
    }

    #timer {
        font-size: 24px;
        font-weight: bold;
        margin-top: 10px;
        color: #000;
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

<div class="test-container">
    <h1>Selective Attention Test</h1>
    
    <div id="timer">Time Remaining: 60s</div>
    <div id="letter"></div>
    <div id="test-id"></div>

    <button id="startButton">Start Test</button>

    <div id="gameOverModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">Test Complete!</div>
            <div class="modal-body">
                Your test has been completed. Click continue to proceed to the next test.
            </div>
            <button id="continueButton" class="modal-button">Continue</button>
        </div>
    </div>
</div>

<script>
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
let testId = '';

async function fetchUniqueTestID() {
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
        document.getElementById('test-id').textContent = `Test ID: ${testId}`;
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
    document.getElementById('timer').textContent = `Time Remaining: ${timeLeft}s`;
}

function animateLetterChange(newLetter) {
    const letterElement = document.getElementById('letter');
    letterElement.classList.remove('letter-change');
    void letterElement.offsetWidth;
    letterElement.classList.add('letter-change');
    letterElement.textContent = newLetter;
}

function generatePPositions() {
    const positions = [];
    const totalLetters = 200;
    const numberOfPs = 40;

    while (positions.length < numberOfPs) {
        const position = Math.floor(Math.random() * totalLetters);
        if (!positions.includes(position)) {
            positions.push(position);
        }
    }

    return positions;
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

document.addEventListener('keydown', function(event) {
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

function startGame() {
    if (gameStarted) return;
    
    gameStarted = true;
    document.getElementById('startButton').style.display = 'none';
    
    timer = setInterval(updateTimer, 1000);
    generateLetter();
}

function showModal() {
    const modal = document.getElementById('gameOverModal');
    modal.style.display = 'block';
}

async function submitForm() {
    const testData = {
        test_id: testId,
        correct_clicks: correctClicks,
        incorrect_clicks: incorrectClicks,
        missed_ps: missedPs,
        total_ps: totalPs,
        completion_time: 60 - timeLeft
    };

    try {
        const response = await fetch('<?php echo esc_url(rest_url('attentrack/v1/save-test-results')); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
            },
            body: JSON.stringify(testData)
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        window.location.href = '<?php echo esc_url(home_url('/selection-page-2')); ?>';
    } catch (error) {
        console.error('Error:', error);
    }
}

document.getElementById('startButton').addEventListener('click', startGame);
document.getElementById('continueButton').addEventListener('click', submitForm);

window.onload = fetchUniqueTestID;
</script>

<?php
get_footer();
?>
