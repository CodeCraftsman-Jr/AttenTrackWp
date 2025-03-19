<?php
/**
 * Template Name: Database Test
 */

// Ensure this is being run within WordPress
if (!defined('ABSPATH')) {
    require_once('../../../../wp-load.php');
}

// Ensure only administrators can access this
if (!current_user_can('administrator')) {
    wp_die('Access denied');
}

// Function to run the database tests
function run_database_tests() {
    global $wpdb;
    $results = array();
    
    // Test 1: Check if tables exist
    $test_results_table = $wpdb->prefix . 'test_results';
    $test_sessions_table = $wpdb->prefix . 'test_sessions';
    
    $results['tables_exist'] = array(
        'test_results' => (bool)$wpdb->get_var("SHOW TABLES LIKE '$test_results_table'"),
        'test_sessions' => (bool)$wpdb->get_var("SHOW TABLES LIKE '$test_sessions_table'")
    );
    
    // Test 2: Check table structures
    if ($results['tables_exist']['test_results']) {
        $results['test_results_structure'] = $wpdb->get_results("DESCRIBE $test_results_table");
    }
    if ($results['tables_exist']['test_sessions']) {
        $results['test_sessions_structure'] = $wpdb->get_results("DESCRIBE $test_sessions_table");
    }
    
    // Test 3: Test data insertion
    if ($results['tables_exist']['test_results'] && $results['tables_exist']['test_sessions']) {
        $wpdb->query('START TRANSACTION');
        
        try {
            // Insert test session
            $session_insert = $wpdb->insert(
                $test_sessions_table,
                array(
                    'user_id' => get_current_user_id(),
                    'start_time' => current_time('mysql'),
                    'completion_status' => 'test',
                    'total_phases_completed' => 0
                ),
                array('%d', '%s', '%s', '%d')
            );
            
            $session_id = $wpdb->insert_id;
            
            // Insert test result
            $result_insert = $wpdb->insert(
                $test_results_table,
                array(
                    'user_id' => get_current_user_id(),
                    'test_id' => 'TEST_' . time(),
                    'test_phase' => 0,
                    'score' => 95.5,
                    'accuracy' => 98.0,
                    'reaction_time' => 0.5,
                    'missed_responses' => 1,
                    'false_alarms' => 0,
                    'responses' => json_encode(array('test_response')),
                    'test_date' => current_time('mysql')
                ),
                array('%d', '%s', '%d', '%f', '%f', '%f', '%d', '%d', '%s', '%s')
            );
            
            $results['test_insert'] = array(
                'session_insert' => $session_insert,
                'session_id' => $session_id,
                'result_insert' => $result_insert,
                'result_id' => $wpdb->insert_id
            );
            
            // Test data retrieval
            if ($result_insert) {
                $test_data = $wpdb->get_row("SELECT * FROM $test_results_table WHERE id = " . $wpdb->insert_id);
                $results['test_retrieval'] = array(
                    'success' => (bool)$test_data,
                    'data' => $test_data
                );
            }
            
            // Rollback test data
            $wpdb->query('ROLLBACK');
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            $results['error'] = $e->getMessage();
        }
    }
    
    return $results;
}

// Run the tests
$test_results = run_database_tests();

// Output results
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Test Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f5f5f5; padding: 10px; }
    </style>
</head>
<body>
    <h1>Database Test Results</h1>
    
    <h2>1. Table Existence Check</h2>
    <ul>
        <li>Test Results Table: 
            <span class="<?php echo $test_results['tables_exist']['test_results'] ? 'success' : 'error'; ?>">
                <?php echo $test_results['tables_exist']['test_results'] ? 'Exists' : 'Missing'; ?>
            </span>
        </li>
        <li>Test Sessions Table: 
            <span class="<?php echo $test_results['tables_exist']['test_sessions'] ? 'success' : 'error'; ?>">
                <?php echo $test_results['tables_exist']['test_sessions'] ? 'Exists' : 'Missing'; ?>
            </span>
        </li>
    </ul>
    
    <?php if (isset($test_results['test_results_structure'])): ?>
    <h2>2. Table Structure</h2>
    <h3>Test Results Table Structure:</h3>
    <pre><?php print_r($test_results['test_results_structure']); ?></pre>
    
    <h3>Test Sessions Table Structure:</h3>
    <pre><?php print_r($test_results['test_sessions_structure']); ?></pre>
    <?php endif; ?>
    
    <?php if (isset($test_results['test_insert'])): ?>
    <h2>3. Data Operations Test</h2>
    <h3>Insert Test:</h3>
    <pre><?php print_r($test_results['test_insert']); ?></pre>
    
    <?php if (isset($test_results['test_retrieval'])): ?>
    <h3>Retrieval Test:</h3>
    <pre><?php print_r($test_results['test_retrieval']); ?></pre>
    <?php endif; ?>
    <?php endif; ?>
    
    <?php if (isset($test_results['error'])): ?>
    <h2>Errors:</h2>
    <pre class="error"><?php echo $test_results['error']; ?></pre>
    <?php endif; ?>
</body>
</html>
