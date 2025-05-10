<?php
/**
 * Template Name: Dashboard
 */

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_safe_redirect(home_url('/signin'));
    exit;
}

// Get dashboard type from URL parameter
$dashboard_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';

// Get current user and their details
global $wpdb;
$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;
$user_roles = $current_user->roles;

// Check if user has the institution role
$is_institution = !empty($user_roles) && in_array('institution', $user_roles);

// Debug information
error_log('Dashboard type: ' . $dashboard_type);
error_log('User ID: ' . $current_user_id);
error_log('User roles: ' . print_r($user_roles, true));
error_log('Is institution: ' . ($is_institution ? 'yes' : 'no'));

// Check if we're viewing a specific user's dashboard (from institution view)
$viewing_user_id = 0;
if ($is_institution && isset($_POST['view_user_id']) && isset($_POST['view_user_nonce'])) {
    if (wp_verify_nonce($_POST['view_user_nonce'], 'view_user_dashboard')) {
        $viewing_user_id = intval($_POST['view_user_id']);
        
        // Debug information for viewing user
        error_log('Viewing user ID: ' . $viewing_user_id);
        
        // Check if this user belongs to the institution
        $institution_id = $current_user_id;
        $check_query = $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}attentrack_institution_members 
            WHERE user_id = %d AND institution_id = %d AND status = 'active'",
            $viewing_user_id,
            $institution_id
        );
        error_log('Check query: ' . $check_query);
        
        $is_member = $wpdb->get_var($check_query);
        error_log('Is member result: ' . ($is_member ? $is_member : 'Not found'));
        
        // If not a member, let's add them to make the view work
        if (!$is_member) {
            error_log('User not found in institution members, adding them');
            
            // Add the user to the institution members table
            $wpdb->insert(
                $wpdb->prefix . 'attentrack_institution_members',
                array(
                    'institution_id' => $institution_id,
                    'user_id' => $viewing_user_id,
                    'status' => 'active',
                    'role' => 'patient',
                    'created_at' => current_time('mysql')
                )
            );
            
            // Check if insertion was successful
            if ($wpdb->insert_id) {
                $is_member = $wpdb->insert_id;
                error_log('User added to institution members, ID: ' . $is_member);
            } else {
                error_log('Failed to add user to institution members: ' . $wpdb->last_error);
            }
        }
        
        if ($is_member) {
            // Include patient dashboard template with the viewing_user_id
            include(get_template_directory() . '/patient-dashboard-template.php');
            exit;
        } else {
            error_log('User is not a member of this institution');
        }
    }
}

// Load the appropriate template based on type parameter or user role
if ($dashboard_type === 'institution' || ($is_institution && empty($dashboard_type))) {
    // Include institution dashboard template
    include(get_template_directory() . '/institution-dashboard-template.php');
    exit;
} elseif ($dashboard_type === 'patient' || (!$is_institution && empty($dashboard_type))) {
    // Include patient dashboard template
    include(get_template_directory() . '/patient-dashboard-template.php');
    exit;
}

// If we get here, something went wrong - redirect to dashboard router
wp_safe_redirect(home_url('/dashboard-router'));
exit;

// Get user meta values
$profile_id = get_user_meta($current_user_id, 'profile_id', true) ?: 'P6141';
$test_id = get_user_meta($current_user_id, 'test_id', true) ?: 'T6005';
$user_code = get_user_meta($current_user_id, 'user_code', true) ?: 'U0858';

// Fetch patient details from database using test_id and user_code
$patient_details_db = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}attentrack_patient_details WHERE test_id = %s AND user_code = %s",
    $test_id,
    $user_code
), ARRAY_A);

// Set default values if patient details not found
if (!$patient_details_db) {
    $patient_details_db = array(
        'patient_id' => $profile_id,
        'test_id' => $test_id,
        'user_code' => $user_code,
        'first_name' => '',
        'last_name' => '',
        'age' => '',
        'gender' => '',
        'email' => '',
        'phone' => '',
        'created_at' => ''
    );
}

// Debug log function
if (!function_exists('debug_to_console')) {
    function debug_to_console($data, $label = '') {
        echo "<!-- DEBUG $label: " . print_r($data, true) . " -->\n";
    }
}

// Log user info
debug_to_console($current_user->ID, 'User ID');
debug_to_console($user_code, 'User Code');
debug_to_console($profile_id, 'Profile ID');
debug_to_console($test_id, 'Test ID');

// Build and log the query for selective attention
$selective_query = $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}attentrack_selective_results 
    WHERE profile_id = %s AND test_id = %s 
    ORDER BY test_date DESC LIMIT 1",
    $profile_id,
    $test_id
);
debug_to_console($selective_query, 'Selective Query');

// Get and log the results
$selective_results = $wpdb->get_row($selective_query);
debug_to_console($selective_results, 'Selective Results');
debug_to_console($wpdb->last_error, 'DB Error');

// Log table structure
$table_structure = $wpdb->get_results("DESCRIBE {$wpdb->prefix}attentrack_selective_results");
debug_to_console($table_structure, 'Table Structure');

// Count total rows in table
$total_rows = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}attentrack_selective_results");
debug_to_console($total_rows, 'Total Rows in Table');

// Get all rows for debugging
$all_results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}attentrack_selective_results");
debug_to_console($all_results, 'All Results in Table');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch results for all tests
$selective_results = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}attentrack_selective_results 
    WHERE profile_id = %s AND test_id = %s 
    ORDER BY test_date DESC LIMIT 1",
    $profile_id,
    $test_id
));

$divided_results = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}attentrack_divided_results 
    WHERE profile_id = %s AND test_id = %s 
    ORDER BY test_date DESC LIMIT 1",
    $profile_id,
    $test_id
));

$alternative_results = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}attentrack_alternative_results 
    WHERE profile_id = %s AND test_id = %s 
    ORDER BY test_date DESC LIMIT 1",
    $profile_id,
    $test_id
));

$extended_results = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}attentrack_extended_results 
    WHERE profile_id = %s AND test_id = %s 
    ORDER BY test_date DESC LIMIT 1",
    $profile_id,
    $test_id
));

// Get all test results for each type
$selective_results_all = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM wp_attentrack_selective_results WHERE profile_id = %s ORDER BY test_date DESC",
    $profile_id
));

$extended_results_all = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM wp_attentrack_extended_results WHERE profile_id = %s ORDER BY test_date DESC",
    $profile_id
));

$divided_results_all = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM wp_attentrack_divided_results WHERE profile_id = %s ORDER BY test_date DESC",
    $profile_id
));

$alternative_results_all = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM wp_attentrack_alternative_results WHERE profile_id = %s ORDER BY test_date DESC",
    $profile_id
));

// Helper function to format date
if (!function_exists('format_test_date')) {
    function format_test_date($date_str) {
        return date('M j, Y g:i A', strtotime($date_str));
    }
}

// Get combined phase results for extended attention test
$phase_totals = array();
if (!empty($extended_results_all)) {
    foreach ($extended_results_all as $result) {
        $phase = $result->phase;
        if (!isset($phase_totals[$phase])) {
            $phase_totals[$phase] = array(
                'correct' => 0,
                'incorrect' => 0,
                'total_time' => 0,
                'count' => 0
            );
        }
        $phase_totals[$phase]['correct'] += $result->correct_responses;
        $phase_totals[$phase]['incorrect'] += $result->incorrect_responses;
        $phase_totals[$phase]['total_time'] += $result->reaction_time;
        $phase_totals[$phase]['count']++;
    }
}

?>

<!-- Page Wrapper -->
<div class="dashboard-container d-flex">
    <!-- Sidebar -->
    <div class="sidebar bg-light p-4" style="width: 300px;">
        <!-- Profile Section -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="border-bottom pb-2 mb-2">Patient Details</h6>
                <dl class="row mb-0">
                    <dt class="col-6">Test ID:</dt>
                    <dd class="col-6 mb-1"><?php echo esc_html($test_id); ?></dd>
                    
                    <dt class="col-6">User Code:</dt>
                    <dd class="col-6 mb-1"><?php echo esc_html($user_code); ?></dd>
                    
                    <dt class="col-6">Patient ID:</dt>
                    <dd class="col-6 mb-1"><?php echo esc_html($patient_details_db['patient_id']); ?></dd>

                    <dt class="col-6">Name:</dt>
                    <dd class="col-6 mb-1">
                        <?php 
                        $full_name = trim($patient_details_db['first_name'] . ' ' . $patient_details_db['last_name']);
                        echo $full_name ? esc_html($full_name) : 'Not set';
                        ?>
                    </dd>
                    
                    <dt class="col-6">Age:</dt>
                    <dd class="col-6 mb-1"><?php echo $patient_details_db['age'] ? esc_html($patient_details_db['age']) : 'Not set'; ?></dd>
                    
                    <dt class="col-6">Gender:</dt>
                    <dd class="col-6 mb-1"><?php echo $patient_details_db['gender'] ? esc_html(ucfirst($patient_details_db['gender'])) : 'Not set'; ?></dd>
                    
                    <dt class="col-6">Email:</dt>
                    <dd class="col-6 mb-1"><?php echo $patient_details_db['email'] ? esc_html($patient_details_db['email']) : 'Not set'; ?></dd>
                    
                    <dt class="col-6">Phone:</dt>
                    <dd class="col-6 mb-1"><?php echo $patient_details_db['phone'] ? esc_html($patient_details_db['phone']) : 'Not set'; ?></dd>
                </dl>
            </div>
        </div>

        <!-- Navigation -->
        <div class="list-group list-group-flush">
            <a href="#profileDetails" class="list-group-item list-group-item-action" data-bs-toggle="tab" role="tab">
                <i class="fas fa-user-circle me-2"></i> Profile Details
            </a>
            <a href="#selectiveTest" class="list-group-item list-group-item-action" data-bs-toggle="tab" role="tab">
                <i class="fas fa-bullseye me-2"></i> Selective Test
            </a>
            <a href="#dividedTest" class="list-group-item list-group-item-action" data-bs-toggle="tab" role="tab">
                <i class="fas fa-layer-group me-2"></i> Divided Test
            </a>
            <a href="#alternativeTest" class="list-group-item list-group-item-action" data-bs-toggle="tab" role="tab">
                <i class="fas fa-random me-2"></i> Alternative Test
            </a>
            <a href="#extendedTest" class="list-group-item list-group-item-action" data-bs-toggle="tab" role="tab">
                <i class="fas fa-clock me-2"></i> Extended Test
            </a>
            <a href="#subscription" class="list-group-item list-group-item-action" data-bs-toggle="tab" role="tab">
                <i class="fas fa-credit-card me-2"></i> Subscription
            </a>
        </div>
    </div>

    <!-- Content Wrapper -->
    <div class="flex-grow-1 p-4">
        <!-- Welcome Section -->
        <div class="card bg-primary text-white mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <img src="<?php echo get_avatar_url($current_user->ID, array('size' => 64)); ?>" 
                             alt="Profile" 
                             class="rounded-circle mb-2" 
                             width="64" 
                             height="64">
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mb-1">Welcome, <?php echo esc_html($current_user->display_name); ?>!</h4>
                        <div class="small">
                            Profile ID: <?php echo esc_html($profile_id); ?><br>
                            Test ID: <?php echo esc_html($test_id); ?><br>
                            User Code: <?php echo esc_html($user_code); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Profile Details Tab -->
            <div class="tab-pane fade" id="profileDetails" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Patient Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-4">Test ID:</dt>
                                    <dd class="col-8"><?php echo esc_html($test_id); ?></dd>
                                    
                                    <dt class="col-4">User Code:</dt>
                                    <dd class="col-8"><?php echo esc_html($user_code); ?></dd>
                                    
                                    <dt class="col-4">Patient ID:</dt>
                                    <dd class="col-8"><?php echo esc_html($patient_details_db['patient_id']); ?></dd>
                                    
                                    <dt class="col-4">First Name:</dt>
                                    <dd class="col-8"><?php echo $patient_details_db['first_name'] ? esc_html($patient_details_db['first_name']) : 'Not set'; ?></dd>
                                    
                                    <dt class="col-4">Last Name:</dt>
                                    <dd class="col-8"><?php echo $patient_details_db['last_name'] ? esc_html($patient_details_db['last_name']) : 'Not set'; ?></dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-4">Age:</dt>
                                    <dd class="col-8"><?php echo $patient_details_db['age'] ? esc_html($patient_details_db['age']) : 'Not set'; ?></dd>
                                    
                                    <dt class="col-4">Gender:</dt>
                                    <dd class="col-8"><?php echo $patient_details_db['gender'] ? esc_html(ucfirst($patient_details_db['gender'])) : 'Not set'; ?></dd>
                                    
                                    <dt class="col-4">Email:</dt>
                                    <dd class="col-8"><?php echo $patient_details_db['email'] ? esc_html($patient_details_db['email']) : 'Not set'; ?></dd>
                                    
                                    <dt class="col-4">Phone:</dt>
                                    <dd class="col-8"><?php echo $patient_details_db['phone'] ? esc_html($patient_details_db['phone']) : 'Not set'; ?></dd>
                                    
                                    <dt class="col-4">Created:</dt>
                                    <dd class="col-8"><?php echo $patient_details_db['created_at'] ? esc_html(date('F j, Y', strtotime($patient_details_db['created_at']))) : 'Not set'; ?></dd>
                                </dl>
                            </div>
                        </div>
                        
                        <?php if (!$patient_details_db['first_name']): ?>
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i> No patient details found for Test ID: <?php echo esc_html($test_id); ?> and User Code: <?php echo esc_html($user_code); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Subscription Tab -->
            <div class="tab-pane fade" id="subscription" role="tabpanel">
                <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                    <div class="alert alert-success">
                        Payment successful! Your subscription is now active.
                    </div>
                <?php endif; ?>

                <?php 
                // Get subscription status
                $subscription = attentrack_get_subscription_status($current_user_id);
                $plan_type = $subscription['plan_type'];
                $status = $subscription['status'];
                $end_date = $subscription['end_date'];
                
                // Get plan details
                $plan_details = [
                    'free' => [
                        'name' => 'Free Plan',
                        'price' => '0',
                        'duration' => '15 seconds',
                        'features' => ['Demo tests only', 'Results not saved', 'Basic features']
                    ],
                    'basic' => [
                        'name' => 'Basic Plan',
                        'price' => '9.99',
                        'duration' => '5 minutes',
                        'features' => ['Full-length tests', 'Save results', 'Basic analytics']
                    ],
                    'premium' => [
                        'name' => 'Premium Plan',
                        'price' => '19.99',
                        'duration' => '5 minutes',
                        'features' => ['Full-length tests', 'Save results', 'Advanced analytics', 'Priority support']
                    ]
                ];
                ?>

                <!-- Current Plan -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Current Subscription</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6>Plan: <?php echo esc_html($plan_details[$plan_type]['name']); ?></h6>
                                <p class="mb-2">Status: 
                                    <span class="badge bg-<?php echo $status === 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst(esc_html($status)); ?>
                                    </span>
                                </p>
                                <?php if ($end_date && $status === 'active'): ?>
                                    <p class="mb-0">Valid until: <?php echo date('F j, Y', strtotime($end_date)); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3">Features:</h6>
                                <ul class="list-unstyled">
                                    <?php foreach ($plan_details[$plan_type]['features'] as $feature): ?>
                                        <li><i class="fas fa-check text-success me-2"></i><?php echo esc_html($feature); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Plans -->
                <h5 class="mb-4">Available Plans</h5>
                <div class="row">
                    <?php foreach ($plan_details as $type => $plan): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 <?php echo $type === $plan_type ? 'border-primary' : ''; ?>">
                                <div class="card-header <?php echo $type === $plan_type ? 'bg-primary text-white' : ''; ?>">
                                    <h5 class="mb-0"><?php echo esc_html($plan['name']); ?></h5>
                                </div>
                                <div class="card-body">
                                    <h3 class="text-center mb-4">$<?php echo esc_html($plan['price']); ?><small class="text-muted">/month</small></h3>
                                    <ul class="list-unstyled">
                                        <?php foreach ($plan['features'] as $feature): ?>
                                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i><?php echo esc_html($feature); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <?php if ($type === $plan_type): ?>
                                        <button class="btn btn-primary w-100" disabled>Current Plan</button>
                                    <?php else: ?>
                                        <a href="<?php echo esc_url(home_url('/checkout?plan=' . $type)); ?>" class="btn btn-outline-primary w-100">
                                            Upgrade Now
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Selective Attention Test -->
            <div class="tab-pane fade" id="selectiveTest" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Selective Attention Test Results</h5>
                        <?php if (!empty($selective_results_all)): ?>
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#selectiveHistory">
                                View History
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($selective_results_all)): ?>
                            <?php $latest = $selective_results_all[0]; ?>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Total Letters</div>
                                        <div class="h4 mb-0"><?php echo $latest->total_letters; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">P Letters</div>
                                        <div class="h4 mb-0"><?php echo $latest->p_letters; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Correct</div>
                                        <div class="h4 mb-0 text-success"><?php echo $latest->correct_responses; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Wrong</div>
                                        <div class="h4 mb-0 text-danger"><?php echo $latest->incorrect_responses; ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="collapse mt-4" id="selectiveHistory">
                                <h6 class="mb-3">Test History</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Total Letters</th>
                                                <th>P Letters</th>
                                                <th>Correct</th>
                                                <th>Incorrect</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($selective_results_all as $result): ?>
                                            <tr>
                                                <td><?php echo format_test_date($result->test_date); ?></td>
                                                <td><?php echo $result->total_letters; ?></td>
                                                <td><?php echo $result->p_letters; ?></td>
                                                <td><?php echo $result->correct_responses; ?></td>
                                                <td><?php echo $result->incorrect_responses; ?></td>
                                                <td><?php echo number_format($result->reaction_time, 2); ?>s</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-center py-3 mb-0">
                                No test results available. 
                                <a href="<?php echo home_url('/selective-attention-test'); ?>" class="btn btn-primary btn-sm ms-2">Take Test</a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Extended Attention Test -->
            <div class="tab-pane fade" id="extendedTest" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Extended Attention Test Results</h5>
                        <?php if (!empty($extended_results_all)): ?>
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#extendedHistory">
                                View History
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($extended_results_all)): ?>
                            <!-- Individual Phase Results -->
                            <div class="mb-4">
                                <h6 class="mb-3">Individual Phase Results</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Phase</th>
                                                <th>Total Correct</th>
                                                <th>Total Wrong</th>
                                                <th>Avg. Time</th>
                                                <th>Success Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php for ($phase = 1; $phase <= 4; $phase++): ?>
                                                <?php if (isset($phase_totals[$phase])): ?>
                                                    <?php 
                                                    $total = $phase_totals[$phase]['correct'] + $phase_totals[$phase]['incorrect'];
                                                    $success_rate = $total > 0 ? round(($phase_totals[$phase]['correct'] / $total) * 100, 1) : 0;
                                                    $avg_time = $phase_totals[$phase]['count'] > 0 ? 
                                                        number_format($phase_totals[$phase]['total_time'] / $phase_totals[$phase]['count'], 2) : 0;
                                                    ?>
                                                    <tr>
                                                        <td>Phase <?php echo $phase; ?></td>
                                                        <td class="text-success"><?php echo $phase_totals[$phase]['correct']; ?></td>
                                                        <td class="text-danger"><?php echo $phase_totals[$phase]['incorrect']; ?></td>
                                                        <td><?php echo $avg_time; ?>s</td>
                                                        <td>
                                                            <div class="progress" style="height: 20px;">
                                                                <div class="progress-bar bg-success" 
                                                                     role="progressbar" 
                                                                     style="width: <?php echo $success_rate; ?>%"
                                                                     aria-valuenow="<?php echo $success_rate; ?>" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                    <?php echo $success_rate; ?>%
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Cumulative Results -->
                            <div class="mb-4">
                                <h6 class="mb-3">Cumulative Results (All Phases)</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Total Correct</th>
                                                <th>Total Wrong</th>
                                                <th>Overall Success Rate</th>
                                                <th>Average Response Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <?php
                                                $total_correct = 0;
                                                $total_wrong = 0;
                                                $total_time = 0;
                                                $total_count = 0;

                                                foreach ($phase_totals as $phase_data) {
                                                    $total_correct += $phase_data['correct'];
                                                    $total_wrong += $phase_data['incorrect'];
                                                    $total_time += $phase_data['total_time'];
                                                    $total_count += $phase_data['count'];
                                                }

                                                $overall_total = $total_correct + $total_wrong;
                                                $overall_success_rate = $overall_total > 0 ? round(($total_correct / $overall_total) * 100, 1) : 0;
                                                $overall_avg_time = $total_count > 0 ? number_format($total_time / $total_count, 2) : 0;
                                                ?>
                                                <td class="text-success"><?php echo $total_correct; ?></td>
                                                <td class="text-danger"><?php echo $total_wrong; ?></td>
                                                <td><?php echo $overall_success_rate; ?>%</td>
                                                <td><?php echo $overall_avg_time; ?> ms</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Latest Result -->
                            <?php $latest = $extended_results_all[0]; ?>
                            <h6 class="mb-3">Latest Test Result (Phase <?php echo $latest->phase; ?>)</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Phase</div>
                                        <div class="h4 mb-0"><?php echo $latest->phase; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Correct</div>
                                        <div class="h4 mb-0 text-success"><?php echo $latest->correct_responses; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Wrong</div>
                                        <div class="h4 mb-0 text-danger"><?php echo $latest->incorrect_responses; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Time</div>
                                        <div class="h4 mb-0"><?php echo number_format($latest->reaction_time, 2); ?>s</div>
                                    </div>
                                </div>
                            </div>

                            <div class="collapse mt-4" id="extendedHistory">
                                <h6 class="mb-3">Test History</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Phase</th>
                                                <th>Correct</th>
                                                <th>Incorrect</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($extended_results_all as $result): ?>
                                            <tr>
                                                <td><?php echo format_test_date($result->test_date); ?></td>
                                                <td><?php echo $result->phase; ?></td>
                                                <td><?php echo $result->correct_responses; ?></td>
                                                <td><?php echo $result->incorrect_responses; ?></td>
                                                <td><?php echo number_format($result->reaction_time, 2); ?>s</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-center py-3 mb-0">
                                No test results available. 
                                <a href="<?php echo home_url('/extended-attention-test'); ?>" class="btn btn-success btn-sm ms-2">Take Test</a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Divided Attention Test -->
            <div class="tab-pane fade" id="dividedTest" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Divided Attention Test Results</h5>
                        <?php if (!empty($divided_results_all)): ?>
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#dividedHistory">
                                View History
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($divided_results_all)): ?>
                            <?php $latest = $divided_results_all[0]; ?>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Correct</div>
                                        <div class="h4 mb-0 text-success"><?php echo $latest->correct_responses; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Wrong</div>
                                        <div class="h4 mb-0 text-danger"><?php echo $latest->incorrect_responses; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Missed</div>
                                        <div class="h4 mb-0 text-warning"><?php echo $latest->missed_responses; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Time</div>
                                        <div class="h4 mb-0"><?php echo number_format($latest->reaction_time, 2); ?>ms</div>
                                    </div>
                                </div>
                            </div>

                            <div class="collapse mt-4" id="dividedHistory">
                                <h6 class="mb-3">Test History</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Correct</th>
                                                <th>Incorrect</th>
                                                <th>Missed</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($divided_results_all as $result): ?>
                                            <tr>
                                                <td><?php echo format_test_date($result->test_date); ?></td>
                                                <td><?php echo $result->correct_responses; ?></td>
                                                <td><?php echo $result->incorrect_responses; ?></td>
                                                <td><?php echo $result->missed_responses; ?></td>
                                                <td><?php echo number_format($result->reaction_time, 2); ?>ms</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-center py-3 mb-0">
                                No test results available. 
                                <a href="<?php echo home_url('/divided-attention-test'); ?>" class="btn btn-info btn-sm ms-2">Take Test</a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Alternative Attention Test -->
            <div class="tab-pane fade" id="alternativeTest" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Alternative Attention Test Results</h5>
                        <?php if (!empty($alternative_results_all)): ?>
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#alternativeHistory">
                                View History
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($alternative_results_all)): ?>
                            <?php $latest = $alternative_results_all[0]; ?>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Correct</div>
                                        <div class="h4 mb-0 text-success"><?php echo $latest->correct_responses; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Wrong</div>
                                        <div class="h4 mb-0 text-danger"><?php echo $latest->incorrect_responses; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Items</div>
                                        <div class="h4 mb-0"><?php echo $latest->total_items_shown; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Time</div>
                                        <div class="h4 mb-0"><?php echo number_format($latest->reaction_time, 2); ?>s</div>
                                    </div>
                                </div>
                            </div>

                            <div class="collapse mt-4" id="alternativeHistory">
                                <h6 class="mb-3">Test History</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Correct</th>
                                                <th>Incorrect</th>
                                                <th>Items</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($alternative_results_all as $result): ?>
                                            <tr>
                                                <td><?php echo format_test_date($result->test_date); ?></td>
                                                <td><?php echo $result->correct_responses; ?></td>
                                                <td><?php echo $result->incorrect_responses; ?></td>
                                                <td><?php echo $result->total_items_shown; ?></td>
                                                <td><?php echo number_format($result->reaction_time, 2); ?>s</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-center py-3 mb-0">
                                No test results available. 
                                <a href="<?php echo home_url('/alternative-attention-test'); ?>" class="btn btn-warning btn-sm ms-2">Take Test</a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Tab Navigation */
.list-group-item {
    border: none;
    border-radius: 0;
    padding: 1rem 1.5rem;
    margin-bottom: 0.25rem;
    transition: all 0.3s ease;
}

.list-group-item:hover {
    background-color: rgba(13, 110, 253, 0.1);
}

.list-group-item.active {
    background-color: #0d6efd;
    color: white;
}

/* Profile Details */
.profile-section dt {
    font-weight: 600;
    color: #495057;
}

.profile-section dd {
    color: #212529;
}

/* Card Styles */
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

.card-header {
    border-top-left-radius: 0.5rem !important;
    border-top-right-radius: 0.5rem !important;
    padding: 1rem 1.5rem;
}

.card-body {
    padding: 1.5rem;
}

/* Tab Content Animation */
.tab-pane {
    transition: all 0.3s ease;
    opacity: 0;
}

.tab-pane.show {
    opacity: 1;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-container {
        flex-direction: column;
    }
    
    .sidebar {
        width: 100% !important;
        margin-bottom: 1.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get hash from URL
    let hash = window.location.hash;
    
    // If hash exists and matches a tab
    if (hash) {
        // Remove the '#' symbol
        hash = hash.replace('#', '');
        
        // Find the tab link
        const tabLink = document.querySelector(`a[href="#${hash}"]`);
        if (tabLink) {
            // Find all tab panes
            const tabPanes = document.querySelectorAll('.tab-pane');
            tabPanes.forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            // Find all tab links
            const tabLinks = document.querySelectorAll('.list-group-item');
            tabLinks.forEach(link => {
                link.classList.remove('active');
            });
            
            // Activate the selected tab
            tabLink.classList.add('active');
            document.getElementById(hash).classList.add('show', 'active');
        }
    }
    
    // Add click event listeners to all tab links
    document.querySelectorAll('.list-group-item').forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href').replace('#', '');
            
            // Update URL hash without scrolling
            history.pushState(null, null, `#${targetId}`);
            
            // Remove active class from all links
            document.querySelectorAll('.list-group-item').forEach(l => {
                l.classList.remove('active');
            });
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Show the selected tab content
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            document.getElementById(targetId).classList.add('show', 'active');
        });
    });
});
</script>

<?php get_footer(); ?>
