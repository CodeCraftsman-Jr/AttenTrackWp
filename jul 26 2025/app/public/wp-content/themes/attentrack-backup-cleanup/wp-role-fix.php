<?php
/**
 * WordPress Role Fix - No Database Connection Required
 * This uses WordPress functions to fix your role
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once('../../../wp-config.php');
require_once('../../../wp-load.php');

// Prevent any output before we start
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Institution Owner Role</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>

<h1>WordPress Role Fix for Institution Owner</h1>

<?php

// Your user ID
$user_id = 24;

echo "<h2>Current Status Check</h2>";

// Get user data
$user = get_userdata($user_id);
if (!$user) {
    echo "<div class='error'>❌ User not found with ID: $user_id</div>";
    exit;
}

echo "<div class='info'>";
echo "<strong>User Found:</strong><br>";
echo "• ID: " . $user->ID . "<br>";
echo "• Username: " . $user->user_login . "<br>";
echo "• Display Name: " . $user->display_name . "<br>";
echo "• Email: " . $user->user_email . "<br>";
echo "• Current Roles: " . implode(', ', $user->roles) . "<br>";
echo "</div>";

// Check if user needs role fix
$needs_fix = in_array('subscriber', $user->roles) && !in_array('institution_admin', $user->roles);

if ($needs_fix) {
    echo "<div class='warning'>";
    echo "<strong>⚠️ Issue Detected:</strong> User has 'subscriber' role but should be 'institution_admin'<br>";
    echo "</div>";
    
    echo "<h2>Applying Fix</h2>";
    
    // Remove subscriber role
    $user->remove_role('subscriber');
    echo "✅ Removed 'subscriber' role<br>";
    
    // Add institution_admin role
    $user->add_role('institution_admin');
    echo "✅ Added 'institution_admin' role<br>";
    
    // Clear user cache
    wp_cache_delete($user_id, 'users');
    wp_cache_delete($user_id, 'user_meta');
    
    // Get updated user data
    $updated_user = get_userdata($user_id);
    
    echo "<div class='success'>";
    echo "<h3>✅ Role Fixed Successfully!</h3>";
    echo "<strong>Updated Roles:</strong> " . implode(', ', $updated_user->roles) . "<br>";
    echo "</div>";
    
    // Test capabilities
    echo "<h2>Capability Verification</h2>";
    $caps_to_test = [
        'access_institution_dashboard',
        'manage_institution_users',
        'view_institution_analytics',
        'configure_institution_settings'
    ];
    
    foreach ($caps_to_test as $cap) {
        $has_cap = user_can($updated_user, $cap);
        $status = $has_cap ? '✅' : '❌';
        echo "$status $cap<br>";
    }
    
    echo "<div class='success'>";
    echo "<h3>🎉 Success! Your role has been fixed.</h3>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li><strong>Log out completely</strong> from WordPress</li>";
    echo "<li><strong>Clear your browser cache</strong> (Ctrl+F5 or Cmd+Shift+R)</li>";
    echo "<li><strong>Log back in</strong> with your credentials</li>";
    echo "<li><strong>Navigate to:</strong> <a href='" . home_url('/dashboard?type=institution') . "'>/dashboard?type=institution</a></li>";
    echo "<li><strong>Verify:</strong> You should now have full access to the institution dashboard</li>";
    echo "</ol>";
    echo "</div>";
    
} else {
    echo "<div class='info'>";
    echo "<h3>ℹ️ No Fix Needed</h3>";
    echo "<p>User already has the correct role or doesn't need fixing.</p>";
    echo "<p>Current roles: " . implode(', ', $user->roles) . "</p>";
    echo "</div>";
}

echo "<h2>Institution Data Verification</h2>";

// Check institution data
global $wpdb;
$institution = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}attentrack_institutions WHERE user_id = %d",
    $user_id
));

if ($institution) {
    echo "<div class='info'>";
    echo "<strong>✅ Institution Data Found:</strong><br>";
    echo "• Institution ID: " . $institution->id . "<br>";
    echo "• Institution Name: " . $institution->institution_name . "<br>";
    echo "• Status: " . $institution->status . "<br>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<strong>⚠️ No Institution Data Found</strong><br>";
    echo "This might indicate a deeper issue with your account setup.";
    echo "</div>";
}

?>

<h2>Troubleshooting</h2>
<p>If you still can't access the institution dashboard after following the steps above:</p>
<ul>
    <li><strong>Clear all caches:</strong> Browser cache, WordPress cache, server cache</li>
    <li><strong>Try incognito/private browsing</strong> to rule out cache issues</li>
    <li><strong>Check browser console</strong> for JavaScript errors</li>
    <li><strong>Verify URL:</strong> Make sure you're going to <code>/dashboard?type=institution</code></li>
</ul>

</body>
</html>

<?php
ob_end_flush();
?>
