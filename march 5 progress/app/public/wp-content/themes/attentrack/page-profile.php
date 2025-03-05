<?php
/*
Template Name: User Profile
*/

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/sign-in'));
    exit;
}

get_header();

$user = wp_get_current_user();
$user_details = get_user_details($user->ID);
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Profile Information</h5>
                    <div class="text-center mb-3">
                        <?php echo get_avatar($user->ID, 150, '', '', array('class' => 'rounded-circle')); ?>
                    </div>
                    <p class="card-text"><strong>Name:</strong> <?php echo esc_html($user->display_name); ?></p>
                    <p class="card-text"><strong>Email:</strong> <?php echo esc_html($user->user_email); ?></p>
                    <p class="card-text"><strong>Member since:</strong> <?php echo date('F j, Y', strtotime($user->user_registered)); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Medical Details</h5>
                    
                    <!-- Alert Messages -->
                    <div class="alert alert-success alert-dismissible fade show" id="saveSuccess" style="display: none;" role="alert">
                        <span class="message">Details saved successfully!</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <div class="alert alert-danger alert-dismissible fade show" id="saveError" style="display: none;" role="alert">
                        <span class="message">Error saving details.</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    
                    <form id="userDetailsForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                    value="<?php echo esc_attr($user_details['full_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="age" class="form-label">Age</label>
                                <input type="number" class="form-control" id="age" name="age" 
                                    value="<?php echo esc_attr($user_details['age'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select...</option>
                                    <option value="male" <?php selected(($user_details['gender'] ?? ''), 'male'); ?>>Male</option>
                                    <option value="female" <?php selected(($user_details['gender'] ?? ''), 'female'); ?>>Female</option>
                                    <option value="other" <?php selected(($user_details['gender'] ?? ''), 'other'); ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="medical_history" class="form-label">Medical History</label>
                            <textarea class="form-control" id="medical_history" name="medical_history" rows="3"><?php echo esc_textarea($user_details['medical_history'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="symptoms" class="form-label">Current Symptoms</label>
                            <textarea class="form-control" id="symptoms" name="symptoms" rows="3"><?php echo esc_textarea($user_details['symptoms'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="diagnosis" class="form-label">Diagnosis</label>
                            <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3"><?php echo esc_textarea($user_details['diagnosis'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="treatment_plan" class="form-label">Treatment Plan</label>
                            <textarea class="form-control" id="treatment_plan" name="treatment_plan" rows="3"><?php echo esc_textarea($user_details['treatment_plan'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="next_appointment" class="form-label">Next Appointment</label>
                            <input type="datetime-local" class="form-control" id="next_appointment" name="next_appointment" 
                                value="<?php echo esc_attr($user_details['next_appointment'] ?? ''); ?>">
                        </div>

                        <button type="submit" class="btn btn-primary" id="submitBtn">Save Details</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('userDetailsForm');
    const submitBtn = document.getElementById('submitBtn');
    const successAlert = document.getElementById('saveSuccess');
    const errorAlert = document.getElementById('saveError');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
        
        // Hide any existing alerts
        successAlert.style.display = 'none';
        errorAlert.style.display = 'none';

        // Collect form data
        const formData = new FormData(this);
        formData.append('action', 'save_user_details');

        // Log the data being sent
        console.log('Sending form data:', Object.fromEntries(formData));

        // Send the request
        fetch(ajax_object.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Server response:', data);
            
            if (data.success) {
                successAlert.querySelector('.message').textContent = data.data.message;
                successAlert.style.display = 'block';
                
                // Update form values with saved data if provided
                if (data.data.data) {
                    Object.entries(data.data.data).forEach(([key, value]) => {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input) {
                            input.value = value;
                        }
                    });
                }
            } else {
                throw new Error(data.data.message || 'Error saving details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorAlert.querySelector('.message').textContent = error.message;
            errorAlert.style.display = 'block';
        })
        .finally(() => {
            // Restore button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Save Details';
        });
    });
});
</script>

<?php get_footer(); ?>
