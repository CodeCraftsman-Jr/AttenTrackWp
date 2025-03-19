// Test Results functionality
function exportTestResults(testId, nonce) {
    // Create a form to submit the POST request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = attentrackAjax.ajaxurl;

    // Add action
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'export_test_results';
    form.appendChild(actionInput);

    // Add test ID
    const testIdInput = document.createElement('input');
    testIdInput.type = 'hidden';
    testIdInput.name = 'test_id';
    testIdInput.value = testId;
    form.appendChild(testIdInput);

    // Add nonce
    const nonceInput = document.createElement('input');
    nonceInput.type = 'hidden';
    nonceInput.name = 'nonce';
    nonceInput.value = nonce;
    form.appendChild(nonceInput);

    // Add form to body and submit
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// Initialize charts and analysis when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Set default Chart.js options
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Arial', sans-serif";
        Chart.defaults.color = '#666';
        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;
    }

    // Add hover effects to comparison boxes
    const comparisonBoxes = document.querySelectorAll('.comparison-box');
    comparisonBoxes.forEach(box => {
        box.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
        });
        box.addEventListener('mouseleave', function() {
            this.style.transform = 'none';
            this.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
        });
    });

    // Add hover effects to error boxes
    const errorBoxes = document.querySelectorAll('.error-box');
    errorBoxes.forEach(box => {
        box.addEventListener('mouseenter', function() {
            this.style.background = '#fff';
        });
        box.addEventListener('mouseleave', function() {
            this.style.background = '#f8f9fa';
        });
    });
});
