jQuery(document).ready(function($) {
    // Ensure the button exists
    const resetButton = $('#lps-reset-button');
    
    if (resetButton.length) {
        resetButton.on('click', function() {
            if (confirm('Are you sure you want to reset settings to defaults?')) {
                $.post(lps_reset_ajax_obj.ajax_url, {
                    action: 'lps_reset_settings',
                    nonce: lps_reset_ajax_obj.nonce
                })
                .done(function(data) {
                    if (data.success) {
                        alert(data.data); // Success message
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert('Failed to reset settings. Please try again.');
                    }
                })
                .fail(function() {
                    alert('An error occurred. Please try again.');
                });
            }
        });
    }
});
