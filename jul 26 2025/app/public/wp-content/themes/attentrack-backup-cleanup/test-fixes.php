<?php
/**
 * Test script to verify the fixes for AttenTrack dashboard issues
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once '../../../wp-load.php';
}

echo "<h1>AttenTrack Dashboard Fixes Test</h1>";

// Test 1: Check if user is logged in
echo "<h2>Test 1: User Authentication</h2>";
if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    echo "✅ User is logged in: " . esc_html($current_user->display_name) . "<br>";
    echo "User ID: " . $current_user->ID . "<br>";
    echo "User roles: " . implode(', ', $current_user->roles) . "<br>";
} else {
    echo "❌ User is not logged in<br>";
}

// Test 2: Check user capabilities
echo "<h2>Test 2: User Capabilities</h2>";
if (is_user_logged_in()) {
    $capabilities_to_test = [
        'institution',
        'access_institution_dashboard',
        'manage_institution_users',
        'view_institution_analytics',
        'configure_institution_settings'
    ];
    
    foreach ($capabilities_to_test as $cap) {
        $has_cap = current_user_can($cap);
        $status = $has_cap ? '✅' : '❌';
        echo "$status $cap<br>";
    }
} else {
    echo "❌ Cannot test capabilities - user not logged in<br>";
}

// Test 3: Check if Firebase scripts are properly ordered
echo "<h2>Test 3: Firebase Script Loading Order</h2>";
global $wp_scripts;
if (isset($wp_scripts->registered['firebase-config-js']) && isset($wp_scripts->registered['firebase-email-auth'])) {
    $firebase_config_deps = $wp_scripts->registered['firebase-config-js']->deps;
    $firebase_email_deps = $wp_scripts->registered['firebase-email-auth']->deps;
    
    echo "Firebase Config dependencies: " . implode(', ', $firebase_config_deps) . "<br>";
    echo "Firebase Email Auth dependencies: " . implode(', ', $firebase_email_deps) . "<br>";
    
    if (in_array('firebase-config-js', $firebase_email_deps)) {
        echo "✅ Firebase Email Auth properly depends on Firebase Config<br>";
    } else {
        echo "❌ Firebase Email Auth does not depend on Firebase Config<br>";
    }
} else {
    echo "❌ Firebase scripts not found<br>";
}

// Test 4: Check if dashboard router is disabled
echo "<h2>Test 4: Dashboard Router Status</h2>";
if (class_exists('AttenTrack_Dashboard_Router')) {
    echo "❌ Dashboard Router is still active (may cause duplicate forms)<br>";
} else {
    echo "✅ Dashboard Router is disabled<br>";
}

// Test 5: Test AJAX endpoint accessibility
echo "<h2>Test 5: AJAX Endpoint Test</h2>";
if (is_user_logged_in()) {
    // Simulate AJAX capability check
    $ajax_endpoints = [
        'institution_get_subscription' => 'access_institution_dashboard',
        'institution_get_users' => 'manage_institution_users',
        'institution_get_analytics' => 'view_institution_analytics'
    ];
    
    foreach ($ajax_endpoints as $endpoint => $required_cap) {
        $has_access = current_user_can('institution') || current_user_can($required_cap);
        $status = $has_access ? '✅' : '❌';
        echo "$status $endpoint (requires: $required_cap)<br>";
    }
} else {
    echo "❌ Cannot test AJAX endpoints - user not logged in<br>";
}

// Test 6: Check for duplicate ID prevention
echo "<h2>Test 6: Template Loading</h2>";
$dashboard_template = locate_template('institution-dashboard-template.php');
if ($dashboard_template) {
    echo "✅ Institution dashboard template found: " . $dashboard_template . "<br>";
} else {
    echo "❌ Institution dashboard template not found<br>";
}

echo "<h2>Summary</h2>";
echo "<p>If you see mostly ✅ marks above, the fixes should be working correctly.</p>";
echo "<p>To fully test:</p>";
echo "<ol>";
echo "<li>Navigate to the dashboard page</li>";
echo "<li>Check browser console for Firebase errors</li>";
echo "<li>Check browser console for duplicate ID warnings</li>";
echo "<li>Verify AJAX calls work (subscription data, user data, analytics)</li>";
echo "</ol>";

echo "<p><strong>Next steps:</strong></p>";
echo "<ul>";
echo "<li>Clear any caches (browser, WordPress, CDN)</li>";
echo "<li>Test the dashboard functionality</li>";
echo "<li>Monitor browser console for errors</li>";
echo "</ul>";
?>
