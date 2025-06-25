<?php
/**
 * Audit Logging System for AttenTrack
 * Tracks all permission-sensitive actions for security and compliance
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Log audit action
 */
function attentrack_log_audit_action($user_id, $action, $resource_type, $resource_id = null, $institution_id = null, $details = array(), $status = 'success') {
    global $wpdb;
    
    // Get user IP and user agent
    $ip_address = attentrack_get_client_ip();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Prepare details as JSON
    $details_json = !empty($details) ? json_encode($details) : null;
    
    // Insert audit log entry
    $result = $wpdb->insert(
        $wpdb->prefix . 'attentrack_audit_log',
        array(
            'user_id' => $user_id,
            'action' => $action,
            'resource_type' => $resource_type,
            'resource_id' => $resource_id,
            'institution_id' => $institution_id,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'details' => $details_json,
            'status' => $status
        ),
        array('%d', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s')
    );
    
    if ($result === false) {
        error_log('Failed to log audit action: ' . $wpdb->last_error);
    }
    
    return $result !== false;
}

/**
 * Get client IP address
 */
function attentrack_get_client_ip() {
    $ip_keys = array(
        'HTTP_CF_CONNECTING_IP',     // Cloudflare
        'HTTP_CLIENT_IP',            // Proxy
        'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
        'HTTP_X_FORWARDED',          // Proxy
        'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
        'HTTP_FORWARDED_FOR',        // Proxy
        'HTTP_FORWARDED',            // Proxy
        'REMOTE_ADDR'                // Standard
    );
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Hook into WordPress actions to log important events
 */

// User login/logout
add_action('wp_login', function($user_login, $user) {
    $institution_id = attentrack_get_user_institution_id($user->ID);
    attentrack_log_audit_action($user->ID, 'user_login', 'authentication', null, $institution_id, array('user_login' => $user_login));
}, 10, 2);

add_action('wp_logout', function($user_id) {
    $institution_id = attentrack_get_user_institution_id($user_id);
    attentrack_log_audit_action($user_id, 'user_logout', 'authentication', null, $institution_id);
});

// Failed login attempts
add_action('wp_login_failed', function($username) {
    $user = get_user_by('login', $username);
    $user_id = $user ? $user->ID : 0;
    $institution_id = $user ? attentrack_get_user_institution_id($user->ID) : null;
    
    attentrack_log_audit_action($user_id, 'login_failed', 'authentication', null, $institution_id, 
        array('username' => $username), 'failure');
});

// User role changes
add_action('set_user_role', function($user_id, $role, $old_roles) {
    $institution_id = attentrack_get_user_institution_id($user_id);
    attentrack_log_audit_action(get_current_user_id(), 'user_role_changed', 'user_management', $user_id, $institution_id,
        array('new_role' => $role, 'old_roles' => $old_roles));
}, 10, 3);

// User creation
add_action('user_register', function($user_id) {
    $institution_id = attentrack_get_user_institution_id(get_current_user_id());
    attentrack_log_audit_action(get_current_user_id(), 'user_created', 'user_management', $user_id, $institution_id);
});

// User deletion
add_action('delete_user', function($user_id) {
    $institution_id = attentrack_get_user_institution_id($user_id);
    attentrack_log_audit_action(get_current_user_id(), 'user_deleted', 'user_management', $user_id, $institution_id);
});

// Profile updates
add_action('profile_update', function($user_id, $old_user_data) {
    $institution_id = attentrack_get_user_institution_id($user_id);
    $current_user_id = get_current_user_id();
    
    // Log who updated the profile
    $action = ($current_user_id == $user_id) ? 'profile_self_updated' : 'profile_updated_by_admin';
    
    attentrack_log_audit_action($current_user_id, $action, 'user_management', $user_id, $institution_id);
}, 10, 2);

/**
 * Custom audit logging functions for AttenTrack specific actions
 */

// Test completion
function attentrack_log_test_completion($user_id, $test_type, $test_id, $results) {
    $institution_id = attentrack_get_user_institution_id($user_id);
    attentrack_log_audit_action($user_id, 'test_completed', 'test_results', $test_id, $institution_id,
        array('test_type' => $test_type, 'score' => $results['score'] ?? null));
}

// Client assignment to staff
function attentrack_log_client_assignment($staff_id, $client_id, $assigned_by, $institution_id) {
    attentrack_log_audit_action($assigned_by, 'client_assigned_to_staff', 'staff_assignment', null, $institution_id,
        array('staff_id' => $staff_id, 'client_id' => $client_id));
}

// Subscription changes
function attentrack_log_subscription_change($user_id, $subscription_id, $action, $details = array()) {
    $institution_id = attentrack_get_user_institution_id($user_id);
    attentrack_log_audit_action($user_id, 'subscription_' . $action, 'subscription_management', $subscription_id, $institution_id, $details);
}

// Data access attempts
function attentrack_log_data_access($user_id, $resource_type, $resource_id, $access_granted = true) {
    $institution_id = attentrack_get_user_institution_id($user_id);
    $status = $access_granted ? 'success' : 'failure';
    
    attentrack_log_audit_action($user_id, 'data_access_attempt', $resource_type, $resource_id, $institution_id,
        array('access_granted' => $access_granted), $status);
}

/**
 * Get audit logs with filtering
 */
function attentrack_get_audit_logs($filters = array(), $limit = 100, $offset = 0) {
    global $wpdb;
    
    $where_conditions = array('1=1');
    $where_values = array();
    
    // Filter by user
    if (!empty($filters['user_id'])) {
        $where_conditions[] = 'user_id = %d';
        $where_values[] = $filters['user_id'];
    }
    
    // Filter by institution
    if (!empty($filters['institution_id'])) {
        $where_conditions[] = 'institution_id = %d';
        $where_values[] = $filters['institution_id'];
    }
    
    // Filter by action
    if (!empty($filters['action'])) {
        $where_conditions[] = 'action = %s';
        $where_values[] = $filters['action'];
    }
    
    // Filter by resource type
    if (!empty($filters['resource_type'])) {
        $where_conditions[] = 'resource_type = %s';
        $where_values[] = $filters['resource_type'];
    }
    
    // Filter by date range
    if (!empty($filters['date_from'])) {
        $where_conditions[] = 'created_at >= %s';
        $where_values[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $where_conditions[] = 'created_at <= %s';
        $where_values[] = $filters['date_to'];
    }
    
    // Filter by status
    if (!empty($filters['status'])) {
        $where_conditions[] = 'status = %s';
        $where_values[] = $filters['status'];
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Add limit and offset
    $where_values[] = $limit;
    $where_values[] = $offset;
    
    $query = "SELECT al.*, u.user_login, u.display_name 
              FROM {$wpdb->prefix}attentrack_audit_log al
              LEFT JOIN {$wpdb->users} u ON al.user_id = u.ID
              WHERE $where_clause
              ORDER BY al.created_at DESC
              LIMIT %d OFFSET %d";
    
    if (!empty($where_values)) {
        $query = $wpdb->prepare($query, $where_values);
    }
    
    return $wpdb->get_results($query);
}

/**
 * Get audit log statistics
 */
function attentrack_get_audit_stats($institution_id = null, $days = 30) {
    global $wpdb;
    
    $where_condition = '';
    $where_values = array();
    
    if ($institution_id) {
        $where_condition = 'WHERE institution_id = %d AND ';
        $where_values[] = $institution_id;
    } else {
        $where_condition = 'WHERE ';
    }
    
    $where_values[] = $days;
    
    $query = "SELECT 
                action,
                status,
                COUNT(*) as count
              FROM {$wpdb->prefix}attentrack_audit_log
              {$where_condition}created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
              GROUP BY action, status
              ORDER BY count DESC";
    
    if (!empty($where_values)) {
        $query = $wpdb->prepare($query, $where_values);
    }
    
    return $wpdb->get_results($query);
}

/**
 * Clean up old audit logs (run via cron)
 */
function attentrack_cleanup_audit_logs($days_to_keep = 365) {
    global $wpdb;
    
    $deleted = $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}attentrack_audit_log 
         WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
        $days_to_keep
    ));
    
    if ($deleted !== false) {
        error_log("Cleaned up $deleted old audit log entries");
    }
    
    return $deleted;
}

// Schedule audit log cleanup
if (!wp_next_scheduled('attentrack_cleanup_audit_logs')) {
    wp_schedule_event(time(), 'weekly', 'attentrack_cleanup_audit_logs');
}

add_action('attentrack_cleanup_audit_logs', function() {
    attentrack_cleanup_audit_logs(365); // Keep logs for 1 year
});
