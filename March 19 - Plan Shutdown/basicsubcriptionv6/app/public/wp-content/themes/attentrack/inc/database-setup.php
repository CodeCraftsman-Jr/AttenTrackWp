<?php
if (!defined('ABSPATH')) exit;

function create_test_results_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // Test Results Table
    $table_name = $wpdb->prefix . 'test_results';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        test_type varchar(50) NOT NULL,
        total_count int(11) NOT NULL,
        p_count int(11) NOT NULL,
        total_responses int(11) NOT NULL,
        correct_responses int(11) NOT NULL,
        accuracy float NOT NULL,
        avg_reaction_time float NOT NULL,
        missed_responses int(11) NOT NULL,
        false_alarms int(11) NOT NULL,
        score int(11) NOT NULL,
        test_date datetime DEFAULT CURRENT_TIMESTAMP,
        session_id varchar(50) NOT NULL,
        responses longtext,
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY test_type (test_type),
        KEY test_date (test_date)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Log table creation result
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
    if ($table_exists) {
        error_log('Test results table created/verified successfully');
    } else {
        error_log('Failed to create test results table');
        error_log('Last MySQL error: ' . $wpdb->last_error);
    }
}

// Create test sessions table
function create_test_sessions_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    $table_name = $wpdb->prefix . 'test_sessions';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        test_id varchar(100) NOT NULL,
        start_time datetime DEFAULT CURRENT_TIMESTAMP,
        end_time datetime,
        status varchar(20) NOT NULL DEFAULT 'started',
        completion_status varchar(20),
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY test_id (test_id),
        KEY status (status)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Log table creation result
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
    if ($table_exists) {
        error_log('Test sessions table created/verified successfully');
    } else {
        error_log('Failed to create test sessions table');
        error_log('Last MySQL error: ' . $wpdb->last_error);
    }
}

// Initialize database tables
function initialize_database_tables() {
    create_test_results_table();
    create_test_sessions_table();
}

// Run on theme activation
add_action('after_switch_theme', 'initialize_database_tables');

// Also run on plugin initialization to ensure tables exist
add_action('init', 'initialize_database_tables');
