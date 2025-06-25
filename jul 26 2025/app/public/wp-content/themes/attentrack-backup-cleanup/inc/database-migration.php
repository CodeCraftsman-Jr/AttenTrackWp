<?php
/**
 * Database Migration Script for Multi-Tier Access Control System
 * Migrates from "patient" terminology to "client" and adds new role-based tables
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main migration function to upgrade database schema
 */
function attentrack_migrate_to_client_system() {
    global $wpdb;
    
    // Start transaction
    $wpdb->query('START TRANSACTION');
    
    try {
        // Step 1: Rename patient tables to client tables
        migrate_patient_to_client_tables();
        
        // Step 2: Create new role-based tables
        create_role_based_tables();
        
        // Step 3: Migrate existing user roles
        migrate_user_roles();
        
        // Step 4: Update existing data references
        update_data_references();
        
        // Commit transaction
        $wpdb->query('COMMIT');
        
        // Log successful migration
        error_log('AttenTrack: Successfully migrated to client-based system');
        
        return true;
        
    } catch (Exception $e) {
        // Rollback on error
        $wpdb->query('ROLLBACK');
        error_log('AttenTrack Migration Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Rename patient tables to client tables
 */
function migrate_patient_to_client_tables() {
    global $wpdb;
    
    $old_table = $wpdb->prefix . 'attentrack_patient_details';
    $new_table = $wpdb->prefix . 'attentrack_client_details';
    
    // Check if old table exists and new table doesn't
    $old_exists = $wpdb->get_var("SHOW TABLES LIKE '$old_table'") == $old_table;
    $new_exists = $wpdb->get_var("SHOW TABLES LIKE '$new_table'") == $new_table;
    
    if ($old_exists && !$new_exists) {
        // Rename the table
        $wpdb->query("RENAME TABLE `$old_table` TO `$new_table`");
        
        // Update column names within the table
        $wpdb->query("ALTER TABLE `$new_table` CHANGE `patient_id` `client_id` varchar(20) NOT NULL");
        
        error_log("Renamed table: $old_table to $new_table");
    }
}

/**
 * Create new role-based access control tables
 */
function create_role_based_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // Staff-Client Assignment Table
    $staff_assignments_table = $wpdb->prefix . 'attentrack_staff_assignments';
    $sql = "CREATE TABLE IF NOT EXISTS $staff_assignments_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        institution_id bigint(20) NOT NULL,
        staff_user_id bigint(20) NOT NULL,
        client_user_id bigint(20) NOT NULL,
        assigned_by bigint(20) NOT NULL,
        assignment_date datetime DEFAULT CURRENT_TIMESTAMP,
        status enum('active','inactive','suspended') DEFAULT 'active',
        notes text,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY staff_client_unique (staff_user_id, client_user_id),
        KEY institution_id (institution_id),
        KEY staff_user_id (staff_user_id),
        KEY client_user_id (client_user_id),
        KEY assigned_by (assigned_by),
        KEY status (status)
    ) $charset_collate;";
    
    // User Role Assignments Table (for complex role management)
    $role_assignments_table = $wpdb->prefix . 'attentrack_user_role_assignments';
    $sql .= "CREATE TABLE IF NOT EXISTS $role_assignments_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        role_type enum('client','staff','institution_admin') NOT NULL,
        institution_id bigint(20) DEFAULT NULL,
        assigned_by bigint(20) DEFAULT NULL,
        capabilities text,
        status enum('active','inactive','suspended') DEFAULT 'active',
        effective_from datetime DEFAULT CURRENT_TIMESTAMP,
        effective_until datetime DEFAULT NULL,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_role_unique (user_id, role_type, institution_id),
        KEY user_id (user_id),
        KEY role_type (role_type),
        KEY institution_id (institution_id),
        KEY status (status)
    ) $charset_collate;";
    
    // Subscription Management Enhancement Table
    $subscription_details_table = $wpdb->prefix . 'attentrack_subscription_details';
    $sql .= "CREATE TABLE IF NOT EXISTS $subscription_details_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        subscription_id bigint(20) NOT NULL,
        institution_id bigint(20) NOT NULL,
        current_members int(11) DEFAULT 0,
        max_members int(11) NOT NULL,
        current_staff int(11) DEFAULT 0,
        max_staff int(11) DEFAULT 0,
        features_enabled text,
        billing_cycle enum('monthly','quarterly','yearly') DEFAULT 'monthly',
        auto_renewal tinyint(1) DEFAULT 1,
        trial_end_date datetime DEFAULT NULL,
        last_billing_date datetime DEFAULT NULL,
        next_billing_date datetime DEFAULT NULL,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY subscription_id (subscription_id),
        KEY institution_id (institution_id)
    ) $charset_collate;";
    
    // Audit Log Table for security tracking
    $audit_log_table = $wpdb->prefix . 'attentrack_audit_log';
    $sql .= "CREATE TABLE IF NOT EXISTS $audit_log_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        action varchar(100) NOT NULL,
        resource_type varchar(50) NOT NULL,
        resource_id bigint(20) DEFAULT NULL,
        institution_id bigint(20) DEFAULT NULL,
        ip_address varchar(45),
        user_agent text,
        details text,
        status enum('success','failure','warning') DEFAULT 'success',
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY action (action),
        KEY resource_type (resource_type),
        KEY institution_id (institution_id),
        KEY created_at (created_at),
        KEY status (status)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    error_log('Created role-based access control tables');
}

/**
 * Migrate existing user roles to new system
 */
function migrate_user_roles() {
    global $wpdb;
    
    // Get all users with patient role and convert to client role
    $patient_users = get_users(array('role' => 'patient'));
    foreach ($patient_users as $user) {
        $user_obj = new WP_User($user->ID);
        $user_obj->remove_role('patient');
        $user_obj->add_role('client');
        
        // Add to role assignments table
        $wpdb->insert(
            $wpdb->prefix . 'attentrack_user_role_assignments',
            array(
                'user_id' => $user->ID,
                'role_type' => 'client',
                'status' => 'active'
            )
        );
    }
    
    // Update institution users to institution_admin role
    $institution_users = get_users(array('role' => 'institution'));
    foreach ($institution_users as $user) {
        $user_obj = new WP_User($user->ID);
        $user_obj->remove_role('institution');
        $user_obj->add_role('institution_admin');
        
        // Get institution ID for this user
        $institution_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}attentrack_institutions WHERE user_id = %d",
            $user->ID
        ));
        
        // Add to role assignments table
        $wpdb->insert(
            $wpdb->prefix . 'attentrack_user_role_assignments',
            array(
                'user_id' => $user->ID,
                'role_type' => 'institution_admin',
                'institution_id' => $institution_id,
                'status' => 'active'
            )
        );
    }
    
    error_log('Migrated existing user roles to new system');
}

/**
 * Update data references from patient to client
 */
function update_data_references() {
    global $wpdb;
    
    // Update user meta references
    $wpdb->query("UPDATE {$wpdb->usermeta} SET meta_key = 'user_type_client' WHERE meta_key = 'user_type' AND meta_value = 'patient'");
    $wpdb->query("UPDATE {$wpdb->usermeta} SET meta_value = 'client' WHERE meta_key = 'user_type' AND meta_value = 'patient'");
    
    // Update any other references in custom tables
    $tables_to_update = array(
        $wpdb->prefix . 'attentrack_selective_results',
        $wpdb->prefix . 'attentrack_extended_results',
        $wpdb->prefix . 'attentrack_divided_results',
        $wpdb->prefix . 'attentrack_alternative_results'
    );
    
    foreach ($tables_to_update as $table) {
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
            // Check if table has patient_id column and rename to client_id
            $columns = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'patient_id'");
            if (!empty($columns)) {
                $wpdb->query("ALTER TABLE `$table` CHANGE `patient_id` `client_id` varchar(20)");
            }
        }
    }
    
    error_log('Updated data references from patient to client');
}

/**
 * Check if migration is needed
 */
function attentrack_migration_needed() {
    global $wpdb;
    
    $old_table = $wpdb->prefix . 'attentrack_patient_details';
    $new_table = $wpdb->prefix . 'attentrack_client_details';
    
    $old_exists = $wpdb->get_var("SHOW TABLES LIKE '$old_table'") == $old_table;
    $new_exists = $wpdb->get_var("SHOW TABLES LIKE '$new_table'") == $new_table;
    
    return $old_exists && !$new_exists;
}

/**
 * Run migration if needed
 */
function attentrack_maybe_run_migration() {
    if (attentrack_migration_needed()) {
        return attentrack_migrate_to_client_system();
    }
    return true;
}

// Hook migration to run on theme activation and admin init
add_action('after_switch_theme', 'attentrack_maybe_run_migration');
add_action('admin_init', 'attentrack_maybe_run_migration');
