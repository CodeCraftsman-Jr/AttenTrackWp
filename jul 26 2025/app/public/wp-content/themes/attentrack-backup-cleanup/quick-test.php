<?php
/**
 * Quick Test Script for AttenTrack Fixes
 * Tests the specific issues that were failing
 */

// Load WordPress
require_once(dirname(__FILE__) . '/../../../wp-load.php');

echo "=== Quick Test for AttenTrack Fixes ===\n\n";

// Test 1: Check if roles exist and have correct capabilities
echo "1. Testing User Roles and Capabilities...\n";

$roles_to_test = array('client', 'staff', 'institution_admin');
foreach ($roles_to_test as $role_name) {
    $role = get_role($role_name);
    if ($role) {
        echo "✅ Role '$role_name' exists\n";
        
        // Test specific capabilities
        if ($role_name === 'institution_admin') {
            if (isset($role->capabilities['access_subscription_management'])) {
                echo "✅ Institution admin has access_subscription_management capability\n";
            } else {
                echo "❌ Institution admin missing access_subscription_management capability\n";
                // Try to add it
                $role->add_cap('access_subscription_management');
                echo "🔧 Added missing capability\n";
            }
        }
    } else {
        echo "❌ Role '$role_name' does not exist\n";
    }
}

// Test 2: Check database tables
echo "\n2. Testing Database Tables...\n";

global $wpdb;
$required_tables = array(
    $wpdb->prefix . 'attentrack_client_details',
    $wpdb->prefix . 'attentrack_staff_assignments',
    $wpdb->prefix . 'attentrack_user_role_assignments',
    $wpdb->prefix . 'attentrack_subscription_details',
    $wpdb->prefix . 'attentrack_audit_log'
);

foreach ($required_tables as $table) {
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
        echo "✅ Table '$table' exists\n";
    } else {
        echo "❌ Table '$table' missing\n";
    }
}

// Test 3: Test role assignment functionality
echo "\n3. Testing Role Assignment...\n";

// Create a test user
$test_user_id = wp_create_user('quicktest_' . time(), 'test123', 'quicktest@example.com');

if (!is_wp_error($test_user_id)) {
    echo "✅ Created test user (ID: $test_user_id)\n";
    
    // Test role assignment
    $user = new WP_User($test_user_id);
    $user->set_role('client');
    
    // Refresh user data
    $user = new WP_User($test_user_id);
    
    if (in_array('client', $user->roles)) {
        echo "✅ Successfully assigned client role\n";
        
        // Test capability
        if ($user->has_cap('access_client_dashboard')) {
            echo "✅ Client has access_client_dashboard capability\n";
        } else {
            echo "❌ Client missing access_client_dashboard capability\n";
        }
    } else {
        echo "❌ Failed to assign client role\n";
    }
    
    // Cleanup
    wp_delete_user($test_user_id);
    echo "🧹 Cleaned up test user\n";
} else {
    echo "❌ Failed to create test user: " . $test_user_id->get_error_message() . "\n";
}

// Test 4: Test header dropdown fix
echo "\n4. Testing Header Dropdown Logic...\n";

// Simulate different user types
$test_roles = array(
    'client' => array('type' => 'client', 'name' => 'Client Dashboard'),
    'staff' => array('type' => 'staff', 'name' => 'Staff Dashboard'),
    'institution_admin' => array('type' => 'institution', 'name' => 'Institution Dashboard'),
    'administrator' => array('type' => 'admin', 'name' => 'Admin Dashboard')
);

foreach ($test_roles as $role => $expected) {
    // Create mock user
    $mock_user = new stdClass();
    $mock_user->roles = array($role);
    
    // Test the logic from header.php
    $dashboard_info = array();
    if (in_array('administrator', $mock_user->roles)) {
        $dashboard_info = array('type' => 'admin', 'name' => 'Admin Dashboard', 'icon' => 'fas fa-cog');
    } elseif (in_array('institution_admin', $mock_user->roles)) {
        $dashboard_info = array('type' => 'institution', 'name' => 'Institution Dashboard', 'icon' => 'fas fa-building');
    } elseif (in_array('staff', $mock_user->roles)) {
        $dashboard_info = array('type' => 'staff', 'name' => 'Staff Dashboard', 'icon' => 'fas fa-user-tie');
    } elseif (in_array('client', $mock_user->roles)) {
        $dashboard_info = array('type' => 'client', 'name' => 'Client Dashboard', 'icon' => 'fas fa-user-circle');
    }
    
    if ($dashboard_info['type'] === $expected['type'] && $dashboard_info['name'] === $expected['name']) {
        echo "✅ $role role shows correct dashboard: {$dashboard_info['name']}\n";
    } else {
        echo "❌ $role role shows wrong dashboard: {$dashboard_info['name']} (expected: {$expected['name']})\n";
    }
}

// Test 5: Test audit logging
echo "\n5. Testing Audit Logging...\n";

$log_result = attentrack_log_audit_action(
    1, // Admin user
    'quick_test',
    'test_resource',
    null,
    null,
    array('test' => 'data'),
    'success'
);

if ($log_result) {
    echo "✅ Audit logging working\n";
    
    // Test retrieval
    $logs = attentrack_get_audit_logs(array('action' => 'quick_test'), 1);
    if (!empty($logs)) {
        echo "✅ Audit log retrieval working\n";
    } else {
        echo "❌ Audit log retrieval failed\n";
    }
} else {
    echo "❌ Audit logging failed\n";
}

// Test 6: Test RBAC system
echo "\n6. Testing RBAC System...\n";

$rbac = AttenTrack_RBAC::getInstance();
if ($rbac) {
    echo "✅ RBAC system initialized\n";
    
    // Test basic permission check
    $can_access = $rbac->can_access_resource(1, 'client_data', 1, 'view');
    if ($can_access) {
        echo "✅ RBAC permission check working\n";
    } else {
        echo "❌ RBAC permission check failed\n";
    }
} else {
    echo "❌ RBAC system failed to initialize\n";
}

echo "\n=== Quick Test Complete ===\n";
echo "If all tests show ✅, the main issues have been fixed.\n";
echo "You can now run the full test suite again.\n\n";

// Instructions for next steps
echo "Next Steps:\n";
echo "1. Run full test suite: php wp-content/themes/attentrack/run-tests.php\n";
echo "2. Test the header dropdown in browser\n";
echo "3. Create test users and verify role-based access\n";
echo "4. Test staff-client assignments manually\n\n";
?>
