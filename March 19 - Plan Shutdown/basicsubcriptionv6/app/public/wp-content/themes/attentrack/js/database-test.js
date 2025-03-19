// Database Test Script
document.addEventListener('DOMContentLoaded', function() {
    console.log('Database test script loaded');
    
    // Check database status first
    checkDatabaseStatus()
        .then(status => {
            if (status.ready) {
                console.log('✅ Database is ready');
                document.getElementById('db-status').innerHTML = 
                    `<div class="status-success">Database is ready and accessible</div>`;
                testSaveResults();
            } else {
                console.error('❌ Database is not ready:', status.message);
                document.getElementById('db-status').innerHTML = 
                    `<div class="status-error">Database Error: ${status.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Failed to check database status:', error);
            document.getElementById('db-status').innerHTML = 
                `<div class="status-error">Failed to check database status: ${error.message}</div>`;
        });
});

// Check database status
async function checkDatabaseStatus() {
    try {
        const response = await fetch('/wp-json/attentrack/v1/check-database', {
            headers: {
                'X-WP-Nonce': wpApiSettings.nonce
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return {
            ready: data.status === 'ready',
            message: data.message
        };
    } catch (error) {
        console.error('Database status check failed:', error);
        throw error;
    }
}

// Test saving results
async function testSaveResults() {
    console.log('Testing save results functionality...');
    
    // Generate test data
    const testData = {
        total_count: 100,
        p_count: 20,
        total_responses: 18,
        correct_responses: 15,
        accuracy: 83.33,
        avg_reaction_time: 0.345,
        missed_responses: 5,
        false_alarms: 3,
        score: 75,
        responses: JSON.stringify([
            { letter: 'p', correct: true, reactionTime: 342 },
            { letter: 'p', correct: false, reactionTime: 456 }
        ])
    };

    try {
        const response = await fetch('/wp-json/attentrack/v1/save-test-results', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpApiSettings.nonce
            },
            body: JSON.stringify(testData)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        console.log('✅ Test save result:', result);
        
        document.getElementById('save-status').innerHTML = 
            `<div class="status-success">
                Successfully saved test results
                <pre>${JSON.stringify(result, null, 2)}</pre>
            </div>`;
            
        // Test retrieving the saved results
        testRetrieveResults(result.result_id);
        
    } catch (error) {
        console.error('Failed to save test results:', error);
        document.getElementById('save-status').innerHTML = 
            `<div class="status-error">Failed to save test results: ${error.message}</div>`;
    }
}

// Test retrieving results
async function testRetrieveResults(resultId) {
    try {
        const response = await fetch(`/wp-json/attentrack/v1/test-results/${resultId}`, {
            headers: {
                'X-WP-Nonce': wpApiSettings.nonce
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        console.log('✅ Retrieved test result:', result);
        
        document.getElementById('retrieve-status').innerHTML = 
            `<div class="status-success">
                Successfully retrieved test results
                <pre>${JSON.stringify(result, null, 2)}</pre>
            </div>`;
            
    } catch (error) {
        console.error('Failed to retrieve test results:', error);
        document.getElementById('retrieve-status').innerHTML = 
            `<div class="status-error">Failed to retrieve test results: ${error.message}</div>`;
    }
}

// Add event listeners for manual testing
document.addEventListener('DOMContentLoaded', function() {
    const testButtons = {
        'check-db': checkDatabaseStatus,
        'save-test': testSaveResults
    };

    Object.entries(testButtons).forEach(([id, handler]) => {
        const button = document.getElementById(id);
        if (button) {
            button.addEventListener('click', handler);
        }
    });
});
