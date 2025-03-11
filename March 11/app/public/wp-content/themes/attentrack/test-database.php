<?php
// Load WordPress
require_once dirname(__FILE__) . '/../../../wp-load.php';

// Function to check table existence and structure
function check_table_structure() {
    global $wpdb;
    
    $results = array();
    
    // Check test_results table
    $test_results_table = $wpdb->prefix . 'test_results';
    $test_sessions_table = $wpdb->prefix . 'test_sessions';
    
    // Check if tables exist
    $results['test_results_exists'] = (bool)$wpdb->get_var("SHOW TABLES LIKE '$test_results_table'");
    $results['test_sessions_exists'] = (bool)$wpdb->get_var("SHOW TABLES LIKE '$test_sessions_table'");
    
    if ($results['test_results_exists']) {
        // Check test_results columns
        $columns = $wpdb->get_results("DESCRIBE $test_results_table");
        $results['test_results_columns'] = array_map(function($col) {
            return array(
                'name' => $col->Field,
                'type' => $col->Type
            );
        }, $columns);
    }
    
    if ($results['test_sessions_exists']) {
        // Check test_sessions columns
        $columns = $wpdb->get_results("DESCRIBE $test_sessions_table");
        $results['test_sessions_columns'] = array_map(function($col) {
            return array(
                'name' => $col->Field,
                'type' => $col->Type
            );
        }, $columns);
    }
    
    return $results;
}

// Function to test data insertion and retrieval
function test_data_operations() {
    global $wpdb;
    $results = array();
    
    $test_results_table = $wpdb->prefix . 'test_results';
    $test_sessions_table = $wpdb->prefix . 'test_sessions';
    
    // Start transaction
    $wpdb->query('START TRANSACTION');
    
    try {
        // Test session insertion
        $session_data = array(
            'user_id' => get_current_user_id(),
            'start_time' => current_time('mysql'),
            'completion_status' => 'incomplete',
            'total_phases_completed' => 0
        );
        
        $session_insert = $wpdb->insert($test_sessions_table, $session_data);
        $session_id = $wpdb->insert_id;
        
        $results['session_insert'] = array(
            'success' => (bool)$session_insert,
            'error' => $wpdb->last_error,
            'session_id' => $session_id
        );
        
        // Test result insertion
        if ($session_insert) {
            $test_data = array(
                'user_id' => get_current_user_id(),
                'test_id' => 'TEST_' . time(),
                'test_phase' => 0,
                'score' => 95.5,
                'accuracy' => 98.0,
                'reaction_time' => 0.5,
                'missed_responses' => 1,
                'false_alarms' => 0,
                'responses' => json_encode(['test_response']),
                'test_date' => current_time('mysql')
            );
            
            $result_insert = $wpdb->insert($test_results_table, $test_data);
            $result_id = $wpdb->insert_id;
            
            $results['result_insert'] = array(
                'success' => (bool)$result_insert,
                'error' => $wpdb->last_error,
                'result_id' => $result_id
            );
            
            // Test data retrieval
            if ($result_insert) {
                $retrieved_result = $wpdb->get_row("SELECT * FROM $test_results_table WHERE id = $result_id");
                $results['data_retrieval'] = array(
                    'success' => (bool)$retrieved_result,
                    'data' => $retrieved_result
                );
            }
        }
        
        // Rollback test data
        $wpdb->query('ROLLBACK');
        
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        $results['error'] = $e->getMessage();
    }
    
    return $results;
}

// Run tests and output results
header('Content-Type: application/json');

$structure_results = check_table_structure();
$operation_results = test_data_operations();

echo json_encode(array(
    'structure_check' => $structure_results,
    'operation_test' => $operation_results
), JSON_PRETTY_PRINT);
