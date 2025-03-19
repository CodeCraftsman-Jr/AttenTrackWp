// Selective Attention Test Part 1
document.addEventListener('DOMContentLoaded', function() {
    let timeLeft = 60;
    let timerInterval;
    let gameStarted = false;
    let totalPs = 0;
    let totalLetters = 0;
    let testId = '';
    let responses = [];

    // Initialize UI elements
    const startButton = document.getElementById('startButton');
    const letterElement = document.getElementById('letter');
    const timerElement = document.getElementById('timer');
    const gameOverModal = document.getElementById('gameOverModal');
    const testIdElement = document.getElementById('test-id');

    // Function to get a unique test ID
    async function fetchUniqueTestID() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_unique_test_id');
            formData.append('_ajax_nonce', attentrack_ajax.nonce);

            const response = await fetch(attentrack_ajax.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();
            console.log('Received test ID response:', data);

            if (data.success && data.data) {
                testId = data.data;
                testIdElement.textContent = `Test ID: ${testId}`;
                console.log('Test ID set to:', testId);
                return true;
            } else {
                console.error('Server error:', data);
                throw new Error(data.data?.message || 'Failed to get test ID');
            }
        } catch (error) {
            console.error('Error fetching test ID:', error);
            alert('Error initializing test. Please refresh the page and try again.');
            return false;
        }
    }

    // Function to start the game
    async function startGame() {
        if (gameStarted) return;
        
        const gotTestId = await fetchUniqueTestID();
        if (!gotTestId) {
            console.error('Failed to get test ID, cannot start game');
            return;
        }
        
        gameStarted = true;
        startButton.style.display = 'none';
        showLetter();
        startTimer();
        
        console.log('Game started with test ID:', testId);
    }

    // Function to show letters
    function showLetter() {
        if (!gameStarted) return;

        const isP = Math.random() < 0.5;
        const letter = isP ? 'P' : 'R';
        letterElement.textContent = letter;
        letterElement.classList.add('letter-change');
        
        const startTime = Date.now();
        
        setTimeout(() => {
            letterElement.classList.remove('letter-change');
        }, 500);

        totalLetters++;
        if (isP) totalPs++;
        
        // Record response data
        responses.push({
            letter: letter,
            correct: false,
            missed: true,
            reactionTime: 2000 // Default reaction time if no response
        });

        console.log('Letter shown:', letter, 'Total letters:', totalLetters, 'Total Ps:', totalPs);

        if (gameStarted && timeLeft > 0) {
            const randomInterval = Math.random() * (2000 - 500) + 500;
            setTimeout(showLetter, randomInterval);
        }
    }

    // Function to handle user response
    function handleResponse(event) {
        if (!gameStarted) return;
        
        if (event.code === 'Space') {
            const currentLetter = letterElement.textContent;
            const currentResponse = responses[responses.length - 1];
            
            if (currentResponse) {
                currentResponse.missed = false;
                currentResponse.correct = currentLetter === 'P';
                currentResponse.reactionTime = Date.now() - currentResponse.startTime;
            }
        }
    }

    // Function to start timer
    function startTimer() {
        timerInterval = setInterval(() => {
            timeLeft--;
            timerElement.textContent = `Time Remaining: ${timeLeft}s`;
            
            if (timeLeft <= 0) {
                endGame();
            }
        }, 1000);
    }

    // Function to end game
    function endGame() {
        gameStarted = false;
        clearInterval(timerInterval);
        gameOverModal.style.display = 'block';
        
        console.log('Game ended. Saving results...');
        saveTestResults();
    }

    // Function to save test results
    async function saveTestResults() {
        try {
            if (!testId) {
                throw new Error('Test ID not initialized');
            }

            console.log('Saving test results...');
            console.log('Test ID:', testId);
            console.log('Total Letters:', totalLetters);
            console.log('Total Ps:', totalPs);
            
            const formData = new FormData();
            formData.append('action', 'save_test_results');
            formData.append('test_id', testId);
            formData.append('test_type', 'phase_0');
            formData.append('test_phase', '0');
            formData.append('score', totalPs);
            formData.append('accuracy', ((totalPs / totalLetters) * 100).toFixed(2));
            formData.append('reaction_time', '0');
            formData.append('missed_responses', responses.filter(r => r.missed).length);
            formData.append('false_alarms', responses.filter(r => !r.correct && !r.missed).length);
            formData.append('responses', JSON.stringify(responses));
            formData.append('total_letters', totalLetters);
            formData.append('p_letters', totalPs);
            formData.append('_ajax_nonce', attentrack_ajax.nonce);

            console.log('Form data:', Object.fromEntries(formData));

            const response = await fetch(attentrack_ajax.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Server response:', data);
            
            if (!data.success) {
                throw new Error(data.data?.message || 'Failed to save test results');
            }

            alert('Test results saved successfully!');
            window.location.href = '/dashboard';
        } catch (error) {
            console.error('Error saving test results:', error);
            alert('Error saving test results: ' + error.message);
        }
    }

    // Event listeners
    startButton.addEventListener('click', startGame);
    document.addEventListener('keydown', handleResponse);
});
