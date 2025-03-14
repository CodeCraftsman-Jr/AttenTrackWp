<?php
/**
 * Template Name: Test Verification
 */

// Ensure only administrators can access this
if (!is_user_logged_in() || !current_user_can('administrator')) {
    wp_die('Unauthorized access');
}

get_header();

global $wpdb;
$test_results_table = $wpdb->prefix . 'test_results';
$test_sessions_table = $wpdb->prefix . 'test_sessions';

// Get recent test results
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

<div class="container mt-5">
    <h1>Test Phase 0 Verification</h1>

    <div class="card mb-4">
        <div class="card-header">
            <h2>Test Page Access</h2>
        </div>
        <div class="card-body">
            <?php
            $test_page = get_page_by_path('selective-attention-test');
            if ($test_page) {
                $test_url = get_permalink($test_page->ID);
                echo "<div class='test-link'>";
                echo "<strong>Test Page URL:</strong><br>";
                echo "<a href='$test_url' target='_blank'>$test_url</a>";
                echo "<p>Open this link in a new tab to run the actual test.</p>";
                echo "</div>";
            } else {
                echo '<div class="alert alert-warning">Test page not found. Please create a page with the slug "selective-attention-test".</div>';
            }
            ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2>Recent Test Results</h2>
        </div>
        <div class="card-body">
            <?php if (empty($recent_results)): ?>
                <div class="alert alert-info">No test results found. Try running the test first.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>User</th>
                                <th>Score</th>
                                <th>Accuracy</th>
                                <th>Reaction Time</th>
                                <th>Missed</th>
                                <th>False Alarms</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_results as $result): ?>
                                <tr>
                                    <td><?php echo esc_html($result->test_date); ?></td>
                                    <td><?php echo esc_html($result->display_name); ?></td>
                                    <td><?php echo number_format($result->score, 2); ?></td>
                                    <td><?php echo number_format($result->accuracy, 1); ?>%</td>
                                    <td><?php echo number_format($result->reaction_time, 3); ?>s</td>
                                    <td><?php echo esc_html($result->missed_responses); ?></td>
                                    <td><?php echo esc_html($result->false_alarms); ?></td>
                                    <td>
                                        <span class="badge <?php echo $result->completion_status === 'complete' ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo esc_html($result->completion_status); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>How to Test</h2>
        </div>
        <div class="card-body">
            <ol class="list-group list-group-numbered">
                <li class="list-group-item">Click the "Open Test Page" button above to start a new test</li>
                <li class="list-group-item">Click "Start" on the test page</li>
                <li class="list-group-item">Press spacebar whenever you see the letter 'p'</li>
                <li class="list-group-item">Complete the 80-second test</li>
                <li class="list-group-item">Return to this page to verify your results</li>
            </ol>
        </div>
    </div>
</div>

<?php get_footer(); ?>
