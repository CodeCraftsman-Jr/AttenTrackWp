console.log('Test Phase 1 script starting...');

// Variables initialization
let totalCount = 0;
let pCount = 0;
const REQUIRED_P_COUNT = 25;
const letters = ['b', 'd', 'q', 'r'];

let gameStarted = false;
let gameEnded = false;
let currentLetter = '';
let startTime = 0;
let timerInterval;
let timeLeft = 80;
let responses = [];
let responseReceived = false;
let inputLocked = false;
let correctCount = 0;
let incorrectCount = 0;
let missedCount = 0;

// Get DOM elements
const letterElement = document.getElementById('letter');
const inputBox = document.getElementById('inputBox');
const startButton = document.getElementById('startButton');
const timerElement = document.getElementById('timer');
const modal = document.getElementById('gameOverModal');
const modalHeader = modal.querySelector('.modal-header');
const modalBody = modal.querySelector('.modal-body');
const continueButton = document.getElementById('continueButton');

// Hide input box initially
inputBox.style.display = 'none';

// Update timer display
function updateTimer() {
    timeLeft--;
    document.getElementById('timer').textContent = `Time Remaining: ${timeLeft}s`;
    
    // Check for missed response on previous letter
    if (!responseReceived && currentLetter === 'p') {
        console.log('Missed p response');
        missedCount++;
        document.getElementById('unattemptedCount').textContent = missedCount;
        responses.push({
            letter: currentLetter,
            response: '',
            correct: false,
            reactionTime: 2000,
            missed: true
        });
    }

    if (timeLeft <= 0 || (pCount >= REQUIRED_P_COUNT && !responseReceived)) {
        gameEnded = true;
        clearInterval(timerInterval);
        inputBox.disabled = true;
        showModal();
    }
}

// Generate letter
function showLetter() {
    if (gameEnded) return;
    
    // Check if we've shown enough 'p' letters
    if (pCount < REQUIRED_P_COUNT) {
        // Increase chance of 'p' appearing as time runs out
        const remainingTime = timeLeft;
        const pProbability = Math.min(0.8, 0.3 + (0.5 * (1 - remainingTime/80)));
        const letters = ['b', 'd', 'p', 'q', 'r'];
        currentLetter = Math.random() < pProbability ? 'p' : letters[Math.floor(Math.random() * letters.length)];
        if (currentLetter === 'p') pCount++;
    } else {
        // Only show non-p letters once we've met the p count
        const nonPLetters = ['b', 'd', 'q', 'r'];
        currentLetter = nonPLetters[Math.floor(Math.random() * nonPLetters.length)];
    }

    totalCount++;
    console.log('Generated letter:', currentLetter, 'P count:', pCount, 'Total count:', totalCount);

    // Apply animation
    letterElement.textContent = '';
    letterElement.classList.remove('letter-change');
    void letterElement.offsetWidth; // Trigger reflow
    letterElement.textContent = currentLetter;
    letterElement.classList.add('letter-change');

    startTime = Date.now();
    responseReceived = false;
}

// Start game
function startGame() {
    console.log('Starting game...');
    if (!gameStarted) {
        gameStarted = true;
        
        // Show input box and hide start button
        inputBox.style.display = 'block';
        startButton.style.display = 'none';
        
        inputBox.value = '';
        inputBox.focus();
        
        // Start letter generation
        showLetter();
        setInterval(showLetter, 2000);
        
        // Start timer
        timerInterval = setInterval(updateTimer, 1000);
    }
}

// Handle input
inputBox.addEventListener('input', function(e) {
    if (!gameStarted || gameEnded || inputLocked) {
        console.log('Input ignored - game not in correct state');
        return;
    }

    const userInput = e.target.value.toLowerCase();
    console.log('Input received:', userInput);
    
    if (userInput.length > 0) {
        console.log('Processing input:', userInput, 'Current letter:', currentLetter);
        responseReceived = true;
        inputLocked = true;

        const reactionTime = Date.now() - startTime;
        console.log('Reaction time:', reactionTime, 'ms');

        if (currentLetter === 'p') {
            if (userInput === 'p') {
                console.log('Correct response to p');
                correctCount++;
            } else {
                console.log('Incorrect response to p');
                incorrectCount++;
            }
        } else if (userInput === 'p') {
            console.log('False alarm - p pressed when not shown');
            incorrectCount++;
        }

        responses.push({
            letter: currentLetter,
            response: userInput,
            correct: (currentLetter === 'p' && userInput === 'p') || 
                    (currentLetter !== 'p' && userInput !== 'p'),
            reactionTime: reactionTime,
            missed: false
        });

        // Clear input and unlock for next letter
        inputBox.value = '';
        inputLocked = false;
    }
});

function showModal() {
    modalHeader.textContent = `Game Over!`;
    const totalResponses = responses.length;
    const correctResponses = responses.filter(r => r.correct).length;
    const accuracy = (correctResponses / totalResponses) * 100;
    const avgReactionTime = responses.reduce((sum, r) => sum + r.reactionTime, 0) / responses.length / 1000;
    let score = Math.round((accuracy * (2000 - avgReactionTime))/20);
    if (score < 0) score = 0;

    modalBody.innerHTML = `
        Time's up! Here are your results:<br><br>
        Results:<br>
        Total Letters Shown: ${totalCount}<br>
        Total 'P' Letters: ${pCount}<br>
        Total Responses: ${totalResponses}<br>
        Correct Responses: ${correctResponses}<br>
        Accuracy: ${accuracy.toFixed(1)}%<br>
        Average Reaction Time: ${avgReactionTime.toFixed(3)}s<br>
        Missed Responses: ${responses.filter(r => r.missed).length}<br>
        False Alarms: ${responses.filter(r => !r.correct && !r.missed).length}<br>
        Score: ${score}
    `;
    modal.style.display = "block";

    // Save results via AJAX
    jQuery.ajax({
        url: attentrack_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'save_test_results',
            nonce: attentrack_ajax.nonce,
            test_id: testID,
            test_phase: 1,
            score: score,
            accuracy: accuracy,
            reaction_time: avgReactionTime,
            missed_responses: missedCount,
            false_alarms: responses.filter(r => !r.correct && !r.missed).length,
            responses: JSON.stringify(responses),
            total_letters: totalCount,
            p_letters: pCount
        },
        success: function(response) {
            if (response.success) {
                console.log('Test results saved:', response);
                window.location.href = nextTestUrl;
            } else {
                console.error('Failed to save test results:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
}

// Event Listeners
continueButton.addEventListener('click', function() {
    window.location.href = nextTestUrl;
});

startButton.addEventListener('click', startGame);
