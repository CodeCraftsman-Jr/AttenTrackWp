<?php
require_once('../../../../wp-load.php');

global $wpdb;

// Check if tables exist
$test_results_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}test_results'");
$test_sessions_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}test_sessions'");

echo "Test Results Table exists: " . ($test_results_exists ? "Yes" : "No") . "\n";
echo "Test Sessions Table exists: " . ($test_sessions_exists ? "Yes" : "No") . "\n";

if ($test_results_exists) {
    echo "\nTest Results Table Structure:\n";
    $results = $wpdb->get_results("DESCRIBE {$wpdb->prefix}test_results");
    foreach ($results as $row) {
        echo $row->Field . " - " . $row->Type . " - " . $row->Null . " - " . $row->Key . "\n";
    }
}

if ($test_sessions_exists) {
    echo "\nTest Sessions Table Structure:\n";
    $results = $wpdb->get_results("DESCRIBE {$wpdb->prefix}test_sessions");
    foreach ($results as $row) {
        echo $row->Field . " - " . $row->Type . " - " . $row->Null . " - " . $row->Key . "\n";
    }
}
