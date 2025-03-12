<?php
/*
Template Name: Test Phase 0
*/
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
                Your score will be saved and analyzed.
                <br>
                Thank you for participating!
            </div>
            <button class="modal-button" onclick="window.location.href='<?php echo esc_url(home_url('/')); ?>'">Return to Home</button>
        </div>
    </div>
</div>

<script>
let timeLeft = 60;
let timerInterval;
let gameStarted = false;
let totalPs = 0;
let testId = '';

async function fetchUniqueTestID() {
    try {
        const response = await fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_unique_test_id'
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        if (data.success) {
            testId = data.test_id;
            document.getElementById('test-id').textContent = `Test ID: ${testId}`;
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

document.getElementById('startButton').addEventListener('click', startGame);

function startGame() {
    if (gameStarted) return;
    gameStarted = true;
    document.getElementById('startButton').style.display = 'none';
    fetchUniqueTestID();
    showLetter();
    startTimer();
}

function showLetter() {
    const letterElement = document.getElementById('letter');
    letterElement.textContent = 'P';
    letterElement.classList.add('letter-change');
    
    setTimeout(() => {
        letterElement.classList.remove('letter-change');
    }, 500);

    const randomInterval = Math.random() * (2000 - 500) + 500;
    setTimeout(showLetter, randomInterval);
    
    if (Math.random() < 0.5) {
        totalPs++;
    }
}

function startTimer() {
    timerInterval = setInterval(() => {
        timeLeft--;
        document.getElementById('timer').textContent = `Time Remaining: ${timeLeft}s`;
        
        if (timeLeft <= 0) {
            endGame();
        }
    }, 1000);
}

function endGame() {
    clearInterval(timerInterval);
    document.getElementById('gameOverModal').style.display = 'block';
    
    // Save test results
    saveTestResults();
}

async function saveTestResults() {
    try {
        const response = await fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=save_test_results&test_id=${testId}&total_ps=${totalPs}`
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        if (!data.success) {
            console.error('Failed to save test results');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}
</script>

<!-- <?php get_footer(); ?> -->
