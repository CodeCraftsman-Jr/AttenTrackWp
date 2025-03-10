<?php
require_once('../../../wp-load.php');

global $wpdb;

// Create test results table
$test_results_table = $wpdb->prefix . 'test_results';
$test_sessions_table = $wpdb->prefix . 'test_sessions';

$charset_collate = $wpdb->get_charset_collate();

// SQL for test results table
$sql_results = "CREATE TABLE IF NOT EXISTS $test_results_table (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    test_id varchar(100) NOT NULL,
    test_phase int(11) NOT NULL,
    score float NOT NULL,
    accuracy float NOT NULL,
    reaction_time float NOT NULL,
    missed_responses int(11) NOT NULL,
    false_alarms int(11) NOT NULL,
    responses longtext NOT NULL,
    test_date datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (id),
    KEY user_id (user_id),
    KEY test_id (test_id),
    KEY test_phase (test_phase),
    KEY test_date (test_date)
) $charset_collate;";

// SQL for test sessions table
$sql_sessions = "CREATE TABLE IF NOT EXISTS $test_sessions_table (
    session_id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    start_time datetime DEFAULT CURRENT_TIMESTAMP,
    completion_status varchar(20) DEFAULT 'incomplete',
    total_phases_completed int(11) DEFAULT 0,
    notes text,
    PRIMARY KEY  (session_id),
    KEY user_id (user_id),
    KEY start_time (start_time)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

// Create/update the tables
dbDelta($sql_results);
dbDelta($sql_sessions);

// Check if tables were created
$test_results_exists = $wpdb->get_var("SHOW TABLES LIKE '$test_results_table'");
$test_sessions_exists = $wpdb->get_var("SHOW TABLES LIKE '$test_sessions_table'");

echo "Test Results Table exists: " . ($test_results_exists ? "Yes" : "No") . "\n";
echo "Test Sessions Table exists: " . ($test_sessions_exists ? "Yes" : "No") . "\n";

if ($test_results_exists) {
    echo "\nTest Results Table Structure:\n";
    $results = $wpdb->get_results("DESCRIBE $test_results_table");
    foreach ($results as $row) {
        echo $row->Field . " - " . $row->Type . " - " . $row->Null . " - " . $row->Key . "\n";
    }
}

if ($test_sessions_exists) {
    echo "\nTest Sessions Table Structure:\n";
    $results = $wpdb->get_results("DESCRIBE $test_sessions_table");
    foreach ($results as $row) {
        echo $row->Field . " - " . $row->Type . " - " . $row->Null . " - " . $row->Key . "\n";
    }
}
