<?php
/*
Template Name: Patient Details Form
*/

get_header();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Patient Details</h2>
                    
                    <form id="patientDetailsForm" method="post">
                        <?php wp_nonce_field('patient_details_nonce', 'patient_details_nonce'); ?>
                        
                        <div class="mb-3">
                            <label for="patientName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="patientName" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="patientAge" class="form-label">Age</label>
                            <input type="number" class="form-control" id="patientAge" name="age" min="5" max="100" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Gender</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="genderMale" value="male" required>
                                    <label class="form-check-label" for="genderMale">Male</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="female" required>
                                    <label class="form-check-label" for="genderFemale">Female</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="genderOther" value="other" required>
                                    <label class="form-check-label" for="genderOther">Other</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-danger d-none" id="formError"></div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary px-5">Continue</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#patientDetailsForm').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $error = $('#formError');
        const $submitBtn = $form.find('button[type="submit"]');
        
        // Clear previous errors
        $error.addClass('d-none').text('');
        
        // Disable submit button
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
        
        // Collect form data
        const formData = new FormData($form[0]);
        formData.append('action', 'save_patient_details');
        
        // Send AJAX request
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.redirect;
                } else {
                    $error.removeClass('d-none').text(response.data.message);
                    $submitBtn.prop('disabled', false).text('Continue');
                }
            },
            error: function(xhr) {
                let message = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.data) {
                    message = xhr.responseJSON.data.message;
                }
                $error.removeClass('d-none').text(message);
                $submitBtn.prop('disabled', false).text('Continue');
            }
        });
    });
});
</script>

<style>
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1) !important;
}

.card-body {
    padding: 2rem;
}

.form-control {
    border-radius: 8px;
    padding: 0.75rem 1rem;
    border: 1px solid #dee2e6;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.btn-primary {
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,123,255,0.2);
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>

<?php get_footer(); ?>
