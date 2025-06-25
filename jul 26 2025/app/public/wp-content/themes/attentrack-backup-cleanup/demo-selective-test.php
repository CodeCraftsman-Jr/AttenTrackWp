<?php
/*
Template Name: Demo Selective Test
*/

// Check if user has free plan
$current_user_id = get_current_user_id();
$plan_type = get_user_meta($current_user_id, 'subscription_plan_type', true);

if ($plan_type !== 'free') {
    wp_redirect(home_url('/selective-attention-test'));
    exit;
}

get_header();
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Demo Selective Attention Test</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Demo Version:</strong> This is a simplified version of the real test. For the complete assessment, please <a href="<?php echo home_url('/subscription-plans'); ?>" class="alert-link">upgrade your subscription</a>.
                    </div>

                    <!-- Test Instructions -->
                    <div class="mb-4">
                        <h4>Instructions:</h4>
                        <ol>
                            <li>A series of letters will appear on the screen.</li>
                            <li>Click when you see the target letter 'X'.</li>
                            <li>This demo version will only run for 1 minute.</li>
                            <li>Results will not be saved.</li>
                        </ol>
                    </div>

                    <!-- Test Area -->
                    <div id="testArea" class="text-center p-5 border rounded mb-4" style="background-color: #f8f9fa;">
                        <h1 id="stimulusLetter" style="font-size: 72px; min-height: 100px;"></h1>
                    </div>

                    <!-- Controls -->
                    <div class="text-center">
                        <button id="startButton" class="btn btn-primary btn-lg">Start Demo Test</button>
                        <button id="clickButton" class="btn btn-success btn-lg d-none">Click When You See 'X'</button>
                    </div>

                    <!-- Results -->
                    <div id="results" class="mt-4 d-none">
                        <h4>Demo Results:</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Correct Clicks:</th>
                                    <td id="correctClicks">0</td>
                                </tr>
                                <tr>
                                    <th>Incorrect Clicks:</th>
                                    <td id="incorrectClicks">0</td>
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

<script>
jQuery(document).ready(function($) {
    let testRunning = false;
    let correctClicks = 0;
    let incorrectClicks = 0;
    let reactionTimes = [];
    let lastStimulusTime = null;
    let testDuration = 60000; // 1 minute for demo
    let letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    
    $('#startButton').click(function() {
        startTest();
    });

    $('#clickButton').click(function() {
        if (!testRunning) return;
        
        const currentLetter = $('#stimulusLetter').text();
        const currentTime = Date.now();
        
        if (currentLetter === 'X') {
            correctClicks++;
            if (lastStimulusTime) {
                reactionTimes.push(currentTime - lastStimulusTime);
            }
        } else {
            incorrectClicks++;
        }
    });

    function startTest() {
        // Reset variables
        testRunning = true;
        correctClicks = 0;
        incorrectClicks = 0;
        reactionTimes = [];
        
        // Update UI
        $('#startButton').addClass('d-none');
        $('#clickButton').removeClass('d-none');
        $('#results').addClass('d-none');
        
        // Start showing letters
        showLetters();
        
        // End test after duration
        setTimeout(endTest, testDuration);
    }

    function showLetters() {
        if (!testRunning) return;
        
        const randomIndex = Math.floor(Math.random() * letters.length);
        const letter = letters[randomIndex];
        
        $('#stimulusLetter').text(letter);
        lastStimulusTime = Date.now();
        
        // Show next letter after random interval (1-3 seconds)
        const interval = Math.random() * 2000 + 1000;
        setTimeout(showLetters, interval);
    }

    function endTest() {
        testRunning = false;
        $('#stimulusLetter').text('');
        $('#clickButton').addClass('d-none');
        $('#startButton').removeClass('d-none').text('Try Again');
        
        // Calculate average reaction time
        const avgReactionTime = reactionTimes.length > 0 
            ? Math.round(reactionTimes.reduce((a, b) => a + b) / reactionTimes.length) 
            : 0;
        
        // Update results
        $('#correctClicks').text(correctClicks);
        $('#incorrectClicks').text(incorrectClicks);
        $('#avgReactionTime').text(avgReactionTime + ' ms');
        
        // Show results
        $('#results').removeClass('d-none');
    }
});
</script>

<?php get_footer(); ?>
