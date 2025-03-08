<?php
/*
Template Name: Trial Test 1
*/

// get_header();
?>

<div id="overlay"></div>
<div id="startPopup">
    <h2>Start Demo</h2>
    <p>Click the button below to start the demo.</p>
    <button id="startDemoButton">Start Demo</button>
</div>

<div id="endPopup">
    <h2>Demo Completed</h2>
    <p>Click the button below to start the actual test.</p>
    <button id="startTestButton">Start Test</button>
</div>

<div id="timer">Time: 60s</div>
<div id="letter"></div>
<input type="text" id="inputBox" placeholder="Type here..." autocomplete="off">
<button id="startButton">Start</button>

<div id="gameOverModal" class="modal">
    <div class="modal-content">
        <h2>Test Complete!</h2>
        <p>Your score: <span id="finalScore">0</span></p>
        <p>Correct answers: <span id="correctAnswers">0</span></p>
        <p>Wrong answers: <span id="wrongAnswers">0</span></p>
        <button id="continueButton">Continue</button>
    </div>
</div>

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

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: white;
        margin: 15% auto;
        padding: 20px;
        border-radius: 5px;
        width: 70%;
        max-width: 500px;
        position: relative;
        color: black;
    }

    #startPopup, #endPopup {
        display: none;
        position: fixed;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        z-index: 1000;
        color: black;
    }

    #overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }
</style>

<script>
    let testId = null;
    let gameStarted = false;
    let gameEnded = false;
    let timer = null;
    let timeLeft = 60;
    let score = 0;
    let correctAnswers = 0;
    let wrongAnswers = 0;
    let currentLetter = '';
    let inputLocked = false;
    let lastLetterTime = 0;
    let pPositions = [];
    let currentPIndex = 0;

    async function fetchUniqueTestID() {
        try {
            const response = await fetch('<?php echo esc_url(rest_url('attentrack/v1/generate-test-id')); ?>', {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>',
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            testId = data.test_id;
        } catch (error) {
            console.error('Error fetching test ID:', error);
        }
    }

    window.onload = function() {
        showStartPopup();
        fetchUniqueTestID();
    };

    const timerElement = document.getElementById("timer");
    const letterElement = document.getElementById("letter");
    const inputBox = document.getElementById("inputBox");
    const startButton = document.getElementById("startButton");
    const modal = document.getElementById("gameOverModal");
    const finalScoreElement = document.getElementById("finalScore");
    const correctAnswersElement = document.getElementById("correctAnswers");
    const wrongAnswersElement = document.getElementById("wrongAnswers");
    const continueButton = document.getElementById("continueButton");

    function updateTimer() {
        timeLeft--;
        timerElement.textContent = `Time: ${timeLeft}s`;
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            gameEnded = true;
            showModal();
        }
    }

    function animateLetterChange(newLetter) {
        letterElement.style.opacity = "0";
        setTimeout(() => {
            letterElement.textContent = newLetter;
            letterElement.style.opacity = "1";
        }, 150);
    }

    function generatePPositions() {
        pPositions = [];
        let currentTime = 0;
        
        while (currentTime < 60000) {
            const randomInterval = Math.floor(Math.random() * (3000 - 2000 + 1)) + 2000;
            currentTime += randomInterval;
            
            if (currentTime <= 60000) {
                pPositions.push(currentTime);
            }
        }
    }

    function generateLetter() {
        if (gameEnded) return;
        
        const currentTime = Date.now() - lastLetterTime;
        
        if (currentPIndex < pPositions.length && currentTime >= pPositions[currentPIndex]) {
            currentLetter = 'P';
            currentPIndex++;
        } else {
            const letters = ['Q', 'R', 'S', 'T'];
            currentLetter = letters[Math.floor(Math.random() * letters.length)];
        }
        
        animateLetterChange(currentLetter);
        inputLocked = false;
    }

    inputBox.addEventListener('input', function() {
        if (!gameStarted || gameEnded || inputLocked) return;
        
        const userInput = this.value.toUpperCase();
        if (userInput.length > 0) {
            inputLocked = true;
            this.value = '';
            
            if (userInput === currentLetter) {
                score += 10;
                correctAnswers++;
            } else {
                wrongAnswers++;
            }
            
            setTimeout(generateLetter, 500);
        }
    });

    function startGame() {
        if (gameStarted) return;
        
        gameStarted = true;
        startButton.style.display = 'none';
        inputBox.style.display = 'block';
        
        generatePPositions();
        lastLetterTime = Date.now();
        generateLetter();
        
        timer = setInterval(updateTimer, 1000);
        inputBox.focus();
    }

    function showModal() {
        modal.style.display = "block";
        finalScoreElement.textContent = score;
        correctAnswersElement.textContent = correctAnswers;
        wrongAnswersElement.textContent = wrongAnswers;
    }

    async function submitForm() {
        try {
            const response = await fetch('<?php echo esc_url(rest_url('attentrack/v1/save-test-result')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify({
                    test_id: testId,
                    test_type: 'trial1',
                    score: score,
                    correct_answers: correctAnswers,
                    wrong_answers: wrongAnswers
                })
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
        } catch (error) {
            console.error('Error:', error);
        }
    }

    continueButton.addEventListener('click', function() {
        submitForm();
        setTimeout(() => {
            window.location.href = "<?php echo esc_url(home_url('/selectionpage2')); ?>";
        }, 500);
    });

    function startTest() {
        document.getElementById('endPopup').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
        startGame();
    }

    document.getElementById('startTestButton').onclick = startTest;

    function showStartPopup() {
        document.getElementById('startPopup').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    }

    function startDemo() {
        document.getElementById('startPopup').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
        startGame();
    }

    function showEndPopup() {
        document.getElementById('endPopup').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    }

    document.getElementById('startDemoButton').onclick = startDemo;

    startButton.onclick = startGame;
</script>

<!-- <?php get_footer(); ?> -->
