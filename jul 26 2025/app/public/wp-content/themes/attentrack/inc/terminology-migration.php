<?php
/**
 * Terminology Migration Script for AttenTrack
 * Systematically replaces "patient" with "client" throughout the codebase
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Terminology Migration Manager Class
 */
class AttenTrack_Terminology_Migration {
    
    private static $instance = null;
    private $migration_log = array();
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Run complete terminology migration
     */
    public function run_migration() {
        $this->log_message('Starting terminology migration from "patient" to "client"');
        
        try {
            // 1. Migrate database table names and columns
            $this->migrate_database_schema();
            
            // 2. Migrate user meta and options
            $this->migrate_user_meta();
            
            // 3. Migrate file contents
            $this->migrate_file_contents();
            
            // 4. Update WordPress options
            $this->migrate_wordpress_options();
            
            $this->log_message('Terminology migration completed successfully');
            return array('success' => true, 'log' => $this->migration_log);
            
        } catch (Exception $e) {
            $this->log_message('Migration failed: ' . $e->getMessage(), 'error');
            return array('success' => false, 'error' => $e->getMessage(), 'log' => $this->migration_log);
        }
    }
    
    /**
     * Migrate database schema (tables and columns)
     */
    private function migrate_database_schema() {
        global $wpdb;
        
        $this->log_message('Migrating database schema...');
        
        // Tables to rename
        $table_renames = array(
            $wpdb->prefix . 'attentrack_patient_details' => $wpdb->prefix . 'attentrack_client_details'
        );
        
        foreach ($table_renames as $old_table => $new_table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$old_table'") == $old_table) {
                $wpdb->query("RENAME TABLE `$old_table` TO `$new_table`");
                $this->log_message("Renamed table: $old_table -> $new_table");
            }
        }
        
        // Column renames in existing tables
        $column_renames = array(
            $wpdb->prefix . 'attentrack_client_details' => array(
                'patient_id' => 'client_id'
            ),
            $wpdb->prefix . 'attentrack_selective_results' => array(
                'patient_id' => 'client_id'
            ),
            $wpdb->prefix . 'attentrack_extended_results' => array(
                'patient_id' => 'client_id'
            ),
            $wpdb->prefix . 'attentrack_divided_results' => array(
                'patient_id' => 'client_id'
            ),
            $wpdb->prefix . 'attentrack_alternative_results' => array(
                'patient_id' => 'client_id'
            )
        );
        
        foreach ($column_renames as $table => $columns) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
                foreach ($columns as $old_column => $new_column) {
                    // Check if old column exists
                    $column_exists = $wpdb->get_results("SHOW COLUMNS FROM `$table` LIKE '$old_column'");
                    if (!empty($column_exists)) {
                        $wpdb->query("ALTER TABLE `$table` CHANGE `$old_column` `$new_column` VARCHAR(20)");
                        $this->log_message("Renamed column in $table: $old_column -> $new_column");
                    }
                }
            }
        }
    }
    
    /**
     * Migrate user meta and related data
     */
    private function migrate_user_meta() {
        global $wpdb;
        
        $this->log_message('Migrating user meta data...');
        
        // Update user meta keys
        $meta_updates = array(
            'user_type' => array('patient' => 'client'),
            'account_type' => array('patient' => 'client')
        );
        
        foreach ($meta_updates as $meta_key => $value_map) {
            foreach ($value_map as $old_value => $new_value) {
                $updated = $wpdb->update(
                    $wpdb->usermeta,
                    array('meta_value' => $new_value),
                    array('meta_key' => $meta_key, 'meta_value' => $old_value)
                );
                
                if ($updated) {
                    $this->log_message("Updated user meta: $meta_key '$old_value' -> '$new_value' ($updated records)");
                }
            }
        }
        
        // Update capability meta (WordPress roles)
        $capabilities_updated = $wpdb->query(
            "UPDATE {$wpdb->usermeta} 
             SET meta_value = REPLACE(meta_value, 'patient', 'client') 
             WHERE meta_key LIKE '%capabilities%' 
             AND meta_value LIKE '%patient%'"
        );
        
        if ($capabilities_updated) {
            $this->log_message("Updated user capabilities: $capabilities_updated records");
        }
    }
    
    /**
     * Migrate file contents
     */
    private function migrate_file_contents() {
        $this->log_message('Migrating file contents...');
        
        $theme_dir = get_template_directory();
        $files_to_process = $this->get_files_to_migrate($theme_dir);
        
        foreach ($files_to_process as $file_path) {
            $this->migrate_file_content($file_path);
        }
    }
    
    /**
     * Get list of files to migrate
     */
    private function get_files_to_migrate($directory) {
        $files = array();
        $extensions = array('php', 'js', 'css', 'html');
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $extension = strtolower($file->getExtension());
                if (in_array($extension, $extensions)) {
                    $files[] = $file->getPathname();
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Migrate content of a single file
     */
    private function migrate_file_content($file_path) {
        if (!is_readable($file_path) || !is_writable($file_path)) {
            return;
        }
        
        $content = file_get_contents($file_path);
        $original_content = $content;
        
        // Define replacement patterns
        $replacements = array(
            // Database table references
            'attentrack_patient_details' => 'attentrack_client_details',
            
            // Variable names and function parameters
            '$patient_' => '$client_',
            'patient_id' => 'client_id',
            'patient_data' => 'client_data',
            'patient_details' => 'client_details',
            'patient_info' => 'client_info',
            'patient_results' => 'client_results',
            
            // Function names
            'get_patient_' => 'get_client_',
            'save_patient_' => 'save_client_',
            'update_patient_' => 'update_client_',
            'delete_patient_' => 'delete_client_',
            'create_patient_' => 'create_client_',
            
            // Class names and constants
            'Patient' => 'Client',
            'PATIENT_' => 'CLIENT_',
            
            // User-facing text (be careful with context)
            "'patient'" => "'client'",
            '"patient"' => '"client"',
            'Patient Dashboard' => 'Client Dashboard',
            'patient dashboard' => 'client dashboard',
            'Patient Details' => 'Client Details',
            'patient details' => 'client details',
            'Patient Profile' => 'Client Profile',
            'patient profile' => 'client profile',
            
            // Form fields and HTML
            'name="patient_' => 'name="client_',
            'id="patient_' => 'id="client_',
            'class="patient_' => 'class="client_',
            
            // JavaScript variables and functions
            'var patient' => 'var client',
            'let patient' => 'let client',
            'const patient' => 'const client',
            'function patient' => 'function client',
            '.patient' => '.client',
            
            // CSS classes
            '.patient-' => '.client-',
            '#patient-' => '#client-',
            
            // Comments and documentation
            '// Patient' => '// Client',
            '/* Patient' => '/* Client',
            '* Patient' => '* Client',
            '# Patient' => '# Client'
        );
        
        // Apply replacements
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        
        // Special case: WordPress role references
        $content = preg_replace('/role.*=.*[\'"]patient[\'"]/', 'role = "client"', $content);
        $content = preg_replace('/add_role\s*\(\s*[\'"]patient[\'"]/', 'add_role("client"', $content);
        $content = preg_replace('/get_role\s*\(\s*[\'"]patient[\'"]/', 'get_role("client"', $content);
        
        // Only write if content changed
        if ($content !== $original_content) {
            file_put_contents($file_path, $content);
            $this->log_message("Updated file: " . basename($file_path));
        }
    }
    
    /**
     * Migrate WordPress options
     */
    private function migrate_wordpress_options() {
        global $wpdb;
        
        $this->log_message('Migrating WordPress options...');
        
        // Update option values that contain "patient"
        $options_updated = $wpdb->query(
            "UPDATE {$wpdb->options} 
             SET option_value = REPLACE(option_value, 'patient', 'client') 
             WHERE option_name LIKE '%attentrack%' 
             AND option_value LIKE '%patient%'"
        );
        
        if ($options_updated) {
            $this->log_message("Updated WordPress options: $options_updated records");
        }
        
        // Update specific option names if they exist
        $option_renames = array(
            'attentrack_patient_settings' => 'attentrack_client_settings',
            'default_patient_role' => 'default_client_role'
        );
        
        foreach ($option_renames as $old_option => $new_option) {
            $value = get_option($old_option);
            if ($value !== false) {
                update_option($new_option, $value);
                delete_option($old_option);
                $this->log_message("Renamed option: $old_option -> $new_option");
            }
        }
    }
    
    /**
     * Log migration message
     */
    private function log_message($message, $level = 'info') {
        $timestamp = current_time('mysql');
        $this->migration_log[] = array(
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message
        );
        
        // Also log to WordPress error log
        error_log("AttenTrack Migration [$level]: $message");
    }
    
    /**
     * Get migration status
     */
    public function get_migration_status() {
        global $wpdb;
        
        $status = array(
            'database_migrated' => false,
            'files_migrated' => false,
            'remaining_patient_references' => 0
        );
        
        // Check if main table was renamed
        $old_table = $wpdb->prefix . 'attentrack_patient_details';
        $new_table = $wpdb->prefix . 'attentrack_client_details';
        
        $status['database_migrated'] = (
            $wpdb->get_var("SHOW TABLES LIKE '$old_table'") != $old_table &&
            $wpdb->get_var("SHOW TABLES LIKE '$new_table'") == $new_table
        );
        
        // Count remaining "patient" references in user meta
        $patient_references = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} 
             WHERE meta_value LIKE '%patient%' 
             AND meta_key IN ('user_type', 'account_type')"
        );
        
        $status['remaining_patient_references'] = intval($patient_references);
        $status['files_migrated'] = ($patient_references == 0);
        
        return $status;
    }
}

/**
 * AJAX handler for running migration
 */
add_action('wp_ajax_run_terminology_migration', function() {
    check_ajax_referer('attentrack_terminology_migration', 'nonce');
    
    if (!current_user_can('administrator')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    $migration = AttenTrack_Terminology_Migration::getInstance();
    $result = $migration->run_migration();
    
    wp_send_json($result);
});

/**
 * AJAX handler for checking migration status
 */
add_action('wp_ajax_check_migration_status', function() {
    check_ajax_referer('attentrack_terminology_migration', 'nonce');
    
    if (!current_user_can('administrator')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    $migration = AttenTrack_Terminology_Migration::getInstance();
    $status = $migration->get_migration_status();
    
    wp_send_json_success($status);
});
