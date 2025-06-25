<?php
// Ensure WordPress core is loaded
if (!defined('ABSPATH')) {
    require_once(dirname(__FILE__) . '/../../../../../wp-load.php');
}

// Handle client details form submission
function handle_client_details_submission() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'client_details_nonce')) {
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
    
    // Check permissions - only clients can edit their own details, or institution admins can edit their members
    if (!attentrack_can_access_resource('client_data', $user_id, 'edit')) {
        wp_send_json_error(['message' => 'Insufficient permissions'], 403);
        exit;
    }
    
    // Handle viewing user ID for institution admins
    $viewing_user_id = 0;
    if (isset($_POST['viewing_user_id']) && current_user_can('manage_institution_users')) {
        $viewing_user_id = intval($_POST['viewing_user_id']);
        
        // Verify the viewing user belongs to the institution
        $institution_id = attentrack_get_user_institution_id($user_id);
        if (!attentrack_can_access_resource('client_data', $viewing_user_id, 'edit')) {
            wp_send_json_error(['message' => 'You do not have permission to edit this client'], 403);
            exit;
        }
        
        $user_id = $viewing_user_id; // Switch to editing the viewed user
    }
    
    // Validate and sanitize input
    $client_name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $client_age = isset($_POST['age']) ? intval($_POST['age']) : 0;
    $client_gender = isset($_POST['gender']) ? sanitize_text_field($_POST['gender']) : '';
    $client_email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $client_phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    
    // Validate required fields
    if (empty($client_name) || empty($client_age) || empty($client_gender)) {
        wp_send_json_error(['message' => 'Name, age, and gender are required fields'], 400);
        exit;
    }

    // Validate age range
    if ($client_age < 5 || $client_age > 100) {
        wp_send_json_error(['message' => 'Age must be between 5 and 100'], 400);
        exit;
    }

    // Validate email if provided
    if (!empty($client_email) && !is_email($client_email)) {
        wp_send_json_error(['message' => 'Please enter a valid email address'], 400);
        exit;
    }

    // Validate phone if provided
    if (!empty($client_phone) && !preg_match('/^[\d\s\-\+\(\)]+$/', $client_phone)) {
        wp_send_json_error(['message' => 'Please enter a valid phone number'], 400);
        exit;
    }

    // Get existing client details from database
    global $wpdb;
    $profile_id = get_user_meta($user_id, 'profile_id', true);
    $test_id = get_user_meta($user_id, 'test_id', true);
    $user_code = get_user_meta($user_id, 'user_code', true);

    // Check if client details already exist in database
    $existing_client = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}attentrack_client_details 
         WHERE profile_id = %s OR test_id = %s OR user_code = %s",
        $profile_id, $test_id, $user_code
    ));

    $client_data = array(
        'profile_id' => $profile_id,
        'client_id' => $user_code,
        'first_name' => $client_name,
        'last_name' => '', // Can be extended to separate first/last name
        'age' => $client_age,
        'gender' => $client_gender,
        'email' => $client_email,
        'phone' => $client_phone,
        'updated_at' => current_time('mysql')
    );

    if ($existing_client) {
        // Update existing record
        $result = $wpdb->update(
            $wpdb->prefix . 'attentrack_client_details',
            $client_data,
            array('id' => $existing_client->id)
        );
        
        $action = 'client_details_updated';
    } else {
        // Insert new record
        $client_data['created_at'] = current_time('mysql');
        $result = $wpdb->insert(
            $wpdb->prefix . 'attentrack_client_details',
            $client_data
        );
        
        $action = 'client_details_created';
    }

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to save client details to database'], 500);
        exit;
    }

    // Also save as user meta for backward compatibility
    update_user_meta($user_id, 'client_name', $client_name);
    update_user_meta($user_id, 'client_age', $client_age);
    update_user_meta($user_id, 'client_gender', $client_gender);
    update_user_meta($user_id, 'client_email', $client_email);
    update_user_meta($user_id, 'client_phone', $client_phone);
    
    // Update WordPress user email if provided and different
    if (!empty($client_email)) {
        $user = get_userdata($user_id);
        if ($user && $user->user_email !== $client_email) {
            wp_update_user(array(
                'ID' => $user_id,
                'user_email' => $client_email
            ));
        }
    }

    // Log the action
    $institution_id = attentrack_get_user_institution_id($user_id);
    attentrack_log_audit_action(get_current_user_id(), $action, 'client_data', $user_id, $institution_id, array(
        'client_name' => $client_name,
        'updated_by' => get_current_user_id() !== $user_id ? 'admin' : 'self'
    ));
    
    // Return success response
    wp_send_json_success([
        'message' => 'Client details saved successfully',
        'redirect' => $viewing_user_id ? home_url('/dashboard?type=institution') : home_url('/selection-page'),
        'client_data' => $client_data
    ]);
}

// Handle AJAX request for client details submission
add_action('wp_ajax_handle_client_details', 'handle_client_details_submission');
add_action('wp_ajax_nopriv_handle_client_details', 'handle_client_details_submission');

// Get client details for display
function get_client_details($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    // Check permissions
    if (!attentrack_can_access_resource('client_data', $user_id, 'view')) {
        return false;
    }
    
    global $wpdb;
    
    // Get user meta values
    $profile_id = get_user_meta($user_id, 'profile_id', true);
    $test_id = get_user_meta($user_id, 'test_id', true);
    $user_code = get_user_meta($user_id, 'user_code', true);
    
    // Try to get from database first
    $client_details = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}attentrack_client_details 
         WHERE profile_id = %s OR test_id = %s OR user_code = %s
         ORDER BY updated_at DESC LIMIT 1",
        $profile_id, $test_id, $user_code
    ));
    
    if ($client_details) {
        return array(
            'name' => $client_details->first_name . ' ' . $client_details->last_name,
            'first_name' => $client_details->first_name,
            'last_name' => $client_details->last_name,
            'age' => $client_details->age,
            'gender' => $client_details->gender,
            'email' => $client_details->email,
            'phone' => $client_details->phone,
            'profile_id' => $client_details->profile_id,
            'client_id' => $client_details->client_id,
            'created_at' => $client_details->created_at,
            'updated_at' => $client_details->updated_at
        );
    }
    
    // Fallback to user meta (for backward compatibility)
    return array(
        'name' => get_user_meta($user_id, 'client_name', true) ?: get_user_meta($user_id, 'patient_name', true),
        'age' => get_user_meta($user_id, 'client_age', true) ?: get_user_meta($user_id, 'patient_age', true),
        'gender' => get_user_meta($user_id, 'client_gender', true) ?: get_user_meta($user_id, 'patient_gender', true),
        'email' => get_user_meta($user_id, 'client_email', true) ?: get_user_meta($user_id, 'patient_email', true),
        'phone' => get_user_meta($user_id, 'client_phone', true) ?: get_user_meta($user_id, 'patient_phone', true),
        'profile_id' => get_user_meta($user_id, 'profile_id', true),
        'client_id' => get_user_meta($user_id, 'user_code', true)
    );
}

// AJAX handler to get client details
function ajax_get_client_details() {
    check_ajax_referer('attentrack_client_details', 'nonce');
    
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : get_current_user_id();
    
    // Check permissions
    if (!attentrack_can_access_resource('client_data', $user_id, 'view')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    $client_details = get_client_details($user_id);
    
    if ($client_details) {
        wp_send_json_success($client_details);
    } else {
        wp_send_json_error('Client details not found');
    }
}

add_action('wp_ajax_get_client_details', 'ajax_get_client_details');

// Bulk update client details (for institution admins)
function bulk_update_client_details() {
    check_ajax_referer('attentrack_bulk_client_update', 'nonce');
    
    if (!current_user_can('manage_institution_users')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    $updates = $_POST['client_updates'] ?? array();
    $institution_id = attentrack_get_user_institution_id(get_current_user_id());
    
    $successful_updates = array();
    $failed_updates = array();
    
    foreach ($updates as $update) {
        $client_user_id = intval($update['user_id']);
        
        // Verify client belongs to institution
        if (!attentrack_can_access_resource('client_data', $client_user_id, 'edit')) {
            $failed_updates[] = array(
                'user_id' => $client_user_id,
                'error' => 'Client does not belong to your institution'
            );
            continue;
        }
        
        // Update client details
        $client_data = array(
            'first_name' => sanitize_text_field($update['name']),
            'age' => intval($update['age']),
            'gender' => sanitize_text_field($update['gender']),
            'email' => sanitize_email($update['email'] ?? ''),
            'phone' => sanitize_text_field($update['phone'] ?? ''),
            'updated_at' => current_time('mysql')
        );
        
        global $wpdb;
        $profile_id = get_user_meta($client_user_id, 'profile_id', true);
        
        $result = $wpdb->update(
            $wpdb->prefix . 'attentrack_client_details',
            $client_data,
            array('profile_id' => $profile_id)
        );
        
        if ($result !== false) {
            $successful_updates[] = $client_user_id;
            
            // Log the update
            attentrack_log_audit_action(get_current_user_id(), 'bulk_client_update', 'client_data', 
                $client_user_id, $institution_id, array('updated_fields' => array_keys($client_data)));
        } else {
            $failed_updates[] = array(
                'user_id' => $client_user_id,
                'error' => 'Database update failed'
            );
        }
    }
    
    wp_send_json_success(array(
        'successful' => $successful_updates,
        'failed' => $failed_updates
    ));
}

add_action('wp_ajax_bulk_update_client_details', 'bulk_update_client_details');

// Legacy support - redirect old patient endpoints to client endpoints
add_action('wp_ajax_handle_patient_details', function() {
    // Redirect to new client handler
    $_POST['action'] = 'handle_client_details';
    handle_client_details_submission();
});

add_action('wp_ajax_nopriv_handle_patient_details', function() {
    // Redirect to new client handler
    $_POST['action'] = 'handle_client_details';
    handle_client_details_submission();
});
