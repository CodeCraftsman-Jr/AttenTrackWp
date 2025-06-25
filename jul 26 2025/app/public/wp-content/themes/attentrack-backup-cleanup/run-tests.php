<?php
/**
 * Command Line Test Runner for AttenTrack
 * Run this file directly to test the system
 */

// Load WordPress
require_once(dirname(__FILE__) . '/../../../wp-load.php');

// Security check
if (!current_user_can('administrator') && !defined('WP_CLI')) {
    die("Error: Administrator privileges required to run tests.\n");
}

// Include test suite
require_once get_template_directory() . '/tests/test-suite.php';

echo "=== AttenTrack Multi-Tier Access Control System Tests ===\n\n";

// Check if system is ready for testing
echo "1. Checking system prerequisites...\n";

// Check if new tables exist
global $wpdb;
$required_tables = array(
    $wpdb->prefix . 'attentrack_client_details',
    $wpdb->prefix . 'attentrack_staff_assignments',
    $wpdb->prefix . 'attentrack_user_role_assignments',
    $wpdb->prefix . 'attentrack_subscription_details',
    $wpdb->prefix . 'attentrack_audit_log'
);

$missing_tables = array();
foreach ($required_tables as $table) {
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
        $missing_tables[] = $table;
    }
}

if (!empty($missing_tables)) {
    echo "❌ Missing required tables:\n";
    foreach ($missing_tables as $table) {
        echo "   - $table\n";
    }
    echo "\nPlease run the database migration first.\n";
    exit(1);
}

echo "✅ All required tables exist\n";

// Check if roles exist
$required_roles = array('client', 'staff', 'institution_admin');
$wp_roles = wp_roles();
$missing_roles = array();

foreach ($required_roles as $role) {
    if (!isset($wp_roles->roles[$role])) {
        $missing_roles[] = $role;
    }
}

if (!empty($missing_roles)) {
    echo "❌ Missing required roles:\n";
    foreach ($missing_roles as $role) {
        echo "   - $role\n";
    }
    echo "\nPlease ensure all custom roles are created.\n";
    exit(1);
}

echo "✅ All required roles exist\n";

// Check migration status
echo "\n2. Checking migration status...\n";
$migration = AttenTrack_Terminology_Migration::getInstance();
$migration_status = $migration->get_migration_status();

if ($migration_status['database_migrated']) {
    echo "✅ Database migration completed\n";
} else {
    echo "⚠️  Database migration may not be complete\n";
}

if ($migration_status['remaining_patient_references'] == 0) {
    echo "✅ Terminology migration completed\n";
} else {
    echo "⚠️  Found {$migration_status['remaining_patient_references']} remaining 'patient' references\n";
}

// Run automated tests
echo "\n3. Running automated test suite...\n";
echo "This may take a few minutes...\n\n";

try {
    $test_suite = new AttenTrack_Test_Suite();
    $results = $test_suite->run_all_tests();
    
    // Analyze results
    $total_tests = count($results);
    $passed = 0;
    $failed = 0;
    $errors = 0;
    
    foreach ($results as $result) {
        if (strpos($result['message'], 'PASS:') !== false) {
            $passed++;
        } elseif (strpos($result['message'], 'FAIL:') !== false) {
            $failed++;
        } elseif ($result['level'] === 'error') {
            $errors++;
        }
    }
    
    echo "=== TEST RESULTS SUMMARY ===\n";
    echo "Total Tests: $total_tests\n";
    echo "Passed: $passed ✅\n";
    echo "Failed: $failed " . ($failed > 0 ? "❌" : "✅") . "\n";
    echo "Errors: $errors " . ($errors > 0 ? "❌" : "✅") . "\n\n";
    
    if ($failed > 0 || $errors > 0) {
        echo "=== DETAILED RESULTS ===\n";
        foreach ($results as $result) {
            $icon = '📝';
            if ($result['level'] === 'error' || strpos($result['message'], 'FAIL:') !== false) {
                $icon = '❌';
            } elseif (strpos($result['message'], 'PASS:') !== false) {
                $icon = '✅';
            }
            
            echo "$icon {$result['message']}\n";
        }
    }
    
    // Overall status
    echo "\n=== OVERALL STATUS ===\n";
    if ($failed == 0 && $errors == 0) {
        echo "🎉 ALL TESTS PASSED! System is ready for production.\n";
        $exit_code = 0;
    } else {
        echo "⚠️  SOME TESTS FAILED. Please review and fix issues before production deployment.\n";
        $exit_code = 1;
    }
    
} catch (Exception $e) {
    echo "❌ Test suite failed to run: " . $e->getMessage() . "\n";
    $exit_code = 1;
}

// Manual testing reminder
echo "\n=== MANUAL TESTING REQUIRED ===\n";
echo "After automated tests pass, please perform manual testing:\n";
echo "1. Test user role dashboards\n";
echo "2. Verify permission boundaries\n";
echo "3. Test staff-client assignments\n";
echo "4. Validate subscription management\n";
echo "5. Check audit logging\n";
echo "\nSee TESTING_GUIDE.md for detailed manual testing instructions.\n";

// Quick setup guide
echo "\n=== QUICK SETUP FOR TESTING ===\n";
echo "To test the system manually:\n\n";
echo "1. Create test users:\n";
echo "   - Institution Admin: admin@test.com\n";
echo "   - Staff Member: staff@test.com\n";
echo "   - Client: client@test.com\n\n";
echo "2. Access admin test interface:\n";
echo "   WordPress Admin > Tools > AttenTrack Tests\n\n";
echo "3. Or visit directly:\n";
echo "   " . home_url('/wp-content/themes/attentrack/admin-test-page.php') . "\n\n";

// Performance recommendations
echo "=== PERFORMANCE RECOMMENDATIONS ===\n";
echo "For production deployment:\n";
echo "1. Enable object caching (Redis/Memcached)\n";
echo "2. Configure audit log cleanup schedule\n";
echo "3. Set up database indexing for large datasets\n";
echo "4. Configure session storage (database/Redis)\n";
echo "5. Enable HTTPS and security headers\n\n";

echo "Testing completed at: " . date('Y-m-d H:i:s') . "\n";
exit($exit_code ?? 0);
?>
