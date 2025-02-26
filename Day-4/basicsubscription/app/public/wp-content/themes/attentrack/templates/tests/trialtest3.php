<?php
/*
Template Name: Trial Test 3
*/

get_header();
?>

<div class="demo-indicator">DEMO MODE</div>
<div class="welcome-screen" id="welcomeScreen">
    <h1>Color Click Test - Demo Version</h1>
    <p>This is a 30-second demo version of the color click test.</p>
    <button id="startDemoButton">Start Demo</button>
</div>

<div id="gameContainer" class="hidden">
    <div id="timer">30</div>
    <div id="userLog"></div>
    <div id="grid">
        <div class="color-container">
            <div class="color-box" data-color="red" style="background-color: red;">RED</div>
            <div class="color-box" data-color="blue" style="background-color: blue;">BLUE</div>
            <div class="color-box" data-color="green" style="background-color: green;">GREEN</div>
            <div class="color-box" data-color="yellow" style="background-color: yellow; color: black;">YELLOW</div>
        </div>
    </div>
</div>

<div id="gameOverModal" class="modal">
    <div class="modal-content">
        <h2>Test Complete!</h2>
        <p>Your score: <span id="finalScore">0</span></p>
        <p>Correct clicks: <span id="correctClicks">0</span></p>
        <p>Wrong clicks: <span id="wrongClicks">0</span></p>
        <button id="continueButton">Continue</button>
    </div>
</div>

<style>
    body {
        font-family: Arial, sans-serif;
        text-align: center;
        margin: 0;
        padding: 20px;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        align-items: center;
    }

    #grid {
        width: 100%;
        max-width: 1800px;
        margin: 20px auto;
        padding: 20px;
    }

    .color-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        gap: 15px;
        padding: 15px;
        height: 300px;
    }

    .color-box {
        flex: 1;
        height: 100%;
        min-width: 0;
        border: 4px solid black;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(20px, 3vw, 32px);
        color: white;
        font-weight: bold;
        border-radius: 10px;
        padding: 15px;
        margin: 5px;
    }

    .color-box:hover {
        transform: scale(1.05);
        box-shadow: 0px 0px 15px rgba(0,0,0,0.5);
    }

    @media (max-width: 600px) {
        .color-box {
            font-size: 14px;
            padding: 5px;
        }
        body {
            padding: 10px;
        }
    }

    .demo-indicator {
        position: fixed;
        top: 10px;
        right: 10px;
        background-color: #ff6b6b;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
        z-index: 100;
    }

    .welcome-screen {
        text-align: center;
        padding: 20px;
        background-color: white;
        border-radius: 10px;
        margin: 20px;
    }

    .hidden {
        display: none !important;
    }

    #timer {
        font-size: 48px;
        margin: 20px 0;
        color: #2c3e50;
        font-weight: bold;
    }

    #userLog {
        position: absolute;
        top: 20px;
        left: 20px;
        background-color: white;
        padding: 15px 25px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        color: #333;
        font-size: 16px;
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

    #startDemoButton, #continueButton {
        font-size: 1.2em;
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 20px;
    }
</style>

<script>
    let testId = null;
    let gameStarted = false;
    let score = 0;
    let correctClicks = 0;
    let wrongClicks = 0;
    let timeLeft = 30;
    let timer;
    let currentColor = '';
    let lastColor = '';
    const colors = ['red', 'blue', 'green', 'yellow'];
    const colorBoxes = document.querySelectorAll('.color-box');
    const gameContainer = document.getElementById('gameContainer');
    const timerDisplay = document.getElementById('timer');
    const userLog = document.getElementById('userLog');

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
            userLog.textContent = `Test ID: ${testId}`;
        } catch (error) {
            console.error('Error fetching test ID:', error);
        }
    }

    function startDemo() {
        document.getElementById('welcomeScreen').style.display = 'none';
        gameContainer.classList.remove('hidden');
        startGame();
    }

    function startGame() {
        if (gameStarted) return;
        
        gameStarted = true;
        score = 0;
        correctClicks = 0;
        wrongClicks = 0;
        timeLeft = 30;
        
        timer = setInterval(() => {
            timeLeft--;
            timerDisplay.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                endGame();
            }
        }, 1000);
        
        playRandomColor();
    }

    function playRandomColor() {
        if (!gameStarted) return;
        
        const shuffledColors = shuffleColors();
        currentColor = shuffledColors[0];
        
        while (currentColor === lastColor) {
            shuffleColors();
            currentColor = shuffledColors[0];
        }
        
        lastColor = currentColor;
        
        colorBoxes.forEach(box => {
            const boxColor = box.getAttribute('data-color');
            if (boxColor === currentColor) {
                box.style.border = '4px solid gold';
            } else {
                box.style.border = '4px solid black';
            }
        });
    }

    function shuffleColors() {
        const shuffled = [...colors];
        for (let i = shuffled.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
        }
        return shuffled;
    }

    colorBoxes.forEach(box => {
        box.addEventListener('click', (e) => {
            if (!gameStarted) return;

            const clickedColor = box.getAttribute('data-color');
            if (clickedColor === currentColor) {
                score += 10;
                correctClicks++;
                playRandomColor();
            } else {
                wrongClicks++;
            }
        });
    });

    function endGame() {
        clearInterval(timer);
        gameStarted = false;
        
        document.getElementById('gameOverModal').style.display = 'block';
        document.getElementById('finalScore').textContent = score;
        document.getElementById('correctClicks').textContent = correctClicks;
        document.getElementById('wrongClicks').textContent = wrongClicks;
        
        colorBoxes.forEach(box => {
            box.style.border = '4px solid black';
        });
    }

    document.getElementById('startDemoButton').addEventListener('click', startDemo);

    document.getElementById('continueButton').addEventListener('click', async function() {
        try {
            const response = await fetch('<?php echo esc_url(rest_url('attentrack/v1/save-test-result')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify({
                    test_id: testId,
                    test_type: 'trial3',
                    score: score,
                    correct_answers: correctClicks,
                    wrong_answers: wrongClicks
                })
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            window.location.href = "<?php echo esc_url(home_url('/selectionpage2')); ?>";
        } catch (error) {
            console.error('Error:', error);
        }
    });

    // Initialize the test
    fetchUniqueTestID();
</script>

<?php get_footer(); ?>
