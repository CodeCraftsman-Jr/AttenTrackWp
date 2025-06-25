<?php
/**
 * User Data Consolidation
 * 
 * This file handles the consolidation of user data into a single table
 * and provides functions for managing user authentication data.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create a consolidated user data table
 */
function create_consolidated_user_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Consolidated User Data Table
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    $sql = "CREATE TABLE IF NOT EXISTS $user_data_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        username varchar(60),
        user_pass varchar(255),
        profile_id varchar(32) NOT NULL,
        test_id varchar(32) NOT NULL,
        email varchar(100),
        phone_number varchar(20),
        first_name varchar(50),
        last_name varchar(50),
        display_name varchar(100),
        google_id varchar(100),
        facebook_id varchar(100),
        account_type varchar(20) DEFAULT 'user',
        user_status int(11) DEFAULT 0,
        otp varchar(6),
        otp_expiry datetime,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_id (user_id),
        UNIQUE KEY username (username),
        UNIQUE KEY email (email),
        UNIQUE KEY profile_id (profile_id),
        UNIQUE KEY test_id (test_id),
        KEY phone_number (phone_number),
        KEY account_type (account_type)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Check if table was created
    if ($wpdb->get_var("SHOW TABLES LIKE '$user_data_table'") != $user_data_table) {
        error_log("Failed to create consolidated user data table: $user_data_table");
        return false;
    }
    
    error_log("Successfully created consolidated user data table: $user_data_table");
    return true;
}

/**
 * Migrate existing user data to the consolidated table
 */
function migrate_user_data() {
    global $wpdb;
    
    // Create the consolidated table if it doesn't exist
    if (!create_consolidated_user_table()) {
        return false;
    }
    
    // Source tables
    $profiles_table = $wpdb->prefix . 'user_profiles';
    $otps_table = $wpdb->prefix . 'user_otps';
    $users_table = $wpdb->prefix . 'users';
    $usermeta_table = $wpdb->prefix . 'usermeta';
    
    // Target table
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    
    // Check if source tables exist
    $profiles_exists = $wpdb->get_var("SHOW TABLES LIKE '$profiles_table'") === $profiles_table;
    
    // Get admin user IDs to exclude them from migration
    $admin_users = get_users(array('role' => 'administrator', 'fields' => 'ID'));
    $admin_ids = implode(',', array_map('intval', $admin_users));
    $exclude_condition = !empty($admin_ids) ? "AND u.ID NOT IN ($admin_ids)" : "";
    
    // Begin transaction
    $wpdb->query('START TRANSACTION');
    
    try {
        // Get all non-admin users
        $users = $wpdb->get_results("
            SELECT u.ID, u.user_email, u.display_name, u.user_login, u.user_pass, u.user_status
            FROM $users_table u
            WHERE 1=1 $exclude_condition
        ");
        
        foreach ($users as $user) {
            // Get user metadata
            $first_name = get_user_meta($user->ID, 'first_name', true);
            $last_name = get_user_meta($user->ID, 'last_name', true);
            $google_id = get_user_meta($user->ID, 'google_id', true);
            $facebook_id = get_user_meta($user->ID, 'facebook_id', true);
            $account_type = get_user_meta($user->ID, 'account_type', true) ?: 'user';
            
            // Get profile data if profiles table exists
            $profile_id = '';
            $test_id = '';
            $phone_number = '';
            
            if ($profiles_exists) {
                $profile = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM $profiles_table WHERE user_id = %d",
                    $user->ID
                ));
                
                if ($profile) {
                    $profile_id = $profile->profile_id;
                    $test_id = $profile->test_id;
                    $phone_number = $profile->phone_number;
                }
            }
            
            // Generate profile_id and test_id if they don't exist
            if (empty($profile_id)) {
                $profile_id = 'P' . uniqid() . rand(1000, 9999);
            }
            
            if (empty($test_id)) {
                $test_id = 'T' . uniqid() . rand(1000, 9999);
            }
            
            // Check if user already exists in the consolidated table
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $user_data_table WHERE user_id = %d",
                $user->ID
            ));
            
            if ($existing) {
                // Update existing record
                $wpdb->update(
                    $user_data_table,
                    array(
                        'username' => $user->user_login,
                        'user_pass' => $user->user_pass,
                        'profile_id' => $profile_id,
                        'test_id' => $test_id,
                        'email' => $user->user_email,
                        'phone_number' => $phone_number,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'display_name' => $user->display_name,
                        'google_id' => $google_id,
                        'facebook_id' => $facebook_id,
                        'account_type' => $account_type,
                        'user_status' => $user->user_status,
                        'updated_at' => current_time('mysql')
                    ),
                    array('user_id' => $user->ID)
                );
            } else {
                // Insert new record
                $wpdb->insert(
                    $user_data_table,
                    array(
                        'user_id' => $user->ID,
                        'username' => $user->user_login,
                        'user_pass' => $user->user_pass,
                        'profile_id' => $profile_id,
                        'test_id' => $test_id,
                        'email' => $user->user_email,
                        'phone_number' => $phone_number,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'display_name' => $user->display_name,
                        'google_id' => $google_id,
                        'facebook_id' => $facebook_id,
                        'account_type' => $account_type,
                        'user_status' => $user->user_status,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    )
                );
            }
        }
        
        // Commit transaction
        $wpdb->query('COMMIT');
        return true;
    } catch (Exception $e) {
        // Rollback on error
        $wpdb->query('ROLLBACK');
        error_log('Error migrating user data: ' . $e->getMessage());
        return false;
    }
}

/**
 * Drop old user tables after migration
 */
function drop_old_user_tables() {
    global $wpdb;
    
    $profiles_table = $wpdb->prefix . 'user_profiles';
    $otps_table = $wpdb->prefix . 'user_otps';
    
    // Check if tables exist
    $profiles_exists = $wpdb->get_var("SHOW TABLES LIKE '$profiles_table'") === $profiles_table;
    $otps_exists = $wpdb->get_var("SHOW TABLES LIKE '$otps_table'") === $otps_table;
    
    // Drop tables if they exist
    if ($profiles_exists) {
        $wpdb->query("DROP TABLE $profiles_table");
        error_log("Dropped table: $profiles_table");
    }
    
    if ($otps_exists) {
        $wpdb->query("DROP TABLE $otps_table");
        error_log("Dropped table: $otps_table");
    }
    
    return true;
}

/**
 * Get user data from consolidated table
 */
function get_consolidated_user_data($user_id) {
    global $wpdb;
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $user_data_table WHERE user_id = %d",
        $user_id
    ));
}

/**
 * Get user by profile ID
 */
function get_user_by_profile_id($profile_id) {
    global $wpdb;
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    
    $user_id = $wpdb->get_var($wpdb->prepare(
        "SELECT user_id FROM $user_data_table WHERE profile_id = %s",
        $profile_id
    ));
    
    return $user_id ? get_user_by('ID', $user_id) : null;
}

/**
 * Get user by test ID
 */
function get_user_by_test_id($test_id) {
    global $wpdb;
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    
    $user_id = $wpdb->get_var($wpdb->prepare(
        "SELECT user_id FROM $user_data_table WHERE test_id = %s",
        $test_id
    ));
    
    return $user_id ? get_user_by('ID', $user_id) : null;
}

/**
 * Get user by phone number
 */
function get_user_by_phone_number($phone) {
    global $wpdb;
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    
    $user_id = $wpdb->get_var($wpdb->prepare(
        "SELECT user_id FROM $user_data_table WHERE phone_number = %s",
        $phone
    ));
    
    return $user_id ? get_user_by('ID', $user_id) : null;
}

/**
 * Get user by social provider ID
 */
function get_user_by_social_id($provider, $provider_id) {
    global $wpdb;
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    
    $column = $provider . '_id';
    if (!in_array($column, array('google_id', 'facebook_id'))) {
        return null;
    }
    
    $user_id = $wpdb->get_var($wpdb->prepare(
        "SELECT user_id FROM $user_data_table WHERE $column = %s",
        $provider_id
    ));
    
    return $user_id ? get_user_by('ID', $user_id) : null;
}

/**
 * Create or update user in consolidated table
 */
function create_or_update_consolidated_user($user_id, $data = array()) {
    global $wpdb;
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    
    // Ensure user_id is valid
    if (empty($user_id) || !is_numeric($user_id)) {
        return false;
    }
    
    // Check if user exists in the consolidated table
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $user_data_table WHERE user_id = %d",
        $user_id
    ));
    
    // Get WP user data
    $wp_user = get_user_by('ID', $user_id);
    if (!$wp_user) {
        return false;
    }
    
    // Prepare data with defaults
    $user_data = array_merge(array(
        'email' => $wp_user->user_email,
        'display_name' => $wp_user->display_name,
        'first_name' => get_user_meta($user_id, 'first_name', true),
        'last_name' => get_user_meta($user_id, 'last_name', true),
        'account_type' => get_user_meta($user_id, 'account_type', true) ?: 'user',
        'updated_at' => current_time('mysql')
    ), $data);
    
    // Generate profile_id and test_id if not provided
    if (empty($user_data['profile_id'])) {
        $user_data['profile_id'] = 'P' . uniqid() . rand(1000, 9999);
    }
    
    if (empty($user_data['test_id'])) {
        $user_data['test_id'] = 'T' . uniqid() . rand(1000, 9999);
    }
    
    if ($existing) {
        // Update existing record
        $result = $wpdb->update(
            $user_data_table,
            $user_data,
            array('user_id' => $user_id)
        );
    } else {
        // Insert new record
        $user_data['user_id'] = $user_id;
        $user_data['created_at'] = current_time('mysql');
        
        $result = $wpdb->insert(
            $user_data_table,
            $user_data
        );
    }
    
    if ($result === false) {
        error_log('Error creating/updating consolidated user: ' . $wpdb->last_error);
        return false;
    }
    
    // Get the updated user data
    return get_consolidated_user_data($user_id);
}

/**
 * Generate and store OTP for user
 */
function generate_and_store_consolidated_otp($user_id) {
    global $wpdb;
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    
    // Generate OTP
    $otp = wp_rand(100000, 999999);
    $expiry = date('Y-m-d H:i:s', time() + (10 * 60)); // 10 minutes
    
    // Update user record with OTP
    $wpdb->update(
        $user_data_table,
        array(
            'otp' => $otp,
            'otp_expiry' => $expiry
        ),
        array('user_id' => $user_id)
    );
    
    return $otp;
}

/**
 * Verify OTP for user
 */
function verify_consolidated_otp($user_id, $otp) {
    global $wpdb;
    $user_data_table = $wpdb->prefix . 'attentrack_user_data';
    
    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $user_data_table 
        WHERE user_id = %d 
        AND otp = %s 
        AND otp_expiry > NOW()",
        $user_id,
        $otp
    ));
    
    if ($result) {
        // Clear OTP after successful verification
        $wpdb->update(
            $user_data_table,
            array(
                'otp' => null,
                'otp_expiry' => null
            ),
            array('user_id' => $user_id)
        );
        
        return true;
    }
    
    return false;
}

/**
 * Run the migration process
 */
function run_user_data_migration() {
    // Create the consolidated table
    if (!create_consolidated_user_table()) {
        return array(
            'success' => false,
            'message' => 'Failed to create consolidated user table'
        );
    }
    
    // Migrate user data
    if (!migrate_user_data()) {
        return array(
            'success' => false,
            'message' => 'Failed to migrate user data'
        );
    }
    
    // Drop old tables
    if (!drop_old_user_tables()) {
        return array(
            'success' => false,
            'message' => 'Failed to drop old user tables'
        );
    }
    
    return array(
        'success' => true,
        'message' => 'User data migration completed successfully'
    );
}

// Add admin page for migration - REMOVED
// function add_user_data_migration_page() {
//     add_management_page(
//         'User Data Migration',
//         'User Data Migration',
//         'manage_options',
//         'user-data-migration',
//         'display_user_data_migration_page'
//     );
// }
// add_action('admin_menu', 'add_user_data_migration_page');

// Display migration page
function display_user_data_migration_page() {
    ?>
    <div class="wrap">
        <h1>User Data Migration</h1>
        
        <p>This tool will consolidate all user data into a single table for easier management.</p>
        <p><strong>Warning:</strong> This process will migrate all user data except for administrators and remove the old tables.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('user_data_migration_nonce', 'user_data_migration_nonce'); ?>
            <input type="hidden" name="action" value="run_user_data_migration">
            <p>
                <input type="submit" name="submit" class="button button-primary" value="Run Migration">
            </p>
        </form>
        
        <?php
        // Handle migration request
        if (isset($_POST['action']) && $_POST['action'] === 'run_user_data_migration') {
            if (!isset($_POST['user_data_migration_nonce']) || !wp_verify_nonce($_POST['user_data_migration_nonce'], 'user_data_migration_nonce')) {
                echo '<div class="notice notice-error"><p>Security check failed.</p></div>';
                return;
            }
            
            $result = run_user_data_migration();
            
            if ($result['success']) {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }
        ?>
    </div>
    <?php
}
