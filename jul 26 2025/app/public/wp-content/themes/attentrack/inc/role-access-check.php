<?php
/**
 * Role Access Checks
 * 
 * This file handles role-based access checks and duplicate user prevention
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include multi-tier roles functions
require_once(dirname(__FILE__) . '/multi-tier-roles.php');

/**
 * Check if user is trying to access a dashboard they shouldn't have access to
 * 
 * @param string $account_type The account type being used for login
 * @param WP_User $user The WordPress user object
 * @return array|bool Returns false if access is allowed, or an error array if access is denied
 */
function check_role_access($account_type, $user) {
    if (!$user || !is_a($user, 'WP_User')) {
        return array(
            'error' => true,
            'message' => 'Invalid user account'
        );
    }
    
    $has_institution_role = in_array('institution', (array) $user->roles);
    $is_admin = in_array('administrator', (array) $user->roles);
    
    // Admins can access everything
    if ($is_admin) {
        return false;
    }
    
    // Get the stored account type from user meta
    $stored_account_type = get_user_meta($user->ID, 'account_type', true);
    
    // Fix inconsistency between role and account_type if needed
    if ($has_institution_role && $stored_account_type === 'user') {
        // User has institution role but account_type is 'user' - fix this inconsistency
        update_user_meta($user->ID, 'account_type', 'institution');
        $stored_account_type = 'institution';
    } else if (!$has_institution_role && $stored_account_type === 'institution') {
        // User has regular role but account_type is 'institution' - fix this inconsistency
        update_user_meta($user->ID, 'account_type', 'user');
        $stored_account_type = 'user';
    }
    
    // Check if account type matches user's role
    if ($account_type === 'institution' && !$has_institution_role) {
        return array(
            'error' => true,
            'message' => 'You are trying to sign in as an institution, but your account is registered as a regular user. Please sign in with the correct account type.'
        );
    }
    
    if ($account_type === 'user' && $has_institution_role) {
        return array(
            'error' => true,
            'message' => 'You are trying to sign in as a regular user, but your account is registered as an institution. Please sign in with the correct account type.'
        );
    }
    
    // Access is allowed
    return false;
}

/**
 * Check for duplicate user data during registration
 * 
 * @param array $user_data The user data to check for duplicates
 * @return array|bool Returns false if no duplicates found, or an error array if duplicates exist
 */
function check_duplicate_user($user_data) {
    global $wpdb;
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    
    // Check for duplicate email
    if (!empty($user_data['email'])) {
        $email = $user_data['email'];
        
        // Check WordPress users table
        if (email_exists($email)) {
            return array(
                'error' => true,
                'message' => 'This email address is already registered. Please use a different email or sign in with your existing account.'
            );
        }
        
        // Check our custom table
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $user_data_table WHERE email = %s",
            $email
        ));
        
        if ($exists) {
            return array(
                'error' => true,
                'message' => 'This email address is already registered. Please use a different email or sign in with your existing account.'
            );
        }
    }
    
    // Check for duplicate phone number
    if (!empty($user_data['phone_number'])) {
        $phone = $user_data['phone_number'];
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $user_data_table WHERE phone_number = %s",
            $phone
        ));
        
        if ($exists) {
            return array(
                'error' => true,
                'message' => 'This phone number is already registered. Please use a different phone number or sign in with your existing account.'
            );
        }
    }
    
    // Check for duplicate username
    if (!empty($user_data['username'])) {
        $username = $user_data['username'];
        
        // Check WordPress users table
        if (username_exists($username)) {
            return array(
                'error' => true,
                'message' => 'This username is already taken. Please choose a different username.'
            );
        }
        
        // Check our custom table
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $user_data_table WHERE username = %s",
            $username
        ));
        
        if ($exists) {
            return array(
                'error' => true,
                'message' => 'This username is already taken. Please choose a different username.'
            );
        }
    }
    
    // No duplicates found
    return false;
}

/**
 * Get appropriate redirect URL based on user role
 * 
 * @param WP_User $user The WordPress user object
 * @param string $account_type The account type being used for login
 * @return string The redirect URL
 */
function get_role_redirect_url($user, $account_type = null) {
    if (!$user || !is_a($user, 'WP_User')) {
        return home_url('/signin');
    }
    
    if (in_array('administrator', (array) $user->roles)) {
        return admin_url();
    } else if ($account_type === 'institution' || in_array('institution', (array) $user->roles)) {
        return home_url('/dashboard?type=institution');
    } else {
        return home_url('/dashboard?type=patient');
    }
}

/**
 * Update a user's role and account type in all relevant tables
 *
 * @param int $user_id The user ID to update
 * @param string $new_role The new role to set (client, staff, institution_admin, subscriber, administrator)
 * @param string $new_account_type The new account type to set (user or institution)
 * @return bool True on success, false on failure
 */
function update_user_role_and_account_type($user_id, $new_role, $new_account_type) {
    global $wpdb;

    if (!$user_id) {
        return false;
    }

    // Validate role
    $valid_roles = array('client', 'staff', 'institution_admin', 'subscriber', 'administrator');
    if (!in_array($new_role, $valid_roles)) {
        error_log("Invalid role specified: $new_role");
        return false;
    }

    // Update WordPress role using proper WordPress functions
    $user = new WP_User($user_id);
    if (!$user->exists()) {
        return false;
    }

    // Set the new role (this removes all existing roles and sets the new one)
    $user->set_role($new_role);

    // Update the account_type in wp_usermeta
    update_user_meta($user_id, 'account_type', $new_account_type);

    // Update the account_type in wp_attentrack_user_data
    $wpdb->update(
        $wpdb->prefix . 'attentrack_user_data',
        array('account_type' => $new_account_type),
        array('user_id' => $user_id)
    );

    // Update role in institution members table if user is part of an institution
    $institution_id = attentrack_get_user_institution_id($user_id);
    if ($institution_id) {
        // Map WordPress roles to institution roles
        $institution_role_map = array(
            'client' => 'client',
            'staff' => 'staff',
            'institution_admin' => 'admin',
            'subscriber' => 'member',
            'administrator' => 'admin'
        );

        $institution_role = isset($institution_role_map[$new_role]) ? $institution_role_map[$new_role] : 'member';

        // Update institution members table
        $wpdb->update(
            $wpdb->prefix . 'attentrack_institution_members',
            array('role' => $institution_role),
            array('user_id' => $user_id)
        );
    }

    // Clear user caches to ensure immediate effect
    wp_cache_delete($user_id, 'users');
    wp_cache_delete($user_id, 'user_meta');
    clean_user_cache($user_id);

    // Log the role change for audit purposes
    error_log("Role updated for user $user_id: $new_role (account_type: $new_account_type)");

    return true;
}


