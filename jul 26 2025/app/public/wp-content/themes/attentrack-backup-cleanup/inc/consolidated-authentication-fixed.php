<?php
/**
 * Consolidated Authentication Functions
 * 
 * This file handles authentication using the consolidated user data table
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/user-data-consolidation.php';
require_once get_template_directory() . '/inc/role-access-check.php';

/**
 * AJAX handler for verifying Firebase tokens
 */
function verify_firebase_token() {
    if (!isset($_POST['token']) || !isset($_POST['provider']) || !isset($_POST['email']) || !isset($_POST['name'])) {
        wp_send_json_error('Missing required parameters');
        return;
    }
    
    // Verify nonce
    if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'verify_firebase_token_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $token = sanitize_text_field($_POST['token']);
    $provider = sanitize_text_field($_POST['provider']);
    $email = sanitize_email($_POST['email']);
    $name = sanitize_text_field($_POST['name']);
    $account_type = isset($_POST['account_type']) ? sanitize_text_field($_POST['account_type']) : 'user';
    
    global $wpdb;
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    
    // Check if user exists by email
    $user = get_user_by('email', $email);
    
    // If user doesn't exist, create a new one
    if (!$user) {
        // Generate a unique username from email
        $username = sanitize_user(strtolower(explode('@', $email)[0]), true);
        $original_username = $username;
        $counter = 1;
        
        // Make sure username is unique
        while (username_exists($username)) {
            $username = $original_username . $counter;
            $counter++;
        }
        
        // Generate a random password
        $password = wp_generate_password(12, true, true);
        
        // Create the user
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error($user_id->get_error_message());
            return;
        }
        
        // Set user display name
        wp_update_user(array(
            'ID' => $user_id,
            'display_name' => $name
        ));
        
        // Set user meta
        update_user_meta($user_id, $provider . '_id', $token);
        update_user_meta($user_id, 'account_type', $account_type);
        
        // Get the user object
        $user = get_user_by('ID', $user_id);
        
        // Prepare user data for consolidated table
        $user_data = array(
            'email' => $email,
            'display_name' => $name,
            'account_type' => $account_type
        );
        
        // Set the appropriate provider ID
        if ($provider === 'google') {
            $user_data['google_id'] = $token;
        } elseif ($provider === 'facebook') {
            $user_data['facebook_id'] = $token;
        }
        
        create_or_update_consolidated_user($user_id, $user_data);
    } else {
        // If user exists in the consolidated table, update their data
        $user_data = get_consolidated_user_data($user->ID);
        if ($user_data) {
            // Don't allow changing account type through the authentication process
            // Use the existing account type from the database
            $existing_account_type = $user_data->account_type;
            
            // Update user data but preserve the account type
            $wpdb->update(
                $user_data_table,
                array(
                    'email' => $email,
                    'display_name' => $name,
                    'google_id' => $provider === 'google' ? $token : $user_data->google_id,
                    'facebook_id' => $provider === 'facebook' ? $token : $user_data->facebook_id,
                    // Keep the existing account type, don't update it
                    'updated_at' => current_time('mysql')
                ),
                array('user_id' => $user->ID)
            );
            
            // Use the existing account type from the database instead of the one from the client
            $account_type = $existing_account_type;
        } else {
            // Update existing user's provider ID in user meta for backward compatibility
            update_user_meta($user->ID, $provider . '_id', $token);
            
            // Get existing user data
            $user_data = array();
            
            // Set the appropriate provider ID
            if ($provider === 'google') {
                $user_data['google_id'] = $token;
            } elseif ($provider === 'facebook') {
                $user_data['facebook_id'] = $token;
            }
            
            // Update user in consolidated table
            create_or_update_consolidated_user($user->ID, $user_data);
        }
        
        // Update existing user's provider ID in user meta for backward compatibility
        update_user_meta($user->ID, $provider . '_id', $token);
    }
    
    // Set user role based on account type
    $user_obj = new WP_User($user->ID);
    if ($account_type === 'institution') {
        $user_obj->set_role('institution');
    } else {
        // Only change role if they're not already an administrator
        if (!in_array('administrator', $user_obj->roles)) {
            $user_obj->set_role('subscriber');
        }
    }
    
    // Check if user is trying to access a dashboard they shouldn't have access to
    $access_check = check_role_access($account_type, $user_obj);
    if ($access_check !== false) {
        wp_send_json_error($access_check['message']);
        return;
    }
    
    // Log the user in
    wp_clear_auth_cookie();
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);
    
    // Get appropriate redirect URL based on user role
    $redirect_url = get_role_redirect_url($user_obj, $account_type);
    
    wp_send_json_success(array(
        'message' => 'Authentication successful',
        'redirect' => $redirect_url,
        'user_id' => $user->ID,
        'account_type' => $account_type
    ));
}
add_action('wp_ajax_verify_firebase_token', 'verify_firebase_token');
add_action('wp_ajax_nopriv_verify_firebase_token', 'verify_firebase_token');

/**
 * AJAX handler for sending OTP
 */
function send_otp() {
    if (!isset($_POST['email_or_phone'])) {
        wp_send_json_error('Missing required parameters');
        return;
    }
    
    // Verify nonce
    if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'send_otp_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $email_or_phone = sanitize_text_field($_POST['email_or_phone']);
    $account_type = isset($_POST['account_type']) ? sanitize_text_field($_POST['account_type']) : 'user';
    
    // Determine if input is email or phone
    $is_email = filter_var($email_or_phone, FILTER_VALIDATE_EMAIL);
    $field_type = $is_email ? 'email' : 'phone_number';
    
    global $wpdb;
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    
    // Check if user exists in the consolidated table
    $user_data = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $user_data_table WHERE $field_type = %s",
        $email_or_phone
    ));
    
    // If user doesn't exist, create a new one
    if (!$user_data) {
        // Generate OTP
        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        // Prepare user data
        $data = array(
            $field_type => $email_or_phone,
            'account_type' => $account_type,
            'otp' => $otp,
            'otp_expiry' => $otp_expiry,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        // Generate unique profile ID and test ID
        $data['profile_id'] = 'P' . uniqid();
        $data['test_id'] = 'T' . uniqid();
        
        // Insert user data
        $wpdb->insert($user_data_table, $data);
        
        if ($wpdb->last_error) {
            wp_send_json_error('Failed to create user: ' . $wpdb->last_error);
            return;
        }
        
        $user_id = $wpdb->insert_id;
    } else {
        // Don't allow changing account type through OTP
        // Use the existing account type from the database
        $existing_account_type = $user_data->account_type;
        
        // Update existing user with new OTP
        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        $wpdb->update(
            $user_data_table,
            array(
                'otp' => $otp,
                'otp_expiry' => $otp_expiry,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $user_data->id)
        );
        
        if ($wpdb->last_error) {
            wp_send_json_error('Failed to update user: ' . $wpdb->last_error);
            return;
        }
        
        $user_id = $user_data->id;
        
        // Use the existing account type
        $account_type = $existing_account_type;
    }
    
    // Send OTP via email or SMS
    if ($is_email) {
        // Send email with OTP
        $to = $email_or_phone;
        $subject = 'Your OTP for AttenTrack';
        $message = "Your OTP is: $otp\nIt will expire in 15 minutes.";
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        $email_sent = wp_mail($to, $subject, $message, $headers);
        
        if (!$email_sent) {
            wp_send_json_error('Failed to send OTP email');
            return;
        }
    } else {
        // TODO: Implement SMS sending
        // For now, just return the OTP in the response for testing
        // In production, this should be removed and replaced with actual SMS sending
    }
    
    wp_send_json_success(array(
        'message' => 'OTP sent successfully',
        'otp' => $otp, // Remove this in production
        'account_type' => $account_type
    ));
}
add_action('wp_ajax_send_otp', 'send_otp');
add_action('wp_ajax_nopriv_send_otp', 'send_otp');

/**
 * AJAX handler for verifying OTP
 */
function verify_otp() {
    if (!isset($_POST['email_or_phone']) || !isset($_POST['otp'])) {
        wp_send_json_error('Missing required parameters');
        return;
    }
    
    // Verify nonce
    if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'verify_otp_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $email_or_phone = sanitize_text_field($_POST['email_or_phone']);
    $otp = sanitize_text_field($_POST['otp']);
    $account_type = isset($_POST['account_type']) ? sanitize_text_field($_POST['account_type']) : 'user';
    
    // Determine if input is email or phone
    $is_email = filter_var($email_or_phone, FILTER_VALIDATE_EMAIL);
    $field_type = $is_email ? 'email' : 'phone_number';
    
    global $wpdb;
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    
    // Check if user exists and OTP is valid
    $user_data = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $user_data_table WHERE $field_type = %s AND otp = %s AND otp_expiry > %s",
        $email_or_phone, $otp, current_time('mysql')
    ));
    
    if (!$user_data) {
        wp_send_json_error('Invalid OTP or OTP expired');
        return;
    }
    
    // Don't allow changing account type through OTP verification
    // Use the existing account type from the database
    $existing_account_type = $user_data->account_type;
    
    // Check if account type matches
    if ($account_type !== $existing_account_type) {
        wp_send_json_error("Your account is already registered as a " . ucfirst($existing_account_type) . ". You cannot change your account type. Please sign in with the correct account type.");
        return;
    }
    
    // Clear OTP after successful verification
    $wpdb->update(
        $user_data_table,
        array(
            'otp' => null,
            'otp_expiry' => null,
            'updated_at' => current_time('mysql')
        ),
        array('id' => $user_data->id)
    );
    
    // Check if user exists in WordPress
    $wp_user = null;
    if ($is_email) {
        $wp_user = get_user_by('email', $email_or_phone);
    } else {
        // Try to find user by phone number in user meta
        $users = get_users(array(
            'meta_key' => 'phone_number',
            'meta_value' => $email_or_phone
        ));
        
        if (!empty($users)) {
            $wp_user = $users[0];
        }
    }
    
    // If user doesn't exist in WordPress, create one
    if (!$wp_user) {
        // Generate a unique username
        $username = $is_email ? sanitize_user(strtolower(explode('@', $email_or_phone)[0]), true) : 'user_' . substr(md5($email_or_phone), 0, 10);
        $original_username = $username;
        $counter = 1;
        
        // Make sure username is unique
        while (username_exists($username)) {
            $username = $original_username . $counter;
            $counter++;
        }
        
        // Generate a random password
        $password = wp_generate_password(12, true, true);
        
        // Create the user
        $user_id = wp_create_user($username, $password, $is_email ? $email_or_phone : '');
        
        if (is_wp_error($user_id)) {
            wp_send_json_error($user_id->get_error_message());
            return;
        }
        
        // Set user meta
        if (!$is_email) {
            update_user_meta($user_id, 'phone_number', $email_or_phone);
        }
        
        update_user_meta($user_id, 'account_type', $account_type);
        
        // Update user in consolidated table
        $wpdb->update(
            $user_data_table,
            array('user_id' => $user_id),
            array('id' => $user_data->id)
        );
        
        // Get the user object
        $wp_user = get_user_by('ID', $user_id);
    } else {
        // Update user_id in consolidated table if needed
        if ($user_data->user_id != $wp_user->ID) {
            $wpdb->update(
                $user_data_table,
                array('user_id' => $wp_user->ID),
                array('id' => $user_data->id)
            );
        }
        
        // Update account_type in user meta
        update_user_meta($wp_user->ID, 'account_type', $account_type);
    }
    
    // Set user role based on account type
    $user_obj = new WP_User($wp_user->ID);
    if ($account_type === 'institution') {
        $user_obj->set_role('institution');
    } else {
        // Only change role if they're not already an administrator
        if (!in_array('administrator', $user_obj->roles)) {
            $user_obj->set_role('subscriber');
        }
    }
    
    // Check if user is trying to access a dashboard they shouldn't have access to
    $access_check = check_role_access($account_type, $user_obj);
    if ($access_check !== false) {
        wp_send_json_error($access_check['message']);
        return;
    }
    
    // Log the user in
    wp_clear_auth_cookie();
    wp_set_current_user($wp_user->ID);
    wp_set_auth_cookie($wp_user->ID);
    
    // Get appropriate redirect URL based on user role
    $redirect_url = get_role_redirect_url($user_obj, $account_type);
    
    wp_send_json_success(array(
        'message' => 'OTP verified successfully',
        'redirect' => $redirect_url,
        'user_id' => $wp_user->ID,
        'account_type' => $account_type
    ));
}
add_action('wp_ajax_verify_otp', 'verify_otp');
add_action('wp_ajax_nopriv_verify_otp', 'verify_otp');

/**
 * AJAX handler for username/password login
 */
function login_with_username_password() {
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        wp_send_json_error('Missing required parameters');
        return;
    }
    
    // Verify nonce
    if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'login_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $username = sanitize_user($_POST['username']);
    $password = $_POST['password'];
    $account_type = isset($_POST['account_type']) ? sanitize_text_field($_POST['account_type']) : 'user';
    
    // Try to authenticate user
    $user = wp_authenticate($username, $password);
    
    if (is_wp_error($user)) {
        wp_send_json_error($user->get_error_message());
        return;
    }
    
    // Get user data from consolidated table
    $user_data = get_consolidated_user_data($user->ID);
    
    // Check if user is trying to change their account type
    if ($user_data && $user_data->account_type && $user_data->account_type !== $account_type) {
        wp_send_json_error("Your account is already registered as a " . ucfirst($user_data->account_type) . ". You cannot change your account type. Please sign in with the correct account type.");
        return;
    }
    
    // Update user meta for backward compatibility
    update_user_meta($user->ID, 'account_type', $account_type);
    
    // Set user role based on account type
    $user_obj = new WP_User($user->ID);
    if ($account_type === 'institution') {
        $user_obj->set_role('institution');
    } else {
        // Only change role if they're not already an administrator
        if (!in_array('administrator', $user_obj->roles)) {
            $user_obj->set_role('subscriber');
        }
    }
    
    // Check if user is trying to access a dashboard they shouldn't have access to
    $access_check = check_role_access($account_type, $user_obj);
    if ($access_check !== false) {
        wp_send_json_error($access_check['message']);
        return;
    }
    
    // Log the user in
    wp_clear_auth_cookie();
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);
    
    // Get updated user data
    $user_data = get_consolidated_user_data($user->ID);
    
    // Get appropriate redirect URL based on user role
    $redirect_url = get_role_redirect_url($user_obj, $account_type);
    
    wp_send_json_success(array(
        'message' => 'Login successful',
        'redirect' => $redirect_url,
        'user_id' => $user->ID,
        'account_type' => $account_type
    ));
}
add_action('wp_ajax_login_with_username_password', 'login_with_username_password');
add_action('wp_ajax_nopriv_login_with_username_password', 'login_with_username_password');

/**
 * AJAX handler for user registration
 */
function register_user() {
    if (!isset($_POST['username']) || !isset($_POST['email']) || !isset($_POST['password'])) {
        wp_send_json_error('Missing required parameters');
        return;
    }
    
    // Verify nonce
    if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'register_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $username = sanitize_user($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $account_type = isset($_POST['account_type']) ? sanitize_text_field($_POST['account_type']) : 'user';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    
    // Check for duplicate user
    $user_data = array(
        'username' => $username,
        'email' => $email,
        'phone_number' => $phone
    );
    
    $duplicate_check = check_duplicate_user($user_data);
    if ($duplicate_check !== false) {
        wp_send_json_error($duplicate_check['message']);
        return;
    }
    
    // Create the user
    $user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($user_id)) {
        wp_send_json_error($user_id->get_error_message());
        return;
    }
    
    // Set user meta
    update_user_meta($user_id, 'account_type', $account_type);
    if (!empty($phone)) {
        update_user_meta($user_id, 'phone_number', $phone);
    }
    
    // Set user role based on account type
    $user_obj = new WP_User($user_id);
    if ($account_type === 'institution') {
        $user_obj->set_role('institution');
    } else {
        $user_obj->set_role('subscriber');
    }
    
    // Prepare user data for consolidated table
    $consolidated_data = array(
        'email' => $email,
        'phone_number' => $phone,
        'account_type' => $account_type
    );
    
    // Create or update user in consolidated table
    create_or_update_consolidated_user($user_id, $consolidated_data);
    
    // Log the user in
    wp_clear_auth_cookie();
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);
    
    // Get appropriate redirect URL based on user role
    $redirect_url = get_role_redirect_url($user_obj, $account_type);
    
    wp_send_json_success(array(
        'message' => 'Registration successful',
        'redirect' => $redirect_url,
        'user_id' => $user_id,
        'account_type' => $account_type
    ));
}
add_action('wp_ajax_register_user', 'register_user');
add_action('wp_ajax_nopriv_register_user', 'register_user');
