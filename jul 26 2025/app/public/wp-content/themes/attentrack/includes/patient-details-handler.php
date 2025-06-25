<?php
// Ensure WordPress core is loaded
if (!defined('ABSPATH')) {
    require_once(dirname(__FILE__) . '/../../../../../wp-load.php');
}

// Handle patient details form submission
function handle_patient_details_submission() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'patient_details_nonce')) {
        wp_send_json_error(['message' => 'Invalid security token'], 403);
        exit;
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'User not logged in'], 401);
        exit;
    }

    // Get current user
    $user_id = get_current_user_id();
    
    // Validate and sanitize input
    $patient_name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $patient_age = isset($_POST['age']) ? intval($_POST['age']) : 0;
    $patient_gender = isset($_POST['gender']) ? sanitize_text_field($_POST['gender']) : '';
    
    // Validate required fields
    if (empty($patient_name) || empty($patient_age) || empty($patient_gender)) {
        wp_send_json_error(['message' => 'All fields are required'], 400);
        exit;
    }

    // Validate age range
    if ($patient_age < 5 || $patient_age > 100) {
        wp_send_json_error(['message' => 'Age must be between 5 and 100'], 400);
        exit;
    }

    // Save patient details as user meta
    update_user_meta($user_id, 'patient_name', $patient_name);
    update_user_meta($user_id, 'patient_age', $patient_age);
    update_user_meta($user_id, 'patient_gender', $patient_gender);
    
    // Return success response
    wp_send_json_success([
        'message' => 'Patient details saved successfully',
        'redirect' => home_url('/selection-page')
    ]);
    exit;
}

// AJAX handler for updating patient details
function update_patient_details_ajax() {
    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update_patient_details_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }
    
    // Get current user
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    
    // Check if we're viewing a specific user (for institution admins)
    $viewing_user_id = isset($_POST['viewing_user_id']) ? intval($_POST['viewing_user_id']) : 0;
    
    // If institution is viewing a user, check permissions
    if ($viewing_user_id > 0 && current_user_can('institution')) {
        global $wpdb;
        $institution_id = $user_id;
        $is_member = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}attentrack_institution_members 
            WHERE user_id = %d AND institution_id = %d AND status = 'active'",
            $viewing_user_id,
            $institution_id
        ));
        
        if ($is_member) {
            $user_id = $viewing_user_id;
        } else {
            wp_send_json_error(array('message' => 'You do not have permission to edit this user'));
            return;
        }
    }
    
    // Get user meta values
    $profile_id = get_user_meta($user_id, 'profile_id', true) ?: 'P' . sprintf('%04d', $user_id);
    $test_id = get_user_meta($user_id, 'test_id', true) ?: 'T' . sprintf('%04d', $user_id);
    $user_code = get_user_meta($user_id, 'user_code', true) ?: 'U' . sprintf('%04d', $user_id);
    
    // Get form data
    $first_name = isset($_POST['firstName']) ? sanitize_text_field($_POST['firstName']) : '';
    $last_name = isset($_POST['lastName']) ? sanitize_text_field($_POST['lastName']) : '';
    $age = isset($_POST['age']) ? intval($_POST['age']) : '';
    $gender = isset($_POST['gender']) ? sanitize_text_field($_POST['gender']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    
    // Update user data in WordPress
    $user_data = array(
        'ID' => $user_id
    );
    
    if (!empty($first_name) && !empty($last_name)) {
        // Only update display name for non-institution users
        if (!user_can($user_id, 'institution')) {
            $user_data['display_name'] = $first_name . ' ' . $last_name;
            $user_data['first_name'] = $first_name;
            $user_data['last_name'] = $last_name;
        }
    }
    
    if (!empty($email)) {
        // Only update email for non-institution users
        if (!user_can($user_id, 'institution')) {
            $user_data['user_email'] = $email;
        }
    }
    
    // Only update if we have data to update and user is not an institution
    if (count($user_data) > 1 && !user_can($user_id, 'institution')) {
        $user_update = wp_update_user($user_data);
        if (is_wp_error($user_update)) {
            wp_send_json_error(array('message' => $user_update->get_error_message()));
            return;
        }
    }
    
    // Update user meta
    if (!empty($phone) && !user_can($user_id, 'institution')) {
        update_user_meta($user_id, 'phone', $phone);
    }
    
    // Update patient details in database
    global $wpdb;
    $patient_table = $wpdb->prefix . 'attentrack_patient_details';
    $institution_members_table = $wpdb->prefix . 'attentrack_institution_members';
    
    // Check if patient details exist
    $patient_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $patient_table WHERE test_id = %s AND user_code = %s",
        $test_id,
        $user_code
    ));
    
    // Update patient details
    $data = array(
        'first_name' => $first_name,
        'last_name' => $last_name,
        'age' => $age,
        'gender' => $gender,
        'email' => $email,
        'phone' => $phone
    );
    
    if ($patient_exists) {
        // Update existing record
        $wpdb->update(
            $patient_table,
            $data,
            array(
                'test_id' => $test_id,
                'user_code' => $user_code
            ),
            array('%s', '%s', '%d', '%s', '%s', '%s'),
            array('%s', '%s')
        );
    } else {
        // Insert new record
        $data['patient_id'] = $profile_id;
        $data['test_id'] = $test_id;
        $data['user_code'] = $user_code;
        $data['created_at'] = current_time('mysql');
        
        $wpdb->insert(
            $patient_table,
            $data,
            array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s')
        );
    }
    
    // Update institution member record if it exists
    $member_record = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $institution_members_table WHERE user_id = %d",
        $user_id
    ));
    
    if ($member_record) {
        $wpdb->update(
            $institution_members_table,
            array(
                'role' => 'patient',
                'updated_at' => current_time('mysql')
            ),
            array('user_id' => $user_id),
            array('%s', '%s'),
            array('%d')
        );
    }
    
    if ($wpdb->last_error) {
        wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error));
        return;
    }
    
    wp_send_json_success(array('message' => 'Patient details updated successfully'));
}

// Register AJAX handlers
add_action('wp_ajax_save_patient_details', 'handle_patient_details_submission');
add_action('wp_ajax_nopriv_save_patient_details', 'handle_patient_details_submission');
add_action('wp_ajax_update_patient_details', 'update_patient_details_ajax');
