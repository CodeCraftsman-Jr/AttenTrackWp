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
    // Check if this is an email link authentication or traditional Firebase auth
    $is_email_link = isset($_POST['token']) && isset($_POST['email']) && !isset($_POST['provider']) && !isset($_POST['name']);
    
    // For traditional Firebase auth (Google, Facebook)
    if (!$is_email_link && (!isset($_POST['token']) || !isset($_POST['provider']) || !isset($_POST['email']) || !isset($_POST['name']))) {
        wp_send_json_error('Missing required parameters');
        return;
    }
    
    // For email link auth
    if ($is_email_link && (!isset($_POST['token']) || !isset($_POST['email']))) {
        wp_send_json_error('Missing required parameters for email authentication');
        return;
    }
    
    // Verify nonce if provided (for backward compatibility, make it optional)
    if (isset($_POST['_ajax_nonce']) && !wp_verify_nonce($_POST['_ajax_nonce'], 'auth-nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $token = sanitize_text_field($_POST['token']);
    $email = sanitize_email($_POST['email']);
    $account_type = isset($_POST['account_type']) ? sanitize_text_field($_POST['account_type']) : 'user';
    
    // Set provider and name based on authentication type
    if ($is_email_link) {
        $provider = 'email';
        // For email link auth, use the part before @ as the name
        $name = ucfirst(strtolower(explode('@', $email)[0]));
    } else {
        $provider = sanitize_text_field($_POST['provider']);
        $name = sanitize_text_field($_POST['name']);
    }
    
    global $wpdb;
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    
    // Check if user exists in WordPress
    $user = get_user_by('email', $email);
    
    // Check if user exists in the consolidated table (even if not in WordPress yet)
    $existing_consolidated_user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $user_data_table WHERE email = %s",
        $email
    ));
    
    // If user exists in consolidated table, enforce their existing account type
    if ($existing_consolidated_user && $existing_consolidated_user->account_type !== $account_type) {
        wp_send_json_error(array(
            'error' => true,
            'message' => "This email is already registered as a " . ucfirst($existing_consolidated_user->account_type) . ". Please sign in with the correct account type."
        ));
        return;
    }
    
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
        
        // Create a new user
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error($user_id->get_error_message());
            return;
        }
        
        wp_update_user(array(
            'ID' => $user_id,
            'display_name' => $name
        ));
        
        // Assign the correct role based on account type
        $user = get_user_by('ID', $user_id);
        if ($account_type === 'institution') {
            // Remove default role
            $user->remove_role('subscriber');
            // Add institution role
            $user->add_role('institution');
        }
        
        // Update user meta for backward compatibility
        update_user_meta($user_id, 'account_type', $account_type);
        update_user_meta($user_id, $provider . '_id', $token);
        
        // Get the user object
        $user = get_user_by('ID', $user_id);
        
        // Check user role and account type match
        $has_institution_role = in_array('institution', (array) $user->roles);
        $is_admin = in_array('administrator', (array) $user->roles);
        
        // If not admin, enforce strict role-based access
        if (!$is_admin) {
            if ($account_type === 'institution' && !$has_institution_role) {
                // User is trying to login as institution but doesn't have institution role
                wp_send_json_error(array(
                    'error' => true,
                    'message' => 'You are trying to sign in as an institution, but your account is registered as a regular user. Please sign in with the correct account type.'
                ));
                return;
            }
            
            if ($account_type === 'user' && $has_institution_role) {
                // User is trying to login as regular user but has institution role
                wp_send_json_error(array(
                    'error' => true,
                    'message' => 'You are trying to sign in as a regular user, but your account is registered as an institution. Please sign in with the correct account type.'
                ));
                return;
            }
        }
        
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
    // Check if we're using the new parameter name (email_phone) or the old one (email_or_phone)
    $email_or_phone = isset($_POST['email_phone']) ? $_POST['email_phone'] : (isset($_POST['email_or_phone']) ? $_POST['email_or_phone'] : null);
    
    if (!$email_or_phone) {
        wp_send_json_error('Missing required parameters');
        return;
    }
    
    // Verify nonce if provided (for backward compatibility, make it optional)
    if (isset($_POST['_ajax_nonce']) && !wp_verify_nonce($_POST['_ajax_nonce'], 'auth-nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Check if this is a signup request
    $is_signup = isset($_POST['is_signup']) && ($_POST['is_signup'] === 'true' || $_POST['is_signup'] === true);
    
    // We already sanitized email_or_phone above, no need to do it again
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
        
        // Try to send email
        $email_sent = wp_mail($to, $subject, $message, $headers);
        
        // For local development - log the OTP and include it in the response
        // IMPORTANT: Remove this in production!
        error_log("OTP for $email_or_phone: $otp");
        
        // In local development, we'll consider it a success even if email fails
        // This allows testing without a working mail server
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // Include OTP in response for development only
            $GLOBALS['dev_otp'] = $otp;
        } else if (!$email_sent) {
            // In production, fail if email doesn't send
            wp_send_json_error('Failed to send OTP email');
            return;
        }
    } else {
        // TODO: Implement SMS sending
        // For now, just log the OTP for testing
        error_log("OTP for $email_or_phone: $otp");
        // In local development, include OTP in response
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $GLOBALS['dev_otp'] = $otp;
        }
    }
    
    // Prepare response data
    $response_data = array(
        'message' => 'OTP sent successfully',
        'account_type' => $account_type
    );
    
    // Include OTP in response for development environments only
    if (defined('WP_DEBUG') && WP_DEBUG && isset($GLOBALS['dev_otp'])) {
        $response_data['dev_otp'] = $GLOBALS['dev_otp'];
        $response_data['dev_mode'] = true;
        $response_data['note'] = 'This OTP is included for development purposes only. Remove in production.';
    }
    
    wp_send_json_success($response_data);
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
    if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'auth-nonce')) {
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
    if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'auth-nonce')) {
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
    
    // Check user role
    $has_institution_role = in_array('institution', (array) $user->roles);
    $is_admin = in_array('administrator', (array) $user->roles);
    
    // If not admin, enforce strict role-based access
    if (!$is_admin) {
        if ($account_type === 'institution' && !$has_institution_role) {
            wp_send_json_error('You are trying to sign in as an institution, but your account is registered as a regular user. Please sign in with the correct account type.');
            return;
        }
        
        if ($account_type === 'user' && $has_institution_role) {
            wp_send_json_error('You are trying to sign in as a regular user, but your account is registered as an institution. Please sign in with the correct account type.');
            return;
        }
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
    if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'auth-nonce')) {
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
    
    // Check if email already exists in the consolidated table
    global $wpdb;
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    $existing_user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $user_data_table WHERE email = %s",
        $email
    ));
    
    // If user exists in consolidated table, enforce their existing account type
    if ($existing_user) {
        if ($existing_user->account_type !== $account_type) {
            wp_send_json_error("This email is already registered as a " . ucfirst($existing_user->account_type) . ". Please sign in with the correct account type.");
            return;
        }
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
