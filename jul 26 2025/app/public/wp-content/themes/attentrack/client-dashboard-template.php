<?php
/**
 * Client Dashboard Template
 * Dashboard for clients to access tests, view results, and manage profile
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check if user is logged in and has appropriate permissions
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

// Get current user and viewing user (for institution admins viewing client dashboards)
global $attentrack_viewing_user_id;
$current_user = wp_get_current_user();
$viewing_user_id = $attentrack_viewing_user_id ?? 0;
$display_user_id = $viewing_user_id ?: $current_user->ID;
$display_user = get_userdata($display_user_id);

// Check permissions
if (!attentrack_can_access_resource('client_data', $display_user_id, 'view')) {
    wp_die('You do not have permission to access this dashboard.');
}

// Get client details
$client_details = get_client_details($display_user_id);
$is_viewing_other = ($viewing_user_id && $viewing_user_id != $current_user->ID);

// Get test results
global $wpdb;
$profile_id = get_user_meta($display_user_id, 'profile_id', true);

get_header();
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-3 col-lg-2">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <?php if ($is_viewing_other): ?>
                            <i class="fas fa-user-circle"></i> Viewing Client
                        <?php else: ?>
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        <?php endif; ?>
                    </h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="?type=client&section=overview" class="list-group-item list-group-item-action <?php echo (!isset($_GET['section']) || $_GET['section'] == 'overview') ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Overview
                    </a>
                    <?php if (!$is_viewing_other): ?>
                    <a href="?type=client&section=tests" class="list-group-item list-group-item-action <?php echo (isset($_GET['section']) && $_GET['section'] == 'tests') ? 'active' : ''; ?>">
                        <i class="fas fa-brain"></i> Take Tests
                    </a>
                    <?php endif; ?>
                    <a href="?type=client&section=results" class="list-group-item list-group-item-action <?php echo (isset($_GET['section']) && $_GET['section'] == 'results') ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i> Test Results
                    </a>
                    <a href="?type=client&section=profile" class="list-group-item list-group-item-action <?php echo (isset($_GET['section']) && $_GET['section'] == 'profile') ? 'active' : ''; ?>">
                        <i class="fas fa-user-edit"></i> Profile
                    </a>
                    <?php if ($is_viewing_other): ?>
                    <a href="?type=institution" class="list-group-item list-group-item-action">
                        <i class="fas fa-arrow-left"></i> Back to Institution
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>
                        <?php if ($is_viewing_other): ?>
                            Client Dashboard - <?php echo esc_html($display_user->display_name); ?>
                        <?php else: ?>
                            Welcome, <?php echo esc_html($display_user->display_name); ?>
                        <?php endif; ?>
                    </h2>
                    <p class="text-muted mb-0">
                        <?php if ($client_details && !empty($client_details['profile_id'])): ?>
                            Profile ID: <?php echo esc_html($client_details['profile_id']); ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <?php if (!$is_viewing_other): ?>
                        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php
            $section = $_GET['section'] ?? 'overview';
            
            switch ($section) {
                case 'tests':
                    include get_template_directory() . '/dashboard-sections/client-tests.php';
                    break;
                    
                case 'results':
                    include get_template_directory() . '/dashboard-sections/client-results.php';
                    break;
                    
                case 'profile':
                    include get_template_directory() . '/dashboard-sections/client-profile.php';
                    break;
                    
                default: // overview
                    ?>
                    <!-- Overview Section -->
                    <div class="row">
                        <!-- Profile Summary -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-user"></i> Profile Summary</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($client_details): ?>
                                        <p><strong>Name:</strong> <?php echo esc_html($client_details['name'] ?: 'Not provided'); ?></p>
                                        <p><strong>Age:</strong> <?php echo esc_html($client_details['age'] ?: 'Not provided'); ?></p>
                                        <p><strong>Gender:</strong> <?php echo esc_html($client_details['gender'] ?: 'Not provided'); ?></p>
                                        <?php if (!empty($client_details['email'])): ?>
                                            <p><strong>Email:</strong> <?php echo esc_html($client_details['email']); ?></p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="text-muted">Profile information not available.</p>
                                        <a href="?type=client&section=profile" class="btn btn-primary btn-sm">Complete Profile</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-bar"></i> Test Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Get test counts
                                    $test_counts = array(
                                        'selective' => $wpdb->get_var($wpdb->prepare(
                                            "SELECT COUNT(*) FROM {$wpdb->prefix}attentrack_selective_results WHERE profile_id = %s",
                                            $profile_id
                                        )),
                                        'extended' => $wpdb->get_var($wpdb->prepare(
                                            "SELECT COUNT(*) FROM {$wpdb->prefix}attentrack_extended_results WHERE profile_id = %s",
                                            $profile_id
                                        )),
                                        'divided' => $wpdb->get_var($wpdb->prepare(
                                            "SELECT COUNT(*) FROM {$wpdb->prefix}attentrack_divided_results WHERE profile_id = %s",
                                            $profile_id
                                        )),
                                        'alternative' => $wpdb->get_var($wpdb->prepare(
                                            "SELECT COUNT(*) FROM {$wpdb->prefix}attentrack_alternative_results WHERE profile_id = %s",
                                            $profile_id
                                        ))
                                    );
                                    
                                    $total_tests = array_sum($test_counts);
                                    ?>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h3 class="text-primary"><?php echo $total_tests; ?></h3>
                                            <p class="mb-0">Total Tests</p>
                                        </div>
                                        <div class="col-6">
                                            <h3 class="text-success"><?php echo max($test_counts); ?></h3>
                                            <p class="mb-0">Best Category</p>
                                        </div>
                                    </div>
                                    
                                    <?php if ($total_tests > 0): ?>
                                        <hr>
                                        <small class="text-muted">
                                            Selective: <?php echo $test_counts['selective']; ?> | 
                                            Extended: <?php echo $test_counts['extended']; ?> | 
                                            Divided: <?php echo $test_counts['divided']; ?> | 
                                            Alternative: <?php echo $test_counts['alternative']; ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-history"></i> Recent Test Results</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Get recent test results
                                    $recent_results = $wpdb->get_results($wpdb->prepare(
                                        "SELECT 'selective' as test_type, correct_responses, incorrect_responses, reaction_time, test_date
                                         FROM {$wpdb->prefix}attentrack_selective_results 
                                         WHERE profile_id = %s
                                         UNION ALL
                                         SELECT 'extended' as test_type, correct_responses, incorrect_responses, reaction_time, test_date
                                         FROM {$wpdb->prefix}attentrack_extended_results 
                                         WHERE profile_id = %s
                                         UNION ALL
                                         SELECT 'divided' as test_type, correct_responses, incorrect_responses, reaction_time, test_date
                                         FROM {$wpdb->prefix}attentrack_divided_results 
                                         WHERE profile_id = %s
                                         UNION ALL
                                         SELECT 'alternative' as test_type, correct_responses, incorrect_responses, reaction_time, test_date
                                         FROM {$wpdb->prefix}attentrack_alternative_results 
                                         WHERE profile_id = %s
                                         ORDER BY test_date DESC 
                                         LIMIT 5",
                                        $profile_id, $profile_id, $profile_id, $profile_id
                                    ));
                                    ?>
                                    
                                    <?php if ($recent_results): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Test Type</th>
                                                        <th>Correct</th>
                                                        <th>Incorrect</th>
                                                        <th>Reaction Time</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($recent_results as $result): ?>
                                                        <tr>
                                                            <td><?php echo esc_html(ucfirst($result->test_type)); ?></td>
                                                            <td><span class="badge badge-success"><?php echo $result->correct_responses; ?></span></td>
                                                            <td><span class="badge badge-danger"><?php echo $result->incorrect_responses; ?></span></td>
                                                            <td><?php echo number_format($result->reaction_time, 2); ?>ms</td>
                                                            <td><?php echo date('M j, Y', strtotime($result->test_date)); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="text-center">
                                            <a href="?type=client&section=results" class="btn btn-outline-primary btn-sm">View All Results</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No test results yet.</p>
                                            <?php if (!$is_viewing_other): ?>
                                                <a href="?type=client&section=tests" class="btn btn-primary">Take Your First Test</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    break;
            }
            ?>
        </div>
    </div>
</div>

<style>
.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item.active {
    background-color: #007bff;
    border-color: #007bff;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.badge {
    font-size: 0.75em;
}
</style>

<?php get_footer(); ?>
