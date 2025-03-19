<?php
/**
 * Template Name: Database Test Page
 */

if (!is_user_logged_in() || !current_user_can('administrator')) {
    wp_die('Unauthorized access');
}

get_header();

// Function to check table structure
function check_table_structure() {
    global $wpdb;
    $results = array();
    
    // Check test_results table
    $test_results_table = $wpdb->prefix . 'test_results';
    $test_sessions_table = $wpdb->prefix . 'test_sessions';
    
    // Check if tables exist
    $results['test_results_exists'] = (bool)$wpdb->get_var("SHOW TABLES LIKE '$test_results_table'");
    $results['test_sessions_exists'] = (bool)$wpdb->get_var("SHOW TABLES LIKE '$test_sessions_table'");
    
    return $results;
}

// Function to test data operations
function test_data_operations() {
    global $wpdb;
    $results = array();
    
    $test_results_table = $wpdb->prefix . 'test_results';
    $test_sessions_table = $wpdb->prefix . 'test_sessions';
    
    // Start transaction
    $wpdb->query('START TRANSACTION');
    
    try {
        // Insert test session
        $session_data = array(
            'user_id' => get_current_user_id(),
            'start_time' => current_time('mysql'),
            'completion_status' => 'incomplete',
            'total_phases_completed' => 0
        );
        
        $session_insert = $wpdb->insert($test_sessions_table, $session_data);
        $session_id = $wpdb->insert_id;
        
        // Insert test result
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
            
            if ($result_insert) {
                $results['success'] = true;
                $results['message'] = 'Test data successfully inserted and retrieved';
            } else {
                $results['success'] = false;
                $results['message'] = 'Failed to insert test result: ' . $wpdb->last_error;
            }
        } else {
            $results['success'] = false;
            $results['message'] = 'Failed to insert test session: ' . $wpdb->last_error;
        }
        
        // Rollback test data
        $wpdb->query('ROLLBACK');
        
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        $results['success'] = false;
        $results['message'] = 'Error: ' . $e->getMessage();
    }
    
    return $results;
}

// Run tests
$structure_check = check_table_structure();
$operation_test = test_data_operations();
?>

<div class="container mt-5">
    <h1>Database Test Results</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Table Structure Check</h2>
        </div>
        <div class="card-body">
            <ul class="list-group">
                <li class="list-group-item">
                    Test Results Table exists: 
                    <span class="badge <?php echo $structure_check['test_results_exists'] ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $structure_check['test_results_exists'] ? 'Yes' : 'No'; ?>
                    </span>
                </li>
                <li class="list-group-item">
                    Test Sessions Table exists: 
                    <span class="badge <?php echo $structure_check['test_sessions_exists'] ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $structure_check['test_sessions_exists'] ? 'Yes' : 'No'; ?>
                    </span>
                </li>
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Data Operations Test</h2>
        </div>
        <div class="card-body">
            <div class="alert <?php echo $operation_test['success'] ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $operation_test['message']; ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
