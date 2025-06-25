<?php
/**
 * Dashboard Access Control
 * 
 * This file handles access control for dashboard pages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if the current user has access to the institution dashboard
 * If not, redirect them to the appropriate dashboard
 */
function enforce_institution_dashboard_access() {
    // Only run this check on the institution dashboard page
    if (!is_page_template('institution-dashboard-template.php')) {
        return;
    }
    
    // If user is not logged in, redirect to login page
    if (!is_user_logged_in()) {
        wp_redirect(home_url('/signin'));
        exit;
    }
    
    // Get current user
    $current_user = wp_get_current_user();
    
    // Admins can access everything
    if (in_array('administrator', (array) $current_user->roles)) {
        return;
    }
    
    // Check if user has institution role
    $has_institution_role = in_array('institution', (array) $current_user->roles);
    
    // Get the stored account type from user meta
    $stored_account_type = get_user_meta($current_user->ID, 'account_type', true);
    
    // If user doesn't have institution role, redirect to patient dashboard
    if (!$has_institution_role) {
        // Log the access attempt
        error_log('User ' . $current_user->user_login . ' (ID: ' . $current_user->ID . ') attempted to access institution dashboard but has roles: ' . implode(', ', $current_user->roles));
        
        // Show an error message
        wp_die('You do not have permission to access the institution dashboard. <a href="' . home_url('/dashboard?type=patient') . '">Go to your dashboard</a>');
        exit;
    }
}
add_action('template_redirect', 'enforce_institution_dashboard_access');

/**
 * Check if the current user has access to the patient dashboard
 * If not, redirect them to the appropriate dashboard
 */
function enforce_patient_dashboard_access() {
    // Only run this check on the patient dashboard page
    if (!is_page_template('patient-dashboard-template.php')) {
        return;
    }
    
    // If user is not logged in, redirect to login page
    if (!is_user_logged_in()) {
        wp_redirect(home_url('/signin'));
        exit;
    }
    
    // Get current user
    $current_user = wp_get_current_user();
    
    // Admins can access everything
    if (in_array('administrator', (array) $current_user->roles)) {
        return;
    }
    
    // Check if user has institution role
    $has_institution_role = in_array('institution', (array) $current_user->roles);
    
    // If user has institution role, redirect to institution dashboard
    if ($has_institution_role) {
        // Log the access attempt
        error_log('User ' . $current_user->user_login . ' (ID: ' . $current_user->ID . ') attempted to access patient dashboard but has roles: ' . implode(', ', $current_user->roles));
        
        // Show an error message
        wp_die('You do not have permission to access the patient dashboard. <a href="' . home_url('/dashboard?type=institution') . '">Go to your dashboard</a>');
        exit;
    }
}
add_action('template_redirect', 'enforce_patient_dashboard_access');

/**
 * Fix inconsistencies between user roles and account types
 * Run this on every page load to ensure data consistency
 */
function fix_user_role_account_type_inconsistencies() {
    // Only run this for logged-in users
    if (!is_user_logged_in()) {
        return;
    }
    
    // Get current user
    $current_user = wp_get_current_user();
    
    // Admins are excluded from this check
    if (in_array('administrator', (array) $current_user->roles)) {
        return;
    }
    
    // Check if user has institution role
    $has_institution_role = in_array('institution', (array) $current_user->roles);
    
    // Get the stored account type from user meta
    $stored_account_type = get_user_meta($current_user->ID, 'account_type', true);
    
    // Fix inconsistency between role and account_type if needed
    if ($has_institution_role && $stored_account_type === 'user') {
        // User has institution role but account_type is 'user' - fix this inconsistency
        update_user_meta($current_user->ID, 'account_type', 'institution');
        
        // Log the fix
        error_log('Fixed inconsistency for user ' . $current_user->user_login . ' (ID: ' . $current_user->ID . '): Changed account_type from user to institution');
    } else if (!$has_institution_role && $stored_account_type === 'institution') {
        // User has regular role but account_type is 'institution' - fix this inconsistency
        update_user_meta($current_user->ID, 'account_type', 'user');
        
        // Log the fix
        error_log('Fixed inconsistency for user ' . $current_user->user_login . ' (ID: ' . $current_user->ID . '): Changed account_type from institution to user');
    }
    
    // Also update the consolidated user data table
    global $wpdb;
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    
    // Check if the table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$user_data_table'") == $user_data_table) {
        // Get the account type from the consolidated table
        $consolidated_account_type = $wpdb->get_var($wpdb->prepare(
            "SELECT account_type FROM $user_data_table WHERE user_id = %d",
            $current_user->ID
        ));
        
        // If there's an inconsistency in the consolidated table, fix it
        if ($has_institution_role && $consolidated_account_type === 'user') {
            $wpdb->update(
                $user_data_table,
                array('account_type' => 'institution'),
                array('user_id' => $current_user->ID)
            );
            
            // Log the fix
            error_log('Fixed inconsistency in consolidated table for user ' . $current_user->user_login . ' (ID: ' . $current_user->ID . '): Changed account_type from user to institution');
        } else if (!$has_institution_role && $consolidated_account_type === 'institution') {
            $wpdb->update(
                $user_data_table,
                array('account_type' => 'user'),
                array('user_id' => $current_user->ID)
            );
            
            // Log the fix
            error_log('Fixed inconsistency in consolidated table for user ' . $current_user->user_login . ' (ID: ' . $current_user->ID . '): Changed account_type from institution to user');
        }
    }
}
add_action('wp', 'fix_user_role_account_type_inconsistencies');
