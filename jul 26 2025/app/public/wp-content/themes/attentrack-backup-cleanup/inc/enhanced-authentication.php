<?php
/**
 * Enhanced Authentication System for AttenTrack
 * Role-specific security levels, session management, and credential validation
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced Authentication Manager Class
 */
class AttenTrack_Enhanced_Auth {
    
    private static $instance = null;
    
    // Session timeout settings (in seconds)
    const CLIENT_SESSION_TIMEOUT = 3600;      // 1 hour
    const STAFF_SESSION_TIMEOUT = 7200;       // 2 hours  
    const INSTITUTION_ADMIN_SESSION_TIMEOUT = 14400; // 4 hours
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        // Hook into WordPress authentication
        add_filter('authenticate', array($this, 'enhanced_authenticate'), 30, 3);
        add_action('wp_login', array($this, 'handle_successful_login'), 10, 2);
        add_action('wp_logout', array($this, 'handle_logout'));
        add_action('init', array($this, 'check_session_timeout'));
        add_action('wp_login_failed', array($this, 'handle_failed_login'));
        
        // Add custom login security
        add_action('login_form', array($this, 'add_login_security_fields'));
        add_filter('wp_authenticate_user', array($this, 'validate_login_security'), 10, 2);
    }
    
    /**
     * Enhanced authentication with role-specific validation
     */
    public function enhanced_authenticate($user, $username, $password) {
        // Skip if already authenticated or if it's an error
        if (is_wp_error($user) || is_a($user, 'WP_User')) {
            return $user;
        }
        
        // Get user by username or email
        $user_obj = get_user_by('login', $username);
        if (!$user_obj) {
            $user_obj = get_user_by('email', $username);
        }
        
        if (!$user_obj) {
            return new WP_Error('invalid_username', 'Invalid username or email');
        }
        
        // Check if user account is active
        if (!$this->is_user_account_active($user_obj->ID)) {
            return new WP_Error('account_inactive', 'Your account has been deactivated. Please contact your administrator.');
        }
        
        // Check subscription status for institution users
        if (in_array('institution_admin', $user_obj->roles) || in_array('staff', $user_obj->roles) || in_array('client', $user_obj->roles)) {
            $subscription_check = $this->check_subscription_status($user_obj->ID);
            if (is_wp_error($subscription_check)) {
                return $subscription_check;
            }
        }
        
        // Validate password strength requirements
        if (!$this->validate_password_requirements($user_obj, $password)) {
            return new WP_Error('weak_password', 'Password does not meet security requirements');
        }
        
        // Check for suspicious login attempts
        if ($this->is_suspicious_login($user_obj->ID)) {
            return new WP_Error('suspicious_activity', 'Suspicious activity detected. Please try again later or contact support.');
        }
        
        return $user;
    }
    
    /**
     * Handle successful login with role-specific setup
     */
    public function handle_successful_login($user_login, $user) {
        // Set role-specific session timeout
        $this->set_role_based_session_timeout($user);
        
        // Update last login time
        update_user_meta($user->ID, 'last_login', current_time('mysql'));
        update_user_meta($user->ID, 'last_login_ip', attentrack_get_client_ip());
        
        // Clear failed login attempts
        delete_user_meta($user->ID, 'failed_login_attempts');
        delete_user_meta($user->ID, 'last_failed_login');
        
        // Log successful login
        attentrack_log_audit_action($user->ID, 'successful_login', 'authentication', null, 
            attentrack_get_user_institution_id($user->ID), array(
                'user_role' => $user->roles[0] ?? 'unknown',
                'login_method' => 'password'
            ));
        
        // Set security headers
        $this->set_security_headers($user);
    }
    
    /**
     * Handle logout
     */
    public function handle_logout($user_id) {
        if ($user_id) {
            // Log logout
            attentrack_log_audit_action($user_id, 'user_logout', 'authentication', null, 
                attentrack_get_user_institution_id($user_id));
            
            // Clear session data
            delete_user_meta($user_id, 'session_start_time');
            delete_user_meta($user_id, 'last_activity');
        }
    }
    
    /**
     * Handle failed login attempts
     */
    public function handle_failed_login($username) {
        $user = get_user_by('login', $username);
        if (!$user) {
            $user = get_user_by('email', $username);
        }
        
        if ($user) {
            // Increment failed attempts
            $failed_attempts = get_user_meta($user->ID, 'failed_login_attempts', true) ?: 0;
            $failed_attempts++;
            
            update_user_meta($user->ID, 'failed_login_attempts', $failed_attempts);
            update_user_meta($user->ID, 'last_failed_login', current_time('mysql'));
            
            // Lock account after 5 failed attempts
            if ($failed_attempts >= 5) {
                update_user_meta($user->ID, 'account_locked_until', date('Y-m-d H:i:s', strtotime('+30 minutes')));
                
                // Log account lockout
                attentrack_log_audit_action($user->ID, 'account_locked', 'authentication', null, 
                    attentrack_get_user_institution_id($user->ID), array(
                        'failed_attempts' => $failed_attempts,
                        'locked_until' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
                    ), 'warning');
            }
        }
    }
    
    /**
     * Check session timeout based on user role
     */
    public function check_session_timeout() {
        if (!is_user_logged_in()) {
            return;
        }
        
        $user = wp_get_current_user();
        $last_activity = get_user_meta($user->ID, 'last_activity', true);
        
        if (!$last_activity) {
            update_user_meta($user->ID, 'last_activity', current_time('mysql'));
            return;
        }
        
        $timeout = $this->get_role_session_timeout($user);
        $last_activity_time = strtotime($last_activity);
        
        if (time() - $last_activity_time > $timeout) {
            // Session expired
            wp_logout();
            wp_redirect(wp_login_url() . '?session_expired=1');
            exit;
        }
        
        // Update last activity
        update_user_meta($user->ID, 'last_activity', current_time('mysql'));
    }
    
    /**
     * Set role-based session timeout
     */
    private function set_role_based_session_timeout($user) {
        $timeout = $this->get_role_session_timeout($user);
        
        // Set session start time
        update_user_meta($user->ID, 'session_start_time', current_time('mysql'));
        update_user_meta($user->ID, 'last_activity', current_time('mysql'));
        update_user_meta($user->ID, 'session_timeout', $timeout);
    }
    
    /**
     * Get session timeout for user role
     */
    private function get_role_session_timeout($user) {
        if (in_array('client', $user->roles)) {
            return self::CLIENT_SESSION_TIMEOUT;
        } elseif (in_array('staff', $user->roles)) {
            return self::STAFF_SESSION_TIMEOUT;
        } elseif (in_array('institution_admin', $user->roles)) {
            return self::INSTITUTION_ADMIN_SESSION_TIMEOUT;
        }
        
        return self::CLIENT_SESSION_TIMEOUT; // Default to most restrictive
    }
    
    /**
     * Check if user account is active
     */
    private function is_user_account_active($user_id) {
        // Check if account is locked
        $locked_until = get_user_meta($user_id, 'account_locked_until', true);
        if ($locked_until && strtotime($locked_until) > time()) {
            return false;
        }
        
        // Check if account is deactivated
        $account_status = get_user_meta($user_id, 'account_status', true);
        if ($account_status === 'deactivated' || $account_status === 'suspended') {
            return false;
        }
        
        // Check institution membership status
        $user = get_userdata($user_id);
        if (in_array('client', $user->roles) || in_array('staff', $user->roles)) {
            global $wpdb;
            $membership = $wpdb->get_var($wpdb->prepare(
                "SELECT status FROM {$wpdb->prefix}attentrack_institution_members WHERE user_id = %d",
                $user_id
            ));
            
            if ($membership !== 'active') {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check subscription status
     */
    private function check_subscription_status($user_id) {
        $institution_id = attentrack_get_user_institution_id($user_id);
        
        if (!$institution_id) {
            return new WP_Error('no_institution', 'User is not associated with any institution');
        }
        
        $subscription_manager = AttenTrack_Subscription_Manager::getInstance();
        $subscription = $subscription_manager->get_institution_subscription($institution_id);
        
        if (!$subscription) {
            return new WP_Error('no_subscription', 'Institution does not have an active subscription');
        }
        
        if ($subscription->status !== 'active') {
            return new WP_Error('subscription_inactive', 'Institution subscription is not active');
        }
        
        if (strtotime($subscription->end_date) < time()) {
            return new WP_Error('subscription_expired', 'Institution subscription has expired');
        }
        
        return true;
    }
    
    /**
     * Validate password requirements
     */
    private function validate_password_requirements($user, $password) {
        // For now, use WordPress default validation
        // This can be enhanced with custom password policies
        return wp_check_password($password, $user->user_pass, $user->ID);
    }
    
    /**
     * Check for suspicious login patterns
     */
    private function is_suspicious_login($user_id) {
        $failed_attempts = get_user_meta($user_id, 'failed_login_attempts', true) ?: 0;
        $last_failed = get_user_meta($user_id, 'last_failed_login', true);
        
        // Check if too many failed attempts recently
        if ($failed_attempts >= 3 && $last_failed) {
            $time_since_last_failed = time() - strtotime($last_failed);
            if ($time_since_last_failed < 300) { // 5 minutes
                return true;
            }
        }
        
        // Check for rapid login attempts from different IPs
        $current_ip = attentrack_get_client_ip();
        $last_login_ip = get_user_meta($user_id, 'last_login_ip', true);
        
        if ($last_login_ip && $last_login_ip !== $current_ip) {
            $last_login = get_user_meta($user_id, 'last_login', true);
            if ($last_login && (time() - strtotime($last_login)) < 300) { // 5 minutes
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Set security headers
     */
    private function set_security_headers($user) {
        if (!headers_sent()) {
            // Set security headers based on user role
            header('X-Frame-Options: DENY');
            header('X-Content-Type-Options: nosniff');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            
            // More restrictive headers for admin users
            if (in_array('institution_admin', $user->roles)) {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            }
        }
    }
    
    /**
     * Add security fields to login form
     */
    public function add_login_security_fields() {
        wp_nonce_field('attentrack_login_security', 'login_security_nonce');
        echo '<input type="hidden" name="login_timestamp" value="' . time() . '">';
    }
    
    /**
     * Validate login security
     */
    public function validate_login_security($user, $password) {
        if (is_wp_error($user)) {
            return $user;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['login_security_nonce'] ?? '', 'attentrack_login_security')) {
            return new WP_Error('security_check_failed', 'Security check failed');
        }
        
        // Check for form replay attacks
        $login_timestamp = intval($_POST['login_timestamp'] ?? 0);
        if (abs(time() - $login_timestamp) > 300) { // 5 minutes
            return new WP_Error('form_expired', 'Login form has expired. Please refresh and try again.');
        }
        
        return $user;
    }
}

// Initialize enhanced authentication
AttenTrack_Enhanced_Auth::getInstance();

/**
 * Custom login redirect based on user role
 */
function attentrack_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('administrator', $user->roles)) {
            return admin_url();
        } elseif (in_array('institution_admin', $user->roles)) {
            return home_url('/dashboard?type=institution');
        } elseif (in_array('staff', $user->roles)) {
            return home_url('/dashboard?type=staff');
        } elseif (in_array('client', $user->roles)) {
            return home_url('/dashboard?type=client');
        }
    }
    
    return $redirect_to;
}
add_filter('login_redirect', 'attentrack_login_redirect', 10, 3);

/**
 * Add session expired message to login form
 */
function attentrack_login_message($message) {
    if (isset($_GET['session_expired'])) {
        $message .= '<div class="alert alert-warning">Your session has expired. Please log in again.</div>';
    }
    return $message;
}
add_filter('login_message', 'attentrack_login_message');
