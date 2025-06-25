<?php
/**
 * Fix User Role Script
 * 
 * This script updates the role and account type for a specific user
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );

// Include role access check functions
require_once( dirname( __FILE__ ) . '/inc/role-access-check.php' );

// User ID to update
$user_id = 21; // blackloverz333

// Update the user's role and account type
$result = update_user_role_and_account_type($user_id, 'subscriber', 'user');

if ($result) {
    echo "Successfully updated user ID $user_id to subscriber role and user account type.";
} else {
    echo "Failed to update user ID $user_id.";
}

// Check the current status
$user = get_user_by('ID', $user_id);
if ($user) {
    echo "\n\nCurrent user details:";
    echo "\nUsername: " . $user->user_login;
    echo "\nRoles: " . implode(', ', $user->roles);
    echo "\nAccount Type: " . get_user_meta($user_id, 'account_type', true);
    
    global $wpdb;
    $user_data = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}attentrack_user_data WHERE user_id = %d",
        $user_id
    ));
    
    if ($user_data) {
        echo "\nConsolidated table account_type: " . $user_data->account_type;
    } else {
        echo "\nUser not found in consolidated table.";
    }
}
