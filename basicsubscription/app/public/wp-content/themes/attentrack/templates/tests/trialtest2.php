<?php
/*
Template Name: Trial Test 2
*/

get_header();
?>

<div class="demo-indicator">DEMO MODE</div>
<div class="welcome-screen" id="welcomeScreen">
    <h1>Attention Testing - Demo Version</h1>
    <p>This is a 30-second demo version of the attention test.</p>
    <button id="startDemoButton">Start Demo</button>
</div>

<div class="test-container" style="display: none;">
    <div id="test-id"></div>
    <h1>Alphabet Test</h1>
    <div class="container">
        <div class="left-section">
            <div class="alphabet-grid"></div>
        </div>
        <div class="right-section">
            <div class="timer">30</div>
            <div class="current-number"></div>
            <input type="text" id="inputField" maxlength="1" placeholder="Type the corresponding letter">
            <div class="score">Score: <span id="scoreValue">0</span></div>
        </div>
    </div>
</div>

<div id="gameOverModal" class="modal">
    <div class="modal-content">
        <h2>Test Complete!</h2>
        <p>Your final score: <span id="finalScore">0</span></p>
        <p>Correct answers: <span id="correctAnswers">0</span></p>
        <p>Wrong answers: <span id="wrongAnswers">0</span></p>
        <button id="continueButton">Continue</button>
    </div>
</div>

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

    .demo-indicator {
        position: fixed;
        top: 10px;
        right: 10px;
        background-color: #ff4444;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
        z-index: 1000;
    }

    .welcome-screen {
        text-align: center;
        background-color: rgba(255, 255, 255, 0.9);
        padding: 40px;
        border-radius: 10px;
        color: #333;
    }

    #startDemoButton {
        font-size: 1.2em;
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 20px;
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
        font-size: 1.5em;
        margin: 20px 0;
        color: #000000;
    }

    #inputField {
        font-size: 1.2em;
        padding: 10px;
        margin: 20px 0;
        width: 200px;
        text-align: center;
        border: 2px solid #ccc;
        border-radius: 5px;
    }

    .score {
        font-size: 1.5em;
        margin-top: 20px;
        color: #000000;
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

    #continueButton {
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
    let timer;
    let timeLeft = 30;
    let score = 0;
    let correctAnswers = 0;
    let wrongAnswers = 0;
    let currentRandomNumber;
    let alphabetMap = {};
    let testStarted = false;

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
            document.getElementById('test-id').textContent = `Test ID: ${testId}`;
        } catch (error) {
            console.error('Error fetching test ID:', error);
        }
    }

    function updateLog(message) {
        console.log(message);
    }

    function nextRound() {
        if (!testStarted) return;
        
        currentRandomNumber = Math.floor(Math.random() * 26) + 1;
        document.querySelector('.current-number').textContent = `Number: ${currentRandomNumber}`;
        document.getElementById('inputField').value = '';
        document.getElementById('inputField').focus();
    }

    function startTest() {
        testStarted = true;
        document.getElementById('welcomeScreen').style.display = 'none';
        document.querySelector('.test-container').style.display = 'block';
        document.getElementById('inputField').focus();
        startTimer();
        nextRound();
    }

    function startTimer() {
        timer = setInterval(() => {
            timeLeft--;
            document.querySelector('.timer').textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                testStarted = false;
                document.getElementById('gameOverModal').style.display = 'block';
                document.getElementById('finalScore').textContent = score;
                document.getElementById('correctAnswers').textContent = correctAnswers;
                document.getElementById('wrongAnswers').textContent = wrongAnswers;
            }
        }, 1000);
    }

    function startDemo() {
        document.getElementById('welcomeScreen').style.display = 'none';
        document.querySelector('.test-container').style.display = 'block';
        startTest();
    }

    // Initialize alphabet grid and mapping
    const alphabetGrid = document.querySelector('.alphabet-grid');
    for (let i = 1; i <= 26; i++) {
        const letter = String.fromCharCode(64 + i);
        alphabetMap[i] = letter;
        const div = document.createElement('div');
        div.textContent = `${i} - ${letter}`;
        alphabetGrid.appendChild(div);
    }

    document.getElementById('inputField').addEventListener('input', function (e) {
        if (!testStarted) return;
        
        const userAnswer = e.target.value.toUpperCase();
        const correctAnswer = alphabetMap[currentRandomNumber];
        
        if (userAnswer.length === 1) {
            if (userAnswer === correctAnswer) {
                score += 10;
                correctAnswers++;
                document.getElementById('scoreValue').textContent = score;
            } else {
                wrongAnswers++;
            }
            nextRound();
        }
    });

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
                    test_type: 'trial2',
                    score: score,
                    correct_answers: correctAnswers,
                    wrong_answers: wrongAnswers
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
