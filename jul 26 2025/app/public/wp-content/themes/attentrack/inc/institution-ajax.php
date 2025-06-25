<?php
// AJAX handlers for Institution Dashboard
add_action('wp_ajax_institution_get_subscription', 'institution_get_subscription_ajax');
add_action('wp_ajax_institution_get_users', 'institution_get_users_ajax');
add_action('wp_ajax_institution_get_analytics', 'institution_get_analytics_ajax');
add_action('wp_ajax_institution_add_user', 'institution_add_user_ajax');
add_action('wp_ajax_institution_remove_user', 'institution_remove_user_ajax');
add_action('wp_ajax_institution_edit_details', 'institution_edit_details_handler');

function institution_get_subscription_ajax() {
    // Verify user is logged in and has institution access
    if (!is_user_logged_in() || (!current_user_can('institution') && !current_user_can('access_institution_dashboard'))) {
        wp_send_json_error(['message' => 'Unauthorized access'], 403);
        exit;
    }
    
    $user_id = get_current_user_id();
    
    // Get institution data
    $institution = attentrack_get_institution($user_id);
    
    // If institution doesn't exist in the new table yet, create it
    if (!$institution) {
        $institution_id = attentrack_create_or_update_institution($user_id);
        if ($institution_id) {
            $institution = attentrack_get_institution($user_id);
        }
    }
    
    // Get subscription data
    $subscription = attentrack_get_subscription_status($user_id);

    // Debug logging
    error_log('Institution AJAX - User ID: ' . $user_id);
    error_log('Institution AJAX - Subscription data: ' . print_r($subscription, true));

    // Update institution member limit from subscription
    if (isset($subscription['member_limit']) && $institution) {
        global $wpdb;
        $update_result = $wpdb->update(
            $wpdb->prefix . 'attentrack_institutions',
            ['member_limit' => $subscription['member_limit']],
            ['id' => $institution['id']]
        );

        if ($update_result === false) {
            error_log('Failed to update institution member limit: ' . $wpdb->last_error);
        }

        // Update the institution data
        $institution['member_limit'] = $subscription['member_limit'];
    }

    // Validate subscription data before sending
    if (!$subscription || !is_array($subscription)) {
        error_log('Invalid subscription data for user ' . $user_id);
        $subscription = [
            'has_subscription' => false,
            'plan_name' => 'Free',
            'plan_name_formatted' => 'Free',
            'status' => 'inactive',
            'member_limit' => 1,
            'members_used' => 0
        ];
    }

    // Return combined data
    wp_send_json_success([
        'institution' => $institution,
        'subscription' => $subscription,
        'debug' => [
            'user_id' => $user_id,
            'has_subscription' => isset($subscription['has_subscription']) ? $subscription['has_subscription'] : false,
            'plan_name' => isset($subscription['plan_name']) ? $subscription['plan_name'] : 'unknown'
        ]
    ]);
}

function institution_get_users_ajax() {
    // Verify user is logged in and has institution access
    if (!is_user_logged_in() || (!current_user_can('institution') && !current_user_can('manage_institution_users'))) {
        wp_send_json_error(['message' => 'Unauthorized access'], 403);
        exit;
    }
    
    $user_id = get_current_user_id();
    
    // Get institution data
    $institution = attentrack_get_institution($user_id);
    
    // If institution doesn't exist in the new table yet, create it
    if (!$institution) {
        $institution_id = attentrack_create_or_update_institution($user_id);
        if ($institution_id) {
            $institution = attentrack_get_institution($user_id);
        } else {
            wp_send_json_error(['message' => 'Institution not found'], 404);
            exit;
        }
    }
    
    // Get institution members
    $members = attentrack_get_institution_members($institution['id']);
    
    // Format data for response
    $users = [];
    foreach ($members as $member) {
        $user_data = get_userdata($member['user_id']);
        if ($user_data) {
            // Get last active time
            $last_active = get_user_meta($member['user_id'], 'last_active', true);
            $last_active_formatted = $last_active ? date('Y-m-d H:i:s', $last_active) : 'Never';
            
            // Get tests taken count
            global $wpdb;
            $tests_taken = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}attentrack_selective_results WHERE user_id = %d",
                $member['user_id']
            ));
            
            // Get added_by username instead of ID
            $added_by_user = null;
            if (!empty($member['added_by'])) {
                $added_by_user = get_userdata($member['added_by']);
            }
            $added_by_name = $added_by_user ? $added_by_user->display_name : 'Unknown';
            
            $users[] = [
                'id' => $member['user_id'],
                'name' => $user_data->display_name,
                'email' => $user_data->user_email,
                'phone' => get_user_meta($member['user_id'], 'phone', true),
                'role' => $member['role'],
                'last_active' => $last_active_formatted,
                'tests_taken' => $tests_taken,
                'status' => $member['status'],
                'added_by' => $added_by_name,
                'added_by_id' => $member['added_by']
            ];
        }
    }
    
    wp_send_json_success(['users' => $users]);
}

function institution_get_analytics_ajax() {
    // Verify user is logged in and has institution access
    if (!is_user_logged_in() || (!current_user_can('institution') && !current_user_can('view_institution_analytics'))) {
        wp_send_json_error(['message' => 'Unauthorized access'], 403);
        exit;
    }
    
    $user_id = get_current_user_id();
    
    // Get institution data
    $institution = attentrack_get_institution($user_id);
    
    // If institution doesn't exist in the new table yet, create it
    if (!$institution) {
        $institution_id = attentrack_create_or_update_institution($user_id);
        if ($institution_id) {
            $institution = attentrack_get_institution($user_id);
        } else {
            wp_send_json_error(['message' => 'Institution not found'], 404);
            exit;
        }
    }
    
    // Get analytics data
    $analytics = attentrack_get_institution_analytics($institution['id']);
    
    wp_send_json_success(['analytics' => $analytics]);
}

function institution_add_user_ajax() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'institution_add_user_nonce')) {
        wp_send_json_error(['message' => 'Invalid security token'], 403);
        exit;
    }
    
    // Verify user is logged in and has institution access
    if (!is_user_logged_in() || (!current_user_can('institution') && !current_user_can('manage_institution_users'))) {
        wp_send_json_error(['message' => 'Unauthorized access'], 403);
        exit;
    }
    
    // Validate required fields
    if (!isset($_POST['name']) || !isset($_POST['email'])) {
        wp_send_json_error(['message' => 'Missing required fields'], 400);
        exit;
    }
    
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $role = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : 'member';
    
    $institution_id = get_current_user_id();
    
    // Get institution data
    $institution = attentrack_get_institution($institution_id);
    
    // If institution doesn't exist in the new table yet, create it
    if (!$institution) {
        $new_institution_id = attentrack_create_or_update_institution($institution_id);
        if ($new_institution_id) {
            $institution = attentrack_get_institution($institution_id);
        } else {
            wp_send_json_error(['message' => 'Institution not found'], 404);
            exit;
        }
    }
    
    // Check subscription limits
    $subscription = attentrack_get_subscription_status($institution_id);
    $current_members = $institution['members_used'];
    
    if (isset($subscription['member_limit']) && $current_members >= $subscription['member_limit']) {
        wp_send_json_error(['message' => 'Member limit reached. Please upgrade your subscription.'], 400);
        exit;
    }
    
    // Check if user already exists
    $existing_user = get_user_by('email', $email);
    
    if ($existing_user) {
        // Add existing user to institution
        $result = attentrack_add_institution_member($institution['id'], $existing_user->ID, $role, $institution_id);

        // Clear user caches to ensure immediate effect
        wp_cache_delete($existing_user->ID, 'users');
        wp_cache_delete($existing_user->ID, 'user_meta');
        clean_user_cache($existing_user->ID);

        if ($result) {
            wp_send_json_success([
                'message' => 'User added successfully',
                'user' => [
                    'id' => $existing_user->ID,
                    'name' => $existing_user->display_name,
                    'email' => $existing_user->user_email,
                    'phone' => $phone,
                    'role' => $role
                ]
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to add user to institution'], 500);
        }
    } else {
        // Create new user
        $username = sanitize_user(current(explode('@', $email)));
        
        // Check if username already exists, if so, add a random number
        if (username_exists($username)) {
            $username = $username . rand(100, 999);
        }
        
        $random_password = wp_generate_password();
        $user_id = wp_create_user($username, $random_password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => $user_id->get_error_message()], 400);
            exit;
        }
        
        // Set user role based on the role parameter
        $user = new WP_User($user_id);

        // Map institution roles to WordPress roles
        $role_map = array(
            'client' => 'client',
            'staff' => 'staff',
            'admin' => 'institution_admin',
            'member' => 'subscriber'
        );

        $wp_role = isset($role_map[$role]) ? $role_map[$role] : 'subscriber';
        $user->set_role($wp_role);
        
        // Update user meta
        wp_update_user([
            'ID' => $user_id,
            'display_name' => $name
        ]);
        
        if ($phone) {
            update_user_meta($user_id, 'phone', $phone);
        }
        
        // Generate unique IDs for the user
        $profile_id = 'P' . sprintf('%04d', $user_id);
        $test_id = 'T' . sprintf('%04d', $user_id);
        $user_code = 'U' . sprintf('%04d', $user_id);

        // Save the IDs as user meta
        update_user_meta($user_id, 'profile_id', $profile_id);
        update_user_meta($user_id, 'test_id', $test_id);
        update_user_meta($user_id, 'user_code', $user_code);

        // Set account type based on role
        $account_type = ($wp_role === 'institution_admin') ? 'institution' : 'user';
        update_user_meta($user_id, 'account_type', $account_type);
        update_user_meta($user_id, 'institution_id', $institution_id);

        // Create user record in the patient_details table (keeping for backward compatibility)
        global $wpdb;
        $patient_table = $wpdb->prefix . 'attentrack_patient_details';

        $wpdb->insert(
            $patient_table,
            array(
                'patient_id' => $profile_id,
                'test_id' => $test_id,
                'user_code' => $user_code,
                'first_name' => $name,
                'last_name' => '',
                'email' => $email,
                'phone' => $phone,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        // Add user to institution with correct role
        $result = attentrack_add_institution_member($institution['id'], $user_id, $role, $institution_id);

        // Clear user caches to ensure immediate effect
        wp_cache_delete($user_id, 'users');
        wp_cache_delete($user_id, 'user_meta');
        clean_user_cache($user_id);

        if ($result) {
            // Send welcome email with login details
            $institution_name = get_user_meta($institution_id, 'institution_name', true);
            if (empty($institution_name)) {
                $institution_user = get_userdata($institution_id);
                $institution_name = $institution_user ? $institution_user->display_name : 'your institution';
            }
            
            $subject = 'Welcome to AttenTrack - Your Account Details';
            $message = "Hello $name,\n\n";
            $message .= "Welcome to AttenTrack! Your account has been created by $institution_name.\n\n";
            $message .= "Here are your login details:\n";
            $message .= "Username: $username\n";
            $message .= "Password: $random_password\n\n";
            $message .= "Please login at " . home_url('/signin') . " and change your password after your first login.\n\n";
            $message .= "Thank you,\nAttenTrack Team";
            
            wp_mail($email, $subject, $message);
            
            wp_send_json_success([
                'message' => 'User created and added successfully',
                'user' => [
                    'id' => $user_id,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'role' => 'patient'
                ]
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to add user to institution'], 500);
        }
    }
}

function institution_remove_user_ajax() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'institution_remove_user_nonce')) {
        wp_send_json_error(['message' => 'Invalid security token'], 403);
        exit;
    }
    
    // Verify user is logged in and has institution access
    if (!is_user_logged_in() || (!current_user_can('institution') && !current_user_can('manage_institution_users'))) {
        wp_send_json_error(['message' => 'Unauthorized access'], 403);
        exit;
    }
    
    // Validate required fields
    if (!isset($_POST['user_id'])) {
        wp_send_json_error(['message' => 'Missing user ID'], 400);
        exit;
    }
    
    $user_id = intval($_POST['user_id']);
    $institution_id = get_current_user_id();
    
    // Get institution data
    $institution = attentrack_get_institution($institution_id);
    
    // If institution doesn't exist in the new table yet, create it
    if (!$institution) {
        $new_institution_id = attentrack_create_or_update_institution($institution_id);
        if ($new_institution_id) {
            $institution = attentrack_get_institution($institution_id);
        } else {
            wp_send_json_error(['message' => 'Institution not found'], 404);
            exit;
        }
    }
    
    // Remove user from institution
    $result = attentrack_remove_institution_member($institution['id'], $user_id);
    
    if ($result) {
        wp_send_json_success(['message' => 'User removed successfully']);
    } else {
        wp_send_json_error(['message' => 'Failed to remove user from institution'], 500);
    }
}

function institution_edit_details_handler() {
    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'institution_edit_details_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }
    
    // Check if user is logged in and has institution access
    if (!is_user_logged_in() || (!current_user_can('institution') && !current_user_can('configure_institution_settings'))) {
        wp_send_json_error(array('message' => 'Permission denied'));
        return;
    }
    
    // Get current user
    $current_user = wp_get_current_user();
    $institution_id = $current_user->ID;
    
    // Get form data
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $address = isset($_POST['address']) ? sanitize_text_field($_POST['address']) : '';
    $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
    $state = isset($_POST['state']) ? sanitize_text_field($_POST['state']) : '';
    $zip = isset($_POST['zip']) ? sanitize_text_field($_POST['zip']) : '';
    $country = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '';
    $website = isset($_POST['website']) ? esc_url_raw($_POST['website']) : '';
    
    // Validate required fields
    if (empty($name) || empty($email)) {
        wp_send_json_error(array('message' => 'Name and email are required'));
        return;
    }
    
    // Update user data
    $user_data = array(
        'ID' => $institution_id,
        'display_name' => $name,
        'user_email' => $email
    );
    
    $user_id = wp_update_user($user_data);
    
    if (is_wp_error($user_id)) {
        wp_send_json_error(array('message' => $user_id->get_error_message()));
        return;
    }
    
    // Update user meta
    update_user_meta($institution_id, 'phone', $phone);
    update_user_meta($institution_id, 'address', $address);
    update_user_meta($institution_id, 'city', $city);
    update_user_meta($institution_id, 'state', $state);
    update_user_meta($institution_id, 'zip', $zip);
    update_user_meta($institution_id, 'country', $country);
    update_user_meta($institution_id, 'website', $website);
    
    // Update institution record in database if it exists
    global $wpdb;
    $institution_table = $wpdb->prefix . 'attentrack_institutions';
    
    $institution = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $institution_table WHERE user_id = %d",
        $institution_id
    ));
    
    if ($institution) {
        $wpdb->update(
            $institution_table,
            array(
                'institution_name' => $name,
                'contact_email' => $email,
                'contact_phone' => $phone,
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'postal_code' => $zip,
                'country' => $country,
                'website' => $website,
                'updated_at' => current_time('mysql')
            ),
            array('user_id' => $institution_id),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
    }
    
    wp_send_json_success(array('message' => 'Institution details updated successfully'));
}
