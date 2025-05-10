<?php
/*
Template Name: Demo Alternative Attention Test
*/

// Start session if not already started
if (!session_id()) {
    session_start();
}

// Set demo test parameters
$_SESSION['test_time_limit'] = 20;
$_SESSION['disable_db_storage'] = true;
$_SESSION['is_demo'] = true;

// Get current user's IDs
$user_id = get_current_user_id();
$user_ids = get_user_ids($user_id);

// Log IDs for verification
error_log('Demo Alternative Attention Test - User IDs:');
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
    <title>Demo Alternative Attention Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px;
            gap: 40px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .left-section {
            flex: 1;
            max-width: 600px;
        }

        .right-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
        }

        .timer-section {
            text-align: center;
            margin-bottom: 40px;
        }

        #timer {
            font-size: 72px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        #current-number {
            font-size: 48px;
            color: #333;
            margin-top: 20px;
            font-weight: bold;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin: 20px auto;
        }

        .grid-item {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            aspect-ratio: 1;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .grid-item:hover {
            background-color: #e9ecef;
        }

        .letter {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .number {
            font-size: 20px;
            color: #666;
        }

        #startButton {
            background-color: #00e6e6;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 30px;
        }

        #startButton:hover {
            background-color: #00b3b3;
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px;
            margin: 20px 0;
            border: 1px solid transparent;
            border-radius: 4px;
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
            text-align: center;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 90%;
        }

        .hidden {
            display: none;
        }

        #inputField {
            font-size: 24px;
            padding: 12px 20px;
            width: 200px;
            text-align: center;
            margin: 20px 0;
            border: 2px solid #ddd;
            border-radius: 5px;
            transition: border-color 0.3s;
        }

        #inputField:focus {
            outline: none;
            border-color: #00e6e6;
        }

        #inputField.hidden {
            display: none;
        }
    </style>
</head>
<body>
    <h1>Alternative Attention Test</h1>

    <!-- Demo Notice -->
    <div class="alert">
        <i class="fas fa-info-circle"></i>
        This is a 60-second demo version. For full access to tests and results, please upgrade your subscription.
    </div>

    <div class="container">
        <div class="left-section">
            <div class="grid-container">
                <div class="grid-item">
                    <div class="letter">A</div>
                    <div class="number">1</div>
                </div>
                <div class="grid-item">
                    <div class="letter">B</div>
                    <div class="number">2</div>
                </div>
                <div class="grid-item">
                    <div class="letter">C</div>
                    <div class="number">3</div>
                </div>
                <div class="grid-item">
                    <div class="letter">D</div>
                    <div class="number">4</div>
                </div>
                <div class="grid-item">
                    <div class="letter">E</div>
                    <div class="number">5</div>
                </div>
                <div class="grid-item">
                    <div class="letter">F</div>
                    <div class="number">6</div>
                </div>
                <div class="grid-item">
                    <div class="letter">G</div>
                    <div class="number">7</div>
                </div>
                <div class="grid-item">
                    <div class="letter">H</div>
                    <div class="number">8</div>
                </div>
                <div class="grid-item">
                    <div class="letter">I</div>
                    <div class="number">9</div>
                </div>
                <div class="grid-item">
                    <div class="letter">J</div>
                    <div class="number">10</div>
                </div>
                <div class="grid-item">
                    <div class="letter">K</div>
                    <div class="number">11</div>
                </div>
                <div class="grid-item">
                    <div class="letter">L</div>
                    <div class="number">12</div>
                </div>
                <div class="grid-item">
                    <div class="letter">M</div>
                    <div class="number">13</div>
                </div>
                <div class="grid-item">
                    <div class="letter">N</div>
                    <div class="number">14</div>
                </div>
                <div class="grid-item">
                    <div class="letter">O</div>
                    <div class="number">15</div>
                </div>
                <div class="grid-item">
                    <div class="letter">P</div>
                    <div class="number">16</div>
                </div>
                <div class="grid-item">
                    <div class="letter">Q</div>
                    <div class="number">17</div>
                </div>
                <div class="grid-item">
                    <div class="letter">R</div>
                    <div class="number">18</div>
                </div>
                <div class="grid-item">
                    <div class="letter">S</div>
                    <div class="number">19</div>
                </div>
                <div class="grid-item">
                    <div class="letter">T</div>
                    <div class="number">20</div>
                </div>
                <div class="grid-item">
                    <div class="letter">U</div>
                    <div class="number">21</div>
                </div>
                <div class="grid-item">
                    <div class="letter">V</div>
                    <div class="number">22</div>
                </div>
                <div class="grid-item">
                    <div class="letter">W</div>
                    <div class="number">23</div>
                </div>
                <div class="grid-item">
                    <div class="letter">X</div>
                    <div class="number">24</div>
                </div>
                <div class="grid-item">
                    <div class="letter">Y</div>
                    <div class="number">25</div>
                </div>
                <div class="grid-item">
                    <div class="letter">Z</div>
                    <div class="number">26</div>
                </div>
            </div>
        </div>

        <div class="right-section">
            <div class="timer-section">
                <div id="timer">60</div>
                <div id="current-number"></div>
            </div>
            <input type="text" id="inputField" maxlength="1" placeholder="Enter letter" class="hidden">
            <button id="startButton">Start Test</button>
        </div>
    </div>

    <div id="results-modal" class="modal">
        <div class="modal-content">
            <h2>Demo Results</h2>
            <p>Correct Responses: <span id="correct-count">0</span></p>
            <p>Wrong Responses: <span id="wrong-count">0</span></p>
            <p>Average Reaction Time: <span id="avg-reaction-time">0</span> ms</p>
            <div class="alert mt-4">
                <i class="fas fa-star"></i>
                Want to see your full results and track your progress? 
                <a href="<?php echo home_url('/subscription-plans'); ?>">Upgrade your subscription</a>
            </div>
            <div class="mt-4">
                <button id="retryButton" class="btn btn-secondary">Try Again</button>
                <a href="<?php echo home_url('/subscription-plans'); ?>" class="btn btn-primary">Upgrade Now</a>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const timer = document.getElementById('timer');
        const currentNumber = document.getElementById('current-number');
        const inputField = document.getElementById('inputField');
        const startButton = document.getElementById('startButton');
        const gridItems = document.querySelectorAll('.grid-item');
        const resultsModal = document.getElementById('results-modal');
        const retryButton = document.getElementById('retryButton');
        
        let timeLeft = 60;
        let isTestRunning = false;
        let timerInterval;
        let correctCount = 0;
        let wrongCount = 0;
        let reactionTimes = [];
        let totalResponseTime = 0;
        let roundCount = 0;
        let totalLettersShown = 0;
        let lastLetterTime = null;
        let currentLetter = null;
        let currentTargetNumber = null;

        function getRandomGridItem() {
            const index = Math.floor(Math.random() * gridItems.length);
            const item = gridItems[index];
            const letter = item.querySelector('.letter').textContent;
            const number = parseInt(item.querySelector('.number').textContent);
            return { item, letter, number };
        }

        function showNewTarget() {
            if (!isTestRunning) return;

            const { letter, number } = getRandomGridItem();
            currentLetter = letter;
            currentTargetNumber = number;
            currentNumber.textContent = number;
            lastLetterTime = Date.now();
            totalLettersShown++;
            inputField.value = '';
            inputField.focus();
        }

        function startTest() {
            console.log('Starting test...');
            isTestRunning = true;
            timeLeft = 60;
            correctCount = 0;
            wrongCount = 0;
            reactionTimes = [];
            totalResponseTime = 0;
            roundCount = 0;
            totalLettersShown = 0;
            
            startButton.style.display = 'none';
            inputField.classList.remove('hidden');
            inputField.focus();
            timer.textContent = timeLeft;
            currentNumber.textContent = '';

            timerInterval = setInterval(() => {
                timeLeft--;
                timer.textContent = timeLeft;
                
                if (timeLeft <= 0) {
                    endTest();
                }
            }, 1000);

            showNewTarget();
        }

        function endTest() {
            console.log('Test ended');
            isTestRunning = false;
            clearInterval(timerInterval);
            inputField.classList.add('hidden');
            currentNumber.textContent = '';

            const avgReactionTime = roundCount > 0 
                ? Math.round(totalResponseTime / roundCount) 
                : 0;

            document.getElementById('correct-count').textContent = correctCount;
            document.getElementById('wrong-count').textContent = wrongCount;
            document.getElementById('avg-reaction-time').textContent = avgReactionTime;
            resultsModal.style.display = 'block';
        }

        startButton.addEventListener('click', startTest);

        retryButton.addEventListener('click', function() {
            resultsModal.style.display = 'none';
            startButton.style.display = 'block';
            timer.textContent = '60';
            currentNumber.textContent = '';
            inputField.classList.add('hidden');
        });

        inputField.addEventListener('input', function(e) {
            if (!isTestRunning || !currentLetter) return;

            const userInput = e.target.value.toUpperCase();
            if (userInput.length > 0) {
                const reactionTime = Date.now() - lastLetterTime;
                roundCount++;

                if (userInput === currentLetter) {
                    correctCount++;
                    totalResponseTime += reactionTime;
                } else {
                    wrongCount++;
                }

                setTimeout(showNewTarget, 500);
            }
        });

        // Remove click handlers from grid items since we're using keyboard input
        gridItems.forEach(item => {
            item.style.cursor = 'default';
        });
    });
    </script>
</body>
</html>

<?php get_footer(); ?>
