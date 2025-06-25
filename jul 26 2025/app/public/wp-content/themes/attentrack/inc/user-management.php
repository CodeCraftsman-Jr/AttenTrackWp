<?php
/**
 * User Management Functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create custom tables for user management
 */
function create_custom_user_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // User OTPs Table
    $otps_table = $wpdb->prefix . 'user_otps';
    $sql = "CREATE TABLE IF NOT EXISTS $otps_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        otp varchar(6) NOT NULL,
        expiry datetime NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";
    
    // User Profiles Table
    $profiles_table = $wpdb->prefix . 'user_profiles';
    $sql .= "CREATE TABLE IF NOT EXISTS $profiles_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        profile_id varchar(32) NOT NULL,
        test_id varchar(32) NOT NULL,
        phone_number varchar(15),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY profile_id (profile_id),
        UNIQUE KEY test_id (test_id),
        KEY user_id (user_id),
        KEY phone_number (phone_number)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Generate OTP
 */
function generate_otp() {
    return wp_rand(100000, 999999);
}

/**
 * Store OTP in database
 */
function store_user_otp($user_id, $otp) {
    global $wpdb;
    $table = $wpdb->prefix . 'user_otps';
    
    // Delete any existing OTPs
    $wpdb->delete($table, array('user_id' => $user_id));
    
    // Insert new OTP
    $expiry = date('Y-m-d H:i:s', time() + (10 * 60)); // 10 minutes
    $wpdb->insert($table, array(
        'user_id' => $user_id,
        'otp' => $otp,
        'expiry' => $expiry
    ));
}

/**
 * Generate and store OTP
 */
function generate_and_store_otp($user_id) {
    $otp = generate_otp();
    store_user_otp($user_id, $otp);
    return $otp;
}

/**
 * Verify OTP
 */
function verify_user_otp($user_id, $otp) {
    global $wpdb;
    $table = $wpdb->prefix . 'user_otps';
    
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table 
        WHERE user_id = %d 
        AND otp = %s 
        AND expiry > NOW()",
        $user_id,
        $otp
    ));
    
    if ($row) {
        // Delete the used OTP
        $wpdb->delete($table, array('id' => $row->id));
        return true;
    }
    return false;
}

/**
 * Generate Profile ID
 */
function generate_profile_id() {
    return 'P' . uniqid() . rand(1000, 9999);
}

/**
 * Generate Test ID
 */
function generate_test_id() {
    return 'T' . uniqid() . rand(1000, 9999);
}

/**
 * Get user by phone number
 */
function get_user_by_phone($phone) {
    global $wpdb;
    $profiles_table = $wpdb->prefix . 'user_profiles';
    
    $user_id = $wpdb->get_var($wpdb->prepare(
        "SELECT user_id FROM $profiles_table WHERE phone_number = %s LIMIT 1",
        $phone
    ));
    
    return $user_id ? get_user_by('ID', $user_id) : null;
}

/**
 * Create or update user profile
 */
function create_or_update_user_profile($user_id, $phone = null) {
    global $wpdb;
    
    // Validate user ID
    if (empty($user_id) || !is_numeric($user_id)) {
        error_log('Invalid user ID in create_or_update_user_profile: ' . print_r($user_id, true));
        return array(
            'profile_id' => '',
            'test_id' => ''
        );
    }
    
    // Check if the profiles table exists
    $profiles_table = $wpdb->prefix . 'user_profiles';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$profiles_table'") === $profiles_table;
    
    if (!$table_exists) {
        // Create tables if they don't exist
        error_log('User profiles table does not exist, creating it now');
        create_custom_user_tables();
    }
    
    // Check if profile exists
    $profile = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $profiles_table WHERE user_id = %d",
        $user_id
    ));
    
    if ($profile) {
        // Update existing profile
        if ($phone) {
            $wpdb->update(
                $profiles_table,
                array('phone_number' => $phone),
                array('id' => $profile->id)
            );
        }
        return array(
            'profile_id' => $profile->profile_id,
            'test_id' => $profile->test_id
        );
    } else {
        // Create new profile
        $profile_id = generate_profile_id();
        $test_id = generate_test_id();
        
        $result = $wpdb->insert(
            $profiles_table,
            array(
                'user_id' => $user_id,
                'profile_id' => $profile_id,
                'test_id' => $test_id,
                'phone_number' => $phone
            )
        );
        
        if ($result === false) {
            error_log('Failed to insert user profile: ' . $wpdb->last_error);
            return array(
                'profile_id' => '',
                'test_id' => ''
            );
        }
        
        return array(
            'profile_id' => $profile_id,
            'test_id' => $test_id
        );
    }
}

add_action('after_switch_theme', 'create_custom_user_tables');
