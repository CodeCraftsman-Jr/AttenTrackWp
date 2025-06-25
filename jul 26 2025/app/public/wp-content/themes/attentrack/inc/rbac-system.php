<?php
/**
 * Role-Based Access Control (RBAC) System for AttenTrack
 * Implements comprehensive permission checking and data isolation
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * RBAC Permission Checker Class
 */
class AttenTrack_RBAC {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Check if user can access specific resource
     */
    public function can_access_resource($user_id, $resource_type, $resource_id = null, $action = 'view') {
        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }
        
        switch ($resource_type) {
            case 'client_data':
                return $this->can_access_client_data($user_id, $resource_id, $action);
            case 'test_results':
                return $this->can_access_test_results($user_id, $resource_id, $action);
            case 'institution_data':
                return $this->can_access_institution_data($user_id, $resource_id, $action);
            case 'subscription_management':
                return $this->can_access_subscription_management($user_id, $resource_id, $action);
            case 'user_management':
                return $this->can_access_user_management($user_id, $resource_id, $action);
            default:
                return false;
        }
    }
    
    /**
     * Check client data access permissions
     */
    private function can_access_client_data($user_id, $client_id, $action) {
        $user = get_userdata($user_id);
        
        // Clients can only access their own data
        if (in_array('client', $user->roles)) {
            return $user_id == $client_id;
        }
        
        // Staff can only access assigned clients
        if (in_array('staff', $user->roles)) {
            return attentrack_staff_can_access_client($user_id, $client_id);
        }
        
        // Institution admins can access all clients in their institution
        if (in_array('institution_admin', $user->roles)) {
            return $this->client_belongs_to_institution($client_id, attentrack_get_user_institution_id($user_id));
        }
        
        // Administrators can access everything
        if (in_array('administrator', $user->roles)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check test results access permissions
     */
    private function can_access_test_results($user_id, $test_owner_id, $action) {
        // Same logic as client data access
        return $this->can_access_client_data($user_id, $test_owner_id, $action);
    }
    
    /**
     * Check institution data access permissions
     */
    private function can_access_institution_data($user_id, $institution_id, $action) {
        $user = get_userdata($user_id);
        
        // Clients cannot access institution data
        if (in_array('client', $user->roles)) {
            return false;
        }
        
        // Staff can only view limited institution data
        if (in_array('staff', $user->roles)) {
            $user_institution = attentrack_get_user_institution_id($user_id);
            return $user_institution == $institution_id && $action == 'view';
        }
        
        // Institution admins can access their own institution data
        if (in_array('institution_admin', $user->roles)) {
            $user_institution = attentrack_get_user_institution_id($user_id);
            return $user_institution == $institution_id;
        }
        
        // Administrators can access everything
        if (in_array('administrator', $user->roles)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check subscription management access permissions
     */
    private function can_access_subscription_management($user_id, $subscription_id, $action) {
        $user = get_userdata($user_id);
        
        // Only institution admins and administrators can access subscription management
        if (in_array('institution_admin', $user->roles)) {
            // Check if subscription belongs to their institution
            global $wpdb;
            $subscription = $wpdb->get_row($wpdb->prepare(
                "SELECT s.*, sd.institution_id 
                FROM {$wpdb->prefix}attentrack_subscriptions s
                LEFT JOIN {$wpdb->prefix}attentrack_subscription_details sd ON s.id = sd.subscription_id
                WHERE s.id = %d",
                $subscription_id
            ));
            
            if ($subscription) {
                $user_institution = attentrack_get_user_institution_id($user_id);
                return $user_institution == $subscription->institution_id;
            }
        }
        
        // Administrators can access everything
        if (in_array('administrator', $user->roles)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check user management access permissions
     */
    private function can_access_user_management($user_id, $target_user_id, $action) {
        $user = get_userdata($user_id);
        $target_user = get_userdata($target_user_id);
        
        if (!$target_user) {
            return false;
        }
        
        // Clients cannot manage other users
        if (in_array('client', $user->roles)) {
            return $user_id == $target_user_id && in_array($action, ['view', 'edit_profile']);
        }
        
        // Staff cannot manage users
        if (in_array('staff', $user->roles)) {
            return false;
        }
        
        // Institution admins can manage users in their institution
        if (in_array('institution_admin', $user->roles)) {
            $user_institution = attentrack_get_user_institution_id($user_id);
            $target_institution = attentrack_get_user_institution_id($target_user_id);
            
            return $user_institution == $target_institution;
        }
        
        // Administrators can manage all users
        if (in_array('administrator', $user->roles)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if client belongs to institution
     */
    private function client_belongs_to_institution($client_id, $institution_id) {
        global $wpdb;
        
        $membership = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}attentrack_institution_members 
            WHERE user_id = %d AND institution_id = %d AND status = 'active'",
            $client_id, $institution_id
        ));
        
        return !empty($membership);
    }
    
    /**
     * Get filtered data based on user permissions
     */
    public function filter_data_by_permissions($user_id, $data_type, $data) {
        $user = get_userdata($user_id);
        
        switch ($data_type) {
            case 'client_list':
                return $this->filter_client_list($user_id, $data);
            case 'test_results':
                return $this->filter_test_results($user_id, $data);
            case 'institution_members':
                return $this->filter_institution_members($user_id, $data);
            default:
                return $data;
        }
    }
    
    /**
     * Filter client list based on user permissions
     */
    private function filter_client_list($user_id, $clients) {
        $user = get_userdata($user_id);
        
        // Clients can only see themselves
        if (in_array('client', $user->roles)) {
            return array_filter($clients, function($client) use ($user_id) {
                return $client->ID == $user_id;
            });
        }
        
        // Staff can only see assigned clients
        if (in_array('staff', $user->roles)) {
            $assigned_clients = attentrack_get_staff_assigned_clients($user_id);
            $assigned_ids = array_map(function($client) { return $client->ID; }, $assigned_clients);
            
            return array_filter($clients, function($client) use ($assigned_ids) {
                return in_array($client->ID, $assigned_ids);
            });
        }
        
        // Institution admins and administrators see all clients in their scope
        return $clients;
    }
    
    /**
     * Filter test results based on user permissions
     */
    private function filter_test_results($user_id, $results) {
        $filtered_results = array();
        
        foreach ($results as $result) {
            if ($this->can_access_test_results($user_id, $result->user_id, 'view')) {
                $filtered_results[] = $result;
            }
        }
        
        return $filtered_results;
    }
    
    /**
     * Filter institution members based on user permissions
     */
    private function filter_institution_members($user_id, $members) {
        $user = get_userdata($user_id);
        
        // Staff can only see themselves and assigned clients
        if (in_array('staff', $user->roles)) {
            $assigned_clients = attentrack_get_staff_assigned_clients($user_id);
            $assigned_ids = array_map(function($client) { return $client->ID; }, $assigned_clients);
            $assigned_ids[] = $user_id; // Include self
            
            return array_filter($members, function($member) use ($assigned_ids) {
                return in_array($member->user_id, $assigned_ids);
            });
        }
        
        // Institution admins and administrators see all members
        return $members;
    }
}

/**
 * Convenience functions for permission checking
 */
function attentrack_can_access_resource($resource_type, $resource_id = null, $action = 'view', $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $rbac = AttenTrack_RBAC::getInstance();
    return $rbac->can_access_resource($user_id, $resource_type, $resource_id, $action);
}

function attentrack_filter_data_by_permissions($data_type, $data, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $rbac = AttenTrack_RBAC::getInstance();
    return $rbac->filter_data_by_permissions($user_id, $data_type, $data);
}

/**
 * Middleware for protecting AJAX endpoints
 */
function attentrack_rbac_ajax_middleware() {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $user_id = get_current_user_id();
    
    if (!$user_id) {
        wp_send_json_error('Authentication required');
        return;
    }
    
    // Define protected actions and their required permissions
    $protected_actions = array(
        'get_client_data' => array('resource' => 'client_data', 'action' => 'view'),
        'update_client_data' => array('resource' => 'client_data', 'action' => 'edit'),
        'get_test_results' => array('resource' => 'test_results', 'action' => 'view'),
        'manage_subscription' => array('resource' => 'subscription_management', 'action' => 'edit'),
        'manage_users' => array('resource' => 'user_management', 'action' => 'edit')
    );
    
    if (isset($protected_actions[$action])) {
        $permission = $protected_actions[$action];
        $resource_id = $_POST['resource_id'] ?? $_GET['resource_id'] ?? null;
        
        if (!attentrack_can_access_resource($permission['resource'], $resource_id, $permission['action'])) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
    }
}

// Hook RBAC middleware to AJAX requests
add_action('wp_ajax_nopriv_attentrack_rbac_check', 'attentrack_rbac_ajax_middleware');
add_action('wp_ajax_attentrack_rbac_check', 'attentrack_rbac_ajax_middleware');
