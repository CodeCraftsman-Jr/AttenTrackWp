<?php
/*
Template Name: Patient Details Form Template
*/

// Handle AJAX form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (wp_doing_ajax() || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'))) {
    try {
        global $wpdb;
        $current_user_id = get_current_user_id();
        
        // Sanitize and validate input data
        $new_patient_data = array(
            'patient_id' => sanitize_text_field($_POST['patientId']),
            'test_id' => sanitize_text_field($_POST['testId']),
            'user_code' => sanitize_text_field($_POST['userCode']),
            'first_name' => sanitize_text_field($_POST['firstName']),
            'last_name' => sanitize_text_field($_POST['lastName']),
            'age' => intval($_POST['age']),
            'gender' => sanitize_text_field($_POST['gender']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'created_at' => current_time('mysql')
        );

        // Check for existing patient details
        $patient_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}attentrack_patient_details 
            WHERE patient_id = %s OR test_id = %s OR user_code = %s",
            $new_patient_data['patient_id'], $new_patient_data['test_id'], $new_patient_data['user_code']
        ));
        
        if ($patient_data) {
            // Update existing record
            $result = $wpdb->update(
                $wpdb->prefix . 'attentrack_patient_details',
                $new_patient_data,
                array('id' => $patient_data->id),
                array(
                    '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s'
                ),
                array('%d')
            );
        } else {
            // Insert new record
            $result = $wpdb->insert(
                $wpdb->prefix . 'attentrack_patient_details',
                $new_patient_data,
                array(
                    '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s'
                )
            );
        }

        if ($result === false) {
            wp_send_json_error(array('message' => 'Failed to save patient details'));
        } else {
            // Save IDs to user meta
            update_user_meta($current_user_id, 'profile_id', $new_patient_data['patient_id']);
            update_user_meta($current_user_id, 'test_id', $new_patient_data['test_id']);
            update_user_meta($current_user_id, 'user_code', $new_patient_data['user_code']);
            
            wp_send_json_success(array('message' => 'Patient details saved successfully'));
        }
    } catch (Exception $e) {
        wp_send_json_error(array('message' => 'An error occurred: ' . $e->getMessage()));
    }
    exit;
}

get_header();

// Get current user and their meta values
global $wpdb;
$current_user_id = get_current_user_id();

// Get or set default IDs
$profile_id = get_user_meta($current_user_id, 'profile_id', true);
$test_id = get_user_meta($current_user_id, 'test_id', true);
$user_code = get_user_meta($current_user_id, 'user_code', true);

// Check for existing patient details in the database
$patient_data = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}attentrack_patient_details 
    WHERE patient_id = %s OR test_id = %s OR user_code = %s",
    $profile_id, $test_id, $user_code
));

?>

<style>
    .container {
        max-width: 800px;
        margin: 40px auto;
        padding: 30px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .patient-summary {
        background: #f8f9fa;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 30px;
    }

    .patient-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .info-item {
        padding: 15px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .info-item strong {
        color: #2c3e50;
        display: block;
        margin-bottom: 5px;
        font-size: 0.9em;
    }

    .info-item span {
        color: #34495e;
        font-size: 1.1em;
    }

    .button-group {
        display: flex;
        gap: 15px;
        margin-top: 25px;
    }

    .button {
        flex: 1;
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .edit-button {
        background: #e67e22;
        color: white;
    }

    .continue-button {
        background: #27ae60;
        color: white;
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        color: #2c3e50;
        font-weight: 500;
    }

    input, select {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s;
    }

    input:focus, select:focus {
        border-color: #3498db;
        outline: none;
    }

    .readonly {
        background: #f8f9fa;
        cursor: not-allowed;
    }

    .form-header {
        margin-bottom: 30px;
        text-align: center;
    }

    .form-header h2 {
        color: #2c3e50;
        font-size: 24px;
        margin-bottom: 10px;
    }

    .form-header p {
        color: #7f8c8d;
    }
</style>

<div class="container">
    <div class="form-header">
        <h2>Patient Information</h2>
        <?php if ($patient_data): ?>
            <p>Your information is already saved. You can continue to the tests or edit your details.</p>
        <?php else: ?>
            <p>Please fill in your details to proceed with the tests.</p>
        <?php endif; ?>
    </div>

    <?php if ($patient_data): ?>
        <div class="patient-summary">
            <div class="patient-info">
                <div class="info-item">
                    <strong>Patient ID</strong>
                    <span><?php echo esc_html($patient_data->patient_id); ?></span>
                </div>
                <div class="info-item">
                    <strong>Name</strong>
                    <span><?php echo esc_html($patient_data->first_name . ' ' . $patient_data->last_name); ?></span>
                </div>
                <div class="info-item">
                    <strong>Age</strong>
                    <span><?php echo esc_html($patient_data->age); ?></span>
                </div>
                <div class="info-item">
                    <strong>Gender</strong>
                    <span><?php echo esc_html(ucfirst($patient_data->gender)); ?></span>
                </div>
                <div class="info-item">
                    <strong>Email</strong>
                    <span><?php echo esc_html($patient_data->email); ?></span>
                </div>
                <div class="info-item">
                    <strong>Phone</strong>
                    <span><?php echo esc_html($patient_data->phone); ?></span>
                </div>
            </div>
            
            <div class="button-group">
                <button type="button" class="button edit-button" onclick="toggleEditForm()">
                    Edit Information
                </button>
                <button type="button" class="button continue-button" onclick="window.location.href='<?php echo esc_url(home_url('/selection-page')); ?>'">
                    Continue to Tests
                </button>
            </div>
        </div>
    <?php endif; ?>

    <form id="patientForm" style="<?php echo $patient_data ? 'display: none;' : ''; ?>">
        <div class="form-group">
            <label for="patientId">Patient ID</label>
            <input type="text" id="patientId" name="patientId" class="readonly" 
                value="<?php echo esc_attr($patient_data ? $patient_data->patient_id : $profile_id); ?>" readonly>
        </div>

        <div class="form-group">
            <label for="testId">Test ID</label>
            <input type="text" id="testId" name="testId" class="readonly"
                value="<?php echo esc_attr($patient_data ? $patient_data->test_id : $test_id); ?>" readonly>
        </div>

        <div class="form-group">
            <label for="userCode">User Code</label>
            <input type="text" id="userCode" name="userCode" class="readonly"
                value="<?php echo esc_attr($patient_data ? $patient_data->user_code : $user_code); ?>" readonly>
        </div>

        <div class="form-group">
            <label for="firstName">First Name</label>
            <input type="text" id="firstName" name="firstName" 
                value="<?php echo esc_attr($patient_data ? $patient_data->first_name : ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="lastName">Last Name</label>
            <input type="text" id="lastName" name="lastName"
                value="<?php echo esc_attr($patient_data ? $patient_data->last_name : ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="age">Age</label>
            <input type="number" id="age" name="age" min="0" max="120"
                value="<?php echo esc_attr($patient_data ? $patient_data->age : ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender" required>
                <option value="">Select Gender</option>
                <option value="male" <?php echo ($patient_data && $patient_data->gender === 'male') ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo ($patient_data && $patient_data->gender === 'female') ? 'selected' : ''; ?>>Female</option>
                <option value="other" <?php echo ($patient_data && $patient_data->gender === 'other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                value="<?php echo esc_attr($patient_data ? $patient_data->email : ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone"
                value="<?php echo esc_attr($patient_data ? $patient_data->phone : ''); ?>" required>
        </div>

        <div class="button-group">
            <?php if ($patient_data): ?>
                <button type="button" class="button edit-button" onclick="cancelEdit()">Cancel</button>
            <?php endif; ?>
            <button type="submit" class="button continue-button">Save and Continue</button>
        </div>
    </form>
</div>

<script>
function toggleEditForm() {
    document.querySelector('.patient-summary').style.display = 'none';
    document.getElementById('patientForm').style.display = 'block';
}

function cancelEdit() {
    document.querySelector('.patient-summary').style.display = 'block';
    document.getElementById('patientForm').style.display = 'none';
}

document.getElementById('patientForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.textContent = 'Saving...';
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?php echo esc_url(home_url('/selection-page')); ?>';
        } else {
            alert(data.data.message || 'An error occurred');
            submitButton.disabled = false;
            submitButton.textContent = 'Save and Continue';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the data');
        submitButton.disabled = false;
        submitButton.textContent = 'Save and Continue';
    });
});
</script>

<?php get_footer(); ?>
