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
                                    <th>Test Phase</th>
                                    <th>Total Responses</th>
                                    <th>Correct Responses</th>
                                    <th>Accuracy</th>
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
                                    $test_phase = get_post_meta(get_the_ID(), 'test_phase', true);
                                    $total_responses = get_post_meta(get_the_ID(), 'total_responses', true);
                                    $correct_responses = get_post_meta(get_the_ID(), 'correct_responses', true);
                                    $accuracy = get_post_meta(get_the_ID(), 'accuracy', true);
                                    $test_id = get_post_meta(get_the_ID(), 'test_id', true);
                                ?>
                                <tr>
                                    <td><?php echo get_the_date('F j, Y g:i a'); ?></td>
                                    <td>Phase <?php echo esc_html($test_phase); ?></td>
                                    <td><?php echo esc_html($total_responses); ?></td>
                                    <td><?php echo esc_html($correct_responses); ?></td>
                                    <td><?php echo number_format(floatval($accuracy), 2); ?>%</td>
                                    <td>
                                        <a href="<?php echo esc_url(add_query_arg('test_id', $test_id, home_url('/testresults'))); ?>" class="btn btn-sm btn-info">View Details</a>
                                    </td>
                                </tr>
                                <?php
                                endwhile;
                                wp_reset_postdata();

                                if (!$results->have_posts()) :
                                ?>
                                <tr>
                                    <td colspan="6" class="text-center">No test history available.</td>
                                </tr>
                                <?php endif; ?>
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
                                    <?php
                                    $all_results = new WP_Query(array(
                                        'post_type' => 'test_result',
                                        'posts_per_page' => -1,
                                        'author' => get_current_user_id(),
                                        'orderby' => 'date',
                                        'order' => 'DESC'
                                    ));

                                    $total_tests = 0;
                                    $total_accuracy = 0;
                                    $phase_accuracies = array();

                                    while ($all_results->have_posts()) : $all_results->the_post();
                                        $accuracy = floatval(get_post_meta(get_the_ID(), 'accuracy', true));
                                        $phase = get_post_meta(get_the_ID(), 'test_phase', true);
                                        
                                        if (!isset($phase_accuracies[$phase])) {
                                            $phase_accuracies[$phase] = array();
                                        }
                                        $phase_accuracies[$phase][] = $accuracy;
                                        
                                        $total_accuracy += $accuracy;
                                        $total_tests++;
                                    endwhile;
                                    wp_reset_postdata();

                                    $avg_accuracy = $total_tests > 0 ? $total_accuracy / $total_tests : 0;
                                    ?>
                                    <div class="mt-3">
                                        <p><strong>Total Tests Taken:</strong> <?php echo $total_tests; ?></p>
                                        <p><strong>Overall Average Accuracy:</strong> <?php echo number_format($avg_accuracy, 2); ?>%</p>
                                        <?php foreach ($phase_accuracies as $phase => $accuracies) : ?>
                                            <p><strong>Phase <?php echo esc_html($phase); ?> Average:</strong> 
                                               <?php echo number_format(array_sum($accuracies) / count($accuracies), 2); ?>%</p>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Progress Tracking</h5>
                                    <div class="progress-stats">
                                        <?php foreach ($phase_accuracies as $phase => $accuracies) : 
                                            $latest = end($accuracies);
                                            $first = reset($accuracies);
                                            $improvement = $latest - $first;
                                        ?>
                                            <div class="mb-3">
                                                <h6>Phase <?php echo esc_html($phase); ?></h6>
                                                <p>Improvement: 
                                                    <span class="<?php echo $improvement >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                        <?php echo ($improvement >= 0 ? '+' : '') . number_format($improvement, 2); ?>%
                                                    </span>
                                                </p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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

// Profile photo upload handling
document.getElementById('photoInput').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        const formData = new FormData(document.getElementById('photoUploadForm'));
        formData.append('action', 'handle_profile_photo');

        fetch(attentrack_ajax.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            const alert = document.getElementById('photoUploadAlert');
            if (data.success) {
                // Update profile photo
                document.querySelector('#profile img').src = data.data.photo_url;
                alert.className = 'alert alert-success';
                alert.textContent = 'Photo updated successfully!';
            } else {
                alert.className = 'alert alert-danger';
                alert.textContent = data.data.message || 'Failed to update photo.';
            }
            alert.style.display = 'block';
            setTimeout(() => alert.style.display = 'none', 3000);
        })
        .catch(error => {
            console.error('Error:', error);
            const alert = document.getElementById('photoUploadAlert');
            alert.className = 'alert alert-danger';
            alert.textContent = 'An error occurred. Please try again.';
            alert.style.display = 'block';
            setTimeout(() => alert.style.display = 'none', 3000);
        });
    }
});

// Account deletion handling
function showDeleteConfirmation() {
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}

function deleteAccount() {
    const nonce = document.getElementById('delete_account_nonce').value;
    
    fetch(attentrack_ajax.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'delete_account',
            'nonce': nonce
        }),
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?php echo esc_js(home_url()); ?>';
        } else {
            alert(data.data.message || 'Failed to delete account.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function enableEdit() {
    // Enable all form fields except Patient ID
    const form = document.getElementById('patientDetailsForm');
    const inputs = form.querySelectorAll('input:not([name="patientId"]), select, textarea');
    inputs.forEach(input => input.disabled = false);
    
    // Show edit buttons
    document.getElementById('editButtons').style.display = 'block';
}

function cancelEdit() {
    // Disable all form fields
    const form = document.getElementById('patientDetailsForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => input.disabled = true);
    
    // Hide edit buttons and reset form
    document.getElementById('editButtons').style.display = 'none';
    form.reset();
}

// Handle form submission
document.getElementById('patientDetailsForm').addEventListener('submit', async function(event) {
    event.preventDefault();
    
    const formData = new FormData(this);
    const jsonData = {};
    formData.forEach((value, key) => {
        jsonData[key] = value;
    });

    try {
        const response = await fetch('<?php echo esc_url(home_url("/wp-json/attentrack/v1/save-patient-details")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
            },
            body: JSON.stringify(jsonData),
            credentials: 'same-origin'
        });

        const data = await response.json();
        
        if (data.success) {
            // Disable form fields and hide edit buttons
            const inputs = this.querySelectorAll('input, select, textarea');
            inputs.forEach(input => input.disabled = true);
            document.getElementById('editButtons').style.display = 'none';
            
            // Show success message
            alert('Details updated successfully!');
        } else {
            throw new Error(data.message || 'Failed to update details');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to update details. Please try again.');
    }
});
</script>

<?php get_footer(); ?>
