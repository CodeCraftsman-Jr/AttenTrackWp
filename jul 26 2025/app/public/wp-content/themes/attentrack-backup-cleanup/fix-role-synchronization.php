<?php
/**
 * Role Synchronization Fix Script
 * 
 * This script fixes role synchronization issues between WordPress roles
 * and custom institution member roles.
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );

// Include required functions
require_once( dirname( __FILE__ ) . '/inc/role-access-check.php' );
require_once( dirname( __FILE__ ) . '/inc/multi-tier-roles.php' );

// Ensure we have admin privileges
if (!current_user_can('administrator')) {
    wp_die('You need administrator privileges to run this script.');
}

echo "<h1>Role Synchronization Fix</h1>";
echo "<p>This script will fix role synchronization issues and migrate old roles to new ones.</p>";

// Step 1: Ensure new roles exist
echo "<h2>Step 1: Creating/Updating Role Definitions</h2>";
attentrack_create_custom_roles();
echo "✅ Custom roles created/updated<br>";

// Step 2: Migrate old roles
echo "<h2>Step 2: Migrating Old Roles</h2>";

// Get users with old 'patient' role
$patient_users = get_users(array('role' => 'patient'));
echo "Found " . count($patient_users) . " users with 'patient' role<br>";

foreach ($patient_users as $user) {
    $result = update_user_role_and_account_type($user->ID, 'client', 'user');
    if ($result) {
        echo "✅ Migrated user {$user->user_login} (ID: {$user->ID}) from 'patient' to 'client'<br>";
    } else {
        echo "❌ Failed to migrate user {$user->user_login} (ID: {$user->ID})<br>";
    }
}

// Get users with old 'institution' role
$institution_users = get_users(array('role' => 'institution'));
echo "Found " . count($institution_users) . " users with 'institution' role<br>";

foreach ($institution_users as $user) {
    $result = update_user_role_and_account_type($user->ID, 'institution_admin', 'institution');
    if ($result) {
        echo "✅ Migrated user {$user->user_login} (ID: {$user->ID}) from 'institution' to 'institution_admin'<br>";
    } else {
        echo "❌ Failed to migrate user {$user->user_login} (ID: {$user->ID})<br>";
    }
}

// Step 3: Fix role synchronization for existing users
echo "<h2>Step 3: Synchronizing Existing User Roles</h2>";

global $wpdb;

// Get all users who are members of institutions
$institution_members = $wpdb->get_results("
    SELECT im.user_id, im.role as institution_role, u.user_login
    FROM {$wpdb->prefix}attentrack_institution_members im
    JOIN {$wpdb->users} u ON im.user_id = u.ID
    WHERE im.status = 'active'
");

echo "Found " . count($institution_members) . " institution members to synchronize<br>";

foreach ($institution_members as $member) {
    $user = get_userdata($member->user_id);
    if (!$user) continue;
    
    $current_wp_roles = $user->roles;
    $institution_role = $member->institution_role;
    
    // Map institution roles to WordPress roles
    $role_map = array(
        'client' => 'client',
        'staff' => 'staff',
        'admin' => 'institution_admin',
        'member' => 'subscriber',
        'patient' => 'client',  // Legacy mapping
        'employee' => 'staff'   // Legacy mapping
    );
    
    $expected_wp_role = isset($role_map[$institution_role]) ? $role_map[$institution_role] : 'subscriber';
    
    // Check if WordPress role matches institution role
    if (!in_array($expected_wp_role, $current_wp_roles)) {
        echo "🔄 Synchronizing user {$user->user_login} (ID: {$user->ID}): institution role '{$institution_role}' -> WordPress role '{$expected_wp_role}'<br>";
        
        $account_type = ($expected_wp_role === 'institution_admin') ? 'institution' : 'user';
        $result = update_user_role_and_account_type($user->ID, $expected_wp_role, $account_type);
        
        if ($result) {
            echo "✅ Successfully synchronized<br>";
        } else {
            echo "❌ Failed to synchronize<br>";
        }
    } else {
        echo "✅ User {$user->user_login} already synchronized<br>";
    }
}

// Step 4: Clean up old roles
echo "<h2>Step 4: Cleaning Up Old Roles</h2>";
remove_role('patient');
remove_role('institution');
echo "✅ Removed old 'patient' and 'institution' roles<br>";

// Step 5: Clear all caches
echo "<h2>Step 5: Clearing Caches</h2>";
wp_cache_flush();
echo "✅ WordPress cache cleared<br>";

// Clear user caches for all users
$all_users = get_users(array('fields' => 'ID'));
foreach ($all_users as $user_id) {
    wp_cache_delete($user_id, 'users');
    wp_cache_delete($user_id, 'user_meta');
    clean_user_cache($user_id);
}
echo "✅ User caches cleared for " . count($all_users) . " users<br>";

echo "<h2>✅ Role Synchronization Complete!</h2>";
echo "<p>All roles have been synchronized. Users should now see their correct roles immediately.</p>";

// Display summary
echo "<h3>Summary of Available Roles:</h3>";
echo "<ul>";
echo "<li><strong>client</strong> - Test takers (formerly 'patient')</li>";
echo "<li><strong>staff</strong> - Institution employees</li>";
echo "<li><strong>institution_admin</strong> - Institution owners (formerly 'institution')</li>";
echo "<li><strong>subscriber</strong> - Regular users</li>";
echo "<li><strong>administrator</strong> - System administrators</li>";
echo "</ul>";

echo "<p><a href='" . home_url('/dashboard') . "'>Return to Dashboard</a></p>";
?>
