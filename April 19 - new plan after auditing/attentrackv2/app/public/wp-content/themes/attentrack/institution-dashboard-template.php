<?php
/*
Template Name: Institution Admin Dashboard
*/

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_safe_redirect(home_url('/signin'));
    exit;
}

// Check if user has the institution role
if (!current_user_can('institution')) {
    wp_safe_redirect(home_url('/user-dashboard'));
    exit;
}

get_header();

// Get current user
$current_user = wp_get_current_user();
$institution_id = $current_user->ID;

// Get subscription data
if (!function_exists('attentrack_get_subscription_status')) {
    require_once get_template_directory() . '/includes/subscription-functions.php';
}
$subscription = attentrack_get_subscription_status($institution_id);
?>

<style>
    .institution-dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    .dashboard-section {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .dashboard-section h2 {
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
        margin-top: 0;
    }
    .user-table, .subscription-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    .user-table th, .user-table td, .subscription-table th, .subscription-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .user-table th, .subscription-table th {
        background-color: #f8f9fa;
    }
    .analytics-cards {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-top: 15px;
    }
    .analytics-card {
        flex: 1 1 220px;
        background: #e8f4f8;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #3498db;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        min-width: 220px;
    }
    .analytics-title {
        font-size: 18px;
        color: #3498db;
        margin-bottom: 10px;
    }
    .analytics-value {
        font-size: 32px;
        font-weight: bold;
        color: #2c3e50;
    }
    .action-buttons {
        display: flex;
        gap: 10px;
    }
    .action-buttons .btn {
        padding: 2px 8px;
        font-size: 0.8rem;
    }
    .status-badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .status-active {
        background-color: #d4edda;
        color: #155724;
    }
    .status-inactive {
        background-color: #f8d7da;
        color: #721c24;
    }
    .status-pending {
        background-color: #fff3cd;
        color: #856404;
    }
    .add-user-form {
        margin-top: 20px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 15px;
    }
    .form-group {
        flex: 1 1 200px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }
    .form-group input, .form-group select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
</style>

<div class="institution-dashboard-container">
    <h1>Institution Dashboard</h1>
    
    <!-- Welcome Section -->
    <div class="dashboard-section">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>Welcome, <?php echo esc_html($current_user->display_name); ?></h2>
                <p>Manage your institution's subscriptions, users, and view analytics.</p>
            </div>
            <div>
                <button id="editInstitutionBtn" class="btn btn-secondary me-2">Edit Institution Details</button>
                <a href="<?php echo esc_url(home_url('/subscription-plans')); ?>" class="btn btn-primary">Upgrade Plan</a>
            </div>
        </div>
    </div>
    
    <!-- Institution Details Edit Form (hidden by default) -->
    <div class="dashboard-section" id="institutionEditForm" style="display: none;">
        <h2>Edit Institution Details</h2>
        <form id="editInstitutionForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="institutionName">Institution Name</label>
                    <input type="text" id="institutionName" name="institutionName" value="<?php echo esc_attr($current_user->display_name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="institutionEmail">Email</label>
                    <input type="email" id="institutionEmail" name="institutionEmail" value="<?php echo esc_attr($current_user->user_email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="institutionPhone">Phone</label>
                    <input type="text" id="institutionPhone" name="institutionPhone" value="<?php echo esc_attr(get_user_meta($current_user->ID, 'phone', true)); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="institutionAddress">Address</label>
                    <input type="text" id="institutionAddress" name="institutionAddress" value="<?php echo esc_attr(get_user_meta($current_user->ID, 'address', true)); ?>">
                </div>
                <div class="form-group">
                    <label for="institutionCity">City</label>
                    <input type="text" id="institutionCity" name="institutionCity" value="<?php echo esc_attr(get_user_meta($current_user->ID, 'city', true)); ?>">
                </div>
                <div class="form-group">
                    <label for="institutionState">State</label>
                    <input type="text" id="institutionState" name="institutionState" value="<?php echo esc_attr(get_user_meta($current_user->ID, 'state', true)); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="institutionZip">Zip Code</label>
                    <input type="text" id="institutionZip" name="institutionZip" value="<?php echo esc_attr(get_user_meta($current_user->ID, 'zip', true)); ?>">
                </div>
                <div class="form-group">
                    <label for="institutionCountry">Country</label>
                    <input type="text" id="institutionCountry" name="institutionCountry" value="<?php echo esc_attr(get_user_meta($current_user->ID, 'country', true)); ?>">
                </div>
                <div class="form-group">
                    <label for="institutionWebsite">Website</label>
                    <input type="url" id="institutionWebsite" name="institutionWebsite" value="<?php echo esc_attr(get_user_meta($current_user->ID, 'website', true)); ?>">
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" id="cancelInstitutionEdit" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
    
    <!-- Subscription Management Section -->
    <div class="dashboard-section" id="subscriptionSection">
        <h2>Subscription Management</h2>
        <?php
        // Debug subscription data
        error_log('Subscription data: ' . print_r($subscription, true));
        
        // Check if there's an active subscription in progress
        global $wpdb;
        $active_subscription = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}attentrack_subscriptions 
            WHERE user_id = %d AND status = 'active'
            ORDER BY created_at DESC LIMIT 1",
            $institution_id
        ));
        
        // If we have a database record but the subscription array doesn't show it as active,
        // refresh the subscription data
        if ($active_subscription && (!isset($subscription['has_subscription']) || !$subscription['has_subscription'])) {
            // Force refresh subscription data
            $subscription = array(
                'has_subscription' => true,
                'plan_name' => $active_subscription->plan_name,
                'plan_name_formatted' => ucfirst(str_replace('_', ' ', $active_subscription->plan_name)),
                'plan_group' => $active_subscription->plan_group,
                'status' => $active_subscription->status,
                'member_limit' => $active_subscription->member_limit,
                'days_limit' => $active_subscription->days_limit,
                'members_used' => 0, // Will be updated below
                'days_used' => 0,
                'days_remaining' => 30,
                'start_date' => $active_subscription->start_date,
                'end_date' => $active_subscription->end_date,
                'is_expired' => false,
                'is_institution' => true
            );
            
            // Get members count
            $members_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}attentrack_institution_members 
                WHERE institution_id = %d",
                $institution_id
            ));
            
            if ($members_count !== null) {
                $subscription['members_used'] = (int)$members_count;
            }
        }
        ?>
        
        <table class="subscription-table">
            <tr>
                <th>Plan Name</th>
                <th>Status</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Members Allowed</th>
                <th>Members Used</th>
                <th>Actions</th>
            </tr>
            <tr>
                <td id="planName"><?php echo isset($subscription['plan_name_formatted']) ? esc_html($subscription['plan_name_formatted']) : 'Free'; ?></td>
                <td>
                    <?php if (isset($subscription['status'])): ?>
                        <span class="status-badge status-<?php echo esc_attr($subscription['status']); ?>">
                            <?php echo esc_html(ucfirst($subscription['status'])); ?>
                        </span>
                    <?php else: ?>
                        <span class="status-badge status-inactive">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php 
                    if (isset($subscription['start_date']) && !empty($subscription['start_date'])) {
                        // Add proper error handling for invalid dates
                        try {
                            $start_timestamp = strtotime($subscription['start_date']);
                            if ($start_timestamp !== false) {
                                echo esc_html(date('M j, Y', $start_timestamp));
                            } else {
                                echo esc_html($subscription['start_date']);
                            }
                        } catch (Exception $e) {
                            echo esc_html($subscription['start_date']);
                        }
                    } else {
                        echo date('M j, Y'); // Show today's date for new subscriptions
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if (isset($subscription['end_date']) && !empty($subscription['end_date'])) {
                        try {
                            $end_timestamp = strtotime($subscription['end_date']);
                            if ($end_timestamp !== false) {
                                echo esc_html(date('M j, Y', $end_timestamp));
                            } else {
                                echo esc_html($subscription['end_date']);
                            }
                        } catch (Exception $e) {
                            echo esc_html($subscription['end_date']);
                        }
                    } else {
                        echo 'No expiration';
                    }
                    ?>
                </td>
                <td><?php echo isset($subscription['member_limit']) ? esc_html($subscription['member_limit']) : '1'; ?></td>
                <td id="membersUsed"><?php echo isset($subscription['members_used']) ? esc_html($subscription['members_used']) : '0'; ?></td>
                <td><a href="<?php echo esc_url(home_url('/subscription-plans')); ?>" class="btn btn-primary btn-sm">Change Plan</a></td>
            </tr>
        </table>
    </div>
    
    <!-- User Management Section -->
    <div class="dashboard-section" id="userManagementSection">
        <div class="d-flex justify-content-between align-items-center">
            <h2>User Management</h2>
            <button class="btn btn-primary" id="showAddUserForm">Add New User</button>
        </div>
        
        <!-- Add User Form (hidden by default) -->
        <div class="add-user-form" id="addUserForm" style="display: none;">
            <h4>Add New User</h4>
            <form id="newUserForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="userName">Name</label>
                        <input type="text" id="userName" name="userName" required>
                    </div>
                    <div class="form-group">
                        <label for="userEmail">Email</label>
                        <input type="email" id="userEmail" name="userEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="userPhone">Phone</label>
                        <input type="tel" id="userPhone" name="userPhone">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="userRole">Role</label>
                        <select id="userRole" name="userRole">
                            <option value="member">Member</option>
                            <option value="patient">Patient</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="userPassword">Password</label>
                        <input type="password" id="userPassword" name="userPassword" required>
                    </div>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Add User</button>
                    <button type="button" class="btn btn-secondary" id="cancelAddUser">Cancel</button>
                </div>
            </form>
        </div>
        
        <table class="user-table" id="userTable">
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Last Active</th>
                <th>Tests Taken</th>
                <th>Added By</th>
                <th>Actions</th>
            </tr>
            <!-- User rows will be dynamically loaded here -->
        </table>
    </div>
    
    <!-- Analytics Section -->
    <div class="dashboard-section" id="analyticsSection">
        <h2>Analytics</h2>
        <div class="analytics-cards">
            <div class="analytics-card">
                <div class="analytics-title">Total Users</div>
                <div class="analytics-value" id="totalUsers">-</div>
            </div>
            <div class="analytics-card">
                <div class="analytics-title">Active Users</div>
                <div class="analytics-value" id="activeUsers">-</div>
            </div>
            <div class="analytics-card">
                <div class="analytics-title">Tests Taken</div>
                <div class="analytics-value" id="testsTaken">-</div>
            </div>
            <div class="analytics-card">
                <div class="analytics-title">Subscription Status</div>
                <div class="analytics-value" id="subscriptionStatus">
                    <?php if (isset($subscription['status'])): ?>
                        <span class="status-badge status-<?php echo esc_attr($subscription['status']); ?>">
                            <?php echo esc_html(ucfirst($subscription['status'])); ?>
                        </span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Test Performance Section -->
        <div class="mt-4">
            <h3>Test Performance Overview</h3>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Test Type</th>
                            <th>Total Tests</th>
                            <th>Average Score</th>
                            <th>Best Performance</th>
                            <th>Needs Improvement</th>
                        </tr>
                    </thead>
                    <tbody id="testPerformanceTable">
                        <!-- Test performance data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Define ajaxurl if it's not already defined
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

jQuery(document).ready(function($) {
    // Toggle Add User Form
    $('#showAddUserForm').click(function() {
        $('#addUserForm').slideDown();
    });
    
    $('#cancelAddUser').click(function() {
        $('#addUserForm').slideUp();
        $('#newUserForm')[0].reset();
    });
    
    // Handle Add User Form Submission
    $('#newUserForm').submit(function(e) {
        e.preventDefault();
        
        const formData = {
            action: 'institution_add_user',
            name: $('#userName').val(),
            email: $('#userEmail').val(),
            phone: $('#userPhone').val(),
            role: $('#userRole').val(),
            password: $('#userPassword').val(),
            nonce: '<?php echo wp_create_nonce('institution_add_user_nonce'); ?>'
        };
        
        $.post(ajaxurl, formData, function(response) {
            if (response.success) {
                alert('User added successfully!');
                $('#addUserForm').slideUp();
                $('#newUserForm')[0].reset();
                
                // Reload user list
                loadUsers();
            } else {
                alert('Error: ' + response.data.message);
            }
        });
    });
    
    // Fetch and display subscription info
    function loadSubscription() {
        $.post(ajaxurl, { action: 'institution_get_subscription' }, function(response) {
            if (response.success && response.data) {
                // Update members used count
                if (response.data.subscription && response.data.subscription.members_used !== undefined) {
                    $('#membersUsed').text(response.data.subscription.members_used || '0');
                } else {
                    $('#membersUsed').text('0');
                }
            } else {
                console.log('Subscription data not available');
                $('#membersUsed').text('0');
            }
        }).fail(function(xhr, status, error) {
            console.error('Error loading subscription data:', error);
            $('#membersUsed').text('0');
        });
    }
    
    // Fetch and display users
    function loadUsers() {
        $.post(ajaxurl, { action: 'institution_get_users' }, function(response) {
            if (response.success && response.data && Array.isArray(response.data.users)) {
                let rows = '';
                response.data.users.forEach(function(user) {
                    rows += `
                        <tr>
                            <td>${user.id}</td>
                            <td>${user.name}</td>
                            <td>${user.email}</td>
                            <td>${user.phone || '-'}</td>
                            <td>${user.role || 'Member'}</td>
                            <td>${user.last_active || 'Never'}</td>
                            <td>${user.tests_taken || '0'}</td>
                            <td>${user.added_by || '-'}</td>
                            <td class="action-buttons">
                                <button class="btn btn-primary view-user" data-id="${user.id}">View</button>
                                <button class="btn btn-danger remove-user" data-id="${user.id}">Remove</button>
                            </td>
                        </tr>
                    `;
                });
                
                // Clear existing rows (except header)
                $('#userTable tr:not(:first)').remove();
                
                // Add new rows
                $('#userTable').append(rows);
                
                // Attach event handlers to new buttons
                attachUserActionHandlers();
            } else {
                console.log('User data not available');
                $('#userTable tr:not(:first)').remove();
                $('#userTable').append('<tr><td colspan="9" style="text-align:center;">No users found</td></tr>');
            }
        }).fail(function(xhr, status, error) {
            console.error('Error loading user data:', error);
            $('#userTable tr:not(:first)').remove();
            $('#userTable').append('<tr><td colspan="9" style="text-align:center;">Error loading users</td></tr>');
        });
    }
    
    // Attach event handlers to user action buttons
    function attachUserActionHandlers() {
        $('.view-user').click(function() {
            const userId = $(this).data('id');
            // Create a temporary form to post to the dashboard page
            const form = $('<form>', {
                'method': 'post',
                'action': '<?php echo home_url('/dashboard'); ?>'
            });
            
            // Add the user_id as a hidden field
            $('<input>').attr({
                'type': 'hidden',
                'name': 'view_user_id',
                'value': userId
            }).appendTo(form);
            
            // Add a nonce for security
            $('<input>').attr({
                'type': 'hidden',
                'name': 'view_user_nonce',
                'value': '<?php echo wp_create_nonce('view_user_dashboard'); ?>'
            }).appendTo(form);
            
            // Submit the form
            form.appendTo('body').submit();
        });
        
        $('.remove-user').click(function() {
            const userId = $(this).data('id');
            if (confirm('Are you sure you want to remove this user?')) {
                $.post(ajaxurl, {
                    action: 'institution_remove_user',
                    user_id: userId,
                    nonce: '<?php echo wp_create_nonce('institution_remove_user_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('User removed successfully!');
                        loadUsers(); // Reload the user list
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                });
            }
        });
    }
    
    // Fetch and display analytics
    function loadAnalytics() {
        $.post(ajaxurl, { action: 'institution_get_analytics' }, function(response) {
            if (response.success && response.data && response.data.analytics) {
                $('#totalUsers').text(response.data.analytics.total_users || '0');
                $('#activeUsers').text(response.data.analytics.active_users || '0');
                $('#testsTaken').text(response.data.analytics.tests_taken || '0');
                
                // Load test performance data if available
                if (response.data.analytics.test_performance && Array.isArray(response.data.analytics.test_performance)) {
                    let rows = '';
                    response.data.analytics.test_performance.forEach(function(test) {
                        rows += `
                            <tr>
                                <td>${test.name}</td>
                                <td>${test.total}</td>
                                <td>${test.average_score}%</td>
                                <td>${test.best_performer || '-'}</td>
                                <td>${test.needs_improvement || '-'}</td>
                            </tr>
                        `;
                    });
                    $('#testPerformanceTable').html(rows);
                }
            } else {
                console.log('Analytics data not available');
                $('#totalUsers').text('0');
                $('#activeUsers').text('0');
                $('#testsTaken').text('0');
            }
        }).fail(function(xhr, status, error) {
            console.error('Error loading analytics data:', error);
            $('#totalUsers').text('0');
            $('#activeUsers').text('0');
            $('#testsTaken').text('0');
        });
    }
    
    // Load all data on page load
    loadSubscription();
    loadUsers();
    loadAnalytics();
    
    // Toggle Institution Edit Form
    $('#editInstitutionBtn').click(function() {
        $('#institutionEditForm').slideDown();
    });
    
    $('#cancelInstitutionEdit').click(function() {
        $('#institutionEditForm').slideUp();
        $('#editInstitutionForm')[0].reset();
    });
    
    // Handle Institution Edit Form Submission
    $('#editInstitutionForm').submit(function(e) {
        e.preventDefault();
        
        const formData = {
            action: 'institution_edit_details',
            name: $('#institutionName').val(),
            email: $('#institutionEmail').val(),
            phone: $('#institutionPhone').val(),
            address: $('#institutionAddress').val(),
            city: $('#institutionCity').val(),
            state: $('#institutionState').val(),
            zip: $('#institutionZip').val(),
            country: $('#institutionCountry').val(),
            website: $('#institutionWebsite').val(),
            nonce: '<?php echo wp_create_nonce('institution_edit_details_nonce'); ?>'
        };
        
        $.post(ajaxurl, formData, function(response) {
            if (response.success) {
                alert('Institution details updated successfully!');
                $('#institutionEditForm').slideUp();
                $('#editInstitutionForm')[0].reset();
            } else {
                alert('Error: ' + response.data.message);
            }
        });
    });
});
</script>

<?php get_footer(); ?>
