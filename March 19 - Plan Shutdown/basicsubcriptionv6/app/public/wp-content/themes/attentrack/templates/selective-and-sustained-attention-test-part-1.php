<?php
/**
 * Template Name: Selective and Sustained Attention Test Part 1
 */

get_header(); ?>

<div class="container">
    <div id="test-container" class="test-section">
        <h2>Selective Attention Test</h2>
        <div class="status-message" id="status-message" style="display: none;"></div>
        <div id="test-controls">
            <button id="test-save-button" class="test-button">
                <span class="loading-spinner" style="display: none;"></span>
                Test Save Results
            </button>
        </div>
    </div>

    <!-- Results display container -->
    <div id="test-results" class="results-section" style="display: none;">
        <h3>Test Results</h3>
        <!-- Test results will be displayed here -->
    </div>

    <!-- Console log container for debugging -->
    <div id="console-log" class="debug-section">
        <h4>Test Progress Log</h4>
        <!-- Console logs will be displayed here -->
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusMessage = document.getElementById('status-message');
    const testButton = document.getElementById('test-save-button');
    const spinner = testButton.querySelector('.loading-spinner');

    function showStatus(message, type) {
        statusMessage.textContent = message;
        statusMessage.className = 'status-message status-' + type;
        statusMessage.style.display = 'block';
    }

    // Verify database connection
    fetch('/wp-json/attentrack/v1/check-table-status')
        .then(response => response.json())
        .then(data => {
            console.log('Database status:', data);
            if (!data.exists) {
                showStatus('Database table not found. Please contact administrator.', 'error');
                testResultsDisplay.logError(new Error('Database table not found'));
            }
        })
        .catch(error => {
            console.error('Error checking database status:', error);
            showStatus('Failed to check database status.', 'error');
            testResultsDisplay.logError(error);
        });

    // Sample test data
    const sampleResults = {
        total_count: 100,
        p_count: 20,
        total_responses: 18,
        correct_responses: 15,
        accuracy: 83.33,
        avg_reaction_time: 0.345,
        missed_responses: 5,
        false_alarms: 3,
        score: 75
    };

    // Test button click handler
    testButton.onclick = function() {
        console.log('Testing save results functionality...');
        
        // Show loading state
        spinner.style.display = 'inline-block';
        testButton.disabled = true;
        showStatus('Saving test results...', 'info');

        // Save results
        window.testHandler.saveTestResults(sampleResults)
            .then(() => {
                showStatus('Test results saved successfully!', 'success');
                document.getElementById('test-results').style.display = 'block';
            })
            .catch(error => {
                showStatus('Failed to save test results.', 'error');
                testResultsDisplay.logError(error);
            })
            .finally(() => {
                spinner.style.display = 'none';
                testButton.disabled = false;
            });
    };
});
</script>

<?php get_footer(); ?>
