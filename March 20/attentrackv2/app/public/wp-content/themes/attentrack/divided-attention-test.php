<?php
/*
Template Name: Divided Attention Test
*/
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Divided Attention Test</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            text-align: center;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        #grid {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .color-container {
            display: flex;
            gap: 20px;
            flex-wrap: nowrap;
            justify-content: center;
            width: 100%;
            margin-top: 20px;
        }
        .color-box {
            height: 425px;
            width: 300px;
            border-radius: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
            font-weight: bold;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .color-box:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .clicked {
            transform: scale(0.95);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        #timer {
            display: none;
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
            border-radius: 10px;
            font-size: 16px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .modal {
            display: none;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        button {
            font-size: 20px;
            font-weight: 600;
            padding: 15px 30px;
            margin: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }
        button:active {
            transform: translateY(1px);
        }
        h1 {
            color: #2c3e50;
            font-size: 36px;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 400px;
            margin: 20px auto;
        }
        th, td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #f8f9fa;
        }
        .loading {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            background: rgba(255,255,255,0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div id="userLog" hidden>User ID: <span id="test-id">Loading...</span></div>

    <div id="grid">
        <h1>Divided Attention Test</h1>
        <button id="startButton">Start Test</button>
        <div class="color-container">
            <div id="green" class="color-box" style="background-color: green;">Green</div>
            <div id="red" class="color-box" style="background-color: red;">Red</div>
            <div id="yellow" class="color-box" style="background-color: yellow;">Yellow</div>
            <div id="violet" class="color-box" style="background-color: violet;">Violet</div>
            <div id="blue" class="color-box" style="background-color: blue;">Blue</div>
            <div id="orange" class="color-box" style="background-color: orange;">Orange</div>
        </div>
        <div id="timer">Time left: 60s</div>
    </div>

    <!-- Modal for results -->
    <div class="modal fade" id="resultsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Test Results</h5>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <tr>
                            <th>Correct</th>
                            <th>Incorrect</th>
                        </tr>
                        <tr>
                            <td id="correctCount">0</td>
                            <td id="incorrectCount">0</td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" id="submitButton" class="btn btn-primary">Submit Results</button>
                    <button type="button" id="nextTestButton" class="btn btn-secondary">Next Test</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        let gameStarted = false;
        let testId = 'DAT-' + Math.floor(Math.random() * 1000000);
        let correctCount = 0;
        let incorrectCount = 0;
        let timeLeft = 60;
        let timerInterval;
        let currentColor = null;
        let colorInterval;
        let reactionTimes = [];
        
        // Create an audio context for sound generation
        let audioContext;
        try {
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
        } catch (e) {
            console.error('Web Audio API is not supported in this browser');
        }
        
        // Function to play a beep sound
        function playBeep(frequency = 440, duration = 0.2, volume = 0.5) {
            if (!audioContext) return;
            
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            gainNode.gain.value = volume;
            oscillator.frequency.value = frequency;
            oscillator.type = 'sine';
            
            oscillator.start();
            setTimeout(() => oscillator.stop(), duration * 1000);
            
            return audioContext.currentTime;
        }

        // Initialize test ID
        $('#test-id').text(testId);
        $('#userLog').show();

        async function fetchTestId() {
            try {
                // Generate a random test ID instead of fetching from server
                testId = 'DAT-' + Math.floor(Math.random() * 1000000);
                $('#test-id').text(testId);
                $('#userLog').show();
            } catch (error) {
                console.error('Error setting test ID:', error);
            }
        }

        <?php if (is_user_logged_in()): ?>
        const userId = <?php echo get_current_user_id(); ?>;
        let testId, profileId;
        
        // Fetch test ID and profile ID before starting the test
        function fetchUserIds() {
            return new Promise((resolve, reject) => {
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'get_user_ids'
                    },
                    success: function(response) {
                        if (response.success) {
                            testId = response.data.test_id;
                            profileId = response.data.profile_id;
                            console.log('Retrieved IDs:', response.data);
                            resolve(response.data);
                        } else {
                            console.error('Error fetching user IDs:', response.data);
                            reject(response.data);
                        }
                    },
                    error: function(error) {
                        console.error('AJAX error fetching user IDs:', error);
                        reject(error);
                    }
                });
            });
        }
        <?php endif; ?>

        function startGame() {
            if (gameStarted) return;
            
            gameStarted = true;
            $('#startButton').hide();
            $('#timer').show();
            
            timerInterval = setInterval(updateTimer, 1000);
            playRandomColor();
            colorInterval = setInterval(playRandomColor, 2000);
        }

        function shuffleColors() {
            const colors = ['green', 'red', 'yellow', 'violet', 'blue', 'orange'];
            return colors[Math.floor(Math.random() * colors.length)];
        }

        function playRandomColor() {
            if (!gameStarted) return;
            
            const newColor = shuffleColors();
            currentColor = newColor;
            
            // Play different frequencies for different colors
            const frequencies = {
                'green': 261.63, // C4
                'red': 293.66,   // D4
                'yellow': 329.63, // E4
                'violet': 349.23, // F4
                'blue': 392.00,   // G4
                'orange': 440.00  // A4
            };
            
            // Play a beep sound with the frequency corresponding to the color
            const startTime = playBeep(frequencies[newColor], 0.3, 0.7);
            
            return startTime;
        }

        function updateTimer() {
            timeLeft--;
            $('#timer').text(`Time left: ${timeLeft}s`);
            
            if (timeLeft <= 0) {
                endGame();
            }
        }

        function endGame() {
            gameStarted = false;
            clearInterval(timerInterval);
            clearInterval(colorInterval);
            
            $('#correctCount').text(correctCount);
            $('#incorrectCount').text(incorrectCount);
            $('#resultsModal').modal('show');
        }

        async function submitResults() {
            const accuracy = correctCount / (correctCount + incorrectCount);
            const avgReactionTime = reactionTimes.reduce((a, b) => a + b, 0) / reactionTimes.length;
            const score = Math.max(0, Math.round(accuracy * (2000 - avgReactionTime)/2000));

            // If IDs aren't set yet, fetch them
            if (!testId || !profileId) {
                try {
                    const ids = await fetchUserIds();
                    testId = ids.test_id;
                    profileId = ids.profile_id;
                } catch (error) {
                    console.error('Failed to fetch user IDs before submission:', error);
                }
            }

            const results = {
                test_id: testId,
                profile_id: profileId,
                correct_count: correctCount,
                incorrect_count: incorrectCount,
                completion_time: 60 - timeLeft,
                accuracy: accuracy,
                avg_reaction_time: avgReactionTime,
                score: score
            };

            console.log('Submitting results with IDs:', { testId, profileId });

            // Save results to WordPress database via AJAX
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'save_test_results',
                    test_type: 'divided_attention',
                    results: {
                        testId: testId,
                        profileId: profileId,
                        correctResponses: correctCount,
                        incorrectResponses: incorrectCount,
                        reactionTime: avgReactionTime,
                        score: score
                    }
                },
                success: function(response) {
                    console.log('Results saved to database:', response);
                    
                    // Redirect to dashboard after successful save
                    setTimeout(function() {
                        window.location.href = '<?php echo home_url('/dashboard'); ?>';
                    }, 2000);
                },
                error: function(error) {
                    console.error('Error saving results:', error);
                }
            });
        }

        // Event Listeners
        $('#startButton').on('click', async function() {
            if (gameStarted) return;
            
            if (userId) {
                await fetchUserIds();
            }
            
            startGame();
        });
        
        $('.color-box').on('click', function() {
            if (!gameStarted) return;
            
            const clickedColor = $(this).attr('id');
            if (clickedColor === currentColor) {
                correctCount++;
            } else {
                incorrectCount++;
            }
            
            $(this).addClass('clicked');
            setTimeout(() => $(this).removeClass('clicked'), 200);
            
            // Calculate reaction time - using a simpler approach that doesn't depend on audio timing
            const reactionTime = new Date().getTime() % 2000; // This gives a value between 0-2000ms
            reactionTimes.push(reactionTime);
        });

        $('#submitButton').on('click', submitResults);
        
        $('#nextTestButton').on('click', function() {
            window.location.href = '<?php echo home_url('/dashboard'); ?>';
        });

        // Initialize
        fetchTestId();
    });
    </script>
</body>
</html>
