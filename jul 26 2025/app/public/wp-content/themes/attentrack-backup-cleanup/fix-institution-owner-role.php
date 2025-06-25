<?php
/**
 * Fix Institution Owner Role
 * This script will restore the correct role for institution owners who were incorrectly set to subscriber
 */

// Prevent direct access and avoid header issues
if (!defined('ABSPATH')) {
    define('WP_USE_THEMES', false);
    require_once '../../../wp-load.php';
}

// Start output buffering to prevent header issues
ob_start();

echo "<h1>Fix Institution Owner Role</h1>";

// Function to fix institution owner roles
function fix_institution_owner_roles() {
    global $wpdb;
    
    echo "<h2>Scanning for Institution Owners with Incorrect Roles</h2>";
    
    // Get all users who have institution data but are subscribers
    $institution_owners = $wpdb->get_results("
        SELECT u.ID, u.user_login, u.display_name, u.user_email, i.id as institution_id, i.institution_name
        FROM {$wpdb->users} u
        INNER JOIN {$wpdb->prefix}attentrack_institutions i ON u.ID = i.user_id
        WHERE u.ID IN (
            SELECT user_id FROM {$wpdb->usermeta} 
            WHERE meta_key = '{$wpdb->prefix}capabilities' 
            AND meta_value LIKE '%subscriber%'
        )
    ");
    
    if (empty($institution_owners)) {
        echo "✅ No institution owners found with incorrect subscriber role.<br>";
        return;
    }
    
    echo "<p>Found " . count($institution_owners) . " institution owner(s) with incorrect subscriber role:</p>";
    
    foreach ($institution_owners as $owner) {
        echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #007cba;'>";
        echo "<strong>User:</strong> {$owner->display_name} ({$owner->user_login})<br>";
        echo "<strong>Email:</strong> {$owner->user_email}<br>";
        echo "<strong>Institution:</strong> {$owner->institution_name}<br>";
        echo "<strong>User ID:</strong> {$owner->ID}<br>";
        
        // Fix the role
        $user = get_userdata($owner->ID);
        if ($user) {
            // Remove subscriber role
            $user->remove_role('subscriber');
            
            // Add institution_admin role
            $user->add_role('institution_admin');
            
            echo "<strong>✅ Fixed:</strong> Changed role from 'subscriber' to 'institution_admin'<br>";
        } else {
            echo "<strong>❌ Error:</strong> Could not load user data<br>";
        }
        echo "</div>";
    }
    
    return count($institution_owners);
}

// Check current user first
if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    echo "<h2>Current User Check</h2>";
    echo "<strong>You are:</strong> {$current_user->display_name} (ID: {$current_user->ID})<br>";
    echo "<strong>Current roles:</strong> " . implode(', ', $current_user->roles) . "<br>";
}

// Run the fix
$fixed_count = fix_institution_owner_roles();

if ($fixed_count > 0) {
    echo "<div style='background: #d4edda; padding: 20px; margin: 20px 0; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<h3>✅ Success!</h3>";
    echo "<p><strong>Fixed {$fixed_count} institution owner role(s).</strong></p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li><strong>Log out and log back in</strong> to refresh your session</li>";
    echo "<li><strong>Clear browser cache</strong> (Ctrl+F5 or Cmd+Shift+R)</li>";
    echo "<li><strong>Navigate to:</strong> <a href='" . home_url('/dashboard?type=institution') . "'>/dashboard?type=institution</a></li>";
    echo "<li><strong>Verify:</strong> You should now have full access to the institution dashboard</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 20px; margin: 20px 0; border: 1px solid #ffeaa7; border-radius: 5px;'>";
    echo "<h3>⚠️ No Issues Found</h3>";
    echo "<p>No institution owners were found with incorrect subscriber roles.</p>";
    echo "<p>If you're still having access issues, there might be a different problem.</p>";
    echo "</div>";
}

// Additional verification
echo "<h2>Verification</h2>";
if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    
    // Refresh user data
    wp_cache_delete($current_user->ID, 'users');
    wp_cache_delete($current_user->ID, 'user_meta');
    $current_user = get_userdata($current_user->ID);
    
    echo "<strong>Your updated roles:</strong> " . implode(', ', $current_user->roles) . "<br>";
    
    $institution_caps = [
        'access_institution_dashboard',
        'manage_institution_users', 
        'view_institution_analytics',
        'configure_institution_settings'
    ];
    
    echo "<strong>Your capabilities:</strong><br>";
    foreach ($institution_caps as $cap) {
        $has_cap = user_can($current_user, $cap);
        $status = $has_cap ? '✅' : '❌';
        echo "$status $cap<br>";
    }
}

echo "<h2>Troubleshooting</h2>";
echo "<p>If you still can't access the institution dashboard after following the steps above:</p>";
echo "<ul>";
echo "<li><strong>Check browser console</strong> for any JavaScript errors</li>";
echo "<li><strong>Try incognito/private browsing</strong> to rule out cache issues</li>";
echo "<li><strong>Contact system administrator</strong> if problems persist</li>";
echo "</ul>";

// Flush output buffer
ob_end_flush();
?>
