<?php
/**
 * Template Name: Divided Attention Test
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// get_header();
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        text-align: center;
        margin: 0;
        padding: 20px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
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

<!-- Audio elements -->
<?php
$colors = ['green', 'red', 'yellow', 'violet', 'blue', 'orange'];
foreach ($colors as $color) {
    echo "<audio id='{$color}Sound' preload='auto'>";
    echo "<source src='" . get_template_directory_uri() . "/assets/sounds/button-16.mp3' type='audio/mpeg'>";
    echo "</audio>";
}
?>

<script>
jQuery(document).ready(function($) {
    let gameStarted = false;
    let testId = null;
    let correctCount = 0;
    let incorrectCount = 0;
    let timeLeft = 60;
    let timer;
    let currentColor = null;
    let colorInterval;
    let reactionTimes = [];

    // Fetch test ID
    async function fetchTestId() {
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
            $('#test-id').text(testId);
            $('#userLog').show();
        } catch (error) {
            console.error('Error fetching test ID:', error);
        }
    }

    function startGame() {
        if (gameStarted) return;
        
        gameStarted = true;
        $('#startButton').hide();
        $('#timer').show();
        
        timer = setInterval(updateTimer, 1000);
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
        
        const audio = document.getElementById(newColor + 'Sound');
        if (audio) {
            audio.currentTime = 0;
            audio.play();
        }
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
        clearInterval(timer);
        clearInterval(colorInterval);
        
        $('#correctCount').text(correctCount);
        $('#incorrectCount').text(incorrectCount);
        $('#resultsModal').modal('show');
    }

    async function submitResults() {
        const accuracy = correctCount / (correctCount + incorrectCount);
        const avgReactionTime = reactionTimes.reduce((a, b) => a + b, 0) / reactionTimes.length;
        const score = Math.max(0, Math.round(accuracy * (2000 - avgReactionTime)/2000));

        const results = {
            test_id: testId,
            correct_count: correctCount,
            incorrect_count: incorrectCount,
            completion_time: 60 - timeLeft,
            accuracy: accuracy,
            avg_reaction_time: avgReactionTime,
            score: score
        };

        try {
            const response = await fetch('<?php echo esc_url(rest_url('attentrack/v1/save-test-results')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify(results)
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            window.location.href = '<?php echo esc_url(home_url('/home-2')); ?>';
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Event Listeners
    $('#startButton').on('click', startGame);
    
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
        
        const reactionTime = new Date().getTime() - audio.currentTime * 1000;
        reactionTimes.push(reactionTime);
    });

    $('#submitButton').on('click', submitResults);
    
    $('#nextTestButton').on('click', function() {
        window.location.href = '<?php echo esc_url(home_url('/home-2')); ?>';
    });

    // Initialize
    fetchTestId();
});
</script>

<!-- <?php get_footer(); ?> -->
