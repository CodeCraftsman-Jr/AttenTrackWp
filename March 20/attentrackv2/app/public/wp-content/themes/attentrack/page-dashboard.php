<?php
/**
 * Template Name: Dashboard
 */

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_safe_redirect(home_url('/signin'));
    exit;
}

$current_user = wp_get_current_user();

// Ensure user has test ID and profile ID
if (function_exists('ensure_user_ids')) {
    $ids = ensure_user_ids($current_user->ID);
    $profile_id = $ids['profile_id'];
    $test_id = $ids['test_id'];
} else {
    // Fallback if function doesn't exist
    $profile_id = get_user_meta($current_user->ID, 'profile_id', true);
    $test_id = get_user_meta($current_user->ID, 'test_id', true);
    
    // Generate IDs if not exist
    if (empty($profile_id)) {
        $profile_id = 'P-' . $current_user->ID;
        update_user_meta($current_user->ID, 'profile_id', $profile_id);
    }
    
    if (empty($test_id)) {
        $test_id = 'T-' . $current_user->ID . '-' . time();
        update_user_meta($current_user->ID, 'test_id', $test_id);
    }
}

get_header();
?>

<div class="dashboard py-5">
    <div class="container">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo get_avatar_url($current_user->ID, array('size' => 64)); ?>" 
                                 alt="<?php echo esc_attr($current_user->display_name); ?>"
                                 class="rounded-circle me-3"
                                 width="64"
                                 height="64">
                            <div>
                                <h4 class="mb-1">Welcome, <?php echo esc_html($current_user->display_name); ?>!</h4>
                                <p class="mb-0">User ID: <?php echo esc_html($current_user->ID); ?></p>
                                <p class="mb-0">Profile ID: <?php echo esc_html($profile_id); ?></p>
                                <p class="mb-0">Test ID: <?php echo esc_html($test_id); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="nav flex-column nav-pills">
                            <button class="nav-link active mb-2" data-bs-toggle="pill" data-bs-target="#profile">
                                <i class="fas fa-user-circle me-2"></i>Profile Details
                            </button>
                            <button class="nav-link mb-2" data-bs-toggle="pill" data-bs-target="#test-results">
                                <i class="fas fa-chart-bar me-2"></i>Test Results
                            </button>
                            <button class="nav-link mb-2" data-bs-toggle="pill" data-bs-target="#test-analysis">
                                <i class="fas fa-chart-line me-2"></i>Test Analysis
                            </button>
                            <button class="nav-link mb-2" data-bs-toggle="pill" data-bs-target="#receipts">
                                <i class="fas fa-receipt me-2"></i>Receipts
                            </button>
                            <button class="nav-link mb-2" data-bs-toggle="pill" data-bs-target="#billing">
                                <i class="fas fa-credit-card me-2"></i>Billing Details
                            </button>
                            <button class="nav-link mb-2" data-bs-toggle="pill" data-bs-target="#subscription">
                                <i class="fas fa-sync-alt me-2"></i>Subscription Management
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#attention-test">
                                <i class="fas fa-brain me-2"></i>Attention Test
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="tab-content">
                    <!-- Profile Section -->
                    <div class="tab-pane fade show active" id="profile">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title mb-0">Profile Details</h5>
                            </div>
                            <div class="card-body">
                                <form id="profile-form" class="needs-validation" novalidate>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" class="form-control" value="<?php echo esc_attr($current_user->display_name); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" value="<?php echo esc_attr($current_user->user_email); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" value="<?php echo esc_attr(get_user_meta($current_user->ID, 'phone_number', true)); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Profile ID</label>
                                            <input type="text" class="form-control" value="<?php echo esc_attr($profile_id); ?>" readonly>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Update Profile
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Additional Details Card -->
                        <div class="card mt-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title mb-0">Account Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="border rounded p-3">
                                            <h6 class="text-muted mb-2">Account Status</h6>
                                            <p class="mb-0">
                                                <span class="badge bg-success">Active</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3">
                                            <h6 class="text-muted mb-2">Member Since</h6>
                                            <p class="mb-0">
                                                <?php echo date('F j, Y', strtotime($current_user->user_registered)); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3">
                                            <h6 class="text-muted mb-2">Tests Completed</h6>
                                            <p class="mb-0">
                                                <?php 
                                                $completed_tests = 0;
                                                for ($i = 1; $i <= 4; $i++) {
                                                    if (get_user_meta($current_user->ID, "test_Phase {$i}_score", true)) {
                                                        $completed_tests++;
                                                    }
                                                }
                                                echo $completed_tests . ' / 4';
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3">
                                            <h6 class="text-muted mb-2">Last Test Date</h6>
                                            <p class="mb-0">
                                                <?php 
                                                $last_test = get_user_meta($current_user->ID, 'last_test_date', true);
                                                echo $last_test ? date('F j, Y', strtotime($last_test)) : 'No tests taken';
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Results Section -->
                    <div class="tab-pane fade" id="test-results">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title mb-0">Test Results</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Test Phase</th>
                                                <th>Score</th>
                                                <th>Time</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $test_phases = array(
                                                'Phase 1' => 'Selective Attention Test',
                                                'Phase 2' => 'Selective Attention Test Extended',
                                                'Phase 3' => 'Divided Attention Test',
                                                'Phase 4' => 'Alternative Attention Test'
                                            );

                                            $test_urls = array(
                                                'Phase 1' => '/selective-attention-test',
                                                'Phase 2' => '/selective-attention-test-extended',
                                                'Phase 3' => '/divided-attention-test',
                                                'Phase 4' => '/alternative-attention-test'
                                            );

                                            $test_keys = array(
                                                'Phase 1' => 'selective_attention',
                                                'Phase 2' => 'selective_attention_extended',
                                                'Phase 3' => 'divided_attention',
                                                'Phase 4' => 'alternative_attention'
                                            );

                                            foreach ($test_phases as $phase => $name) {
                                                $test_key = $test_keys[$phase];
                                                $score = get_user_meta($current_user->ID, "test_{$test_key}_score", true) ?: get_user_meta($current_user->ID, "test_{$phase}_score", true);
                                                $time = get_user_meta($current_user->ID, "test_{$test_key}_time", true) ?: get_user_meta($current_user->ID, "test_{$phase}_time", true);
                                                $status = $score ? 'Completed' : 'Not Started';
                                                $status_class = $score ? 'success' : 'warning';
                                                ?>
                                                <tr>
                                                    <td><?php echo esc_html($name); ?></td>
                                                    <td><?php echo $score ? esc_html($score) : '-'; ?></td>
                                                    <td><?php echo $time ? esc_html($time) : '-'; ?></td>
                                                    <td><span class="badge bg-<?php echo $status_class; ?>"><?php echo esc_html($status); ?></span></td>
                                                    <td>
                                                        <a href="<?php echo esc_url(home_url($test_urls[$phase])); ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <?php echo $score ? 'Retake Test' : 'Start Test'; ?>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Analysis Section -->
                    <div class="tab-pane fade" id="test-analysis">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title mb-0">Test Analysis</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    This section provides detailed analysis of your test performance across all phases.
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title">Performance Overview</h6>
                                                <div class="chart-container" style="position: relative; height:300px;">
                                                    <canvas id="performanceChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title">Strengths</h6>
                                                <ul class="list-group list-group-flush">
                                                    <?php
                                                    // Sample strengths - in production, this would be dynamic based on test results
                                                    $strengths = array(
                                                        'Visual search efficiency',
                                                        'Response time consistency',
                                                        'Distraction resistance'
                                                    );
                                                    
                                                    foreach ($strengths as $strength) {
                                                        echo '<li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-success me-2"></i>' . esc_html($strength) . '</li>';
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title">Areas for Improvement</h6>
                                                <ul class="list-group list-group-flush">
                                                    <?php
                                                    // Sample areas for improvement - in production, this would be dynamic
                                                    $improvements = array(
                                                        'Sustained attention duration',
                                                        'Task switching efficiency',
                                                        'Multiple stimuli processing'
                                                    );
                                                    
                                                    foreach ($improvements as $improvement) {
                                                        echo '<li class="list-group-item bg-transparent"><i class="fas fa-exclamation-circle text-warning me-2"></i>' . esc_html($improvement) . '</li>';
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Receipts Section -->
                    <div class="tab-pane fade" id="receipts">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title mb-0">Receipts</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Receipt ID</th>
                                                <th>Date</th>
                                                <th>Description</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Sample receipts - in production, this would be fetched from database
                                            $receipts = array(
                                                array(
                                                    'id' => 'RCP-' . rand(1000, 9999),
                                                    'date' => date('Y-m-d', strtotime('-2 days')),
                                                    'description' => 'Monthly Subscription',
                                                    'amount' => '$19.99',
                                                    'status' => 'Paid'
                                                ),
                                                array(
                                                    'id' => 'RCP-' . rand(1000, 9999),
                                                    'date' => date('Y-m-d', strtotime('-32 days')),
                                                    'description' => 'Monthly Subscription',
                                                    'amount' => '$19.99',
                                                    'status' => 'Paid'
                                                )
                                            );
                                            
                                            foreach ($receipts as $receipt) {
                                                echo '<tr>';
                                                echo '<td>' . esc_html($receipt['id']) . '</td>';
                                                echo '<td>' . esc_html($receipt['date']) . '</td>';
                                                echo '<td>' . esc_html($receipt['description']) . '</td>';
                                                echo '<td>' . esc_html($receipt['amount']) . '</td>';
                                                echo '<td><span class="badge bg-success">' . esc_html($receipt['status']) . '</span></td>';
                                                echo '<td><a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-download me-1"></i>Download</a></td>';
                                                echo '</tr>';
                                            }
                                            
                                            if (empty($receipts)) {
                                                echo '<tr><td colspan="6" class="text-center">No receipts found</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Billing Section -->
                    <div class="tab-pane fade" id="billing">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title mb-0">Billing Details</h5>
                            </div>
                            <div class="card-body">
                                <form id="billing-form" class="needs-validation" novalidate>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Cardholder Name</label>
                                            <input type="text" class="form-control" value="<?php echo esc_attr($current_user->display_name); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Card Number</label>
                                            <input type="text" class="form-control" value="•••• •••• •••• 4242" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Expiration Date</label>
                                            <input type="text" class="form-control" value="12/25" readonly>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">Billing Address</label>
                                            <input type="text" class="form-control" value="<?php echo esc_attr(get_user_meta($current_user->ID, 'billing_address', true) ?: '123 Main St, Anytown, ST 12345'); ?>" required>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Update Billing Details
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Section -->
                    <div class="tab-pane fade" id="subscription">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title mb-0">Subscription Management</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-success mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-check-circle fa-2x me-3"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="alert-heading mb-1">Active Subscription</h6>
                                            <p class="mb-0">Your Premium plan is active and will renew on <?php echo date('F j, Y', strtotime('+30 days')); ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-4 border-primary">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="card-title mb-0">Current Plan: Premium</h6>
                                            <span class="badge bg-primary">Active</span>
                                        </div>
                                        <ul class="list-group list-group-flush mb-3">
                                            <li class="list-group-item bg-transparent"><i class="fas fa-check text-primary me-2"></i>Unlimited access to all tests</li>
                                            <li class="list-group-item bg-transparent"><i class="fas fa-check text-primary me-2"></i>Detailed performance analytics</li>
                                            <li class="list-group-item bg-transparent"><i class="fas fa-check text-primary me-2"></i>Personalized improvement recommendations</li>
                                            <li class="list-group-item bg-transparent"><i class="fas fa-check text-primary me-2"></i>Priority support</li>
                                        </ul>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">$19.99/month</h6>
                                                <small class="text-muted">Next billing date: <?php echo date('F j, Y', strtotime('+30 days')); ?></small>
                                            </div>
                                            <button class="btn btn-outline-danger">
                                                <i class="fas fa-times-circle me-2"></i>Cancel Subscription
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Payment History</h6>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Amount</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><?php echo date('Y-m-d', strtotime('-2 days')); ?></td>
                                                        <td>$19.99</td>
                                                        <td><span class="badge bg-success">Successful</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo date('Y-m-d', strtotime('-32 days')); ?></td>
                                                        <td>$19.99</td>
                                                        <td><span class="badge bg-success">Successful</span></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attention Test Section -->
                    <div class="tab-pane fade" id="attention-test">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title mb-0">Attention Tests Results</h5>
                            </div>
                            <div class="card-body">
                                <!-- Selective Attention Test -->
                                <div class="card mb-4 border-primary">
                                    <div class="card-header bg-primary bg-opacity-10">
                                        <h6 class="card-title mb-0">Selective Attention Test</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        // Get Selective Attention Test data
                                        $sat_total_letters = get_user_meta($current_user->ID, "test_selective_attention_total_letters", true);
                                        $sat_p_letters = get_user_meta($current_user->ID, "test_selective_attention_p_letters", true);
                                        $sat_correct = get_user_meta($current_user->ID, "test_selective_attention_correct", true);
                                        $sat_incorrect = get_user_meta($current_user->ID, "test_selective_attention_incorrect", true);
                                        $sat_reaction_time = get_user_meta($current_user->ID, "test_selective_attention_time", true);
                                        $sat_score = get_user_meta($current_user->ID, "test_selective_attention_score", true);
                                        
                                        if ($sat_score): // If test has been taken
                                        ?>
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="flex-shrink-0">
                                                        <div class="icon-circle bg-primary text-white">
                                                            <i class="fas fa-search"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-1">Test Performance</h6>
                                                        <p class="text-muted mb-0 small">Find specific targets among distractors</p>
                                                    </div>
                                                </div>
                                                
                                                <div class="text-center my-4">
                                                    <div class="display-4 fw-bold mb-2"><?php echo $sat_score; ?></div>
                                                    <div class="progress" style="height: 10px;">
                                                        <div class="progress-bar bg-primary" 
                                                             role="progressbar" 
                                                             style="width: <?php echo $sat_score; ?>%;" 
                                                             aria-valuenow="<?php echo $sat_score; ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th colspan="2" class="text-center">Test Metrics</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>Total Letters Shown</td>
                                                                <td class="text-end"><?php echo $sat_total_letters ?: 'N/A'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>P Letters Shown</td>
                                                                <td class="text-end"><?php echo $sat_p_letters ?: 'N/A'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Correct Responses</td>
                                                                <td class="text-end"><?php echo $sat_correct ?: 'N/A'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Incorrect Responses</td>
                                                                <td class="text-end"><?php echo $sat_incorrect ?: 'N/A'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Average Reaction Time</td>
                                                                <td class="text-end"><?php echo $sat_reaction_time ? $sat_reaction_time . ' ms' : 'N/A'; ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="text-center my-4">
                                            <div class="text-muted mb-3">You haven't completed this test yet</div>
                                            <a href="<?php echo esc_url(home_url('/selective-attention-test')); ?>" class="btn btn-primary">
                                                Start Test
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Selective Attention Test Extended (with 4 phases) -->
                                <div class="card mb-4 border-success">
                                    <div class="card-header bg-success bg-opacity-10">
                                        <h6 class="card-title mb-0">Selective Attention Test Extended</h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Combined Results Card for all 4 phases -->
                                        <div class="card mb-4 border-dark">
                                            <div class="card-header bg-dark text-white">
                                                <h6 class="card-title mb-0">Overall Results (All Phases)</h6>
                                            </div>
                                            <div class="card-body">
                                                <?php
                                                // Calculate overall metrics for all 4 phases
                                                $total_score = 0;
                                                $total_correct = 0;
                                                $total_incorrect = 0;
                                                $total_reaction_time = 0;
                                                $completed_phases = 0;
                                                
                                                for ($i = 1; $i <= 4; $i++) {
                                                    $phase_score = get_user_meta($current_user->ID, "test_selective_attention_extended_phase{$i}_score", true);
                                                    $phase_correct = get_user_meta($current_user->ID, "test_selective_attention_extended_phase{$i}_correct", true);
                                                    $phase_incorrect = get_user_meta($current_user->ID, "test_selective_attention_extended_phase{$i}_incorrect", true);
                                                    $phase_reaction_time = get_user_meta($current_user->ID, "test_selective_attention_extended_phase{$i}_time", true);
                                                    
                                                    if ($phase_score) {
                                                        $total_score += intval($phase_score);
                                                        $total_correct += intval($phase_correct ?: 0);
                                                        $total_incorrect += intval($phase_incorrect ?: 0);
                                                        $total_reaction_time += intval($phase_reaction_time ?: 0);
                                                        $completed_phases++;
                                                    }
                                                }
                                                
                                                $overall_score = $completed_phases > 0 ? round($total_score / $completed_phases) : 0;
                                                $avg_reaction_time = $completed_phases > 0 ? round($total_reaction_time / $completed_phases) : 0;
                                                
                                                if ($completed_phases > 0):
                                                ?>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-center mb-3">
                                                            <div class="flex-shrink-0">
                                                                <div class="icon-circle bg-success text-white">
                                                                    <i class="fas fa-chart-pie"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h6 class="mb-1">Overall Performance</h6>
                                                                <p class="text-muted mb-0 small">Aggregate score across all phases</p>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="text-center my-4">
                                                            <div class="display-4 fw-bold mb-2"><?php echo $overall_score; ?></div>
                                                            <div class="progress" style="height: 10px;">
                                                                <div class="progress-bar bg-success" 
                                                                     role="progressbar" 
                                                                     style="width: <?php echo $overall_score; ?>%;" 
                                                                     aria-valuenow="<?php echo $overall_score; ?>" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100"></div>
                                                            </div>
                                                            <div class="text-muted mt-2">
                                                                <?php echo $completed_phases; ?> of 4 phases completed
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th colspan="2" class="text-center">Combined Metrics</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td>Total Correct Responses</td>
                                                                        <td class="text-end"><?php echo $total_correct; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Total Incorrect Responses</td>
                                                                        <td class="text-end"><?php echo $total_incorrect; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Average Reaction Time</td>
                                                                        <td class="text-end"><?php echo $avg_reaction_time; ?> ms</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Accuracy Rate</td>
                                                                        <td class="text-end">
                                                                            <?php 
                                                                            $total_responses = $total_correct + $total_incorrect;
                                                                            $accuracy = $total_responses > 0 ? round(($total_correct / $total_responses) * 100) : 0;
                                                                            echo $accuracy . '%'; 
                                                                            ?>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php else: ?>
                                                <div class="text-center my-4">
                                                    <div class="text-muted mb-3">You haven't completed any phases of this test yet</div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Individual Phase Cards -->
                                        <h6 class="mb-3">Individual Phase Results</h6>
                                        <div class="row g-4">
                                            <?php
                                            $phase_names = array(
                                                'Phase 1', 'Phase 2', 'Phase 3', 'Phase 4'
                                            );
                                            
                                            for ($i = 1; $i <= 4; $i++) {
                                                // Get phase data
                                                $phase_total_letters = get_user_meta($current_user->ID, "test_selective_attention_extended_phase{$i}_total_letters", true);
                                                $phase_p_letters = get_user_meta($current_user->ID, "test_selective_attention_extended_phase{$i}_p_letters", true);
                                                $phase_correct = get_user_meta($current_user->ID, "test_selective_attention_extended_phase{$i}_correct", true);
                                                $phase_incorrect = get_user_meta($current_user->ID, "test_selective_attention_extended_phase{$i}_incorrect", true);
                                                $phase_reaction_time = get_user_meta($current_user->ID, "test_selective_attention_extended_phase{$i}_time", true);
                                                $phase_score = get_user_meta($current_user->ID, "test_selective_attention_extended_phase{$i}_score", true);
                                                ?>
                                                <div class="col-md-6 mb-4">
                                                    <div class="card h-100 border-success">
                                                        <div class="card-header bg-success bg-opacity-10">
                                                            <h6 class="card-title mb-0">Phase <?php echo $i; ?></h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <?php if ($phase_score): // If phase has been completed ?>
                                                            <div class="row g-3">
                                                                <div class="col-12">
                                                                    <div class="text-center mb-3">
                                                                        <div class="h4 fw-bold"><?php echo $phase_score; ?></div>
                                                                        <div class="progress" style="height: 8px;">
                                                                            <div class="progress-bar bg-success" 
                                                                                 role="progressbar" 
                                                                                 style="width: <?php echo $phase_score; ?>%;" 
                                                                                 aria-valuenow="<?php echo $phase_score; ?>" 
                                                                                 aria-valuemin="0" 
                                                                                 aria-valuemax="100"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="col-12">
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-bordered">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td>Total Letters Shown</td>
                                                                                    <td class="text-end"><?php echo $phase_total_letters ?: 'N/A'; ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>P Letters Shown</td>
                                                                                    <td class="text-end"><?php echo $phase_p_letters ?: 'N/A'; ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Correct Responses</td>
                                                                                    <td class="text-end"><?php echo $phase_correct ?: 'N/A'; ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Incorrect Responses</td>
                                                                                    <td class="text-end"><?php echo $phase_incorrect ?: 'N/A'; ?></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Reaction Time</td>
                                                                                    <td class="text-end"><?php echo $phase_reaction_time ? number_format($phase_reaction_time, 2) . ' ms' : 'N/A'; ?></td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php else: ?>
                                                            <div class="text-center my-4">
                                                                <div class="text-muted mb-3">Phase not completed yet</div>
                                                            </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        
                                        <div class="text-center">
                                            <a href="<?php echo esc_url(home_url('/selective-attention-test-extended')); ?>" class="btn btn-success">
                                                <?php echo $completed_phases > 0 ? 'Retake Test' : 'Start Test'; ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Divided Attention Test -->
                                <div class="card mb-4 border-info">
                                    <div class="card-header bg-info bg-opacity-10">
                                        <h6 class="card-title mb-0">Divided Attention Test</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        // Get Divided Attention Test data
                                        $dat_correct = get_user_meta($current_user->ID, "test_divided_attention_correct", true);
                                        $dat_incorrect = get_user_meta($current_user->ID, "test_divided_attention_incorrect", true);
                                        $dat_reaction_time = get_user_meta($current_user->ID, "test_divided_attention_time", true);
                                        $dat_score = get_user_meta($current_user->ID, "test_divided_attention_score", true);
                                        
                                        if ($dat_score): // If test has been taken
                                        ?>
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="flex-shrink-0">
                                                        <div class="icon-circle bg-info text-white">
                                                            <i class="fas fa-tasks"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-1">Test Performance</h6>
                                                        <p class="text-muted mb-0 small">Handle multiple tasks simultaneously</p>
                                                    </div>
                                                </div>
                                                
                                                <div class="text-center my-4">
                                                    <div class="display-4 fw-bold mb-2"><?php echo $dat_score; ?></div>
                                                    <div class="progress" style="height: 10px;">
                                                        <div class="progress-bar bg-info" 
                                                             role="progressbar" 
                                                             style="width: <?php echo $dat_score; ?>%;" 
                                                             aria-valuenow="<?php echo $dat_score; ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th colspan="2" class="text-center">Test Metrics</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>Correct Responses</td>
                                                                <td class="text-end"><?php echo $dat_correct ?: 'N/A'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Incorrect Responses</td>
                                                                <td class="text-end"><?php echo $dat_incorrect ?: 'N/A'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Average Reaction Time</td>
                                                                <td class="text-end"><?php echo $dat_reaction_time ? $dat_reaction_time . ' ms' : 'N/A'; ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="text-center my-4">
                                            <div class="text-muted mb-3">You haven't completed this test yet</div>
                                            <a href="<?php echo esc_url(home_url('/divided-attention-test')); ?>" class="btn btn-info">
                                                Start Test
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Alternative Attention Test -->
                                <div class="card mb-4 border-warning">
                                    <div class="card-header bg-warning bg-opacity-10">
                                        <h6 class="card-title mb-0">Alternative Attention Test</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        // Get Alternative Attention Test data
                                        $aat_correct = get_user_meta($current_user->ID, "test_alternative_attention_correct", true);
                                        $aat_incorrect = get_user_meta($current_user->ID, "test_alternative_attention_incorrect", true);
                                        $aat_reaction_time = get_user_meta($current_user->ID, "test_alternative_attention_time", true);
                                        $aat_score = get_user_meta($current_user->ID, "test_alternative_attention_score", true);
                                        
                                        if ($aat_score): // If test has been taken
                                        ?>
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="flex-shrink-0">
                                                        <div class="icon-circle bg-warning text-white">
                                                            <i class="fas fa-brain"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-1">Test Performance</h6>
                                                        <p class="text-muted mb-0 small">Manage cognitive processes and responses</p>
                                                    </div>
                                                </div>
                                                
                                                <div class="text-center my-4">
                                                    <div class="display-4 fw-bold mb-2"><?php echo $aat_score; ?></div>
                                                    <div class="progress" style="height: 10px;">
                                                        <div class="progress-bar bg-warning" 
                                                             role="progressbar" 
                                                             style="width: <?php echo $aat_score; ?>%;" 
                                                             aria-valuenow="<?php echo $aat_score; ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th colspan="2" class="text-center">Test Metrics</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>Correct Responses</td>
                                                                <td class="text-end"><?php echo $aat_correct ?: 'N/A'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Incorrect Responses</td>
                                                                <td class="text-end"><?php echo $aat_incorrect ?: 'N/A'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Average Reaction Time</td>
                                                                <td class="text-end"><?php echo $aat_reaction_time ? $aat_reaction_time . ' ms' : 'N/A'; ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="text-center my-4">
                                            <div class="text-muted mb-3">You haven't completed this test yet</div>
                                            <a href="<?php echo esc_url(home_url('/alternative-attention-test')); ?>" class="btn btn-warning">
                                                Start Test
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard {
    background-color: #f8f9fa;
    min-height: calc(100vh - 76px);
}

.icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.nav-pills .nav-link {
    color: #6c757d;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
}

.nav-pills .nav-link:hover {
    background-color: #f8f9fa;
}

.nav-pills .nav-link.active {
    background-color: #0d6efd;
    color: white;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.5rem;
}

.table {
    margin-bottom: 0;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}

.card-header {
    border-bottom: 1px solid #e9ecef;
}

.form-control {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>

<?php get_footer(); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize charts if the Test Analysis tab is visible
    const testAnalysisTab = document.getElementById('test-analysis');
    if (testAnalysisTab) {
        // Get test scores from PHP
        <?php
        $phase_scores = array();
        $phase_names = array('Visual Search', 'Sustained Attention', 'Divided Attention', 'Executive Control');
        
        for ($i = 1; $i <= 4; $i++) {
            $score = get_user_meta($current_user->ID, "test_Phase {$i}_score", true);
            $phase_scores[] = $score ? intval($score) : 0;
        }
        ?>
        
        const phaseNames = <?php echo json_encode($phase_names); ?>;
        const phaseScores = <?php echo json_encode($phase_scores); ?>;
        
        // Performance Chart
        const performanceCtx = document.getElementById('performanceChart');
        if (performanceCtx) {
            new Chart(performanceCtx, {
                type: 'radar',
                data: {
                    labels: phaseNames,
                    datasets: [{
                        label: 'Your Performance',
                        data: phaseScores,
                        fill: true,
                        backgroundColor: 'rgba(13, 110, 253, 0.2)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        pointBackgroundColor: 'rgba(13, 110, 253, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(13, 110, 253, 1)'
                    }, {
                        label: 'Average User',
                        data: [65, 70, 60, 75],
                        fill: true,
                        backgroundColor: 'rgba(108, 117, 125, 0.2)',
                        borderColor: 'rgba(108, 117, 125, 1)',
                        pointBackgroundColor: 'rgba(108, 117, 125, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(108, 117, 125, 1)'
                    }]
                },
                options: {
                    elements: {
                        line: {
                            borderWidth: 3
                        }
                    },
                    scales: {
                        r: {
                            angleLines: {
                                display: true
                            },
                            suggestedMin: 0,
                            suggestedMax: 100
                        }
                    }
                }
            });
        }
    }
});
</script>
