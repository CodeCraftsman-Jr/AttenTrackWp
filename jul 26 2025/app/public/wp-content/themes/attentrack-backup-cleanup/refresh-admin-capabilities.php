<?php
/**
 * Refresh Administrator Capabilities
 * Run this script to ensure administrators have all required capabilities
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once '../../../wp-load.php';
}

// Check if user is administrator
if (!current_user_can('administrator')) {
    die('Access denied. Administrator privileges required.');
}

echo "<h1>Refreshing Administrator Capabilities</h1>";

// Get administrator role
$admin_role = get_role('administrator');
if (!$admin_role) {
    echo "❌ Administrator role not found!<br>";
    exit;
}

echo "<h2>Current Administrator Capabilities</h2>";
$current_caps = $admin_role->capabilities;
$required_caps = array(
    'access_client_dashboard',
    'access_staff_dashboard', 
    'access_institution_dashboard',
    'manage_institution_users',
    'view_all_institution_data',
    'access_subscription_management',
    'manage_billing',
    'configure_institution_settings',
    'view_institution_analytics',
    'assign_clients_to_staff',
    'manage_test_assignments',
    'set_user_permissions'
);

$missing_caps = array();
foreach ($required_caps as $cap) {
    if (isset($current_caps[$cap]) && $current_caps[$cap]) {
        echo "✅ $cap<br>";
    } else {
        echo "❌ $cap (missing)<br>";
        $missing_caps[] = $cap;
    }
}

if (!empty($missing_caps)) {
    echo "<h2>Adding Missing Capabilities</h2>";
    foreach ($missing_caps as $cap) {
        $admin_role->add_cap($cap);
        echo "✅ Added: $cap<br>";
    }
    
    // Clear user capability cache
    wp_cache_delete('user_roles', 'options');
    
    echo "<p><strong>✅ All missing capabilities have been added!</strong></p>";
    echo "<p>Please refresh your dashboard page to test the analytics functionality.</p>";
} else {
    echo "<p><strong>✅ All required capabilities are already present!</strong></p>";
    echo "<p>If you're still getting 403 errors, the issue might be elsewhere.</p>";
}

echo "<h2>Current User Capability Test</h2>";
$current_user = wp_get_current_user();
foreach ($required_caps as $cap) {
    $has_cap = current_user_can($cap);
    $status = $has_cap ? '✅' : '❌';
    echo "$status current_user_can('$cap')<br>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Clear browser cache and refresh the dashboard page</li>";
echo "<li>Check if analytics AJAX call now works</li>";
echo "<li>If still getting 403 errors, check the server error logs</li>";
echo "</ol>";
?>
