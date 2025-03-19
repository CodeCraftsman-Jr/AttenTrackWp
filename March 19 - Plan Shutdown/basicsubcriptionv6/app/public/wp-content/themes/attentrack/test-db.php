<?php
require_once('../../../../wp-load.php');
global $wpdb;

// Check if table exists
$table_name = $wpdb->prefix . 'test_results';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

if (!$table_exists) {
    echo "Table does not exist. Creating table...\n";
    
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        test_type varchar(50) NOT NULL,
        total_letters int(11) NOT NULL DEFAULT 0,
        p_letters int(11) NOT NULL DEFAULT 0,
        total_responses int(11) NOT NULL DEFAULT 0,
        correct_responses int(11) NOT NULL DEFAULT 0,
        accuracy float NOT NULL DEFAULT 0,
        reaction_time float NOT NULL DEFAULT 0,
        missed_responses int(11) NOT NULL DEFAULT 0,
        false_alarms int(11) NOT NULL DEFAULT 0,
        score float NOT NULL DEFAULT 0,
        test_date datetime DEFAULT CURRENT_TIMESTAMP,
        session_id varchar(50) NOT NULL,
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY test_type (test_type),
        KEY test_date (test_date)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    echo "Table created.\n";
} else {
    echo "Table exists. Current structure:\n";
    $results = $wpdb->get_results("DESCRIBE $table_name");
    foreach ($results as $row) {
        echo $row->Field . " - " . $row->Type . " - " . $row->Null . " - " . $row->Key . "\n";
    }
}
