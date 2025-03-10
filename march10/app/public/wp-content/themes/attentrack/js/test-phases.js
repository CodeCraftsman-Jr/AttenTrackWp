// Common functions for test phases
window.attentrack = {
    saveTestResults: function(testId, phase, score, accuracy, reactionTime, missedCount, falseAlarms, responses) {
        return new Promise((resolve, reject) => {
            if (!window.attentrack_ajax || !window.attentrack_ajax.nonce) {
                console.error('AJAX configuration missing');
                reject('AJAX configuration missing');
                return;
            }

            jQuery.ajax({
                url: window.attentrack_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'save_test_results',
                    test_id: testId,
                    test_phase: phase,
                    score: score,
                    accuracy: accuracy,
                    reaction_time: reactionTime,
                    missed_responses: missedCount,
                    false_alarms: falseAlarms,
                    responses: JSON.stringify(responses),
                    nonce: window.attentrack_ajax.nonce
                },
                success: function(response) {
                    console.log('Save response:', response);
                    if (response.success) {
                        console.log('Data saved:', response.data);
                        resolve(true);
                    } else {
                        console.error('Error saving results:', response.data?.message || 'Unknown error');
                        resolve(false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    reject(error);
                }
            });
        });
    }
};
