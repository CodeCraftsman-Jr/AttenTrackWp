<?php
/*
Template Name: Dashboard
*/

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

get_header();
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <?php $current_user = wp_get_current_user(); ?>
                    <h5 class="card-title"><?php echo esc_html($current_user->display_name); ?></h5>
                    <p class="card-text">Patient ID: <?php echo esc_html(get_user_meta($current_user->ID, 'patient_id', true)); ?></p>
                </div>
            </div>
            
            <div class="list-group">
                <a href="#available-tests" class="list-group-item list-group-item-action active" data-bs-toggle="list">Available Tests</a>
                <a href="#test-history" class="list-group-item list-group-item-action" data-bs-toggle="list">Test History</a>
                <a href="#results" class="list-group-item list-group-item-action" data-bs-toggle="list">Results</a>
                <a href="#profile" class="list-group-item list-group-item-action" data-bs-toggle="list">Profile</a>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Available Tests -->
                <div class="tab-pane fade show active" id="available-tests">
                    <h3>Available Tests</h3>
                    <div class="row">
                        <?php
                        $tests = new WP_Query(array(
                            'post_type' => 'attention_test',
                            'posts_per_page' => -1,
                            'orderby' => 'menu_order',
                            'order' => 'ASC'
                        ));

                        while ($tests->have_posts()) : $tests->the_post();
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php the_title(); ?></h5>
                                    <p class="card-text"><?php echo get_the_excerpt(); ?></p>
                                    <a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn-primary">Start Test</a>
                                </div>
                            </div>
                        </div>
                        <?php
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>

                <!-- Test History -->
                <div class="tab-pane fade" id="test-history">
                    <h3>Test History</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Test Name</th>
                                    <th>Score</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $results = new WP_Query(array(
                                    'post_type' => 'test_result',
                                    'posts_per_page' => 10,
                                    'author' => get_current_user_id(),
                                    'orderby' => 'date',
                                    'order' => 'DESC'
                                ));

                                while ($results->have_posts()) : $results->the_post();
                                    $test_data = get_post_meta(get_the_ID(), 'test_data', true);
                                ?>
                                <tr>
                                    <td><?php echo get_the_date(); ?></td>
                                    <td><?php echo esc_html($test_data['test_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo esc_html($test_data['score'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn-sm btn-info">View Details</a>
                                    </td>
                                </tr>
                                <?php
                                endwhile;
                                wp_reset_postdata();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Results -->
                <div class="tab-pane fade" id="results">
                    <h3>Results Analysis</h3>
                    <div class="card mb-4">
                        <div class="card-body">
                            <canvas id="resultsChart"></canvas>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Performance Summary</h5>
                                    <!-- Add performance metrics here -->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Recommendations</h5>
                                    <!-- Add recommendations here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile -->
                <div class="tab-pane fade" id="profile">
                    <h3>Profile Settings</h3>
                    <div class="card">
                        <div class="card-body">
                            <form id="profile-form" method="post">
                                <?php wp_nonce_field('update_profile', 'profile_nonce'); ?>
                                <div class="mb-3">
                                    <label for="display_name" class="form-label">Display Name</label>
                                    <input type="text" class="form-control" id="display_name" name="display_name" value="<?php echo esc_attr($current_user->display_name); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo esc_attr($current_user->user_email); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('resultsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php
                $dates = array();
                $scores = array();
                $results = new WP_Query(array(
                    'post_type' => 'test_result',
                    'posts_per_page' => 10,
                    'author' => get_current_user_id(),
                    'orderby' => 'date',
                    'order' => 'ASC'
                ));
                while ($results->have_posts()) {
                    $dates[] = get_the_date('M d');
                    $test_data = get_post_meta(get_the_ID(), 'test_data', true);
                    $scores[] = $test_data['score'] ?? 0;
                }
                wp_reset_postdata();
                echo json_encode($dates);
            ?>,
            datasets: [{
                label: 'Test Scores',
                data: <?php echo json_encode($scores); ?>,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
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
