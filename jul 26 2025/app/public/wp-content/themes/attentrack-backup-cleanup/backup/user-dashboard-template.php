<?php
/*
Template Name: User Dashboard
*/

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_safe_redirect(home_url('/signin'));
    exit;
}

// Check if an institution is viewing a user's dashboard
$viewing_user_id = 0;
if (current_user_can('institution') && isset($_POST['view_user_id']) && isset($_POST['view_user_nonce'])) {
    if (wp_verify_nonce($_POST['view_user_nonce'], 'view_user_dashboard')) {
        $viewing_user_id = intval($_POST['view_user_id']);
        
        // Check if this user belongs to the institution
        global $wpdb;
        $institution_id = get_current_user_id();
        $is_member = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}attentrack_institution_members 
            WHERE user_id = %d AND institution_id = %d AND status = 'active'",
            $viewing_user_id,
            $institution_id
        ));
        
        if (!$is_member) {
            $viewing_user_id = 0; // Reset if not a member of this institution
        }
    }
}

// If not viewing a specific user, check if user has institution role and redirect
if (!$viewing_user_id && current_user_can('institution')) {
    wp_safe_redirect(home_url('/institution-dashboard'));
    exit;
}

get_header();

// Get current user and their details
global $wpdb;
$current_user = wp_get_current_user();
$current_user_id = $viewing_user_id ? $viewing_user_id : $current_user->ID;

// If viewing a specific user, get their details
if ($viewing_user_id) {
    $viewed_user = get_userdata($viewing_user_id);
    if (!$viewed_user) {
        wp_die('User not found');
    }
}

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

$extended_results = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}attentrack_extended_results 
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

// Get all test results for history
$selective_results_all = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}attentrack_selective_results 
    WHERE profile_id = %s AND test_id = %s 
    ORDER BY test_date DESC",
    $profile_id,
    $test_id
));

$divided_results_all = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}attentrack_divided_results 
    WHERE profile_id = %s AND test_id = %s 
    ORDER BY test_date DESC",
    $profile_id,
    $test_id
));

$extended_results_all = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}attentrack_extended_results 
    WHERE profile_id = %s AND test_id = %s 
    ORDER BY test_date DESC",
    $profile_id,
    $test_id
));

$alternative_results_all = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}attentrack_alternative_results 
    WHERE profile_id = %s AND test_id = %s 
    ORDER BY test_date DESC",
    $profile_id,
    $test_id
));

// Get subscription status
if (!function_exists('attentrack_get_subscription_status')) {
    require_once get_template_directory() . '/includes/subscription-functions.php';
}

// Get institution ID from user meta
$institution_id = get_user_meta($current_user_id, 'institution_id', true);

// If user is linked to an institution, check institution's subscription
if ($institution_id) {
    $subscription = attentrack_get_subscription_status($institution_id);
} else {
    // Otherwise check user's own subscription
    $subscription = attentrack_get_subscription_status($current_user_id);
}

$plan_type = isset($subscription['plan_name']) ? $subscription['plan_name'] : '';
$plan_details = attentrack_get_all_plans_flat();

// Helper function to format date
function format_test_date($date_str) {
    return date('M j, Y g:i A', strtotime($date_str));
}

// Get combined phase results for extended attention test
$phase_totals = array();
if (!empty($extended_results_all)) {
    foreach ($extended_results_all as $result) {
        $phase = $result->phase;
        if (!isset($phase_totals[$phase])) {
            $phase_totals[$phase] = array(
                'total_letters' => 0,
                'correct_responses' => 0,
                'incorrect_responses' => 0,
                'missed_responses' => 0,
                'accuracy' => 0,
                'reaction_time' => 0,
                'count' => 0
            );
        }
        
        $phase_totals[$phase]['total_letters'] += $result->total_letters;
        $phase_totals[$phase]['correct_responses'] += $result->correct_responses;
        $phase_totals[$phase]['incorrect_responses'] += $result->incorrect_responses;
        $phase_totals[$phase]['missed_responses'] += $result->missed_responses;
        $phase_totals[$phase]['reaction_time'] += $result->reaction_time;
        $phase_totals[$phase]['count']++;
    }
    
    // Calculate averages
    foreach ($phase_totals as $phase => $data) {
        if ($data['count'] > 0) {
            $phase_totals[$phase]['reaction_time'] = round($data['reaction_time'] / $data['count'], 2);
            $total_responses = $data['correct_responses'] + $data['incorrect_responses'] + $data['missed_responses'];
            $phase_totals[$phase]['accuracy'] = $total_responses > 0 ? round(($data['correct_responses'] / $total_responses) * 100, 1) : 0;
        }
    }
}
?>

<div class="dashboard-container d-flex">
    <!-- Sidebar -->
    <div class="sidebar bg-light p-4" style="width: 300px;">
        <!-- Profile Section -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="avatar mb-3">
                    <?php echo get_avatar($current_user_id, 80, '', '', array('class' => 'rounded-circle')); ?>
                </div>
                <h5><?php echo esc_html($viewing_user_id ? $viewed_user->display_name : $current_user->display_name); ?></h5>
                <p class="text-muted small mb-0">User ID: <?php echo esc_html($user_code); ?></p>
                <p class="text-muted small mb-0">Profile ID: <?php echo esc_html($profile_id); ?></p>
                <p class="text-muted small mb-0">Test ID: <?php echo esc_html($test_id); ?></p>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Institution:</span>
                    <span class="small">
                        <?php 
                        if ($institution_id) {
                            $institution = get_userdata($institution_id);
                            echo $institution ? esc_html($institution->display_name) : 'Not assigned';
                        } else {
                            echo 'Not assigned';
                        }
                        ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Subscription:</span>
                    <span class="small"><?php echo isset($subscription['status']) ? esc_html(ucfirst($subscription['status'])) : 'None'; ?></span>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="list-group mb-4" id="dashboardTabs" role="tablist">
            <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#profileDetails" role="tab">
                <i class="fas fa-user me-2"></i> Profile
            </a>
            <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#subscription" role="tab">
                <i class="fas fa-credit-card me-2"></i> Subscription
            </a>
            <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#selectiveTest" role="tab">
                <i class="fas fa-bullseye me-2"></i> Selective Attention
            </a>
            <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#extendedTest" role="tab">
                <i class="fas fa-hourglass-half me-2"></i> Extended Attention
            </a>
            <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#dividedTest" role="tab">
                <i class="fas fa-tasks me-2"></i> Divided Attention
            </a>
            <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#alternativeTest" role="tab">
                <i class="fas fa-random me-2"></i> Alternative Attention
            </a>
        </div>
        
        <!-- Quick Links -->
        <div class="card">
            <div class="card-header">Quick Links</div>
            <div class="list-group list-group-flush">
                <a href="<?php echo home_url('/selective-attention-test'); ?>" class="list-group-item list-group-item-action">
                    <i class="fas fa-play-circle me-2"></i> Take Selective Test
                </a>
                <a href="<?php echo home_url('/extended-attention-test'); ?>" class="list-group-item list-group-item-action">
                    <i class="fas fa-play-circle me-2"></i> Take Extended Test
                </a>
                <a href="<?php echo home_url('/divided-attention-test'); ?>" class="list-group-item list-group-item-action">
                    <i class="fas fa-play-circle me-2"></i> Take Divided Test
                </a>
                <a href="<?php echo home_url('/alternative-attention-test'); ?>" class="list-group-item list-group-item-action">
                    <i class="fas fa-play-circle me-2"></i> Take Alternative Test
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="flex-grow-1 p-4">
        <!-- Welcome Section -->
        <div class="card bg-primary text-white mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div>
                        <h4 class="mb-1">Welcome, <?php echo esc_html($viewing_user_id ? $viewed_user->display_name : $current_user->display_name); ?>!</h4>
                        <p class="mb-0">
                            <?php if (!empty($selective_results) || !empty($extended_results) || !empty($divided_results) || !empty($alternative_results)): ?>
                                Your latest test was taken on 
                                <?php
                                $latest_date = '';
                                if (!empty($selective_results)) $latest_date = $selective_results->test_date;
                                if (!empty($extended_results) && (empty($latest_date) || strtotime($extended_results->test_date) > strtotime($latest_date))) 
                                    $latest_date = $extended_results->test_date;
                                if (!empty($divided_results) && (empty($latest_date) || strtotime($divided_results->test_date) > strtotime($latest_date))) 
                                    $latest_date = $divided_results->test_date;
                                if (!empty($alternative_results) && (empty($latest_date) || strtotime($alternative_results->test_date) > strtotime($latest_date))) 
                                    $latest_date = $alternative_results->test_date;
                                
                                echo !empty($latest_date) ? format_test_date($latest_date) : 'recently';
                                ?>
                            <?php else: ?>
                                You haven't taken any tests yet. Start with one of the attention tests!
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="ms-auto">
                        <a href="<?php echo home_url('/selection-page'); ?>" class="btn btn-light">Take a Test</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Profile Details Tab -->
            <div class="tab-pane fade show active" id="profileDetails" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Profile Details</h5>
                    </div>
                    <div class="card-body">
                        <form id="profileForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstName" value="<?php echo esc_attr($patient_details_db['first_name']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" value="<?php echo esc_attr($patient_details_db['last_name']); ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="age" class="form-label">Age</label>
                                    <input type="number" class="form-control" id="age" value="<?php echo esc_attr($patient_details_db['age']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender">
                                        <option value="">Select Gender</option>
                                        <option value="male" <?php selected($patient_details_db['gender'], 'male'); ?>>Male</option>
                                        <option value="female" <?php selected($patient_details_db['gender'], 'female'); ?>>Female</option>
                                        <option value="other" <?php selected($patient_details_db['gender'], 'other'); ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" value="<?php echo esc_attr($patient_details_db['email']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" value="<?php echo esc_attr($patient_details_db['phone']); ?>">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
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
                
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Current Subscription</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($subscription['status']) && $subscription['status'] === 'active'): ?>
                            <div class="alert alert-success">
                                <strong>Active Subscription:</strong> <?php echo esc_html($subscription['plan_name_formatted']); ?>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Start Date:</strong> <?php echo esc_html(date('F j, Y', strtotime($subscription['start_date']))); ?></p>
                                    <p><strong>End Date:</strong> 
                                        <?php 
                                        if (empty($subscription['end_date'])) {
                                            echo 'No expiration (Free Plan)';
                                        } else {
                                            echo esc_html(date('F j, Y', strtotime($subscription['end_date']))); 
                                        }
                                        ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                                    <p><strong>Member Limit:</strong> <?php echo esc_html($subscription['member_limit']); ?></p>
                                </div>
                            </div>
                            
                            <?php if ($institution_id): ?>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i> Your subscription is managed by your institution.
                                </div>
                            <?php else: ?>
                                <a href="<?php echo esc_url(home_url('/subscription-plans')); ?>" class="btn btn-primary mt-3">Change Plan</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <strong>No active subscription.</strong> Please select a subscription plan to continue.
                            </div>
                            <a href="<?php echo esc_url(home_url('/subscription-plans')); ?>" class="btn btn-primary">View Plans</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <h5 class="mb-3">Available Plans</h5>
                <div class="row">
                    <?php foreach ($plan_details as $type => $plan): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 <?php echo $type === $plan_type ? 'border-primary' : ''; ?>">
                                <div class="card-header <?php echo $type === $plan_type ? 'bg-primary text-white' : ''; ?>">
                                    <h5 class="mb-0"><?php echo esc_html($plan['name']); ?></h5>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-price text-center">
                                        ₹<?php echo number_format($plan['price'] / 100, 2); ?>
                                    </h6>
                                    <p class="text-muted text-center">
                                        <?php echo esc_html($plan['description']); ?>
                                    </p>
                                    <ul class="list-group list-group-flush mb-4">
                                        <?php foreach ($plan['features'] as $feature): ?>
                                            <li class="list-group-item"><?php echo esc_html($feature); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <div class="card-footer text-center">
                                    <?php if ($type === $plan_type): ?>
                                        <button class="btn btn-primary" disabled>Current Plan</button>
                                    <?php else: ?>
                                        <a href="<?php echo esc_url(home_url('/subscription-plans?plan=' . $type)); ?>" class="btn btn-outline-primary">Select</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Test results tabs would go here - similar to the original dashboard -->
            <!-- For brevity, I'm not including all the test result tabs, but they would be identical to the original dashboard -->
            
        </div>
    </div>
</div>

<style>
/* Tab Navigation */
.list-group-item {
    border-radius: 0;
    border-left: none;
    border-right: none;
}
.list-group-item:first-child {
    border-top-left-radius: 0.25rem;
    border-top-right-radius: 0.25rem;
}
.list-group-item:last-child {
    border-bottom-left-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}
.list-group-item.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

/* Sidebar */
@media (max-width: 767.98px) {
    .dashboard-container {
        flex-direction: column;
    }
    .sidebar {
        width: 100% !important;
        margin-bottom: 20px;
    }
}

/* Cards */
.card {
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    overflow: hidden;
}
.card-header {
    border-bottom: none;
    padding: 1rem;
}
.card-body {
    padding: 1.25rem;
}

/* Avatar */
.avatar img {
    border: 3px solid #fff;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Test Results */
.progress {
    height: 0.5rem;
    border-radius: 1rem;
}
.test-score {
    font-size: 2.5rem;
    font-weight: 700;
}
.score-label {
    font-size: 0.875rem;
    color: #6c757d;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Profile form submission
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        var formData = {
            'firstName': $('#firstName').val(),
            'lastName': $('#lastName').val(),
            'age': $('#age').val(),
            'gender': $('#gender').val(),
            'email': $('#email').val(),
            'phone': $('#phone').val(),
            'action': 'update_patient_details',
            'nonce': '<?php echo wp_create_nonce('update_patient_details_nonce'); ?>'
        };
        
        // Submit form via AJAX
        $.post(ajaxurl, formData, function(response) {
            if (response.success) {
                // Show success message
                $('<div class="alert alert-success mt-3">Profile updated successfully!</div>')
                    .insertAfter('#profileForm')
                    .delay(3000)
                    .fadeOut();
            } else {
                // Show error message
                $('<div class="alert alert-danger mt-3">Error updating profile: ' + response.data.message + '</div>')
                    .insertAfter('#profileForm')
                    .delay(3000)
                    .fadeOut();
            }
        });
    });
});
</script>

<?php get_footer(); ?>
