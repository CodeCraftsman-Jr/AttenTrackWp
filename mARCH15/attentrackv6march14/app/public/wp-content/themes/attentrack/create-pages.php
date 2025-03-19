<?php
// Load WordPress with absolute path
$wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
require_once($wp_load_path);

// Ensure we're in WordPress context
if (!defined('ABSPATH')) {
    die('Direct access not allowed');
}

// Create Test Data Insertion page
$test_data_page = array(
    'post_title'    => 'Test Data Insertion',
    'post_name'     => 'test-data-insertion',
    'post_status'   => 'publish',
    'post_type'     => 'page',
    'post_content'  => '',
    'post_author'   => 1,
    'ping_status'   => 'closed',
    'comment_status'=> 'closed'
);

$test_data_id = wp_insert_post($test_data_page);
if (!is_wp_error($test_data_id)) {
    update_post_meta($test_data_id, '_wp_page_template', 'templates/test-data-insertion.php');
    echo "Created Test Data Insertion page with ID: $test_data_id\n";
} else {
    echo "Error creating Test Data Insertion page: " . $test_data_id->get_error_message() . "\n";
}

// Create Test Phase 0 page
$test_phase_page = array(
    'post_title'    => 'Test Phase 0',
    'post_name'     => 'test-phase-0',
    'post_status'   => 'publish',
    'post_type'     => 'page',
    'post_content'  => '',
    'post_author'   => 1,
    'ping_status'   => 'closed',
    'comment_status'=> 'closed'
);

$test_phase_id = wp_insert_post($test_phase_page);
if (!is_wp_error($test_phase_id)) {
    update_post_meta($test_phase_id, '_wp_page_template', 'templates/test-phase-0-template.php');
    echo "Created Test Phase 0 page with ID: $test_phase_id\n";
} else {
    echo "Error creating Test Phase 0 page: " . $test_phase_id->get_error_message() . "\n";
}

// Create test results table
global $wpdb;
$table_name = $wpdb->prefix . 'test_results';

echo "Creating test results table...\n";

$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    test_id varchar(50) NOT NULL,
    test_type varchar(50) NOT NULL,
    test_phase int(11) NOT NULL,
    score float NOT NULL DEFAULT 0,
    accuracy float NOT NULL DEFAULT 0,
    reaction_time float NOT NULL DEFAULT 0,
    missed_responses int(11) NOT NULL DEFAULT 0,
    false_alarms int(11) NOT NULL DEFAULT 0,
    total_letters int(11) NOT NULL DEFAULT 0,
    p_letters int(11) NOT NULL DEFAULT 0,
    responses longtext,
    test_date datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY test_id (test_id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

// Set proper encoding for responses column
$wpdb->query("ALTER TABLE $table_name MODIFY responses longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

// Verify table exists
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
if ($table_exists) {
    echo "Test results table created successfully\n";
} else {
    echo "Error: Failed to create test results table\n";
}

echo "Setup complete!\n";
