<?php
/**
 * Check if user-dashboard page exists
 */

// Load WordPress
require_once(dirname(__FILE__) . '/../../../wp-load.php');

echo "=== Checking for user-dashboard page ===\n";

// Check if user-dashboard page exists
$page = get_page_by_path('user-dashboard');
if ($page) {
    echo "❌ Found user-dashboard page:\n";
    echo "   ID: {$page->ID}\n";
    echo "   Status: {$page->post_status}\n";
    echo "   Template: " . get_page_template_slug($page->ID) . "\n";
    echo "   URL: " . get_permalink($page->ID) . "\n";
    echo "\n🔧 This page should be deleted or renamed to avoid conflicts.\n";
} else {
    echo "✅ No user-dashboard page found - this is good!\n";
}

// Check if dashboard page exists
$dashboard_page = get_page_by_path('dashboard');
if ($dashboard_page) {
    echo "\n✅ Dashboard page exists:\n";
    echo "   ID: {$dashboard_page->ID}\n";
    echo "   Status: {$dashboard_page->post_status}\n";
    echo "   Template: " . get_page_template_slug($dashboard_page->ID) . "\n";
    echo "   URL: " . get_permalink($dashboard_page->ID) . "\n";
} else {
    echo "\n❌ Dashboard page not found!\n";
    echo "🔧 You need to create a dashboard page.\n";
}

// Check current user and their role
if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    echo "\n=== Current User Info ===\n";
    echo "User ID: {$current_user->ID}\n";
    echo "Username: {$current_user->user_login}\n";
    echo "Email: {$current_user->user_email}\n";
    echo "Roles: " . implode(', ', $current_user->roles) . "\n";
    
    // Test dashboard type detection
    $user_roles = $current_user->roles;
    $dashboard_type = '';
    if (in_array('administrator', $user_roles)) {
        $dashboard_type = 'admin';
    } elseif (in_array('institution_admin', $user_roles)) {
        $dashboard_type = 'institution';
    } elseif (in_array('staff', $user_roles)) {
        $dashboard_type = 'staff';
    } elseif (in_array('client', $user_roles)) {
        $dashboard_type = 'client';
    } elseif (in_array('institution', $user_roles)) {
        $dashboard_type = 'institution'; // Legacy
    } elseif (in_array('patient', $user_roles)) {
        $dashboard_type = 'client'; // Legacy
    } else {
        $dashboard_type = 'client'; // Default
    }
    
    echo "Detected dashboard type: $dashboard_type\n";
    
    // Check capabilities
    echo "\n=== User Capabilities ===\n";
    $test_caps = array(
        'access_institution_dashboard',
        'access_client_dashboard', 
        'access_staff_dashboard',
        'institution',
        'manage_options'
    );
    
    foreach ($test_caps as $cap) {
        $has_cap = current_user_can($cap) ? '✅' : '❌';
        echo "$has_cap $cap\n";
    }
} else {
    echo "\n⚠️  No user logged in\n";
}

echo "\n=== Recommendations ===\n";
echo "1. Make sure no 'user-dashboard' page exists\n";
echo "2. Ensure 'dashboard' page exists and uses 'page-dashboard.php' template\n";
echo "3. Test by logging in and clicking header dropdown\n";
echo "4. URL should go to /dashboard (not /user-dashboard)\n";
?>
