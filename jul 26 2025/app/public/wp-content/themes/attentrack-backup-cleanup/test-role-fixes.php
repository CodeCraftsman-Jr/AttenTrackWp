<?php
/**
 * Test Role Fixes Script
 * 
 * This script tests the role synchronization fixes to ensure they work correctly.
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );

// Include required functions
require_once( dirname( __FILE__ ) . '/inc/role-access-check.php' );
require_once( dirname( __FILE__ ) . '/inc/institution-functions.php' );

// Ensure we have admin privileges
if (!current_user_can('administrator')) {
    wp_die('You need administrator privileges to run this script.');
}

echo "<h1>Role Fixes Test</h1>";
echo "<p>This script tests the role synchronization fixes.</p>";

// Test 1: Check if new roles exist
echo "<h2>Test 1: Role Definitions</h2>";
$wp_roles = wp_roles();
$available_roles = $wp_roles->get_names();

$expected_roles = array('client', 'staff', 'institution_admin');
foreach ($expected_roles as $role) {
    if (isset($available_roles[$role])) {
        echo "✅ Role '$role' exists<br>";
    } else {
        echo "❌ Role '$role' missing<br>";
    }
}

// Test 2: Test role update function
echo "<h2>Test 2: Role Update Function</h2>";

// Find a test user (preferably not an admin)
$test_users = get_users(array(
    'role__not_in' => array('administrator'),
    'number' => 1
));

if (!empty($test_users)) {
    $test_user = $test_users[0];
    $original_roles = $test_user->roles;
    
    echo "Testing with user: {$test_user->user_login} (ID: {$test_user->ID})<br>";
    echo "Original roles: " . implode(', ', $original_roles) . "<br>";
    
    // Test updating to client role
    $result = update_user_role_and_account_type($test_user->ID, 'client', 'user');
    
    if ($result) {
        echo "✅ Role update function returned success<br>";
        
        // Verify the change
        $updated_user = get_userdata($test_user->ID);
        if (in_array('client', $updated_user->roles)) {
            echo "✅ WordPress role updated correctly<br>";
        } else {
            echo "❌ WordPress role not updated<br>";
        }
        
        // Check account_type meta
        $account_type = get_user_meta($test_user->ID, 'account_type', true);
        if ($account_type === 'user') {
            echo "✅ Account type meta updated correctly<br>";
        } else {
            echo "❌ Account type meta not updated (got: '$account_type')<br>";
        }
        
        // Check institution members table if user is in an institution
        global $wpdb;
        $institution_role = $wpdb->get_var($wpdb->prepare(
            "SELECT role FROM {$wpdb->prefix}attentrack_institution_members WHERE user_id = %d",
            $test_user->ID
        ));
        
        if ($institution_role) {
            echo "Institution role in database: $institution_role<br>";
            if ($institution_role === 'client') {
                echo "✅ Institution role updated correctly<br>";
            } else {
                echo "❌ Institution role not updated correctly<br>";
            }
        } else {
            echo "ℹ️ User not in institution members table<br>";
        }
        
    } else {
        echo "❌ Role update function failed<br>";
    }
} else {
    echo "❌ No test users found<br>";
}

// Test 3: Test AJAX response format
echo "<h2>Test 3: Institution Members Data Format</h2>";

// Get first institution
global $wpdb;
$institution = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}attentrack_institutions LIMIT 1");

if ($institution) {
    echo "Testing with institution: {$institution->name} (ID: {$institution->id})<br>";
    
    // Get members using the same function as AJAX
    $members = attentrack_get_institution_members($institution->id);
    
    if (!empty($members)) {
        echo "Found " . count($members) . " members<br>";
        
        foreach ($members as $member) {
            $user_data = get_userdata($member['user_id']);
            if ($user_data) {
                echo "Member: {$user_data->user_login} - Role: {$member['role']} - WP Roles: " . implode(', ', $user_data->roles) . "<br>";
                
                // Check if roles are synchronized
                $role_map = array(
                    'client' => 'client',
                    'staff' => 'staff',
                    'admin' => 'institution_admin',
                    'member' => 'subscriber'
                );
                
                $expected_wp_role = isset($role_map[$member['role']]) ? $role_map[$member['role']] : 'subscriber';
                
                if (in_array($expected_wp_role, $user_data->roles)) {
                    echo "✅ Roles synchronized<br>";
                } else {
                    echo "❌ Roles not synchronized (expected: $expected_wp_role)<br>";
                }
            }
        }
    } else {
        echo "ℹ️ No members found in institution<br>";
    }
} else {
    echo "ℹ️ No institutions found<br>";
}

// Test 4: Cache clearing test
echo "<h2>Test 4: Cache Clearing</h2>";

if (!empty($test_users)) {
    $test_user = $test_users[0];
    
    // Set a cache value
    wp_cache_set($test_user->ID, 'test_value', 'test_group');
    
    // Update role (which should clear caches)
    update_user_role_and_account_type($test_user->ID, 'staff', 'user');
    
    // Check if cache was cleared
    $cached_value = wp_cache_get($test_user->ID, 'test_group');
    if ($cached_value === false) {
        echo "✅ Cache clearing works (test cache was cleared)<br>";
    } else {
        echo "❌ Cache clearing may not work (test cache still exists)<br>";
    }
    
    // Check user cache specifically
    $user_cache = wp_cache_get($test_user->ID, 'users');
    if ($user_cache === false) {
        echo "✅ User cache was cleared<br>";
    } else {
        echo "ℹ️ User cache exists (may be repopulated)<br>";
    }
}

echo "<h2>✅ Testing Complete!</h2>";
echo "<p>Review the results above to ensure all role fixes are working correctly.</p>";
echo "<p><a href='" . home_url('/dashboard') . "'>Return to Dashboard</a></p>";
?>
