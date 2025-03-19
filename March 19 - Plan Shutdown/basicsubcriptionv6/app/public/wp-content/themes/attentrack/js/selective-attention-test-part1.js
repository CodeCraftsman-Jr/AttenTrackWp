// Test results storage and submission
let testResults = {
    totalCount: 0,
    pCount: 0,
    totalResponses: 0,
    correctResponses: 0,
    accuracy: 0,
    avgReactionTime: 0,
    missedResponses: 0,
    falseAlarms: 0,
    score: 0,
    responses: []
};

// Function to save test results
function saveTestResults(results) {
    return new Promise((resolve, reject) => {
        console.log('Attempting to save test results:', results);

        const data = {
            total_count: results.total_count,
            p_count: results.p_count,
            total_responses: results.total_responses,
            correct_responses: results.correct_responses,
            accuracy: results.accuracy,
            avg_reaction_time: results.avg_reaction_time,
            missed_responses: results.missed_responses,
            false_alarms: results.false_alarms,
            score: results.score
        };

        fetch('/wp-json/attentrack/v1/save-test-results', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpApiSettings.nonce
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(result => {
            console.log('Test results saved successfully:', result);
            
            // Log detailed results
            console.group('Test Results Details');
            console.log('Total Letters:', data.total_count);
            console.log('P Letters:', data.p_count);
            console.log('Total Responses:', data.total_responses);
            console.log('Correct Responses:', data.correct_responses);
            console.log('Accuracy:', data.accuracy.toFixed(2) + '%');
            console.log('Average Reaction Time:', data.avg_reaction_time.toFixed(3) + 's');
            console.log('Missed Responses:', data.missed_responses);
            console.log('False Alarms:', data.false_alarms);
            console.log('Score:', data.score);
            console.groupEnd();

            // Update display
            if (window.testResultsDisplay) {
                window.testResultsDisplay.displayResults(data);
            }

            resolve(result);
        })
        .catch(error => {
            console.error('Error saving test results:', error);
            
            // Log error details
            console.group('Error Details');
            console.error('Error Type:', error.name);
            console.error('Error Message:', error.message);
            console.error('Stack Trace:', error.stack);
            console.groupEnd();

            // Update display
            if (window.testResultsDisplay) {
                window.testResultsDisplay.logError(error);
            }

            reject(error);
        });
    });
}

// Function to record a response
function recordResponse(letter, isCorrect, reactionTime) {
    testResults.responses.push({
        letter: letter,
        correct: isCorrect,
        reactionTime: reactionTime
    });

    testResults.totalResponses++;
    if (isCorrect) {
        testResults.correctResponses++;
    } else if (letter === 'p') {
        testResults.missedResponses++;
    } else {
        testResults.falseAlarms++;
    }
}

// Function to update letter counts
function updateLetterCounts(letter) {
    testResults.totalCount++;
    if (letter === 'p') {
        testResults.pCount++;
    }
}

// Function to calculate score
function calculateScore(accuracy, reactionTime) {
    let score = accuracy;
    
    if (reactionTime < 0.2) {
        score *= 0.8;
    } else if (reactionTime > 0.4) {
        score *= Math.max(0.5, 1 - ((reactionTime - 0.4) / 2));
    }
    
    const errorPenalty = (testResults.missedResponses + testResults.falseAlarms) * 2;
    score = Math.max(0, score - errorPenalty);
    
    return Math.round(score);
}

// Function to check database status
function checkDatabaseStatus() {
    return fetch('/wp-json/attentrack/v1/check-table-status')
        .then(response => response.json())
        .then(data => {
            console.log('Database status check:', data);
            return data.exists;
        })
        .catch(error => {
            console.error('Error checking database status:', error);
            throw error;
        });
}

// Export functions
window.testHandler = {
    recordResponse,
    updateLetterCounts,
    saveTestResults,
    checkDatabaseStatus
};
