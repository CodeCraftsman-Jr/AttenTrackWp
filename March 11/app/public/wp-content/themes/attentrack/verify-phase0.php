<?php
require_once('../../../wp-load.php');

// Ensure only administrators can access this
if (!current_user_can('administrator')) {
    wp_die('Access denied');
}

global $wpdb;
$test_results_table = $wpdb->prefix . 'test_results';
$test_sessions_table = $wpdb->prefix . 'test_sessions';

// Function to run a simulated test
function simulate_test_phase_0() {
    global $wpdb;
    $test_results_table = $wpdb->prefix . 'test_results';
    $test_sessions_table = $wpdb->prefix . 'test_sessions';
    
    // Start transaction
    $wpdb->query('START TRANSACTION');
    
    try {
        // Create test session
        $session_data = array(
            'user_id' => get_current_user_id(),
            'start_time' => current_time('mysql'),
            'completion_status' => 'incomplete',
            'total_phases_completed' => 0
        );
        
        $wpdb->insert($test_sessions_table, $session_data);
        $session_id = $wpdb->insert_id;
        
        // Create test result
        $test_data = array(
            'user_id' => get_current_user_id(),
            'test_id' => 'TEST_' . time(),
            'test_phase' => 0,
            'score' => 95.5,
            'accuracy' => 98.0,
            'reaction_time' => 0.5,
            'missed_responses' => 1,
            'false_alarms' => 0,
            'responses' => json_encode([
                ['letter' => 'p', 'correct' => true, 'reactionTime' => 450],
                ['letter' => 'p', 'correct' => true, 'reactionTime' => 500],
                ['letter' => 'q', 'correct' => true, 'reactionTime' => 480]
            ]),
            'test_date' => current_time('mysql')
        );
        
        $wpdb->insert($test_results_table, $test_data);
        $result_id = $wpdb->insert_id;
        
        // Commit transaction
        $wpdb->query('COMMIT');
        
        return array(
            'success' => true,
            'session_id' => $session_id,
            'result_id' => $result_id
        );
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        return array(
            'success' => false,
            'error' => $e->getMessage()
        );
    }
}

// Function to verify test results
function verify_test_results($result_id) {
    global $wpdb;
    $test_results_table = $wpdb->prefix . 'test_results';
    
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $test_results_table WHERE id = %d",
        $result_id
    ));
    
    return $result;
}

// Run simulation and verification
$simulation = simulate_test_phase_0();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Phase 0 Verification</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f5f5f5; padding: 10px; }
        .button { 
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .test-link {
            display: block;
            margin: 20px 0;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Test Phase 0 Verification</h1>
    
    <?php
    // Get the test phase 0 page URL
    $test_page = get_page_by_path('test-phase-0');
    if ($test_page) {
        $test_url = get_permalink($test_page->ID);
        echo "<div class='test-link'>";
        echo "<strong>Test Page URL:</strong><br>";
        echo "<a href='$test_url' target='_blank'>$test_url</a>";
        echo "<p>Open this link in a new tab to run the actual test.</p>";
        echo "</div>";
    }
    ?>
    
    <h2>Simulated Test Results</h2>
    <?php if ($simulation['success']): ?>
        <p class="success">Test simulation successful!</p>
        <p>Session ID: <?php echo $simulation['session_id']; ?></p>
        <p>Result ID: <?php echo $simulation['result_id']; ?></p>
        
        <?php 
        $verification = verify_test_results($simulation['result_id']);
        if ($verification):
        ?>
            <h3>Stored Data Verification:</h3>
            <pre><?php print_r($verification); ?></pre>
        <?php else: ?>
            <p class="error">Could not verify stored data.</p>
        <?php endif; ?>
        
    <?php else: ?>
        <p class="error">Test simulation failed: <?php echo $simulation['error']; ?></p>
    <?php endif; ?>
    
    <h2>Next Steps</h2>
    <ol>
        <li>Click the test page link above to run an actual test</li>
        <li>Complete the test by responding to 'p' letters</li>
        <li>After completion, the results will be automatically saved</li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=test-phase-0-verify'); ?>" class="button">
                View All Test Results
            </a>
        </li>
    </ol>
</body>
</html>
