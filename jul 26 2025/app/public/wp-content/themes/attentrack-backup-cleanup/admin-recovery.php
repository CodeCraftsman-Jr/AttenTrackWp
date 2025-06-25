<?php
/**
 * Admin Recovery Script
 * 
 * This script creates a new administrator user or updates an existing user to have admin privileges.
 * IMPORTANT: Delete this file after use for security reasons.
 */

// Prevent direct access unless explicitly allowed
if (!defined('ADMIN_RECOVERY_ALLOWED') || !ADMIN_RECOVERY_ALLOWED) {
    // Define a constant to allow access when running the script directly
    define('ADMIN_RECOVERY_ALLOWED', true);
    
    // Check if this is a direct script access
    if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
        // Load WordPress core
        require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');
    } else {
        // If included from another file but not allowed, exit
        exit('Access denied.');
    }
}

// Set up admin details
$admin_username = 'admin_recovery';
$admin_email = 'admin@example.com';
$admin_password = wp_generate_password(16, true, true);
$admin_display_name = 'Admin Recovery';

// Check if user already exists
$existing_user = get_user_by('login', $admin_username);
$existing_email_user = get_user_by('email', $admin_email);

if ($existing_user) {
    // Update existing user to have admin role
    $user_id = $existing_user->ID;
    $user = new WP_User($user_id);
    $user->set_role('administrator');
    
    // Reset password
    wp_set_password($admin_password, $user_id);
    
    echo "<p>Existing user <strong>{$admin_username}</strong> has been updated with administrator privileges.</p>";
} elseif ($existing_email_user) {
    // Update existing user by email to have admin role
    $user_id = $existing_email_user->ID;
    $user = new WP_User($user_id);
    $user->set_role('administrator');
    
    // Reset password
    wp_set_password($admin_password, $user_id);
    
    echo "<p>Existing user <strong>{$existing_email_user->user_login}</strong> has been updated with administrator privileges.</p>";
} else {
    // Create new admin user
    $user_id = wp_create_user($admin_username, $admin_password, $admin_email);
    
    if (is_wp_error($user_id)) {
        echo "<p>Error creating admin user: " . $user_id->get_error_message() . "</p>";
    } else {
        // Set role to administrator
        $user = new WP_User($user_id);
        $user->set_role('administrator');
        
        // Update display name
        wp_update_user([
            'ID' => $user_id,
            'display_name' => $admin_display_name
        ]);
        
        echo "<p>New administrator user has been created successfully!</p>";
    }
}

// Display login credentials
if (isset($user_id) && !is_wp_error($user_id)) {
    echo "<div style='background-color: #f8f9fa; border: 1px solid #ddd; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>Admin Login Credentials</h3>";
    echo "<p><strong>Username:</strong> " . (isset($existing_email_user) ? $existing_email_user->user_login : $admin_username) . "</p>";
    echo "<p><strong>Password:</strong> {$admin_password}</p>";
    echo "<p><strong>Login URL:</strong> <a href='" . wp_login_url() . "'>" . wp_login_url() . "</a></p>";
    echo "<p style='color: red;'><strong>IMPORTANT:</strong> Please save these credentials immediately and delete this file after use!</p>";
    echo "</div>";
}

// Add delete button
echo "<form method='post'>";
echo "<input type='hidden' name='delete_recovery_script' value='1'>";
echo "<button type='submit' style='background-color: #dc3545; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;'>Delete This Script</button>";
echo "</form>";

// Handle script deletion
if (isset($_POST['delete_recovery_script'])) {
    if (unlink(__FILE__)) {
        echo "<p style='color: green;'>Recovery script has been deleted successfully.</p>";
        echo "<script>setTimeout(function() { window.location.href = '" . wp_login_url() . "'; }, 3000);</script>";
        echo "<p>Redirecting to login page in 3 seconds...</p>";
    } else {
        echo "<p style='color: red;'>Failed to delete recovery script. Please delete it manually.</p>";
    }
}
?>
