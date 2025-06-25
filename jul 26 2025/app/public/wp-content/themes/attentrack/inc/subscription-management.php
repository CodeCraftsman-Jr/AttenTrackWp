<?php
/**
 * Enhanced Subscription Management System for AttenTrack
 * Manages institution subscriptions with role-based access and billing integration
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Subscription Management Class
 */
class AttenTrack_Subscription_Manager {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get subscription details for an institution
     */
    public function get_institution_subscription($institution_id) {
        global $wpdb;
        
        $subscription = $wpdb->get_row($wpdb->prepare(
            "SELECT s.*, sd.*, i.institution_name
            FROM {$wpdb->prefix}attentrack_subscriptions s
            LEFT JOIN {$wpdb->prefix}attentrack_subscription_details sd ON s.id = sd.subscription_id
            LEFT JOIN {$wpdb->prefix}attentrack_institutions i ON sd.institution_id = i.id
            WHERE sd.institution_id = %d AND s.status = 'active'
            ORDER BY s.created_at DESC
            LIMIT 1",
            $institution_id
        ));
        
        if ($subscription) {
            // Add usage statistics
            $subscription->current_usage = $this->get_subscription_usage($institution_id);
            $subscription->billing_history = $this->get_billing_history($subscription->id);
        }
        
        return $subscription;
    }
    
    /**
     * Get current subscription usage statistics
     */
    public function get_subscription_usage($institution_id) {
        global $wpdb;
        
        // Count current members by role
        $client_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT im.user_id)
            FROM {$wpdb->prefix}attentrack_institution_members im
            INNER JOIN {$wpdb->usermeta} um ON im.user_id = um.user_id
            WHERE im.institution_id = %d 
            AND im.status = 'active'
            AND um.meta_key = '{$wpdb->prefix}capabilities'
            AND um.meta_value LIKE %s",
            $institution_id, '%client%'
        ));
        
        $staff_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT im.user_id)
            FROM {$wpdb->prefix}attentrack_institution_members im
            INNER JOIN {$wpdb->usermeta} um ON im.user_id = um.user_id
            WHERE im.institution_id = %d 
            AND im.status = 'active'
            AND um.meta_key = '{$wpdb->prefix}capabilities'
            AND um.meta_value LIKE %s",
            $institution_id, '%staff%'
        ));
        
        // Count tests taken this month
        $tests_this_month = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*)
            FROM {$wpdb->prefix}attentrack_selective_results sr
            INNER JOIN {$wpdb->prefix}attentrack_institution_members im ON sr.user_id = im.user_id
            WHERE im.institution_id = %d 
            AND sr.test_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)",
            $institution_id
        ));
        
        return array(
            'client_count' => intval($client_count),
            'staff_count' => intval($staff_count),
            'total_members' => intval($client_count) + intval($staff_count),
            'tests_this_month' => intval($tests_this_month)
        );
    }
    
    /**
     * Check if institution can add more members
     */
    public function can_add_members($institution_id, $additional_members = 1) {
        $subscription = $this->get_institution_subscription($institution_id);
        
        if (!$subscription) {
            return array('allowed' => false, 'reason' => 'No active subscription');
        }
        
        $current_usage = $subscription->current_usage;
        $max_members = $subscription->max_members;
        
        if ($current_usage['total_members'] + $additional_members > $max_members) {
            return array(
                'allowed' => false, 
                'reason' => 'Member limit exceeded',
                'current' => $current_usage['total_members'],
                'max' => $max_members,
                'requested' => $additional_members
            );
        }
        
        return array('allowed' => true);
    }
    
    /**
     * Update subscription member limits
     */
    public function update_member_limits($subscription_id, $new_limits) {
        global $wpdb;
        
        if (!current_user_can('access_subscription_management')) {
            return new WP_Error('permission_denied', 'Insufficient permissions');
        }
        
        $result = $wpdb->update(
            $wpdb->prefix . 'attentrack_subscription_details',
            array(
                'max_members' => intval($new_limits['max_members']),
                'max_staff' => intval($new_limits['max_staff'] ?? 0),
                'updated_at' => current_time('mysql')
            ),
            array('subscription_id' => $subscription_id)
        );
        
        if ($result !== false) {
            // Log the change
            $institution_id = $wpdb->get_var($wpdb->prepare(
                "SELECT institution_id FROM {$wpdb->prefix}attentrack_subscription_details WHERE subscription_id = %d",
                $subscription_id
            ));
            
            attentrack_log_audit_action(get_current_user_id(), 'subscription_limits_updated', 
                'subscription_management', $subscription_id, $institution_id, $new_limits);
        }
        
        return $result !== false;
    }
    
    /**
     * Extend subscription period
     */
    public function extend_subscription($subscription_id, $additional_months, $payment_details = array()) {
        global $wpdb;
        
        if (!current_user_can('access_subscription_management')) {
            return new WP_Error('permission_denied', 'Insufficient permissions');
        }
        
        // Get current subscription
        $subscription = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}attentrack_subscriptions WHERE id = %d",
            $subscription_id
        ));
        
        if (!$subscription) {
            return new WP_Error('subscription_not_found', 'Subscription not found');
        }
        
        // Calculate new end date
        $current_end = new DateTime($subscription->end_date);
        $current_end->add(new DateInterval('P' . $additional_months . 'M'));
        $new_end_date = $current_end->format('Y-m-d H:i:s');
        
        // Update subscription
        $result = $wpdb->update(
            $wpdb->prefix . 'attentrack_subscriptions',
            array(
                'end_date' => $new_end_date,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $subscription_id)
        );
        
        if ($result !== false) {
            // Update billing details
            $wpdb->update(
                $wpdb->prefix . 'attentrack_subscription_details',
                array(
                    'last_billing_date' => current_time('mysql'),
                    'next_billing_date' => $new_end_date,
                    'updated_at' => current_time('mysql')
                ),
                array('subscription_id' => $subscription_id)
            );
            
            // Log the extension
            $institution_id = $wpdb->get_var($wpdb->prepare(
                "SELECT institution_id FROM {$wpdb->prefix}attentrack_subscription_details WHERE subscription_id = %d",
                $subscription_id
            ));
            
            attentrack_log_audit_action(get_current_user_id(), 'subscription_extended', 
                'subscription_management', $subscription_id, $institution_id, 
                array('additional_months' => $additional_months, 'new_end_date' => $new_end_date));
        }
        
        return $result !== false;
    }
    
    /**
     * Suspend subscription
     */
    public function suspend_subscription($subscription_id, $reason = '') {
        global $wpdb;
        
        if (!current_user_can('access_subscription_management')) {
            return new WP_Error('permission_denied', 'Insufficient permissions');
        }
        
        $result = $wpdb->update(
            $wpdb->prefix . 'attentrack_subscriptions',
            array(
                'status' => 'suspended',
                'updated_at' => current_time('mysql')
            ),
            array('id' => $subscription_id)
        );
        
        if ($result !== false) {
            // Log the suspension
            $institution_id = $wpdb->get_var($wpdb->prepare(
                "SELECT institution_id FROM {$wpdb->prefix}attentrack_subscription_details WHERE subscription_id = %d",
                $subscription_id
            ));
            
            attentrack_log_audit_action(get_current_user_id(), 'subscription_suspended', 
                'subscription_management', $subscription_id, $institution_id, 
                array('reason' => $reason));
        }
        
        return $result !== false;
    }
    
    /**
     * Reactivate subscription
     */
    public function reactivate_subscription($subscription_id) {
        global $wpdb;
        
        if (!current_user_can('access_subscription_management')) {
            return new WP_Error('permission_denied', 'Insufficient permissions');
        }
        
        $result = $wpdb->update(
            $wpdb->prefix . 'attentrack_subscriptions',
            array(
                'status' => 'active',
                'updated_at' => current_time('mysql')
            ),
            array('id' => $subscription_id)
        );
        
        if ($result !== false) {
            // Log the reactivation
            $institution_id = $wpdb->get_var($wpdb->prepare(
                "SELECT institution_id FROM {$wpdb->prefix}attentrack_subscription_details WHERE subscription_id = %d",
                $subscription_id
            ));
            
            attentrack_log_audit_action(get_current_user_id(), 'subscription_reactivated', 
                'subscription_management', $subscription_id, $institution_id);
        }
        
        return $result !== false;
    }
    
    /**
     * Get billing history
     */
    public function get_billing_history($subscription_id, $limit = 10) {
        global $wpdb;
        
        // This would integrate with your payment processor's API
        // For now, we'll return audit log entries related to billing
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}attentrack_audit_log 
            WHERE resource_type = 'subscription_management' 
            AND resource_id = %d 
            AND action LIKE '%billing%' OR action LIKE '%payment%'
            ORDER BY created_at DESC 
            LIMIT %d",
            $subscription_id, $limit
        ));
    }
    
    /**
     * Generate subscription usage report
     */
    public function generate_usage_report($institution_id, $start_date = null, $end_date = null) {
        global $wpdb;
        
        if (!$start_date) {
            $start_date = date('Y-m-01'); // First day of current month
        }
        if (!$end_date) {
            $end_date = date('Y-m-t'); // Last day of current month
        }
        
        // Get test usage by type
        $test_usage = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                'selective' as test_type,
                COUNT(*) as test_count,
                COUNT(DISTINCT sr.user_id) as unique_users
            FROM {$wpdb->prefix}attentrack_selective_results sr
            INNER JOIN {$wpdb->prefix}attentrack_institution_members im ON sr.user_id = im.user_id
            WHERE im.institution_id = %d 
            AND sr.test_date BETWEEN %s AND %s
            
            UNION ALL
            
            SELECT 
                'extended' as test_type,
                COUNT(*) as test_count,
                COUNT(DISTINCT er.user_id) as unique_users
            FROM {$wpdb->prefix}attentrack_extended_results er
            INNER JOIN {$wpdb->prefix}attentrack_institution_members im ON er.user_id = im.user_id
            WHERE im.institution_id = %d 
            AND er.test_date BETWEEN %s AND %s
            
            UNION ALL
            
            SELECT 
                'divided' as test_type,
                COUNT(*) as test_count,
                COUNT(DISTINCT dr.user_id) as unique_users
            FROM {$wpdb->prefix}attentrack_divided_results dr
            INNER JOIN {$wpdb->prefix}attentrack_institution_members im ON dr.user_id = im.user_id
            WHERE im.institution_id = %d 
            AND dr.test_date BETWEEN %s AND %s
            
            UNION ALL
            
            SELECT 
                'alternative' as test_type,
                COUNT(*) as test_count,
                COUNT(DISTINCT ar.user_id) as unique_users
            FROM {$wpdb->prefix}attentrack_alternative_results ar
            INNER JOIN {$wpdb->prefix}attentrack_institution_members im ON ar.user_id = im.user_id
            WHERE im.institution_id = %d 
            AND ar.test_date BETWEEN %s AND %s",
            $institution_id, $start_date, $end_date,
            $institution_id, $start_date, $end_date,
            $institution_id, $start_date, $end_date,
            $institution_id, $start_date, $end_date
        ));
        
        // Get member activity
        $member_activity = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                u.display_name,
                u.user_email,
                COUNT(*) as total_tests,
                MAX(sr.test_date) as last_test_date
            FROM {$wpdb->users} u
            INNER JOIN {$wpdb->prefix}attentrack_institution_members im ON u.ID = im.user_id
            LEFT JOIN {$wpdb->prefix}attentrack_selective_results sr ON u.ID = sr.user_id 
                AND sr.test_date BETWEEN %s AND %s
            WHERE im.institution_id = %d AND im.status = 'active'
            GROUP BY u.ID, u.display_name, u.user_email
            ORDER BY total_tests DESC",
            $start_date, $end_date, $institution_id
        ));
        
        return array(
            'period' => array(
                'start_date' => $start_date,
                'end_date' => $end_date
            ),
            'test_usage' => $test_usage,
            'member_activity' => $member_activity,
            'subscription' => $this->get_institution_subscription($institution_id)
        );
    }
}

/**
 * AJAX handlers for subscription management
 */

// Get subscription details
add_action('wp_ajax_get_subscription_details', function() {
    check_ajax_referer('attentrack_subscription_management', 'nonce');
    
    if (!current_user_can('access_subscription_management')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    $institution_id = intval($_POST['institution_id']);
    
    $subscription_manager = AttenTrack_Subscription_Manager::getInstance();
    $subscription = $subscription_manager->get_institution_subscription($institution_id);
    
    wp_send_json_success($subscription);
});

// Update member limits
add_action('wp_ajax_update_member_limits', function() {
    check_ajax_referer('attentrack_subscription_management', 'nonce');
    
    if (!current_user_can('access_subscription_management')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    $subscription_id = intval($_POST['subscription_id']);
    $new_limits = array(
        'max_members' => intval($_POST['max_members']),
        'max_staff' => intval($_POST['max_staff'] ?? 0)
    );
    
    $subscription_manager = AttenTrack_Subscription_Manager::getInstance();
    $result = $subscription_manager->update_member_limits($subscription_id, $new_limits);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success('Member limits updated successfully');
    }
});

// Generate usage report
add_action('wp_ajax_generate_usage_report', function() {
    check_ajax_referer('attentrack_subscription_management', 'nonce');
    
    if (!current_user_can('view_institution_analytics')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    $institution_id = intval($_POST['institution_id']);
    $start_date = sanitize_text_field($_POST['start_date'] ?? '');
    $end_date = sanitize_text_field($_POST['end_date'] ?? '');
    
    $subscription_manager = AttenTrack_Subscription_Manager::getInstance();
    $report = $subscription_manager->generate_usage_report($institution_id, $start_date, $end_date);
    
    wp_send_json_success($report);
});
