<?php
require_once('../../../wp-load.php');

global $wpdb;

echo "Attempting to create tables using WordPress database connection...\n";

// SQL for test results table
$test_results_table = $wpdb->prefix . 'test_results';
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
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY test_id (test_id),
    KEY test_phase (test_phase),
    KEY test_date (test_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// SQL for test sessions table
$test_sessions_table = $wpdb->prefix . 'test_sessions';
$sql_sessions = "CREATE TABLE IF NOT EXISTS $test_sessions_table (
    session_id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    start_time datetime DEFAULT CURRENT_TIMESTAMP,
    completion_status varchar(20) DEFAULT 'incomplete',
    total_phases_completed int(11) DEFAULT 0,
    notes text,
    PRIMARY KEY (session_id),
    KEY user_id (user_id),
    KEY start_time (start_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

// Create tables using dbDelta
dbDelta($sql_results);
dbDelta($sql_sessions);

// Verify tables exist
$test_results_exists = $wpdb->get_var("SHOW TABLES LIKE '$test_results_table'");
echo "\nTest Results Table exists: " . ($test_results_exists ? "Yes" : "No") . "\n";

$test_sessions_exists = $wpdb->get_var("SHOW TABLES LIKE '$test_sessions_table'");
echo "Test Sessions Table exists: " . ($test_sessions_exists ? "Yes" : "No") . "\n";

// Show table structure
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
