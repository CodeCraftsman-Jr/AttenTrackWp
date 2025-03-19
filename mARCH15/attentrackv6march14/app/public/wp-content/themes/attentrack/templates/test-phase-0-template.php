<?php
/*
Template Name: Test Phase 0
*/

get_header();
?>

<div class="test-phase-0-container">
    <h1>Selective Attention Test - Phase 0</h1>
    <div id="timer">Time Remaining: 60s</div>
    <div id="test-id"></div>
    <div id="letter"></div>
    <button id="startButton" class="btn btn-primary">Start Test</button>
</div>

<style>
    .test-phase-0-container {
        text-align: center;
        padding: 30px;
        margin: 0;
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
        min-height: 80vh;
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
        min-width: 200px;
        min-height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        margin: 50px 0;
    }

    #startButton {
        font-size: 24px;
        padding: 15px 30px;
        margin-top: 20px;
        cursor: pointer;
    }

    .letter-change {
        animation: fadeInOut 0.5s ease-in-out;
    }

    @keyframes fadeInOut {
        0% { opacity: 0; }
        50% { opacity: 1; }
        100% { opacity: 0; }
    }
</style>

<script>
// Wait for jQuery and DOM to be ready
jQuery(document).ready(function($) {
    console.log('Test Phase 0 script initialized');
    console.log('AJAX URL:', attentrack_ajax.ajax_url);
    console.log('Nonce:', attentrack_ajax.nonce);

    let testId = '';
    let totalLetters = 0;
    let totalPs = 0;
    let timeLeft = 60;
    let timerInterval;
    let gameStarted = false;

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
            console.log('Received test ID:', data);

            if (data.success && data.data) {
                testId = data.data;
                console.log('Test ID set to:', testId);
                document.getElementById('test-id').textContent = `Test ID: ${testId}`;
            } else {
                throw new Error('Failed to get test ID');
            }
        } catch (error) {
            console.error('Error fetching test ID:', error);
            alert('Error initializing test. Please refresh the page and try again.');
        }
    }

    function startGame() {
        if (gameStarted) return;
        gameStarted = true;
        document.getElementById('startButton').style.display = 'none';
        fetchUniqueTestID();
        showLetter();
        startTimer();
    }

    function showLetter() {
        if (!gameStarted) return;
        
        const letterElement = document.getElementById('letter');
        const isP = Math.random() < 0.5;
        letterElement.textContent = isP ? 'P' : 'R';
        letterElement.classList.add('letter-change');
        
        setTimeout(() => {
            letterElement.classList.remove('letter-change');
        }, 500);

        totalLetters++;
        if (isP) totalPs++;
        
        console.log('Letters shown:', totalLetters, 'Ps shown:', totalPs);

        if (gameStarted && timeLeft > 0) {
            const randomInterval = Math.random() * (2000 - 500) + 500;
            setTimeout(showLetter, randomInterval);
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
        gameStarted = false;
        clearInterval(timerInterval);
        saveTestResults();
    }

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
            formData.append('missed_responses', '0');
            formData.append('false_alarms', '0');
            formData.append('responses', JSON.stringify([]));
            formData.append('total_letters', totalLetters);
            formData.append('p_letters', totalPs);
            formData.append('_ajax_nonce', attentrack_ajax.nonce);

            console.log('Form data:');
            for (const [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            const response = await fetch(attentrack_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': attentrack_ajax.nonce
                },
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
            window.location.href = '<?php echo esc_url(home_url('/dashboard')); ?>';
        } catch (error) {
            console.error('Error saving test results:', error);
            alert('Error saving test results: ' + error.message);
        }
    }

    // Set up event listeners
    $('#startButton').on('click', startGame);
});
</script>

<?php get_footer(); ?>
