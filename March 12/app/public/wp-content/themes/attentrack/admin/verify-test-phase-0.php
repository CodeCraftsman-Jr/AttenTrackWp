<?php
/**
 * Test Phase 0 Results Verification
 */

// Use ABSPATH to get the correct WordPress root path
if (!defined('ABSPATH')) {
    $wp_load = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php';
    if (file_exists($wp_load)) {
        require_once($wp_load);
    } else {
        die('WordPress not found');
    }
}

// Ensure only administrators can access this
if (!current_user_can('administrator')) {
    wp_die('Access denied');
}

global $wpdb;
$test_results_table = $wpdb->prefix . 'test_results';
$test_sessions_table = $wpdb->prefix . 'test_sessions';

// Get the most recent test results for phase 0
$recent_results = $wpdb->get_results($wpdb->prepare(
    "SELECT r.*, s.session_id, s.completion_status, s.total_phases_completed, u.display_name 
    FROM {$test_results_table} r
    LEFT JOIN {$test_sessions_table} s ON r.user_id = s.user_id 
        AND DATE(r.test_date) = DATE(s.start_time)
    LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
    WHERE r.test_phase = %d
    ORDER BY r.test_date DESC
    LIMIT 10",
    0
));

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Phase 0 Results Verification</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        .json-data { max-width: 300px; overflow: auto; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Test Phase 0 Results Verification</h1>
    
    <?php if (empty($recent_results)): ?>
        <p class="error">No test results found for Phase 0. This might indicate that either no tests have been completed or there might be an issue with the test submission.</p>
    <?php else: ?>
        <h2>Most Recent Test Results (Phase 0)</h2>
        <table>
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>User</th>
                    <th>Test ID</th>
                    <th>Score</th>
                    <th>Accuracy</th>
                    <th>Reaction Time</th>
                    <th>Missed</th>
                    <th>False Alarms</th>
                    <th>Session Status</th>
                    <th>Phases Completed</th>
                    <th>Response Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_results as $result): ?>
                    <tr>
                        <td><?php echo esc_html($result->test_date); ?></td>
                        <td><?php echo esc_html($result->display_name); ?></td>
                        <td><?php echo esc_html($result->test_id); ?></td>
                        <td><?php echo esc_html($result->score); ?></td>
                        <td><?php echo esc_html($result->accuracy); ?>%</td>
                        <td><?php echo esc_html($result->reaction_time); ?>s</td>
                        <td><?php echo esc_html($result->missed_responses); ?></td>
                        <td><?php echo esc_html($result->false_alarms); ?></td>
                        <td><?php echo esc_html($result->completion_status); ?></td>
                        <td><?php echo esc_html($result->total_phases_completed); ?></td>
                        <td class="json-data">
                            <pre><?php echo esc_html(print_r(json_decode($result->responses), true)); ?></pre>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Data Validation</h2>
        <ul>
            <?php
            $validation_checks = array(
                'has_user_id' => array_reduce($recent_results, function($carry, $item) {
                    return $carry && !empty($item->user_id);
                }, true),
                'has_test_id' => array_reduce($recent_results, function($carry, $item) {
                    return $carry && !empty($item->test_id);
                }, true),
                'has_session' => array_reduce($recent_results, function($carry, $item) {
                    return $carry && !empty($item->session_id);
                }, true),
                'valid_responses' => array_reduce($recent_results, function($carry, $item) {
                    return $carry && json_decode($item->responses) !== null;
                }, true)
            );
            ?>
            <li>User IDs properly linked: 
                <span class="<?php echo $validation_checks['has_user_id'] ? 'success' : 'error'; ?>">
                    <?php echo $validation_checks['has_user_id'] ? 'Yes' : 'No'; ?>
                </span>
            </li>
            <li>Test IDs generated: 
                <span class="<?php echo $validation_checks['has_test_id'] ? 'success' : 'error'; ?>">
                    <?php echo $validation_checks['has_test_id'] ? 'Yes' : 'No'; ?>
                </span>
            </li>
            <li>Sessions linked: 
                <span class="<?php echo $validation_checks['has_session'] ? 'success' : 'error'; ?>">
                    <?php echo $validation_checks['has_session'] ? 'Yes' : 'No'; ?>
                </span>
            </li>
            <li>Response data valid JSON: 
                <span class="<?php echo $validation_checks['valid_responses'] ? 'success' : 'error'; ?>">
                    <?php echo $validation_checks['valid_responses'] ? 'Yes' : 'No'; ?>
                </span>
            </li>
        </ul>
    <?php endif; ?>

    <h2>Test Phase 0 Implementation Check</h2>
    <?php
    // Check if the saveResults function is properly implemented in test-phase-0.php
    $test_phase_0_path = get_template_directory() . '/templates/test-phase-0.php';
    $test_phase_0_content = file_exists($test_phase_0_path) ? file_get_contents($test_phase_0_path) : '';
    
    $implementation_checks = array(
        'has_save_results' => strpos($test_phase_0_content, 'saveResults') !== false,
        'uses_ajax' => strpos($test_phase_0_content, 'attentrack_ajax') !== false,
        'handles_response' => strpos($test_phase_0_content, 'success') !== false && 
                            strpos($test_phase_0_content, 'error') !== false
    );
    ?>
    <ul>
        <li>SaveResults function implemented: 
            <span class="<?php echo $implementation_checks['has_save_results'] ? 'success' : 'error'; ?>">
                <?php echo $implementation_checks['has_save_results'] ? 'Yes' : 'No'; ?>
            </span>
        </li>
        <li>AJAX configuration present: 
            <span class="<?php echo $implementation_checks['uses_ajax'] ? 'success' : 'error'; ?>">
                <?php echo $implementation_checks['uses_ajax'] ? 'Yes' : 'No'; ?>
            </span>
        </li>
        <li>Response handling implemented: 
            <span class="<?php echo $implementation_checks['handles_response'] ? 'success' : 'error'; ?>">
                <?php echo $implementation_checks['handles_response'] ? 'Yes' : 'No'; ?>
            </span>
        </li>
    </ul>
</body>
</html>
