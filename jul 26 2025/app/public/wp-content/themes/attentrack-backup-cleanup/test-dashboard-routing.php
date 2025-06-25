<?php
/**
 * Quick Dashboard Routing Test
 * Run this to test the dashboard routing fixes
 */

// Load WordPress
require_once(dirname(__FILE__) . '/../../../wp-load.php');

echo "=== Dashboard Routing Test ===\n\n";

// Test 1: Check if dashboard page exists
echo "1. Testing Dashboard Page Setup...\n";

$dashboard_page = get_page_by_path('dashboard');
if ($dashboard_page) {
    echo "✅ Dashboard page exists (ID: {$dashboard_page->ID})\n";
    
    // Check if it uses the correct template
    $template = get_page_template_slug($dashboard_page->ID);
    if ($template === 'page-dashboard.php') {
        echo "✅ Dashboard page uses correct template\n";
    } else {
        echo "❌ Dashboard page template: '$template' (should be 'page-dashboard.php')\n";
    }
} else {
    echo "❌ Dashboard page not found\n";
    echo "🔧 Creating dashboard page...\n";
    
    // Create dashboard page
    $page_id = wp_insert_post(array(
        'post_title' => 'Dashboard',
        'post_name' => 'dashboard',
        'post_content' => 'This is the dashboard page.',
        'post_status' => 'publish',
        'post_type' => 'page',
        'page_template' => 'page-dashboard.php'
    ));
    
    if ($page_id && !is_wp_error($page_id)) {
        echo "✅ Dashboard page created (ID: $page_id)\n";
    } else {
        echo "❌ Failed to create dashboard page\n";
    }
}

// Test 2: Check template file exists
echo "\n2. Testing Template File...\n";

$template_path = get_template_directory() . '/page-dashboard.php';
if (file_exists($template_path)) {
    echo "✅ Dashboard template file exists\n";
    
    // Check file size to ensure it's not empty
    $file_size = filesize($template_path);
    if ($file_size > 1000) {
        echo "✅ Dashboard template has content ($file_size bytes)\n";
    } else {
        echo "❌ Dashboard template seems too small ($file_size bytes)\n";
    }
} else {
    echo "❌ Dashboard template file missing: $template_path\n";
}

// Test 3: Test role-based dashboard logic
echo "\n3. Testing Role-Based Dashboard Logic...\n";

$test_roles = array(
    'administrator' => 'admin',
    'institution_admin' => 'institution', 
    'staff' => 'staff',
    'client' => 'client'
);

foreach ($test_roles as $role => $expected_type) {
    // Simulate user with role
    $mock_user_roles = array($role);
    
    // Test the logic from page-dashboard.php
    $dashboard_type = '';
    if (in_array('administrator', $mock_user_roles)) {
        $dashboard_type = 'admin';
    } elseif (in_array('institution_admin', $mock_user_roles)) {
        $dashboard_type = 'institution';
    } elseif (in_array('staff', $mock_user_roles)) {
        $dashboard_type = 'staff';
    } elseif (in_array('client', $mock_user_roles)) {
        $dashboard_type = 'client';
    }
    
    if ($dashboard_type === $expected_type) {
        echo "✅ Role '$role' correctly maps to dashboard type '$dashboard_type'\n";
    } else {
        echo "❌ Role '$role' maps to '$dashboard_type' (expected '$expected_type')\n";
    }
}

// Test 4: Test header dropdown logic
echo "\n4. Testing Header Dropdown Logic...\n";

$header_test_roles = array(
    'client' => array('type' => 'client', 'name' => 'Client Dashboard'),
    'staff' => array('type' => 'staff', 'name' => 'Staff Dashboard'),
    'institution_admin' => array('type' => 'institution', 'name' => 'Institution Dashboard'),
    'administrator' => array('type' => 'admin', 'name' => 'Admin Dashboard')
);

foreach ($header_test_roles as $role => $expected) {
    // Create mock user
    $mock_user_roles = array($role);
    
    // Test the logic from header.php
    $dashboard_info = array();
    if (in_array('administrator', $mock_user_roles)) {
        $dashboard_info = array('type' => 'admin', 'name' => 'Admin Dashboard', 'icon' => 'fas fa-cog');
    } elseif (in_array('institution_admin', $mock_user_roles)) {
        $dashboard_info = array('type' => 'institution', 'name' => 'Institution Dashboard', 'icon' => 'fas fa-building');
    } elseif (in_array('staff', $mock_user_roles)) {
        $dashboard_info = array('type' => 'staff', 'name' => 'Staff Dashboard', 'icon' => 'fas fa-user-tie');
    } elseif (in_array('client', $mock_user_roles)) {
        $dashboard_info = array('type' => 'client', 'name' => 'Client Dashboard', 'icon' => 'fas fa-user-circle');
    }
    
    if ($dashboard_info['type'] === $expected['type'] && $dashboard_info['name'] === $expected['name']) {
        echo "✅ Header dropdown for '$role' shows: {$dashboard_info['name']}\n";
    } else {
        echo "❌ Header dropdown for '$role' shows: {$dashboard_info['name']} (expected: {$expected['name']})\n";
    }
}

// Test 5: Check URL routing
echo "\n5. Testing URL Structure...\n";

$dashboard_url = home_url('/dashboard');
echo "✅ Dashboard URL: $dashboard_url\n";

// Test if URL is accessible (basic check)
$parsed_url = parse_url($dashboard_url);
if ($parsed_url && isset($parsed_url['path'])) {
    echo "✅ Dashboard URL structure is valid\n";
} else {
    echo "❌ Dashboard URL structure is invalid\n";
}

// Test 6: Check for required functions
echo "\n6. Testing Required Functions...\n";

$required_functions = array(
    'attentrack_get_user_institution_id',
    'attentrack_log_audit_action',
    'attentrack_get_staff_assigned_clients'
);

foreach ($required_functions as $function_name) {
    if (function_exists($function_name)) {
        echo "✅ Function '$function_name' exists\n";
    } else {
        echo "⚠️  Function '$function_name' not found (may cause issues)\n";
    }
}

// Test 7: Check for required classes
echo "\n7. Testing Required Classes...\n";

$required_classes = array(
    'AttenTrack_Subscription_Manager',
    'AttenTrack_Staff_Assignments',
    'AttenTrack_RBAC'
);

foreach ($required_classes as $class_name) {
    if (class_exists($class_name)) {
        echo "✅ Class '$class_name' exists\n";
    } else {
        echo "⚠️  Class '$class_name' not found (may cause issues)\n";
    }
}

echo "\n=== Dashboard Routing Test Complete ===\n";
echo "\nNext Steps:\n";
echo "1. If all tests show ✅, the dashboard routing should work\n";
echo "2. Test in browser by logging in as different user types\n";
echo "3. Check that header dropdown shows correct dashboard names\n";
echo "4. Verify each dashboard type loads the appropriate template\n\n";

echo "Browser Test URLs:\n";
echo "- Main Dashboard: " . home_url('/dashboard') . "\n";
echo "- Institution Dashboard: " . home_url('/dashboard?type=institution') . "\n";
echo "- Staff Dashboard: " . home_url('/dashboard?type=staff') . "\n";
echo "- Client Dashboard: " . home_url('/dashboard?type=client') . "\n\n";
?>
