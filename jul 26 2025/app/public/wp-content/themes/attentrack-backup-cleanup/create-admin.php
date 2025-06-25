<?php
/**
 * Create Emergency Admin User
 * This will create a temporary admin user to fix roles
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once('../../../wp-config.php');
require_once('../../../wp-load.php');

echo "<h1>Create Emergency Admin User</h1>";

// Check if admin user already exists
$admin_user = get_user_by('login', 'emergency_admin');

if ($admin_user) {
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px;'>";
    echo "<h3>⚠️ Admin User Already Exists</h3>";
    echo "<p><strong>Username:</strong> emergency_admin</p>";
    echo "<p><strong>Password:</strong> TempAdmin123!</p>";
    echo "<p><a href='" . admin_url() . "' target='_blank'>Click here to access WordPress Admin</a></p>";
    echo "</div>";
} else {
    // Create emergency admin user
    $user_data = array(
        'user_login' => 'emergency_admin',
        'user_pass' => 'TempAdmin123!',
        'user_email' => 'admin@attentrack.local',
        'display_name' => 'Emergency Admin',
        'role' => 'administrator'
    );
    
    $user_id = wp_insert_user($user_data);
    
    if (is_wp_error($user_id)) {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "<h3>❌ Error Creating Admin User</h3>";
        echo "<p>" . $user_id->get_error_message() . "</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        echo "<h3>✅ Emergency Admin User Created!</h3>";
        echo "<p><strong>Username:</strong> emergency_admin</p>";
        echo "<p><strong>Password:</strong> TempAdmin123!</p>";
        echo "<p><strong>Next Steps:</strong></p>";
        echo "<ol>";
        echo "<li><a href='" . admin_url() . "' target='_blank'>Click here to access WordPress Admin</a></li>";
        echo "<li>Log in with the credentials above</li>";
        echo "<li>Go to Users → All Users</li>";
        echo "<li>Find 'vasanthan22td0728' and edit the user</li>";
        echo "<li>Change role from 'Subscriber' to 'Institution Admin'</li>";
        echo "<li>Save changes</li>";
        echo "<li>Log out and log back in as vasanthan22td0728</li>";
        echo "</ol>";
        echo "</div>";
    }
}

echo "<h2>Manual Instructions</h2>";
echo "<p>Once you have admin access:</p>";
echo "<ol>";
echo "<li><strong>Go to:</strong> <a href='" . admin_url('users.php') . "' target='_blank'>Users → All Users</a></li>";
echo "<li><strong>Find:</strong> vasanthan22td0728 (User ID: 24)</li>";
echo "<li><strong>Click:</strong> Edit</li>";
echo "<li><strong>Change Role:</strong> From 'Subscriber' to 'Institution Admin'</li>";
echo "<li><strong>Click:</strong> Update User</li>";
echo "</ol>";

echo "<h2>After Fixing Your Role</h2>";
echo "<ol>";
echo "<li>Log out from the admin account</li>";
echo "<li>Log in as vasanthan22td0728</li>";
echo "<li>Navigate to: <a href='" . home_url('/dashboard?type=institution') . "'>/dashboard?type=institution</a></li>";
echo "<li>Verify you have full access</li>";
echo "<li>Delete the emergency admin user (optional)</li>";
echo "</ol>";

echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; margin-top: 20px;'>";
echo "<h3>🔒 Security Note</h3>";
echo "<p>Remember to delete the emergency admin user after fixing your role for security purposes.</p>";
echo "</div>";
?>
