<?php
/**
 * Check and Fix User Role Issues
 * This script will help diagnose and fix user role problems
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once '../../../wp-load.php';
}

echo "<h1>AttenTrack User Role Diagnostic</h1>";

// Check if user is logged in
if (!is_user_logged_in()) {
    echo "❌ You are not logged in. Please log in first.";
    exit;
}

$current_user = wp_get_current_user();

echo "<h2>Current User Information</h2>";
echo "<strong>User ID:</strong> " . $current_user->ID . "<br>";
echo "<strong>Username:</strong> " . $current_user->user_login . "<br>";
echo "<strong>Display Name:</strong> " . $current_user->display_name . "<br>";
echo "<strong>Email:</strong> " . $current_user->user_email . "<br>";
echo "<strong>Current Roles:</strong> " . implode(', ', $current_user->roles) . "<br>";

echo "<h2>Available Roles in System</h2>";
$all_roles = wp_roles()->roles;
foreach ($all_roles as $role_key => $role_info) {
    echo "• <strong>$role_key</strong>: " . $role_info['name'] . "<br>";
}

echo "<h2>Institution-Related Capabilities</h2>";
$institution_caps = [
    'institution',
    'access_institution_dashboard',
    'manage_institution_users',
    'view_institution_analytics',
    'configure_institution_settings',
    'access_subscription_management',
    'manage_billing'
];

foreach ($institution_caps as $cap) {
    $has_cap = current_user_can($cap);
    $status = $has_cap ? '✅' : '❌';
    echo "$status $cap<br>";
}

echo "<h2>Institution Data Check</h2>";
// Check if user has institution data
if (function_exists('attentrack_get_institution')) {
    $institution = attentrack_get_institution($current_user->ID);
    if ($institution) {
        echo "✅ Institution data found:<br>";
        echo "• Institution ID: " . $institution['id'] . "<br>";
        echo "• Institution Name: " . $institution['institution_name'] . "<br>";
        echo "• Status: " . $institution['status'] . "<br>";
    } else {
        echo "❌ No institution data found for this user<br>";
    }
}

echo "<h2>Role Fix Options</h2>";

// If user is subscriber but should be institution owner
if (in_array('subscriber', $current_user->roles) && !in_array('institution_admin', $current_user->roles)) {
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>⚠️ Issue Detected:</strong> You have 'subscriber' role but appear to be an institution owner.<br>";
    echo "<strong>Recommended Action:</strong> Change your role to 'institution_admin'<br>";
    echo "</div>";
    
    if (current_user_can('administrator')) {
        echo "<h3>Admin Fix (You can do this)</h3>";
        echo "<p>Since you're an administrator, you can fix this yourself:</p>";
        echo "<form method='post' style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
        echo "<input type='hidden' name='action' value='fix_user_role'>";
        echo "<input type='hidden' name='user_id' value='" . $current_user->ID . "'>";
        wp_nonce_field('fix_user_role_nonce', 'fix_user_role_nonce');
        echo "<label><input type='radio' name='new_role' value='institution_admin' checked> Institution Admin (Recommended for institution owners)</label><br>";
        echo "<label><input type='radio' name='new_role' value='staff'> Staff (Institution employee)</label><br>";
        echo "<label><input type='radio' name='new_role' value='client'> Client (Test taker)</label><br>";
        echo "<label><input type='radio' name='new_role' value='administrator'> Administrator (Full system access)</label><br><br>";
        echo "<input type='submit' value='Fix My Role' class='button button-primary' style='background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer;'>";
        echo "</form>";
    } else {
        echo "<h3>Contact Administrator</h3>";
        echo "<p>You'll need an administrator to change your role. Show them this information:</p>";
        echo "<div style='background: #f1f1f1; padding: 10px; font-family: monospace;'>";
        echo "User ID: " . $current_user->ID . "<br>";
        echo "Current Role: " . implode(', ', $current_user->roles) . "<br>";
        echo "Recommended Role: institution_admin<br>";
        echo "</div>";
    }
}

// Handle form submission
if (isset($_POST['action']) && $_POST['action'] === 'fix_user_role' && wp_verify_nonce($_POST['fix_user_role_nonce'], 'fix_user_role_nonce')) {
    if (current_user_can('administrator')) {
        $user_id = intval($_POST['user_id']);
        $new_role = sanitize_text_field($_POST['new_role']);
        
        // Include the role access check functions
        require_once(get_template_directory() . '/inc/role-access-check.php');

        // Determine account type based on role
        $account_type = ($new_role === 'institution_admin') ? 'institution' : 'user';

        // Update user role using the comprehensive function
        $result = update_user_role_and_account_type($user_id, $new_role, $account_type);

        if ($result) {
            echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ <strong>Role Updated Successfully!</strong><br>";
            echo "User role changed to: <strong>$new_role</strong><br>";
            echo "Account type set to: <strong>$account_type</strong><br>";
            echo "Please refresh this page to see the updated information.";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;'>";
            echo "❌ <strong>Role Update Failed!</strong><br>";
            echo "Please check the error logs for more details.";
            echo "</div>";
        }
    }
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>If your role was fixed above, refresh your dashboard page</li>";
echo "<li>Clear browser cache and try accessing /dashboard?type=institution</li>";
echo "<li>If you still have issues, contact your system administrator</li>";
echo "</ol>";
?>
