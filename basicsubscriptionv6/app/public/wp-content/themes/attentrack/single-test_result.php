<?php
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// Ensure users can only view their own results
$post = get_post();
if (get_current_user_id() !== (int)$post->post_author) {
    wp_die('You do not have permission to view this result.');
}

get_header();

$test_data = get_post_meta(get_the_ID(), 'test_data', true);
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">Test Result Details</h2>
                    
                    <div class="test-meta mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Date:</strong> <?php echo get_the_date('F j, Y g:i a'); ?></p>
                                <p><strong>Test Type:</strong> <?php echo esc_html($test_data['test_name'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Duration:</strong> <?php echo esc_html(floor(($test_data['duration'] ?? 0) / 60)); ?> minutes</p>
                                <p><strong>Score:</strong> <?php echo esc_html($test_data['score'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="performance-analysis mb-4">
                        <h4>Performance Analysis</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <canvas id="accuracyChart"></canvas>
                            </div>
                            <div class="col-md-6">
                                <canvas id="responseTimeChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="detailed-results mb-4">
                        <h4>Detailed Results</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Phase</th>
                                        <th>Accuracy</th>
                                        <th>Response Time</th>
                                        <th>Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($test_data['phases'])) {
                                        foreach ($test_data['phases'] as $phase) {
                                            echo '<tr>';
                                            echo '<td>' . esc_html($phase['name']) . '</td>';
                                            echo '<td>' . esc_html($phase['accuracy'] . '%') . '</td>';
                                            echo '<td>' . esc_html($phase['response_time'] . 'ms') . '</td>';
                                            echo '<td>' . esc_html($phase['score']) . '</td>';
                                            echo '</tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="recommendations">
                        <h4>Recommendations</h4>
                        <div class="alert alert-info">
                            <?php
                            if (!empty($test_data['recommendations'])) {
                                echo '<ul class="mb-0">';
                                foreach ($test_data['recommendations'] as $recommendation) {
                                    echo '<li>' . esc_html($recommendation) . '</li>';
                                }
                                echo '</ul>';
                            } else {
                                echo '<p class="mb-0">No specific recommendations available for this test result.</p>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="<?php echo esc_url(home_url('/dashboard')); ?>" class="btn btn-primary">Back to Dashboard</a>
                        <button class="btn btn-secondary ms-2" onclick="window.print()">Print Results</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Accuracy Chart
    const accuracyCtx = document.getElementById('accuracyChart').getContext('2d');
    new Chart(accuracyCtx, {
        type: 'bar',
        data: {
            labels: <?php 
                $phases = array_map(function($phase) {
                    return $phase['name'];
                }, $test_data['phases'] ?? array());
                echo json_encode($phases);
            ?>,
            datasets: [{
                label: 'Accuracy (%)',
                data: <?php 
                    $accuracies = array_map(function($phase) {
                        return $phase['accuracy'];
                    }, $test_data['phases'] ?? array());
                    echo json_encode($accuracies);
                ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Response Time Chart
    const responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
    new Chart(responseTimeCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($phases); ?>,
            datasets: [{
                label: 'Response Time (ms)',
                data: <?php 
                    $responseTimes = array_map(function($phase) {
                        return $phase['response_time'];
                    }, $test_data['phases'] ?? array());
                    echo json_encode($responseTimes);
                ?>,
                borderColor: 'rgba(54, 162, 235, 1)',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>

<?php get_footer(); ?>
