<?php
/**
 * Template Name: Test Page
 */

get_header();
?>

<div class="test-container">
    <div class="test-header">
        <h1 class="test-title"><?php the_title(); ?></h1>
        <div class="test-controls">
            <button id="startTest" class="btn btn-primary">Start Test</button>
            <button id="pauseTest" class="btn btn-warning" style="display: none;">Pause</button>
            <div id="timer" class="test-timer">00:00</div>
        </div>
    </div>

    <div id="testArea" class="test-area">
        <div id="instructions" class="test-instructions">
            <h2>Test Instructions</h2>
            <p>Follow these instructions carefully:</p>
            <ol>
                <li>You will be presented with various stimuli on the screen.</li>
                <li>Respond as quickly and accurately as possible.</li>
                <li>Use the designated keys or mouse to respond.</li>
                <li>Try to maintain focus throughout the test.</li>
            </ol>
            <div class="text-center mt-4">
                <button id="beginTest" class="btn btn-lg btn-success">Begin Test</button>
            </div>
        </div>

        <div id="stimulusArea" class="stimulus-area" style="display: none;">
            <!-- Stimulus will be displayed here -->
        </div>

        <div id="results" class="test-results" style="display: none;">
            <!-- Results will be displayed here -->
        </div>
    </div>
</div>

<style>
.test-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 1rem;
}

.test-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1rem;
    background: var(--light-bg);
    border-radius: 8px;
}

.test-title {
    margin: 0;
    color: var(--secondary-color);
}

.test-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.test-timer {
    font-size: 1.5rem;
    font-weight: bold;
    padding: 0.5rem 1rem;
    background: var(--secondary-color);
    color: white;
    border-radius: 4px;
}

.test-area {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    min-height: 400px;
}

.test-instructions {
    max-width: 800px;
    margin: 0 auto;
}

.stimulus-area {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 300px;
    position: relative;
}

.test-results {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.stimulus {
    transition: all 0.3s ease;
}

.feedback {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 1.5rem;
    font-weight: bold;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.feedback.show {
    opacity: 1;
}

@media (max-width: 768px) {
    .test-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .test-controls {
        flex-direction: column;
    }

    .test-area {
        padding: 1rem;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    let testInProgress = false;
    let testPaused = false;
    let timer;
    let startTime;
    let elapsedTime = 0;

    // Test configuration
    const testConfig = {
        duration: 300, // 5 minutes in seconds
        stimuliDuration: 2000, // 2 seconds
        interStimulusInterval: 1000, // 1 second
        stimuli: [
            // Will be loaded from WordPress
        ]
    };

    // Start button click handler
    $('#startTest').click(function() {
        $(this).hide();
        $('#instructions').show();
        $('#stimulusArea, #results').hide();
    });

    // Begin test button click handler
    $('#beginTest').click(function() {
        startTest();
    });

    // Pause button click handler
    $('#pauseTest').click(function() {
        if (testPaused) {
            resumeTest();
        } else {
            pauseTest();
        }
    });

    function startTest() {
        testInProgress = true;
        $('#instructions').hide();
        $('#stimulusArea').show();
        $('#pauseTest').show();
        
        startTime = Date.now() - elapsedTime;
        timer = setInterval(updateTimer, 1000);
        
        // Start presenting stimuli
        presentStimuli();
    }

    function pauseTest() {
        testPaused = true;
        clearInterval(timer);
        $('#pauseTest').text('Resume').removeClass('btn-warning').addClass('btn-success');
        $('#stimulusArea').css('opacity', '0.5');
    }

    function resumeTest() {
        testPaused = false;
        startTime = Date.now() - elapsedTime;
        timer = setInterval(updateTimer, 1000);
        $('#pauseTest').text('Pause').removeClass('btn-success').addClass('btn-warning');
        $('#stimulusArea').css('opacity', '1');
    }

    function updateTimer() {
        if (!testPaused) {
            elapsedTime = Date.now() - startTime;
            const seconds = Math.floor(elapsedTime / 1000);
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            
            $('#timer').text(
                (minutes < 10 ? '0' : '') + minutes + ':' +
                (remainingSeconds < 10 ? '0' : '') + remainingSeconds
            );

            if (seconds >= testConfig.duration) {
                endTest();
            }
        }
    }

    function presentStimuli() {
        if (!testInProgress || testPaused) return;

        // Example stimulus presentation
        const stimulus = createStimulus();
        $('#stimulusArea').html(stimulus);

        setTimeout(() => {
            if (testInProgress && !testPaused) {
                $('#stimulusArea').empty();
                
                setTimeout(() => {
                    if (testInProgress && !testPaused) {
                        presentStimuli();
                    }
                }, testConfig.interStimulusInterval);
            }
        }, testConfig.stimuliDuration);
    }

    function createStimulus() {
        // Example stimulus - this should be replaced with actual test stimuli
        const shapes = ['circle', 'square', 'triangle'];
        const colors = ['#ff0000', '#00ff00', '#0000ff'];
        
        const shape = shapes[Math.floor(Math.random() * shapes.length)];
        const color = colors[Math.floor(Math.random() * colors.length)];
        
        return `<div class="stimulus" style="
            width: 100px;
            height: 100px;
            background-color: ${color};
            border-radius: ${shape === 'circle' ? '50%' : '0'};
            transform: ${shape === 'triangle' ? 'rotate(45deg)' : 'none'};
        "></div>`;
    }

    function endTest() {
        testInProgress = false;
        clearInterval(timer);
        $('#stimulusArea').hide();
        $('#pauseTest').hide();
        showResults();
    }

    function showResults() {
        const results = {
            duration: elapsedTime / 1000,
            accuracy: Math.random() * 100, // Replace with actual accuracy
            responseTime: Math.random() * 1000 // Replace with actual response time
        };

        $('#results').html(`
            <h2>Test Results</h2>
            <div class="results-summary">
                <p>Duration: ${Math.floor(results.duration / 60)}m ${Math.floor(results.duration % 60)}s</p>
                <p>Accuracy: ${results.accuracy.toFixed(2)}%</p>
                <p>Average Response Time: ${results.responseTime.toFixed(2)}ms</p>
            </div>
            <div class="text-center mt-4">
                <button id="retakeTest" class="btn btn-primary">Retake Test</button>
            </div>
        `).show();

        // Save results to WordPress
        saveResults(results);
    }

    function saveResults(results) {
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'save_test_results',
                nonce: '<?php echo wp_create_nonce('save_test_results'); ?>',
                results: results
            },
            success: function(response) {
                if (response.success) {
                    console.log('Results saved successfully');
                } else {
                    console.error('Error saving results');
                }
            }
        });
    }

    // Retake test button click handler
    $(document).on('click', '#retakeTest', function() {
        elapsedTime = 0;
        $('#timer').text('00:00');
        $('#startTest').show();
        $('#results').hide();
    });
});
</script>

<?php
get_footer();
?>
