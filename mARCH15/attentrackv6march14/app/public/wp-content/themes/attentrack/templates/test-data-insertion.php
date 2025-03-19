<?php
/*
Template Name: Test Data Insertion
*/

get_header();
?>

<div class="container mt-5">
    <h1>Test Data Insertion Tool</h1>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Insert Test Data</h5>
                    <form id="testDataForm">
                        <div class="mb-3">
                            <label for="testPhase" class="form-label">Test Phase</label>
                            <select class="form-select" id="testPhase" name="test_phase" required>
                                <option value="0">Phase 0</option>
                                <option value="1">Phase 1</option>
                                <option value="2">Phase 2</option>
                                <option value="3">Phase 3</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="score" class="form-label">Score</label>
                            <input type="number" class="form-control" id="score" name="score" required>
                        </div>
                        <div class="mb-3">
                            <label for="accuracy" class="form-label">Accuracy (%)</label>
                            <input type="number" class="form-control" id="accuracy" name="accuracy" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="reactionTime" class="form-label">Reaction Time (ms)</label>
                            <input type="number" class="form-control" id="reactionTime" name="reaction_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="missedResponses" class="form-label">Missed Responses</label>
                            <input type="number" class="form-control" id="missedResponses" name="missed_responses" required>
                        </div>
                        <div class="mb-3">
                            <label for="falseAlarms" class="form-label">False Alarms</label>
                            <input type="number" class="form-control" id="falseAlarms" name="false_alarms" required>
                        </div>
                        <div class="mb-3">
                            <label for="totalLetters" class="form-label">Total Letters</label>
                            <input type="number" class="form-control" id="totalLetters" name="total_letters" required>
                        </div>
                        <div class="mb-3">
                            <label for="pLetters" class="form-label">P Letters</label>
                            <input type="number" class="form-control" id="pLetters" name="p_letters" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Insert Test Data</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Debug Console</h5>
                    <div id="debugConsole" class="bg-dark text-light p-3" style="height: 400px; overflow-y: auto; font-family: monospace;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const debugConsole = $('#debugConsole');

    function log(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const color = type === 'error' ? 'red' : type === 'success' ? 'green' : 'white';
        debugConsole.append(`<div style="color: ${color}">[${timestamp}] ${message}</div>`);
        debugConsole.scrollTop(debugConsole[0].scrollHeight);
    }

    log('Debug console initialized');
    log('AJAX URL: ' + attentrack_ajax.ajax_url);
    log('Nonce available: ' + (attentrack_ajax.nonce ? 'Yes' : 'No'));

    $('#testDataForm').on('submit', async function(e) {
        e.preventDefault();
        log('Form submitted, gathering data...');

        try {
            const formData = new FormData(this);
            formData.append('action', 'save_test_results');
            formData.append('_ajax_nonce', attentrack_ajax.nonce);
            formData.append('test_type', 'phase_' + formData.get('test_phase'));
            formData.append('test_id', 'test_' + Date.now());

            // Create sample responses array
            const totalLetters = parseInt(formData.get('total_letters'));
            const pLetters = parseInt(formData.get('p_letters'));
            const responses = [];
            
            for (let i = 0; i < totalLetters; i++) {
                const isP = i < pLetters;
                responses.push({
                    letter: isP ? 'P' : 'R',
                    correct: Math.random() > 0.2,
                    missed: Math.random() > 0.9,
                    reactionTime: Math.floor(Math.random() * 1000) + 200
                });
            }

            formData.append('responses', JSON.stringify(responses));

            log('Sending data to server...', 'info');
            log('Form data:', 'info');
            for (let [key, value] of formData.entries()) {
                log(`${key}: ${value}`, 'info');
            }

            const response = await fetch(attentrack_ajax.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            log('Server response:', 'info');
            log(JSON.stringify(data, null, 2), data.success ? 'success' : 'error');

            if (data.success) {
                log('Test data inserted successfully!', 'success');
            } else {
                throw new Error(data.data?.message || 'Failed to insert test data');
            }
        } catch (error) {
            log('Error: ' + error.message, 'error');
            console.error('Error details:', error);
        }
    });
});
</script>

<style>
#debugConsole {
    font-size: 12px;
    line-height: 1.4;
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>

<?php get_footer(); ?>
