<?php
/**
 * Multi-Tier User Role System for AttenTrack
 * Implements Client, Staff, and Institution Admin roles with hierarchical permissions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create custom user roles with specific capabilities
 */
function attentrack_create_custom_roles() {
    // Remove old roles if they exist
    remove_role('patient');
    remove_role('institution');
    
    // CLIENT ROLE (formerly patients)
    add_role('client', 'Client', array(
        'read' => true,
        // Client-specific capabilities
        'access_client_dashboard' => true,
        'take_attention_tests' => true,
        'view_own_test_results' => true,
        'edit_own_profile' => true,
        'view_own_profile' => true,
        // Restrictions (explicitly denied)
        'access_subscription_management' => false,
        'view_other_users_data' => false,
        'manage_users' => false,
        'access_admin_functions' => false
    ));
    
    // STAFF ROLE (institution employees)
    add_role('staff', 'Staff', array(
        'read' => true,
        // Staff-specific capabilities
        'access_staff_dashboard' => true,
        'view_assigned_clients' => true,
        'generate_client_reports' => true,
        'communicate_with_clients' => true,
        'view_assigned_test_results' => true,
        // Restrictions (explicitly denied)
        'view_unassigned_clients' => false,
        'manage_users' => false,
        'access_subscription_management' => false,
        'modify_client_assignments' => false,
        'access_institution_settings' => false,
        'view_institution_analytics' => false
    ));
    
    // INSTITUTION ADMIN ROLE (paying customers)
    add_role('institution_admin', 'Institution Admin', array(
        'read' => true,
        // Full institution management capabilities
        'access_institution_dashboard' => true,
        'manage_institution_users' => true,
        'create_client_accounts' => true,
        'create_staff_accounts' => true,
        'edit_client_accounts' => true,
        'edit_staff_accounts' => true,
        'deactivate_user_accounts' => true,
        'assign_clients_to_staff' => true,
        'view_all_institution_data' => true,
        'generate_institution_reports' => true,
        'access_subscription_management' => true,
        'manage_billing' => true,
        'configure_institution_settings' => true,
        'view_institution_analytics' => true,
        'manage_test_assignments' => true,
        'set_user_permissions' => true,
        // File management
        'upload_files' => true,
        'edit_files' => true
    ));
}

/**
 * Add custom capabilities to existing roles
 */
function attentrack_add_custom_capabilities() {
    // Add capabilities to administrator role for full system access
    $admin_role = get_role('administrator');
    if ($admin_role) {
        $admin_capabilities = array(
            'access_client_dashboard',
            'access_staff_dashboard',
            'access_institution_dashboard',
            'manage_institution_users',
            'view_all_institution_data',
            'access_subscription_management',
            'manage_billing',
            'configure_institution_settings',
            'view_institution_analytics',
            'assign_clients_to_staff',
            'manage_test_assignments',
            'set_user_permissions'
        );

        foreach ($admin_capabilities as $cap) {
            $admin_role->add_cap($cap);
        }
    }

    // Ensure institution_admin role has all required capabilities
    $institution_admin_role = get_role('institution_admin');
    if ($institution_admin_role) {
        $institution_admin_role->add_cap('access_subscription_management');
        $institution_admin_role->add_cap('manage_billing');
        $institution_admin_role->add_cap('assign_clients_to_staff');
        $institution_admin_role->add_cap('manage_institution_users');
        $institution_admin_role->add_cap('view_all_institution_data');
    }
}

/**
 * Check if user has specific role within an institution context
 */
function attentrack_user_has_role_in_institution($user_id, $role, $institution_id = null) {
    global $wpdb;
    
    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }
    
    // Check WordPress role first
    if (!in_array($role, $user->roles)) {
        return false;
    }
    
    // If institution context is required, check role assignments table
    if ($institution_id) {
        $assignment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}attentrack_user_role_assignments 
            WHERE user_id = %d AND role_type = %s AND institution_id = %d AND status = 'active'",
            $user_id, $role, $institution_id
        ));
        
        return !empty($assignment);
    }
    
    return true;
}

/**
 * Get user's institution ID
 */
function attentrack_get_user_institution_id($user_id) {
    global $wpdb;
    
    // Check if user is institution admin
    $institution = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}attentrack_institutions WHERE user_id = %d",
        $user_id
    ));
    
    if ($institution) {
        return $institution;
    }
    
    // Check if user is a member of an institution
    $institution = $wpdb->get_var($wpdb->prepare(
        "SELECT institution_id FROM {$wpdb->prefix}attentrack_institution_members 
        WHERE user_id = %d AND status = 'active'",
        $user_id
    ));
    
    return $institution ? $institution : null;
}

/**
 * Check if staff member can access specific client
 */
function attentrack_staff_can_access_client($staff_user_id, $client_user_id) {
    global $wpdb;
    
    // Check if staff is assigned to this client
    $assignment = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}attentrack_staff_assignments 
        WHERE staff_user_id = %d AND client_user_id = %d AND status = 'active'",
        $staff_user_id, $client_user_id
    ));
    
    return !empty($assignment);
}

/**
 * Get clients assigned to a staff member
 */
function attentrack_get_staff_assigned_clients($staff_user_id) {
    global $wpdb;
    
    $client_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT client_user_id FROM {$wpdb->prefix}attentrack_staff_assignments 
        WHERE staff_user_id = %d AND status = 'active'",
        $staff_user_id
    ));
    
    if (empty($client_ids)) {
        return array();
    }
    
    // Get user objects for assigned clients
    $clients = get_users(array(
        'include' => $client_ids,
        'role' => 'client'
    ));
    
    return $clients;
}

/**
 * Assign client to staff member
 */
function attentrack_assign_client_to_staff($institution_id, $staff_user_id, $client_user_id, $assigned_by, $notes = '') {
    global $wpdb;
    
    // Verify that the assigner has permission
    $assigner = get_userdata($assigned_by);
    $can_assign = false;

    if ($assigner) {
        $can_assign = in_array('administrator', $assigner->roles) ||
                     in_array('institution_admin', $assigner->roles) ||
                     user_can($assigner, 'assign_clients_to_staff');
    }

    if (!$can_assign) {
        return new WP_Error('permission_denied', 'You do not have permission to assign clients to staff.');
    }
    
    // Verify staff and client belong to the institution
    $staff_member = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}attentrack_institution_members 
        WHERE user_id = %d AND institution_id = %d AND status = 'active'",
        $staff_user_id, $institution_id
    ));
    
    $client_member = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}attentrack_institution_members 
        WHERE user_id = %d AND institution_id = %d AND status = 'active'",
        $client_user_id, $institution_id
    ));
    
    if (!$staff_member || !$client_member) {
        return new WP_Error('invalid_assignment', 'Staff or client does not belong to this institution.');
    }
    
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
    }
    
    if ($result === false) {
        return new WP_Error('assignment_failed', 'Failed to assign client to staff member.');
    }
    
    // Log the assignment
    attentrack_log_audit_action($assigned_by, 'assign_client_to_staff', 'staff_assignment', $wpdb->insert_id, $institution_id);
    
    return true;
}

/**
 * Remove client assignment from staff member
 */
function attentrack_remove_client_from_staff($staff_user_id, $client_user_id, $removed_by) {
    global $wpdb;
    
    // Verify that the remover has permission
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
    
    if ($result === false) {
        return new WP_Error('removal_failed', 'Failed to remove client assignment.');
    }
    
    // Log the removal
    $institution_id = attentrack_get_user_institution_id($removed_by);
    attentrack_log_audit_action($removed_by, 'remove_client_from_staff', 'staff_assignment', null, $institution_id);
    
    return true;
}

/**
 * Initialize roles on theme activation
 */
function attentrack_init_multi_tier_roles() {
    attentrack_create_custom_roles();
    attentrack_add_custom_capabilities();
}

/**
 * Force refresh roles and capabilities
 */
function attentrack_refresh_roles() {
    // Remove and recreate roles to ensure they have latest capabilities
    remove_role('client');
    remove_role('staff');
    remove_role('institution_admin');

    // Recreate with fresh capabilities
    attentrack_create_custom_roles();
    attentrack_add_custom_capabilities();

    // Clear any cached role data
    wp_cache_delete('user_roles', 'options');
}

// Hook role creation to theme activation and init
add_action('after_switch_theme', 'attentrack_init_multi_tier_roles');
add_action('init', 'attentrack_init_multi_tier_roles');

// Add admin action to refresh roles
add_action('wp_ajax_refresh_attentrack_roles', function() {
    if (current_user_can('administrator')) {
        attentrack_refresh_roles();
        wp_send_json_success('Roles refreshed successfully');
    } else {
        wp_send_json_error('Insufficient permissions');
    }
});

/**
 * Capability mapping for backward compatibility
 */
function attentrack_map_legacy_capabilities($allcaps, $caps, $args, $user) {
    // Map old patient capabilities to client capabilities
    if (isset($allcaps['access_patient_dashboard'])) {
        $allcaps['access_client_dashboard'] = $allcaps['access_patient_dashboard'];
    }
    
    // Map old institution capabilities to institution_admin capabilities
    if (isset($allcaps['manage_institution'])) {
        $allcaps['access_institution_dashboard'] = $allcaps['manage_institution'];
        $allcaps['manage_institution_users'] = $allcaps['manage_institution'];
    }
    
    return $allcaps;
}
add_filter('user_has_cap', 'attentrack_map_legacy_capabilities', 10, 4);
