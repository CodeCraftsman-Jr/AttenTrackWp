<?php
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

get_header();

$test_type = get_post_meta(get_the_ID(), 'test_type', true);
$test_duration = get_post_meta(get_the_ID(), 'test_duration', true);
$test_instructions = get_post_meta(get_the_ID(), 'test_instructions', true);
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <!-- Test Information -->
            <div class="card mb-4" id="test-info">
                <div class="card-body">
                    <h2 class="card-title"><?php the_title(); ?></h2>
                    <div class="test-meta mb-3">
                        <span class="badge bg-primary me-2">Duration: <?php echo esc_html($test_duration); ?> minutes</span>
                        <span class="badge bg-secondary">Type: <?php echo esc_html($test_type); ?></span>
                    </div>
                    <div class="test-instructions">
                        <h4>Instructions:</h4>
                        <?php echo wp_kses_post($test_instructions); ?>
                    </div>
                    <button id="start-test" class="btn btn-primary mt-3">Start Test</button>
                </div>
            </div>

            <!-- Test Interface -->
            <div id="test-interface" class="card" style="display: none;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 id="test-phase"></h3>
                        <div id="timer" class="h4"></div>
                    </div>
                    
                    <div id="test-content" class="mb-4">
                        <!-- Test content will be dynamically inserted here -->
                    </div>

                    <div id="test-controls" class="d-flex justify-content-between">
                        <button id="prev-btn" class="btn btn-secondary" style="display: none;">Previous</button>
                        <button id="next-btn" class="btn btn-primary" style="display: none;">Next</button>
                        <button id="submit-btn" class="btn btn-success" style="display: none;">Submit Test</button>
                    </div>
                </div>
            </div>

            <!-- Results Display -->
            <div id="test-results" class="card" style="display: none;">
                <div class="card-body">
                    <h3>Test Results</h3>
                    <div id="results-content">
                        <!-- Results will be dynamically inserted here -->
                    </div>
                    <a href="<?php echo esc_url(home_url('/dashboard')); ?>" class="btn btn-primary mt-3">Return to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
class AttentionTest {
    constructor() {
        this.currentPhase = 0;
        this.phases = [];
        this.results = [];
        this.timer = null;
        this.timeRemaining = 0;
        
        // DOM Elements
        this.testInfo = document.getElementById('test-info');
        this.testInterface = document.getElementById('test-interface');
        this.testResults = document.getElementById('test-results');
        this.testContent = document.getElementById('test-content');
        this.timerDisplay = document.getElementById('timer');
        this.phaseDisplay = document.getElementById('test-phase');
        
        // Buttons
        this.startButton = document.getElementById('start-test');
        this.prevButton = document.getElementById('prev-btn');
        this.nextButton = document.getElementById('next-btn');
        this.submitButton = document.getElementById('submit-btn');
        
        // Event Listeners
        this.startButton.addEventListener('click', () => this.startTest());
        this.prevButton.addEventListener('click', () => this.previousPhase());
        this.nextButton.addEventListener('click', () => this.nextPhase());
        this.submitButton.addEventListener('click', () => this.submitTest());
    }

    async startTest() {
        this.testInfo.style.display = 'none';
        this.testInterface.style.display = 'block';
        
        // Initialize test phases based on test type
        await this.initializeTest();
        
        // Start first phase
        this.showPhase(0);
    }

    async initializeTest() {
        // Fetch test configuration from WordPress
        const response = await fetch(`/wp-json/attentrack/v1/test/${<?php echo get_the_ID(); ?>}`);
        const testConfig = await response.json();
        
        this.phases = testConfig.phases;
        this.timeRemaining = testConfig.duration * 60; // Convert to seconds
        
        this.startTimer();
    }

    startTimer() {
        this.timer = setInterval(() => {
            this.timeRemaining--;
            this.updateTimerDisplay();
            
            if (this.timeRemaining <= 0) {
                this.submitTest();
            }
        }, 1000);
    }

    updateTimerDisplay() {
        const minutes = Math.floor(this.timeRemaining / 60);
        const seconds = this.timeRemaining % 60;
        this.timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }

    showPhase(phaseIndex) {
        const phase = this.phases[phaseIndex];
        this.phaseDisplay.textContent = `Phase ${phaseIndex + 1}: ${phase.title}`;
        this.testContent.innerHTML = phase.content;
        
        // Update navigation buttons
        this.prevButton.style.display = phaseIndex > 0 ? 'block' : 'none';
        this.nextButton.style.display = phaseIndex < this.phases.length - 1 ? 'block' : 'none';
        this.submitButton.style.display = phaseIndex === this.phases.length - 1 ? 'block' : 'none';
    }

    previousPhase() {
        if (this.currentPhase > 0) {
            this.showPhase(--this.currentPhase);
        }
    }

    nextPhase() {
        if (this.currentPhase < this.phases.length - 1) {
            this.results[this.currentPhase] = this.collectPhaseResults();
            this.showPhase(++this.currentPhase);
        }
    }

    collectPhaseResults() {
        // Implement result collection logic based on test type
        return {
            phaseIndex: this.currentPhase,
            // Add other result data
        };
    }

    async submitTest() {
        clearInterval(this.timer);
        
        // Collect final phase results
        this.results[this.currentPhase] = this.collectPhaseResults();
        
        // Submit results to WordPress
        try {
            const response = await fetch('/wp-json/attentrack/v1/save-result', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify({
                    testId: <?php echo get_the_ID(); ?>,
                    results: this.results,
                    duration: <?php echo esc_js($test_duration * 60); ?> - this.timeRemaining
                })
            });
            
            const data = await response.json();
            this.showResults(data);
        } catch (error) {
            console.error('Error submitting test:', error);
            alert('There was an error submitting your test. Please try again.');
        }
    }

    showResults(results) {
        this.testInterface.style.display = 'none';
        this.testResults.style.display = 'block';
        
        // Display results summary
        document.getElementById('results-content').innerHTML = `
            <div class="alert alert-success">
                <h4>Test Completed Successfully!</h4>
                <p>Your results have been saved. You can view detailed analysis in your dashboard.</p>
            </div>
            <div class="result-summary">
                <h5>Summary:</h5>
                <ul>
                    <li>Score: ${results.score}</li>
                    <li>Time Taken: ${Math.floor(results.duration / 60)} minutes ${results.duration % 60} seconds</li>
                </ul>
            </div>
        `;
    }
}

// Initialize test when document is ready
document.addEventListener('DOMContentLoaded', () => {
    new AttentionTest();
});
</script>

<?php get_footer(); ?>
