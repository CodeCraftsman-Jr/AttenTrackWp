<?php
/**
 * Staff Dashboard Template
 * Dashboard for staff members to view assigned clients and generate reports
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check if user is logged in and has staff role
if (!is_user_logged_in() || !current_user_can('access_staff_dashboard')) {
    wp_redirect(wp_login_url());
    exit;
}

$current_user = wp_get_current_user();
$institution_id = attentrack_get_user_institution_id($current_user->ID);

// Get assigned clients
global $attentrack_assigned_clients;
$assigned_clients = $attentrack_assigned_clients ?? attentrack_get_staff_assigned_clients($current_user->ID);

get_header();
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-3 col-lg-2">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-user-tie"></i> Staff Dashboard</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="?type=staff&section=overview" class="list-group-item list-group-item-action <?php echo (!isset($_GET['section']) || $_GET['section'] == 'overview') ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Overview
                    </a>
                    <a href="?type=staff&section=clients" class="list-group-item list-group-item-action <?php echo (isset($_GET['section']) && $_GET['section'] == 'clients') ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> My Clients (<?php echo count($assigned_clients); ?>)
                    </a>
                    <a href="?type=staff&section=reports" class="list-group-item list-group-item-action <?php echo (isset($_GET['section']) && $_GET['section'] == 'reports') ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary btn-sm btn-block mb-2" onclick="generateClientReport()">
                        <i class="fas fa-file-pdf"></i> Generate Report
                    </button>
                    <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-outline-secondary btn-sm btn-block">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Staff Dashboard</h2>
                    <p class="text-muted mb-0">Welcome, <?php echo esc_html($current_user->display_name); ?></p>
                </div>
                <div>
                    <span class="badge badge-info">
                        <?php echo count($assigned_clients); ?> Assigned Clients
                    </span>
                </div>
            </div>

            <?php
            $section = $_GET['section'] ?? 'overview';
            
            switch ($section) {
                case 'clients':
                    ?>
                    <!-- Assigned Clients Section -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-users"></i> My Assigned Clients</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($assigned_clients)): ?>
                                <div class="row">
                                    <?php foreach ($assigned_clients as $client): ?>
                                        <?php
                                        $client_details = get_client_details($client->ID);
                                        $profile_id = get_user_meta($client->ID, 'profile_id', true);
                                        
                                        // Get recent test count
                                        global $wpdb;
                                        $recent_tests = $wpdb->get_var($wpdb->prepare(
                                            "SELECT COUNT(*) FROM (
                                                SELECT test_date FROM {$wpdb->prefix}attentrack_selective_results WHERE profile_id = %s AND test_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                                UNION ALL
                                                SELECT test_date FROM {$wpdb->prefix}attentrack_extended_results WHERE profile_id = %s AND test_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                                UNION ALL
                                                SELECT test_date FROM {$wpdb->prefix}attentrack_divided_results WHERE profile_id = %s AND test_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                                UNION ALL
                                                SELECT test_date FROM {$wpdb->prefix}attentrack_alternative_results WHERE profile_id = %s AND test_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                            ) as all_tests",
                                            $profile_id, $profile_id, $profile_id, $profile_id
                                        ));
                                        ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h6 class="card-title">
                                                        <?php echo esc_html($client->display_name); ?>
                                                    </h6>
                                                    <p class="card-text small text-muted">
                                                        <?php if ($client_details): ?>
                                                            Age: <?php echo esc_html($client_details['age'] ?: 'N/A'); ?> | 
                                                            Gender: <?php echo esc_html($client_details['gender'] ?: 'N/A'); ?>
                                                        <?php endif; ?>
                                                    </p>
                                                    <p class="card-text">
                                                        <small class="text-muted">
                                                            Tests this month: <span class="badge badge-primary"><?php echo $recent_tests; ?></span>
                                                        </small>
                                                    </p>
                                                </div>
                                                <div class="card-footer">
                                                    <div class="btn-group btn-group-sm w-100">
                                                        <a href="?type=client&view_client=<?php echo $client->ID; ?>" class="btn btn-outline-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        <button class="btn btn-outline-info" onclick="generateClientReport(<?php echo $client->ID; ?>)">
                                                            <i class="fas fa-chart-line"></i> Report
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Clients Assigned</h5>
                                    <p class="text-muted">You don't have any clients assigned to you yet. Please contact your institution administrator.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                    break;
                    
                case 'reports':
                    ?>
                    <!-- Reports Section -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-bar"></i> Client Reports</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Generate Individual Reports</h6>
                                    <div class="form-group">
                                        <label for="clientSelect">Select Client:</label>
                                        <select id="clientSelect" class="form-control">
                                            <option value="">Choose a client...</option>
                                            <?php foreach ($assigned_clients as $client): ?>
                                                <option value="<?php echo $client->ID; ?>">
                                                    <?php echo esc_html($client->display_name); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button class="btn btn-primary" onclick="generateIndividualReport()">
                                        <i class="fas fa-file-pdf"></i> Generate Report
                                    </button>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6>Bulk Reports</h6>
                                    <p class="text-muted">Generate reports for all assigned clients.</p>
                                    <button class="btn btn-info" onclick="generateBulkReport()">
                                        <i class="fas fa-file-archive"></i> Generate All Reports
                                    </button>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <!-- Report History -->
                            <h6>Recent Reports</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Client</th>
                                            <th>Report Type</th>
                                            <th>Generated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                <em>Report history will be displayed here</em>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php
                    break;
                    
                default: // overview
                    ?>
                    <!-- Overview Section -->
                    <div class="row">
                        <!-- Summary Cards -->
                        <div class="col-md-3 mb-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo count($assigned_clients); ?></h4>
                                            <p class="mb-0">Assigned Clients</p>
                                        </div>
                                        <div>
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <?php
                                            // Count active clients (those who took tests recently)
                                            $active_clients = 0;
                                            foreach ($assigned_clients as $client) {
                                                $profile_id = get_user_meta($client->ID, 'profile_id', true);
                                                $recent_activity = $wpdb->get_var($wpdb->prepare(
                                                    "SELECT COUNT(*) FROM (
                                                        SELECT test_date FROM {$wpdb->prefix}attentrack_selective_results WHERE profile_id = %s AND test_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                                        UNION ALL
                                                        SELECT test_date FROM {$wpdb->prefix}attentrack_extended_results WHERE profile_id = %s AND test_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                                        UNION ALL
                                                        SELECT test_date FROM {$wpdb->prefix}attentrack_divided_results WHERE profile_id = %s AND test_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                                        UNION ALL
                                                        SELECT test_date FROM {$wpdb->prefix}attentrack_alternative_results WHERE profile_id = %s AND test_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                                    ) as all_tests",
                                                    $profile_id, $profile_id, $profile_id, $profile_id
                                                ));
                                                if ($recent_activity > 0) $active_clients++;
                                            }
                                            ?>
                                            <h4><?php echo $active_clients; ?></h4>
                                            <p class="mb-0">Active This Week</p>
                                        </div>
                                        <div>
                                            <i class="fas fa-chart-line fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <?php
                                            // Count total tests this month for all assigned clients
                                            $total_tests = 0;
                                            foreach ($assigned_clients as $client) {
                                                $profile_id = get_user_meta($client->ID, 'profile_id', true);
                                                $client_tests = $wpdb->get_var($wpdb->prepare(
                                                    "SELECT COUNT(*) FROM (
                                                        SELECT test_date FROM {$wpdb->prefix}attentrack_selective_results WHERE profile_id = %s AND test_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                                        UNION ALL
                                                        SELECT test_date FROM {$wpdb->prefix}attentrack_extended_results WHERE profile_id = %s AND test_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                                        UNION ALL
                                                        SELECT test_date FROM {$wpdb->prefix}attentrack_divided_results WHERE profile_id = %s AND test_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                                        UNION ALL
                                                        SELECT test_date FROM {$wpdb->prefix}attentrack_alternative_results WHERE profile_id = %s AND test_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                                    ) as all_tests",
                                                    $profile_id, $profile_id, $profile_id, $profile_id
                                                ));
                                                $total_tests += $client_tests;
                                            }
                                            ?>
                                            <h4><?php echo $total_tests; ?></h4>
                                            <p class="mb-0">Tests This Month</p>
                                        </div>
                                        <div>
                                            <i class="fas fa-brain fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>0</h4>
                                            <p class="mb-0">Pending Reviews</p>
                                        </div>
                                        <div>
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Client Activity -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-history"></i> Recent Client Activity</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($assigned_clients)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Client</th>
                                                <th>Last Test</th>
                                                <th>Test Type</th>
                                                <th>Performance</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($assigned_clients, 0, 5) as $client): ?>
                                                <?php
                                                $profile_id = get_user_meta($client->ID, 'profile_id', true);
                                                $last_test = $wpdb->get_row($wpdb->prepare(
                                                    "SELECT 'selective' as test_type, correct_responses, incorrect_responses, test_date
                                                     FROM {$wpdb->prefix}attentrack_selective_results 
                                                     WHERE profile_id = %s
                                                     UNION ALL
                                                     SELECT 'extended' as test_type, correct_responses, incorrect_responses, test_date
                                                     FROM {$wpdb->prefix}attentrack_extended_results 
                                                     WHERE profile_id = %s
                                                     UNION ALL
                                                     SELECT 'divided' as test_type, correct_responses, incorrect_responses, test_date
                                                     FROM {$wpdb->prefix}attentrack_divided_results 
                                                     WHERE profile_id = %s
                                                     UNION ALL
                                                     SELECT 'alternative' as test_type, correct_responses, incorrect_responses, test_date
                                                     FROM {$wpdb->prefix}attentrack_alternative_results 
                                                     WHERE profile_id = %s
                                                     ORDER BY test_date DESC 
                                                     LIMIT 1",
                                                    $profile_id, $profile_id, $profile_id, $profile_id
                                                ));
                                                ?>
                                                <tr>
                                                    <td><?php echo esc_html($client->display_name); ?></td>
                                                    <td>
                                                        <?php if ($last_test): ?>
                                                            <?php echo date('M j, Y', strtotime($last_test->test_date)); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">No tests</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($last_test): ?>
                                                            <span class="badge badge-secondary"><?php echo ucfirst($last_test->test_type); ?></span>
                                                        <?php else: ?>
                                                            -
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($last_test): ?>
                                                            <span class="badge badge-success"><?php echo $last_test->correct_responses; ?></span>
                                                            <span class="badge badge-danger"><?php echo $last_test->incorrect_responses; ?></span>
                                                        <?php else: ?>
                                                            -
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="?type=client&view_client=<?php echo $client->ID; ?>" class="btn btn-sm btn-outline-primary">
                                                            View Details
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No assigned clients to display.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                    break;
            }
            ?>
        </div>
    </div>
</div>

<script>
function generateClientReport(clientId = null) {
    if (!clientId) {
        alert('Please select a client first.');
        return;
    }
    
    // Implementation for generating client reports
    console.log('Generating report for client:', clientId);
    alert('Report generation feature will be implemented.');
}

function generateIndividualReport() {
    const clientId = document.getElementById('clientSelect').value;
    if (!clientId) {
        alert('Please select a client first.');
        return;
    }
    generateClientReport(clientId);
}

function generateBulkReport() {
    // Implementation for bulk report generation
    console.log('Generating bulk reports');
    alert('Bulk report generation feature will be implemented.');
}
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.list-group-item.active {
    background-color: #007bff;
    border-color: #007bff;
}

.bg-primary, .bg-success, .bg-info, .bg-warning {
    background: linear-gradient(45deg, var(--bs-primary), var(--bs-primary-dark)) !important;
}
</style>

<?php get_footer(); ?>
