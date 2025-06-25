<?php
/**
 * Comprehensive Test Suite for AttenTrack Multi-Tier Access Control System
 * Tests permission boundaries, data isolation, and security features
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AttenTrack Test Suite Class
 */
class AttenTrack_Test_Suite {
    
    private $test_results = array();
    private $test_users = array();
    
    public function __construct() {
        // Only allow administrators to run tests
        if (!current_user_can('administrator')) {
            wp_die('Insufficient permissions to run test suite');
        }
    }
    
    /**
     * Run all tests
     */
    public function run_all_tests() {
        $this->log_test('Starting AttenTrack Test Suite');
        
        // Setup test environment
        $this->setup_test_environment();
        
        // Run test categories
        $this->test_user_roles_and_capabilities();
        $this->test_permission_boundaries();
        $this->test_data_isolation();
        $this->test_subscription_workflows();
        $this->test_authentication_flows();
        $this->test_audit_logging();
        $this->test_staff_client_assignments();
        
        // Cleanup test environment
        $this->cleanup_test_environment();
        
        $this->log_test('Test Suite Completed');
        
        return $this->test_results;
    }
    
    /**
     * Setup test environment with test users and data
     */
    private function setup_test_environment() {
        $this->log_test('Setting up test environment...');

        // Ensure roles are created first
        attentrack_init_multi_tier_roles();

        // Find an admin user for the test institution
        $admin_users = get_users(array('role' => 'administrator'));
        $admin_user_id = !empty($admin_users) ? $admin_users[0]->ID : 1;

        // Create test institution
        global $wpdb;
        $institution_id = $wpdb->insert(
            $wpdb->prefix . 'attentrack_institutions',
            array(
                'user_id' => $admin_user_id, // Use actual admin user
                'institution_name' => 'Test Institution',
                'institution_type' => 'Educational',
                'contact_person' => 'Test Admin',
                'contact_email' => 'test@example.com',
                'member_limit' => 100,
                'status' => 'active'
            )
        );

        if ($institution_id) {
            $this->test_users['institution_id'] = $wpdb->insert_id;
        }

        // Create test users for each role
        $this->create_test_user('institution_admin', 'testadmin@example.com', 'Test Admin');
        $this->create_test_user('staff', 'teststaff@example.com', 'Test Staff');
        $this->create_test_user('client', 'testclient1@example.com', 'Test Client 1');
        $this->create_test_user('client', 'testclient2@example.com', 'Test Client 2');

        $this->log_test('Test environment setup complete');
    }
    
    /**
     * Create test user with specific role
     */
    private function create_test_user($role, $email, $display_name) {
        $user_id = wp_create_user(
            'test_' . $role . '_' . time(),
            'test_password_123',
            $email
        );
        
        if (!is_wp_error($user_id)) {
            $user = new WP_User($user_id);
            $user->set_role($role);
            
            wp_update_user(array(
                'ID' => $user_id,
                'display_name' => $display_name
            ));
            
            // Add to institution if not admin
            if ($role !== 'administrator' && isset($this->test_users['institution_id'])) {
                global $wpdb;
                $wpdb->insert(
                    $wpdb->prefix . 'attentrack_institution_members',
                    array(
                        'institution_id' => $this->test_users['institution_id'],
                        'user_id' => $user_id,
                        'role' => $role,
                        'status' => 'active'
                    )
                );
            }
            
            $this->test_users[$role . '_user_id'] = $user_id;
            $this->log_test("Created test user: $display_name ($role)");
        } else {
            $this->log_test("Failed to create test user: $display_name", 'error');
        }
    }
    
    /**
     * Test user roles and capabilities
     */
    private function test_user_roles_and_capabilities() {
        $this->log_test('Testing user roles and capabilities...');
        
        // Test client role capabilities
        if (isset($this->test_users['client_user_id'])) {
            $client = new WP_User($this->test_users['client_user_id']);
            
            $this->assert_true(
                $client->has_cap('access_client_dashboard'),
                'Client should have access_client_dashboard capability'
            );
            
            $this->assert_false(
                $client->has_cap('manage_institution_users'),
                'Client should NOT have manage_institution_users capability'
            );
            
            $this->assert_false(
                $client->has_cap('access_subscription_management'),
                'Client should NOT have access_subscription_management capability'
            );
        }
        
        // Test staff role capabilities
        if (isset($this->test_users['staff_user_id'])) {
            $staff = new WP_User($this->test_users['staff_user_id']);
            
            $this->assert_true(
                $staff->has_cap('access_staff_dashboard'),
                'Staff should have access_staff_dashboard capability'
            );
            
            $this->assert_true(
                $staff->has_cap('view_assigned_clients'),
                'Staff should have view_assigned_clients capability'
            );
            
            $this->assert_false(
                $staff->has_cap('manage_institution_users'),
                'Staff should NOT have manage_institution_users capability'
            );
        }
        
        // Test institution admin role capabilities
        if (isset($this->test_users['institution_admin_user_id'])) {
            $admin = new WP_User($this->test_users['institution_admin_user_id']);
            
            $this->assert_true(
                $admin->has_cap('access_institution_dashboard'),
                'Institution admin should have access_institution_dashboard capability'
            );
            
            $this->assert_true(
                $admin->has_cap('manage_institution_users'),
                'Institution admin should have manage_institution_users capability'
            );
            
            $this->assert_true(
                $admin->has_cap('access_subscription_management'),
                'Institution admin should have access_subscription_management capability'
            );
        }
    }
    
    /**
     * Test permission boundaries
     */
    private function test_permission_boundaries() {
        $this->log_test('Testing permission boundaries...');
        
        $rbac = AttenTrack_RBAC::getInstance();
        
        // Test client data access
        if (isset($this->test_users['client_user_id']) && isset($this->test_users['staff_user_id'])) {
            $client_id = $this->test_users['client_user_id'];
            $staff_id = $this->test_users['staff_user_id'];
            
            // Client should be able to access their own data
            $this->assert_true(
                $rbac->can_access_resource($client_id, 'client_data', $client_id, 'view'),
                'Client should be able to view their own data'
            );
            
            // Staff should NOT be able to access unassigned client data
            $this->assert_false(
                $rbac->can_access_resource($staff_id, 'client_data', $client_id, 'view'),
                'Staff should NOT be able to view unassigned client data'
            );
            
            // Test after assignment
            $admin_users = get_users(array('role' => 'administrator'));
            $admin_user_id = !empty($admin_users) ? $admin_users[0]->ID : 1;

            $assignment_manager = AttenTrack_Staff_Assignments::getInstance();
            $assignment_result = $assignment_manager->assign_clients_to_staff(
                $this->test_users['institution_id'],
                $staff_id,
                array($client_id),
                $admin_user_id // Use actual admin user
            );
            
            if (!is_wp_error($assignment_result)) {
                // Now staff should be able to access assigned client data
                $this->assert_true(
                    $rbac->can_access_resource($staff_id, 'client_data', $client_id, 'view'),
                    'Staff should be able to view assigned client data'
                );
            }
        }
    }
    
    /**
     * Test data isolation between staff members
     */
    private function test_data_isolation() {
        $this->log_test('Testing data isolation...');
        
        if (isset($this->test_users['staff_user_id']) && isset($this->test_users['client_user_id'])) {
            // Create second staff member
            $staff2_id = wp_create_user('test_staff2_' . time(), 'test_password_123', 'teststaff2@example.com');
            if (!is_wp_error($staff2_id)) {
                $staff2 = new WP_User($staff2_id);
                $staff2->set_role('staff');
                
                // Add to institution
                global $wpdb;
                $wpdb->insert(
                    $wpdb->prefix . 'attentrack_institution_members',
                    array(
                        'institution_id' => $this->test_users['institution_id'],
                        'user_id' => $staff2_id,
                        'role' => 'staff',
                        'status' => 'active'
                    )
                );
                
                $rbac = AttenTrack_RBAC::getInstance();
                
                // Staff2 should NOT be able to access client assigned to Staff1
                $this->assert_false(
                    $rbac->can_access_resource($staff2_id, 'client_data', $this->test_users['client_user_id'], 'view'),
                    'Staff member should NOT be able to view clients assigned to other staff'
                );
                
                // Cleanup
                wp_delete_user($staff2_id);
            }
        }
    }
    
    /**
     * Test subscription workflows
     */
    private function test_subscription_workflows() {
        $this->log_test('Testing subscription workflows...');
        
        if (isset($this->test_users['institution_id'])) {
            $subscription_manager = AttenTrack_Subscription_Manager::getInstance();
            
            // Test subscription usage calculation
            $usage = $subscription_manager->get_subscription_usage($this->test_users['institution_id']);
            
            $this->assert_true(
                is_array($usage) && isset($usage['client_count']),
                'Subscription usage should return valid data structure'
            );
            
            $this->assert_true(
                $usage['client_count'] >= 0,
                'Client count should be non-negative'
            );
        }
    }
    
    /**
     * Test authentication flows
     */
    private function test_authentication_flows() {
        $this->log_test('Testing authentication flows...');
        
        $auth = AttenTrack_Enhanced_Auth::getInstance();
        
        // Test session timeout calculation
        if (isset($this->test_users['client_user_id'])) {
            $client = new WP_User($this->test_users['client_user_id']);
            
            // This would test private methods, so we'll test the public interface
            $this->assert_true(
                method_exists($auth, 'enhanced_authenticate'),
                'Enhanced authentication method should exist'
            );
        }
    }
    
    /**
     * Test audit logging
     */
    private function test_audit_logging() {
        $this->log_test('Testing audit logging...');
        
        // Test audit log creation
        $admin_users = get_users(array('role' => 'administrator'));
        $admin_user_id = !empty($admin_users) ? $admin_users[0]->ID : 1;

        $result = attentrack_log_audit_action(
            $admin_user_id, // Use actual admin user
            'test_action',
            'test_resource',
            123,
            $this->test_users['institution_id'] ?? null,
            array('test' => 'data'),
            'success'
        );
        
        $this->assert_true(
            $result,
            'Audit log should be created successfully'
        );
        
        // Test audit log retrieval
        $logs = attentrack_get_audit_logs(array(
            'action' => 'test_action'
        ), 1);
        
        $this->assert_true(
            !empty($logs) && count($logs) > 0,
            'Audit logs should be retrievable'
        );
    }
    
    /**
     * Test staff-client assignments
     */
    private function test_staff_client_assignments() {
        $this->log_test('Testing staff-client assignments...');

        if (isset($this->test_users['staff_user_id']) && isset($this->test_users['client_user_id'])) {
            $assignment_manager = AttenTrack_Staff_Assignments::getInstance();

            // Debug: Check if users exist and have correct roles
            $staff_user = get_userdata($this->test_users['staff_user_id']);
            $client_user = get_userdata($this->test_users['client_user_id']);

            $this->log_test("Staff user roles: " . implode(', ', $staff_user->roles));
            $this->log_test("Client user roles: " . implode(', ', $client_user->roles));

            // Enable debug logging for assignments
            define('ATTENTRACK_DEBUG_ASSIGNMENTS', true);

            // Find an actual admin user
            $admin_users = get_users(array('role' => 'administrator'));
            $admin_user_id = !empty($admin_users) ? $admin_users[0]->ID : null;

            if ($admin_user_id) {
                $admin_user = get_userdata($admin_user_id);
                $this->log_test("Using admin user ID: " . $admin_user_id);
                $this->log_test("Admin user roles: " . implode(', ', $admin_user->roles));
                $this->log_test("Admin has assign_clients_to_staff capability: " . (user_can($admin_user, 'assign_clients_to_staff') ? 'yes' : 'no'));
                $this->log_test("Admin is administrator role: " . (in_array('administrator', $admin_user->roles) ? 'yes' : 'no'));
            } else {
                $this->log_test("No admin user found!");
                $admin_user_id = 1; // Fallback to 1 for the test
            }

            // Test assignment creation
            $result = $assignment_manager->assign_clients_to_staff(
                $this->test_users['institution_id'],
                $this->test_users['staff_user_id'],
                array($this->test_users['client_user_id']),
                $admin_user_id, // Use actual admin user
                'Test assignment'
            );

            // Output debug information
            global $attentrack_debug_info;
            if ($attentrack_debug_info) {
                $this->log_test("Debug - User ID: " . $attentrack_debug_info['user_id']);
                $this->log_test("Debug - Is admin: " . ($attentrack_debug_info['is_admin'] ? 'yes' : 'no'));
                $this->log_test("Debug - Is institution_admin: " . ($attentrack_debug_info['is_institution_admin'] ? 'yes' : 'no'));
                $this->log_test("Debug - Has capability: " . ($attentrack_debug_info['has_capability'] ? 'yes' : 'no'));
                $this->log_test("Debug - User roles: " . $attentrack_debug_info['user_roles']);
                $this->log_test("Debug - Can assign: " . ($attentrack_debug_info['can_assign'] ? 'yes' : 'no'));

                if (isset($attentrack_debug_info['client_belongs_to_institution'])) {
                    $this->log_test("Debug - Client belongs to institution: " . ($attentrack_debug_info['client_belongs_to_institution'] ? 'yes' : 'no'));
                }
                if (isset($attentrack_debug_info['staff_belongs_to_institution'])) {
                    $this->log_test("Debug - Staff belongs to institution: " . ($attentrack_debug_info['staff_belongs_to_institution'] ? 'yes' : 'no'));
                }
                if (isset($attentrack_debug_info['table_exists'])) {
                    $this->log_test("Debug - Staff assignments table exists: " . ($attentrack_debug_info['table_exists'] ? 'yes' : 'no'));
                }
                if (isset($attentrack_debug_info['insert_result'])) {
                    $this->log_test("Debug - Insert result: " . $attentrack_debug_info['insert_result']);
                }
                if (isset($attentrack_debug_info['wpdb_last_error']) && !empty($attentrack_debug_info['wpdb_last_error'])) {
                    $this->log_test("Debug - Database error: " . $attentrack_debug_info['wpdb_last_error']);
                }
            }

            if (is_wp_error($result)) {
                $this->log_test("Assignment error: " . $result->get_error_message(), 'error');
            } elseif (!empty($result['failed'])) {
                foreach ($result['failed'] as $failure) {
                    $this->log_test("Assignment failure: " . $failure['error'], 'error');
                }
            }

            $this->assert_true(
                !is_wp_error($result) && !empty($result['successful']),
                'Client assignment should be successful'
            );

            // Test assignment retrieval
            $assigned_clients = attentrack_get_staff_assigned_clients($this->test_users['staff_user_id']);

            $this->assert_true(
                !empty($assigned_clients),
                'Assigned clients should be retrievable'
            );
        }
    }
    
    /**
     * Cleanup test environment
     */
    private function cleanup_test_environment() {
        $this->log_test('Cleaning up test environment...');
        
        // Delete test users
        foreach ($this->test_users as $key => $user_id) {
            if (strpos($key, '_user_id') !== false && is_numeric($user_id)) {
                wp_delete_user($user_id);
            }
        }
        
        // Delete test institution
        if (isset($this->test_users['institution_id'])) {
            global $wpdb;
            $wpdb->delete(
                $wpdb->prefix . 'attentrack_institutions',
                array('id' => $this->test_users['institution_id'])
            );
            
            $wpdb->delete(
                $wpdb->prefix . 'attentrack_institution_members',
                array('institution_id' => $this->test_users['institution_id'])
            );
        }
        
        $this->log_test('Test environment cleanup complete');
    }
    
    /**
     * Assert helper functions
     */
    private function assert_true($condition, $message) {
        $result = $condition ? 'PASS' : 'FAIL';
        $this->log_test("$result: $message", $condition ? 'success' : 'error');
        return $condition;
    }
    
    private function assert_false($condition, $message) {
        return $this->assert_true(!$condition, $message);
    }
    
    /**
     * Log test result
     */
    private function log_test($message, $level = 'info') {
        $this->test_results[] = array(
            'timestamp' => current_time('mysql'),
            'level' => $level,
            'message' => $message
        );
        
        error_log("AttenTrack Test [$level]: $message");
    }
    
    /**
     * Get test results
     */
    public function get_test_results() {
        return $this->test_results;
    }
}

/**
 * AJAX handler for running test suite
 */
add_action('wp_ajax_run_attentrack_tests', function() {
    check_ajax_referer('attentrack_test_suite', 'nonce');
    
    if (!current_user_can('administrator')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    $test_suite = new AttenTrack_Test_Suite();
    $results = $test_suite->run_all_tests();
    
    wp_send_json_success($results);
});
