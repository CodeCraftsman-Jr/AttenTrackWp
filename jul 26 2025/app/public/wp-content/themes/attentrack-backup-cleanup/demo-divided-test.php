<?php
/*
Template Name: Demo Divided Test
*/

// Check if user has free plan
$current_user_id = get_current_user_id();
$plan_type = get_user_meta($current_user_id, 'subscription_plan_type', true);

if ($plan_type !== 'free') {
    wp_redirect(home_url('/divided-attention-test'));
    exit;
}

get_header();
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Demo Divided Attention Test</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Demo Version:</strong> This is a simplified version of the real test. For the complete assessment, please <a href="<?php echo home_url('/subscription-plans'); ?>" class="alert-link">upgrade your subscription</a>.
                    </div>

                    <!-- Test Instructions -->
                    <div class="mb-4">
                        <h4>Instructions:</h4>
                        <ol>
                            <li>You will see 6 colored boxes on the screen.</li>
                            <li>The colors will shuffle every second.</li>
                            <li>An audio voice will say a color name every 2 seconds.</li>
                            <li>Click the box that matches the color name you hear.</li>
                            <li>This demo version will only run for 1 minute.</li>
                            <li>Results will not be saved.</li>
                        </ol>
                    </div>

                    <!-- Test Area -->
                    <div id="testArea" class="text-center mb-4">
                        <div id="colorGrid" class="d-flex flex-wrap justify-content-center" style="gap: 10px;">
                            <!-- Color boxes will be added here by JavaScript -->
                        </div>
                    </div>

                    <!-- Controls -->
                    <div class="text-center mb-4">
                        <button id="startButton" class="btn btn-primary btn-lg">Start Demo Test</button>
                    </div>

                    <!-- Results -->
                    <div id="results" class="mt-4 d-none">
                        <h4>Demo Results:</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Correct Matches:</th>
                                    <td id="correctMatches">0</td>
                                </tr>
                                <tr>
                                    <th>Wrong Matches:</th>
                                    <td id="wrongMatches">0</td>
                                </tr>
                                <tr>
                                    <th>Missed Responses:</th>
                                    <td id="missedResponses">0</td>
                                </tr>
                                <tr>
                                    <th>Average Reaction Time:</th>
                                    <td id="avgReactionTime">0 ms</td>
                                </tr>
                            </table>
                        </div>
                        <div class="alert alert-warning mt-3">
                            <strong>Note:</strong> The real test offers more detailed analytics, progress tracking, and professional assessment. <a href="<?php echo home_url('/subscription-plans'); ?>" class="alert-link">Upgrade now</a> to access the full version.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.responsivevoice.org/responsivevoice.js?key=YOUR_RESPONSIVE_VOICE_KEY"></script>
<script>
jQuery(document).ready(function($) {
    const colors = ['red', 'blue', 'green', 'yellow', 'purple', 'orange'];
    const colorNames = {
        'red': '#ff0000',
        'blue': '#0000ff',
        'green': '#008000',
        'yellow': '#ffff00',
        'purple': '#800080',
        'orange': '#ffa500'
    };
    
    let testRunning = false;
    let correctMatches = 0;
    let wrongMatches = 0;
    let missedResponses = 0;
    let reactionTimes = [];
    let lastAudioTime = null;
    let currentAudioColor = null;
    let responseTimeout = null;
    let testDuration = 60000; // 1 minute for demo

    // Create color boxes
    colors.forEach(color => {
        const box = $('<div>')
            .addClass('color-box')
            .css({
                'background-color': colorNames[color],
                'width': '100px',
                'height': '100px',
                'cursor': 'pointer',
                'border-radius': '10px',
                'margin': '5px'
            })
            .data('color', color);
        
        box.click(function() {
            if (!testRunning || !currentAudioColor) return;
            
            const clickedColor = $(this).data('color');
            const currentTime = Date.now();
            
            if (clickedColor === currentAudioColor) {
                correctMatches++;
                reactionTimes.push(currentTime - lastAudioTime);
            } else {
                wrongMatches++;
            }
            
            clearTimeout(responseTimeout);
            currentAudioColor = null;
        });
        
        $('#colorGrid').append(box);
    });

    function shuffleColors() {
        if (!testRunning) return;
        
        const boxes = $('.color-box').toArray();
        for (let i = boxes.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            boxes[i].parentNode.insertBefore(boxes[j], boxes[i]);
        }
        
        setTimeout(shuffleColors, 1000);
    }

    function playRandomColor() {
        if (!testRunning) return;
        
        const randomColor = colors[Math.floor(Math.random() * colors.length)];
        currentAudioColor = randomColor;
        lastAudioTime = Date.now();
        
        responsiveVoice.speak(randomColor, "UK English Female", {
            onend: () => {
                responseTimeout = setTimeout(() => {
                    if (currentAudioColor) {
                        missedResponses++;
                        currentAudioColor = null;
                    }
                }, 2000);
            }
        });
        
        setTimeout(playRandomColor, 3000);
    }

    $('#startButton').click(function() {
        if (testRunning) return;
        
        // Reset variables
        testRunning = true;
        correctMatches = 0;
        wrongMatches = 0;
        missedResponses = 0;
        reactionTimes = [];
        currentAudioColor = null;
        
        // Update UI
        $('#results').addClass('d-none');
        $(this).prop('disabled', true);
        
        // Start test components
        shuffleColors();
        playRandomColor();
        
        // End test after duration
        setTimeout(endTest, testDuration);
    });

    function endTest() {
        testRunning = false;
        currentAudioColor = null;
        
        // Stop all timeouts
        clearTimeout(responseTimeout);
        
        // Calculate average reaction time
        const avgReactionTime = reactionTimes.length > 0 
            ? Math.round(reactionTimes.reduce((a, b) => a + b) / reactionTimes.length) 
            : 0;
        
        // Update results
        $('#correctMatches').text(correctMatches);
        $('#wrongMatches').text(wrongMatches);
        $('#missedResponses').text(missedResponses);
        $('#avgReactionTime').text(avgReactionTime + ' ms');
        
        // Update UI
        $('#results').removeClass('d-none');
        $('#startButton').prop('disabled', false).text('Try Again');
    }
});
</script>

<?php get_footer(); ?>
