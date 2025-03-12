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
                    <?php echo do_shortcode('[test_results]'); ?>
                </div>

                <!-- Results -->
                <div class="tab-pane fade" id="results">
                    <h3>Results Analysis</h3>
                    <?php 
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'test_results';
                    $sessions_table = $wpdb->prefix . 'test_sessions';
                    $user_id = get_current_user_id();
                    
                    // Enable error reporting for debugging
                    $wpdb->show_errors();
                    
                    echo '<div class="debug-info" style="background: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 5px;">';
                    echo '<h5>Debug Information</h5>';
                    echo '<p>User ID: ' . esc_html($user_id) . '</p>';
                    echo '<p>Table Name: ' . esc_html($table_name) . '</p>';
                    echo '<p>Sessions Table: ' . esc_html($sessions_table) . '</p>';
                    
                    // Test database connection
                    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                        echo '<p style="color: red;">Error: Test results table does not exist!</p>';
                    } else {
                        echo '<p style="color: green;">Test results table exists.</p>';
                        
                        // Get test phases results with error handling
                        $test_results = $wpdb->get_results($wpdb->prepare(
                            "SELECT 
                                tr.*,
                                COALESCE(ts.start_time, tr.test_date) as test_date
                            FROM $table_name tr
                            LEFT JOIN $sessions_table ts ON tr.session_id = ts.id
                            WHERE tr.user_id = %d
                            ORDER BY test_date DESC",
                            $user_id
                        ));
                        
                        if ($test_results === null) {
                            echo '<p style="color: red;">Error in query: ' . esc_html($wpdb->last_error) . '</p>';
                        } else {
                            echo '<p>Number of results found: ' . count($test_results) . '</p>';
                            if (count($test_results) > 0) {
                                echo '<p>First result data:</p>';
                                echo '<pre>' . esc_html(print_r($test_results[0], true)) . '</pre>';
                            }
                        }
                    }
                    echo '</div>';

                    if (!empty($test_results)) {
                        // Add chart container
                        echo '<div class="card mb-4">
                            <div class="card-body">
                                <canvas id="resultsChart"></canvas>
                            </div>
                        </div>';
                        
                        // Group results by test phase
                        $grouped_results = array();
                        foreach ($test_results as $result) {
                            $test_name = 'Phase ' . $result->test_phase;
                            if (!isset($grouped_results[$test_name])) {
                                $grouped_results[$test_name] = array();
                            }
                            $grouped_results[$test_name][] = $result;
                        }
                    
                        // Display results
                        foreach ($grouped_results as $test_name => $results): ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><?php echo esc_html($test_name); ?> Results</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Score</th>
                                                    <th>Accuracy</th>
                                                    <th>Reaction Time</th>
                                                    <th>Details</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($results as $result): ?>
                                                    <tr>
                                                        <td><?php echo esc_html(date('Y-m-d H:i', strtotime($result->test_date))); ?></td>
                                                        <td><?php echo esc_html(number_format($result->score, 1)); ?></td>
                                                        <td><?php echo esc_html(number_format($result->accuracy, 1)); ?>%</td>
                                                        <td><?php echo esc_html(number_format($result->reaction_time, 3)); ?>s</td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-info" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#resultModal<?php echo esc_attr($result->id); ?>">
                                                                View Details
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Result Details Modals -->
                            <?php foreach ($results as $result): ?>
                                <div class="modal fade" id="resultModal<?php echo esc_attr($result->id); ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><?php echo esc_html($test_name); ?> - Detailed Results</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6>Test Information</h6>
                                                        <p><strong>Date:</strong> <?php echo esc_html(date('Y-m-d H:i', strtotime($result->test_date))); ?></p>
                                                        <p><strong>Duration:</strong> <?php echo esc_html(number_format($result->duration, 2)); ?>s</p>
                                                        <p><strong>Total Questions:</strong> <?php echo esc_html($result->total_questions); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Performance Metrics</h6>
                                                        <p><strong>Score:</strong> <?php echo esc_html(number_format($result->score, 1)); ?></p>
                                                        <p><strong>Accuracy:</strong> <?php echo esc_html(number_format($result->accuracy, 1)); ?>%</p>
                                                        <p><strong>Reaction Time:</strong> <?php echo esc_html(number_format($result->reaction_time, 3)); ?>s</p>
                                                    </div>
                                                </div>
                                                <?php if (!empty($result->additional_data)): ?>
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <h6>Additional Information</h6>
                                                            <pre class="bg-light p-3 rounded"><?php echo esc_html($result->additional_data); ?></pre>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach;
                    } else {
                        echo '<div class="alert alert-info">No test results found. Complete some tests to see your results here.</div>';
                    }
                    ?>
                </div>

                <!-- Profile -->
                <div class="tab-pane fade" id="profile">
                    <h3>Profile Settings</h3>
                    
                    <!-- Profile Photo Section -->
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <?php 
                            $profile_photo_id = get_user_meta($current_user->ID, 'profile_photo_id', true);
                            $profile_photo_url = $profile_photo_id ? wp_get_attachment_url($profile_photo_id) : get_avatar_url($current_user->ID);
                            ?>
                            <img src="<?php echo esc_url($profile_photo_url); ?>" alt="Profile Photo" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            
                            <form id="photoUploadForm" enctype="multipart/form-data" class="mb-3">
                                <input type="file" id="photoInput" name="profile_photo" accept="image/*" style="display: none;">
                                <label for="photoInput" class="btn btn-primary">Change Profile Photo</label>
                                <?php wp_nonce_field('profile_photo_nonce', 'profile_photo_nonce'); ?>
                            </form>
                            <div id="photoUploadAlert" class="alert" style="display: none;"></div>
                        </div>
                    </div>

                    <!-- Patient Details Section -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Patient Details</h5>
                            <button type="button" class="btn btn-primary btn-sm" onclick="enableEdit()">Edit Details</button>
                        </div>
                        <div class="card-body">
                            <form id="patientDetailsForm">
                                <?php
                                // Get patient details from user meta
                                $patient_id = get_user_meta($current_user->ID, 'patient_id', true);
                                $patient_name = get_user_meta($current_user->ID, 'patient_name', true);
                                $name_parts = explode(' ', $patient_name);
                                $first_name = $name_parts[0];
                                $last_name = isset($name_parts[1]) ? implode(' ', array_slice($name_parts, 1)) : '';
                                $patient_dob = get_user_meta($current_user->ID, 'patient_dob', true);
                                $patient_gender = get_user_meta($current_user->ID, 'patient_gender', true);
                                $patient_phone = get_user_meta($current_user->ID, 'patient_phone', true);
                                $patient_email = get_user_meta($current_user->ID, 'patient_email', true);
                                $patient_address = get_user_meta($current_user->ID, 'patient_address', true);
                                $patient_city_state = get_user_meta($current_user->ID, 'patient_city_state', true);
                                $patient_nationality = get_user_meta($current_user->ID, 'patient_nationality', true);
                                $patient_medical_history = get_user_meta($current_user->ID, 'patient_medical_history', true);
                                ?>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="patientId" class="form-label">Patient ID</label>
                                        <input type="text" class="form-control" id="patientId" name="patientId" value="<?php echo esc_attr($patient_id); ?>" readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="firstName" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo esc_attr($first_name); ?>" disabled required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lastName" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo esc_attr($last_name); ?>" disabled required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="dob" class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" id="dob" name="dob" value="<?php echo esc_attr($patient_dob); ?>" disabled required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="gender" class="form-label">Gender</label>
                                        <select class="form-control" id="gender" name="gender" disabled required>
                                            <option value="Male" <?php selected($patient_gender, 'Male'); ?>>Male</option>
                                            <option value="Female" <?php selected($patient_gender, 'Female'); ?>>Female</option>
                                            <option value="Other" <?php selected($patient_gender, 'Other'); ?>>Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo esc_attr($patient_phone); ?>" disabled required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo esc_attr($patient_email); ?>" disabled required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Place of Work/Study</label>
                                    <textarea class="form-control" id="address" name="address" rows="2" disabled required><?php echo esc_textarea($patient_address); ?></textarea>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="insuranceProvider" class="form-label">City & State</label>
                                        <input type="text" class="form-control" id="insuranceProvider" name="insuranceProvider" value="<?php echo esc_attr($patient_city_state); ?>" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="insuranceNumber" class="form-label">Nationality</label>
                                        <input type="text" class="form-control" id="insuranceNumber" name="insuranceNumber" value="<?php echo esc_attr($patient_nationality); ?>" disabled>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="medicalHistory" class="form-label">Medical History</label>
                                    <textarea class="form-control" id="medicalHistory" name="medicalHistory" rows="3" disabled><?php echo esc_textarea($patient_medical_history); ?></textarea>
                                </div>

                                <div class="text-end" id="editButtons" style="display: none;">
                                    <button type="button" class="btn btn-secondary me-2" onclick="cancelEdit()">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            Danger Zone
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Delete Account</h5>
                            <p class="card-text">Once you delete your account, there is no going back. Please be certain.</p>
                            <button class="btn btn-danger" onclick="showDeleteConfirmation()">Delete Account</button>
                            <?php wp_nonce_field('delete_account_nonce', 'delete_account_nonce'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    <?php
    // Get data for the chart
    if (!empty($test_results)) {
        $chart_data = array_slice($test_results, 0, 15); // Get last 15 results
        
        $labels = [];
        $scores = [];
        $accuracies = [];
        $reactionTimes = [];

        foreach ($chart_data as $result) {
            $date = date('m/d', strtotime($result->test_date));
            $labels[] = $result->test_name . ' (' . $date . ')';
            $scores[] = floatval($result->score);
            $accuracies[] = floatval($result->accuracy);
            $reactionTimes[] = floatval($result->reaction_time);
        }
        
        echo "console.log('Chart data prepared:', " . json_encode([
            'labels' => $labels,
            'scores' => $scores,
            'accuracies' => $accuracies,
            'reactionTimes' => $reactionTimes
        ]) . ");\n";
        ?>
        
        try {
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded');
            } else if (!document.getElementById('resultsChart')) {
                console.error('Results chart canvas not found');
            } else {
                console.log('Initializing chart...');
                const ctx = document.getElementById('resultsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($labels); ?>,
                        datasets: [{
                            label: 'Score',
                            data: <?php echo json_encode($scores); ?>,
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1,
                            yAxisID: 'y'
                        }, {
                            label: 'Accuracy (%)',
                            data: <?php echo json_encode($accuracies); ?>,
                            borderColor: 'rgb(255, 99, 132)',
                            tension: 0.1,
                            yAxisID: 'y1'
                        }, {
                            label: 'Reaction Time (s)',
                            data: <?php echo json_encode($reactionTimes); ?>,
                            borderColor: 'rgb(255, 205, 86)',
                            tension: 0.1,
                            yAxisID: 'y2'
                        }]
                    },
                    options: {
                        responsive: true,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        stacked: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Performance Trends'
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Score'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Accuracy (%)'
                                },
                                grid: {
                                    drawOnChartArea: false
                                }
                            },
                            y2: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Reaction Time (s)'
                                },
                                grid: {
                                    drawOnChartArea: false
                                }
                            }
                        }
                    }
                });
                console.log('Chart initialized successfully');
            }
        } catch (error) {
            console.error('Error initializing chart:', error);
        }
    <?php } else { ?>
        console.log('No test results available for chart');
    <?php } ?>
});
</script>

<?php get_footer(); ?>
