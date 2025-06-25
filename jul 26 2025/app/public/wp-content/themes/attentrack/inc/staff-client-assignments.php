<?php
/**
 * Staff-Client Assignment System for AttenTrack
 * Manages assignment of clients to staff members within institutions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Staff-Client Assignment Manager Class
 */
class AttenTrack_Staff_Assignments {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Assign multiple clients to a staff member
     */
    public function assign_clients_to_staff($institution_id, $staff_user_id, $client_user_ids, $assigned_by, $notes = '') {
        global $wpdb;

        // Verify permissions - allow administrators and institution admins
        $assigner = get_userdata($assigned_by);
        $can_assign = false;

        if ($assigner) {
            $is_admin = in_array('administrator', $assigner->roles);
            $is_institution_admin = in_array('institution_admin', $assigner->roles);
            $has_capability = user_can($assigner, 'assign_clients_to_staff');

            $can_assign = $is_admin || $is_institution_admin || $has_capability;

            // Debug logging for tests - store in a global for test access
            if (defined('ATTENTRACK_DEBUG_ASSIGNMENTS') && ATTENTRACK_DEBUG_ASSIGNMENTS) {
                global $attentrack_debug_info;
                $attentrack_debug_info = array(
                    'user_id' => $assigned_by,
                    'is_admin' => $is_admin,
                    'is_institution_admin' => $is_institution_admin,
                    'has_capability' => $has_capability,
                    'user_roles' => implode(', ', $assigner->roles),
                    'can_assign' => $can_assign
                );
            }
        }

        if (!$can_assign) {
            return new WP_Error('permission_denied', 'You do not have permission to assign clients to staff.');
        }
        
        // Verify staff member belongs to institution
        if (!$this->user_belongs_to_institution($staff_user_id, $institution_id)) {
            return new WP_Error('invalid_staff', 'Staff member does not belong to this institution.');
        }
        
        // Verify staff member has staff role
        $staff_user = get_userdata($staff_user_id);
        if (!$staff_user || !in_array('staff', $staff_user->roles)) {
            return new WP_Error('invalid_role', 'User is not a staff member.');
        }
        
        $successful_assignments = array();
        $failed_assignments = array();
        
        foreach ($client_user_ids as $client_user_id) {
            // Debug logging for tests
            if (defined('ATTENTRACK_DEBUG_ASSIGNMENTS') && ATTENTRACK_DEBUG_ASSIGNMENTS) {
                global $attentrack_debug_info;
                $attentrack_debug_info['client_belongs_to_institution'] = $this->user_belongs_to_institution($client_user_id, $institution_id);
                $attentrack_debug_info['staff_belongs_to_institution'] = $this->user_belongs_to_institution($staff_user_id, $institution_id);
            }

            // Verify client belongs to institution
            if (!$this->user_belongs_to_institution($client_user_id, $institution_id)) {
                $failed_assignments[] = array(
                    'client_id' => $client_user_id,
                    'error' => 'Client does not belong to this institution'
                );
                continue;
            }

            // Verify client has client role
            $client_user = get_userdata($client_user_id);
            if (!$client_user || !in_array('client', $client_user->roles)) {
                $failed_assignments[] = array(
                    'client_id' => $client_user_id,
                    'error' => 'User is not a client'
                );
                continue;
            }

            // Create or update assignment
            $result = $this->create_assignment($institution_id, $staff_user_id, $client_user_id, $assigned_by, $notes);

            if (is_wp_error($result)) {
                $failed_assignments[] = array(
                    'client_id' => $client_user_id,
                    'error' => $result->get_error_message()
                );
            } else {
                $successful_assignments[] = $client_user_id;

                // Log the assignment
                attentrack_log_audit_action($assigned_by, 'assign_client_to_staff', 'staff_assignment',
                    $result, $institution_id, array(
                        'staff_id' => $staff_user_id,
                        'client_id' => $client_user_id
                    ));
            }
        }
        
        return array(
            'successful' => $successful_assignments,
            'failed' => $failed_assignments
        );
    }
    
    /**
     * Remove client assignments from staff member
     */
    public function remove_clients_from_staff($staff_user_id, $client_user_ids, $removed_by) {
        global $wpdb;
        
        // Verify permissions - allow administrators and institution admins
        $remover = get_userdata($removed_by);
        $can_remove = false;

        if ($remover) {
            $can_remove = in_array('administrator', $remover->roles) ||
                         in_array('institution_admin', $remover->roles) ||
                         user_can($remover, 'assign_clients_to_staff');
        }

        if (!$can_remove) {
            return new WP_Error('permission_denied', 'You do not have permission to modify client assignments.');
        }
        
        $successful_removals = array();
        $failed_removals = array();
        
        foreach ($client_user_ids as $client_user_id) {
            $result = $wpdb->update(
                $wpdb->prefix . 'attentrack_staff_assignments',
                array(
                    'status' => 'inactive',
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'staff_user_id' => $staff_user_id,
                    'client_user_id' => $client_user_id
                )
            );
            
            if ($result !== false) {
                $successful_removals[] = $client_user_id;
                
                // Log the removal
                $institution_id = attentrack_get_user_institution_id($removed_by);
                attentrack_log_audit_action($removed_by, 'remove_client_from_staff', 'staff_assignment', 
                    null, $institution_id, array(
                        'staff_id' => $staff_user_id,
                        'client_id' => $client_user_id
                    ));
            } else {
                $failed_removals[] = array(
                    'client_id' => $client_user_id,
                    'error' => 'Failed to remove assignment'
                );
            }
        }
        
        return array(
            'successful' => $successful_removals,
            'failed' => $failed_removals
        );
    }
    
    /**
     * Get all staff members and their assigned clients for an institution
     */
    public function get_institution_staff_assignments($institution_id) {
        global $wpdb;
        
        // Get all staff members in the institution
        $staff_members = $wpdb->get_results($wpdb->prepare(
            "SELECT u.ID, u.user_login, u.display_name, u.user_email
            FROM {$wpdb->users} u
            INNER JOIN {$wpdb->prefix}attentrack_institution_members im ON u.ID = im.user_id
            INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
            WHERE im.institution_id = %d 
            AND im.status = 'active'
            AND um.meta_key = '{$wpdb->prefix}capabilities'
            AND um.meta_value LIKE %s",
            $institution_id,
            '%staff%'
        ));
        
        $staff_assignments = array();
        
        foreach ($staff_members as $staff) {
            // Get assigned clients for this staff member
            $assigned_clients = $wpdb->get_results($wpdb->prepare(
                "SELECT u.ID, u.user_login, u.display_name, u.user_email, sa.assignment_date, sa.notes
                FROM {$wpdb->users} u
                INNER JOIN {$wpdb->prefix}attentrack_staff_assignments sa ON u.ID = sa.client_user_id
                WHERE sa.staff_user_id = %d 
                AND sa.status = 'active'
                ORDER BY sa.assignment_date DESC",
                $staff->ID
            ));
            
            $staff_assignments[] = array(
                'staff' => $staff,
                'assigned_clients' => $assigned_clients,
                'client_count' => count($assigned_clients)
            );
        }
        
        return $staff_assignments;
    }
    
    /**
     * Get unassigned clients in an institution
     */
    public function get_unassigned_clients($institution_id) {
        global $wpdb;
        
        $unassigned_clients = $wpdb->get_results($wpdb->prepare(
            "SELECT u.ID, u.user_login, u.display_name, u.user_email
            FROM {$wpdb->users} u
            INNER JOIN {$wpdb->prefix}attentrack_institution_members im ON u.ID = im.user_id
            INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
            LEFT JOIN {$wpdb->prefix}attentrack_staff_assignments sa ON u.ID = sa.client_user_id AND sa.status = 'active'
            WHERE im.institution_id = %d 
            AND im.status = 'active'
            AND um.meta_key = '{$wpdb->prefix}capabilities'
            AND um.meta_value LIKE %s
            AND sa.id IS NULL
            ORDER BY u.display_name",
            $institution_id,
            '%client%'
        ));
        
        return $unassigned_clients;
    }
    
    /**
     * Get assignment history for a client
     */
    public function get_client_assignment_history($client_user_id) {
        global $wpdb;
        
        $history = $wpdb->get_results($wpdb->prepare(
            "SELECT sa.*, 
                    staff.display_name as staff_name,
                    assigner.display_name as assigned_by_name,
                    i.institution_name
            FROM {$wpdb->prefix}attentrack_staff_assignments sa
            LEFT JOIN {$wpdb->users} staff ON sa.staff_user_id = staff.ID
            LEFT JOIN {$wpdb->users} assigner ON sa.assigned_by = assigner.ID
            LEFT JOIN {$wpdb->prefix}attentrack_institutions i ON sa.institution_id = i.id
            WHERE sa.client_user_id = %d
            ORDER BY sa.assignment_date DESC",
            $client_user_id
        ));
        
        return $history;
    }
    
    /**
     * Get workload statistics for staff members
     */
    public function get_staff_workload_stats($institution_id) {
        global $wpdb;
        
        $stats = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                u.ID as staff_id,
                u.display_name as staff_name,
                COUNT(sa.id) as active_assignments,
                AVG(DATEDIFF(NOW(), sa.assignment_date)) as avg_assignment_duration
            FROM {$wpdb->users} u
            INNER JOIN {$wpdb->prefix}attentrack_institution_members im ON u.ID = im.user_id
            INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
            LEFT JOIN {$wpdb->prefix}attentrack_staff_assignments sa ON u.ID = sa.staff_user_id AND sa.status = 'active'
            WHERE im.institution_id = %d 
            AND im.status = 'active'
            AND um.meta_key = '{$wpdb->prefix}capabilities'
            AND um.meta_value LIKE %s
            GROUP BY u.ID, u.display_name
            ORDER BY active_assignments DESC",
            $institution_id,
            '%staff%'
        ));
        
        return $stats;
    }
    
    /**
     * Helper method to check if user belongs to institution
     */
    private function user_belongs_to_institution($user_id, $institution_id) {
        global $wpdb;
        
        $membership = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}attentrack_institution_members 
            WHERE user_id = %d AND institution_id = %d AND status = 'active'",
            $user_id, $institution_id
        ));
        
        return !empty($membership);
    }
    
    /**
     * Helper method to create assignment
     */
    private function create_assignment($institution_id, $staff_user_id, $client_user_id, $assigned_by, $notes) {
        global $wpdb;
        
        // Check if assignment already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}attentrack_staff_assignments 
            WHERE staff_user_id = %d AND client_user_id = %d",
            $staff_user_id, $client_user_id
        ));
        
        if ($existing) {
            // Update existing assignment
            $result = $wpdb->update(
                $wpdb->prefix . 'attentrack_staff_assignments',
                array(
                    'status' => 'active',
                    'notes' => $notes,
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $existing)
            );
            
            return $result !== false ? $existing : new WP_Error('update_failed', 'Failed to update assignment');
        } else {
            // Create new assignment
            $result = $wpdb->insert(
                $wpdb->prefix . 'attentrack_staff_assignments',
                array(
                    'institution_id' => $institution_id,
                    'staff_user_id' => $staff_user_id,
                    'client_user_id' => $client_user_id,
                    'assigned_by' => $assigned_by,
                    'notes' => $notes,
                    'status' => 'active'
                )
            );

            // Debug logging for tests
            if (defined('ATTENTRACK_DEBUG_ASSIGNMENTS') && ATTENTRACK_DEBUG_ASSIGNMENTS) {
                global $attentrack_debug_info;
                $attentrack_debug_info['insert_result'] = $result;
                $attentrack_debug_info['wpdb_last_error'] = $wpdb->last_error;
                $attentrack_debug_info['table_exists'] = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}attentrack_staff_assignments'");
            }

            return $result !== false ? $wpdb->insert_id : new WP_Error('insert_failed', 'Failed to create assignment');
        }
    }
}

/**
 * AJAX handlers for staff-client assignments
 */

// Assign clients to staff
add_action('wp_ajax_assign_clients_to_staff', function() {
    check_ajax_referer('attentrack_staff_assignments', 'nonce');
    
    if (!current_user_can('assign_clients_to_staff')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    $institution_id = intval($_POST['institution_id']);
    $staff_user_id = intval($_POST['staff_user_id']);
    $client_user_ids = array_map('intval', $_POST['client_user_ids']);
    $notes = sanitize_textarea_field($_POST['notes'] ?? '');
    
    $assignment_manager = AttenTrack_Staff_Assignments::getInstance();
    $result = $assignment_manager->assign_clients_to_staff($institution_id, $staff_user_id, $client_user_ids, get_current_user_id(), $notes);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success($result);
    }
});

// Remove clients from staff
add_action('wp_ajax_remove_clients_from_staff', function() {
    check_ajax_referer('attentrack_staff_assignments', 'nonce');
    
    if (!current_user_can('assign_clients_to_staff')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    $staff_user_id = intval($_POST['staff_user_id']);
    $client_user_ids = array_map('intval', $_POST['client_user_ids']);
    
    $assignment_manager = AttenTrack_Staff_Assignments::getInstance();
    $result = $assignment_manager->remove_clients_from_staff($staff_user_id, $client_user_ids, get_current_user_id());
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success($result);
    }
});

// Get staff assignments for institution
add_action('wp_ajax_get_staff_assignments', function() {
    check_ajax_referer('attentrack_staff_assignments', 'nonce');
    
    if (!current_user_can('view_institution_analytics')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    $institution_id = intval($_POST['institution_id']);
    
    $assignment_manager = AttenTrack_Staff_Assignments::getInstance();
    $assignments = $assignment_manager->get_institution_staff_assignments($institution_id);
    $unassigned = $assignment_manager->get_unassigned_clients($institution_id);
    $workload_stats = $assignment_manager->get_staff_workload_stats($institution_id);
    
    wp_send_json_success(array(
        'assignments' => $assignments,
        'unassigned_clients' => $unassigned,
        'workload_stats' => $workload_stats
    ));
});
