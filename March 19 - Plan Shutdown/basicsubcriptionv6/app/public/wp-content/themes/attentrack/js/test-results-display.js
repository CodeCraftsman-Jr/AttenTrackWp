// Test Results Display Handler
class TestResultsDisplay {
    constructor() {
        this.resultsContainer = document.getElementById('test-results');
        this.consoleLogContainer = document.getElementById('console-log');
    }

    // Display test results in the UI
    displayResults(results) {
        // Log results to console first
        this.logToConsole(results);

        if (!this.resultsContainer) {
            console.error('Results container not found');
            return;
        }

        // Create results HTML
        const html = `
            <div class="results-summary">
                <h3>Test Results</h3>
                <div class="result-item">
                    <label>Total Letters:</label>
                    <span>${results.total_count}</span>
                </div>
                <div class="result-item">
                    <label>P Letters:</label>
                    <span>${results.p_count}</span>
                </div>
                <div class="result-item">
                    <label>Accuracy:</label>
                    <span>${results.accuracy.toFixed(2)}%</span>
                </div>
                <div class="result-item">
                    <label>Average Reaction Time:</label>
                    <span>${results.avg_reaction_time.toFixed(3)}s</span>
                </div>
                <div class="result-item">
                    <label>Missed Responses:</label>
                    <span>${results.missed_responses}</span>
                </div>
                <div class="result-item">
                    <label>False Alarms:</label>
                    <span>${results.false_alarms}</span>
                </div>
                <div class="result-item">
                    <label>Score:</label>
                    <span>${results.score}</span>
                </div>
            </div>
        `;

        this.resultsContainer.innerHTML = html;
    }

    // Log results to console with detailed information
    logToConsole(results) {
        console.group('Test Results');
        console.log('Total Letters:', results.total_count);
        console.log('P Letters:', results.p_count);
        console.log('Total Responses:', results.total_responses);
        console.log('Correct Responses:', results.correct_responses);
        console.log('Accuracy:', results.accuracy.toFixed(2) + '%');
        console.log('Average Reaction Time:', results.avg_reaction_time.toFixed(3) + 's');
        console.log('Missed Responses:', results.missed_responses);
        console.log('False Alarms:', results.false_alarms);
        console.log('Score:', results.score);
        console.groupEnd();

        // Display console logs in UI if container exists
        if (this.consoleLogContainer) {
            const logHtml = `
                <div class="console-log-entry">
                    <div class="log-timestamp">${new Date().toLocaleTimeString()}</div>
                    <div class="log-content">
                        <div>Total Letters: ${results.total_count}</div>
                        <div>P Letters: ${results.p_count}</div>
                        <div>Accuracy: ${results.accuracy.toFixed(2)}%</div>
                        <div>Avg Reaction Time: ${results.avg_reaction_time.toFixed(3)}s</div>
                        <div>Score: ${results.score}</div>
                    </div>
                </div>
            `;
            this.consoleLogContainer.insertAdjacentHTML('afterbegin', logHtml);
        }
    }

    // Log errors to console and UI
    logError(error) {
        console.group('Error Details');
        console.error('Error Type:', error.name);
        console.error('Error Message:', error.message);
        console.error('Stack Trace:', error.stack);
        console.groupEnd();

        // Display error in UI if container exists
        if (this.consoleLogContainer) {
            const errorHtml = `
                <div class="console-log-entry error">
                    <div class="log-timestamp">${new Date().toLocaleTimeString()}</div>
                    <div class="log-content error">
                        <div>Error: ${error.message}</div>
                    </div>
                </div>
            `;
            this.consoleLogContainer.insertAdjacentHTML('afterbegin', errorHtml);
        }
    }

    // Add CSS styles for the results display
    addStyles() {
        const styles = `
            .results-summary {
                margin: 20px;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background: #f9f9f9;
            }
            .result-item {
                margin: 10px 0;
                display: flex;
                justify-content: space-between;
            }
            .result-item label {
                font-weight: bold;
                margin-right: 10px;
            }
            #console-log {
                margin: 20px;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background: #f5f5f5;
                max-height: 300px;
                overflow-y: auto;
            }
            .console-log-entry {
                margin: 5px 0;
                padding: 5px;
                border-bottom: 1px solid #eee;
            }
            .log-timestamp {
                color: #666;
                font-size: 0.9em;
            }
            .log-content {
                margin-top: 5px;
            }
            .log-content.error {
                color: #dc3545;
            }
        `;

        const styleSheet = document.createElement('style');
        styleSheet.textContent = styles;
        document.head.appendChild(styleSheet);
    }
}

// Initialize the display handler
const testResultsDisplay = new TestResultsDisplay();
testResultsDisplay.addStyles();

// Export for use in main test script
window.testResultsDisplay = testResultsDisplay;
