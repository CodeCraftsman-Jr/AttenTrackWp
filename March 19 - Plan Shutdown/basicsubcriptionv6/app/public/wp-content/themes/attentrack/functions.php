<?php
if (!defined('ABSPATH')) exit;

// Include required files
require_once get_template_directory() . '/includes/class-bootstrap-walker-nav-menu.php';
require_once get_template_directory() . '/inc/create-auth-pages.php';
require_once get_template_directory() . '/inc/authentication.php';
require_once get_template_directory() . '/inc/database-setup.php';

// Initialize database tables
initialize_database_tables();

// Create required pages
function attentrack_create_required_pages() {
    // Create Database Test page if it doesn't exist
    $database_test_page = get_page_by_path('database-test');
    if (!$database_test_page) {
        $page_data = array(
            'post_title'    => 'Database Test',
            'post_name'     => 'database-test',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_content'  => '',
        );
        $page_id = wp_insert_post($page_data);
        if (!is_wp_error($page_id)) {
            update_post_meta($page_id, '_wp_page_template', 'templates/database-test.php');
        }
    }
}
add_action('after_switch_theme', 'attentrack_create_required_pages');
add_action('init', 'attentrack_create_required_pages');

// Theme Setup
function attentrack_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));

    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'attentrack'),
        'footer'  => __('Footer Menu', 'attentrack'),
    ));
}
add_action('after_setup_theme', 'attentrack_setup');

// Register the test templates
function attentrack_register_test_templates() {
    // Register the templates
    add_filter('theme_page_templates', function($templates) {
        $templates['templates/selective-and-sustained-attention-test-part-1.php'] = 'Selective and Sustained Attention Test Part 1';
        $templates['templates/database-test.php'] = 'Database Test';
        return $templates;
    });

    // Create the test pages if they don't exist
    $test_pages = array(
        'test1phase1' => array(
            'title' => 'Selective and Sustained Attention Test Part 1',
            'template' => 'templates/selective-and-sustained-attention-test-part-1.php'
        ),
        'database-test' => array(
            'title' => 'Database Test',
            'template' => 'templates/database-test.php'
        )
    );

    foreach ($test_pages as $slug => $page_data) {
        $page = get_page_by_path($slug);
        if (!$page) {
            $new_page = array(
                'post_title'    => $page_data['title'],
                'post_name'     => $slug,
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_content'  => '',
            );
            $page_id = wp_insert_post($new_page);
            if (!is_wp_error($page_id)) {
                update_post_meta($page_id, '_wp_page_template', $page_data['template']);
            }
        }
    }
}
add_action('after_setup_theme', 'attentrack_register_test_templates');
add_action('init', 'attentrack_register_test_templates');

// Template loader for test pages
function attentrack_template_loader($template) {
    if (is_page()) {
        $template_slug = get_page_template_slug();
        $valid_templates = array(
            'templates/selective-and-sustained-attention-test-part-1.php',
            'templates/database-test.php'
        );
        
        if (in_array($template_slug, $valid_templates)) {
            $new_template = locate_template(array($template_slug));
            if (!empty($new_template)) {
                return $new_template;
            }
        }
    }
    return $template;
}
add_filter('template_include', 'attentrack_template_loader');

// Enqueue scripts and styles for the theme
function attentrack_enqueue_scripts() {
    // Load test scripts and styles on test pages
    if (is_page(array('test1phase1', 'database-test'))) {
        // Enqueue database test script first
        wp_enqueue_script(
            'database-test',
            get_template_directory_uri() . '/js/database-test.js',
            array('jquery'),
            filemtime(get_template_directory() . '/js/database-test.js'),
            true
        );

        // Add WP API settings for database test
        wp_localize_script('database-test', 'wpApiSettings', array(
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest')
        ));

        // Enqueue test styles
        wp_enqueue_style(
            'test-styles',
            get_template_directory_uri() . '/css/test-styles.css',
            array(),
            filemtime(get_template_directory() . '/css/test-styles.css')
        );

        // Enqueue jQuery first
        wp_enqueue_script('jquery');

        // Enqueue test results display script
        wp_enqueue_script(
            'test-results-display',
            get_template_directory_uri() . '/js/test-results-display.js',
            array('jquery'),
            filemtime(get_template_directory() . '/js/test-results-display.js'),
            true
        );

        // Enqueue selective attention test part 1 script
        wp_enqueue_script(
            'selective-attention-test-part1',
            get_template_directory_uri() . '/js/selective-attention-test-part1.js',
            array('jquery', 'test-results-display'),
            filemtime(get_template_directory() . '/js/selective-attention-test-part1.js'),
            true
        );

        // Localize script with AJAX data and test info
        wp_localize_script(
            'selective-attention-test-part1',
            'testData',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('attentrack_test_nonce'),
                'testID' => isset($_GET['test_id']) ? sanitize_text_field($_GET['test_id']) : '',
                'nextTestUrl' => get_permalink(get_page_by_path('selective-and-sustained-attention-test-part-2')),
                'debug' => WP_DEBUG
            )
        );

        // Add WP API settings
        wp_enqueue_script('wp-api');
        wp_localize_script('wp-api', 'wpApiSettings', array(
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest')
        ));
    }

    // Enqueue theme's main stylesheet
    wp_enqueue_style(
        'attentrack-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version')
    );

    // Enqueue Bootstrap CSS
    wp_enqueue_style(
        'bootstrap',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css',
        array(),
        '5.1.3'
    );

    // Enqueue Font Awesome
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
        array(),
        '5.15.4'
    );

    // Enqueue Google Fonts
    wp_enqueue_style(
        'google-fonts',
        'https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;500;600&family=Bebas+Neue&display=swap',
        array(),
        null
    );

    // Enqueue Bootstrap JS
    wp_enqueue_script(
        'bootstrap-bundle',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js',
        array('jquery'),
        '5.1.3',
        true
    );
    
    // Enqueue Chart.js
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.7.0', true);
    
    // Add custom script for test results
    wp_enqueue_script('attentrack-test-results', get_template_directory_uri() . '/js/test-results.js', array('jquery'), '1.0', true);
    
    // Enqueue authentication script
    wp_enqueue_script('attentrack-auth', get_template_directory_uri() . '/js/auth.js', array('jquery'), '1.0', true);
    
    // Localize script for AJAX
    wp_localize_script('attentrack-test-results', 'attentrackAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));

    // Localize script for authentication
    wp_localize_script('attentrack-auth', 'attentrack_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('attentrack_auth_nonce')
    ));

}
add_action('wp_enqueue_scripts', 'attentrack_enqueue_scripts');

// Enqueue Chart.js and admin scripts
function attentrack_admin_scripts($hook) {
    if ($hook === 'attentrack-db_page_attentrack-results') {
        wp_enqueue_script('attentrack-admin', get_template_directory_uri() . '/js/admin.js', array('jquery', 'chartjs'), '1.0', true);
    }
}
add_action('admin_enqueue_scripts', 'attentrack_admin_scripts');

// Register Custom Post Types
function attentrack_register_post_types() {
    // Register attention test post type
    register_post_type('attention_test', array(
        'labels' => array(
            'name' => __('Attention Tests', 'attentrack'),
            'singular_name' => __('Attention Test', 'attentrack'),
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_icon' => 'dashicons-clipboard',
    ));
}
add_action('init', 'attentrack_register_post_types');

// Firebase Authentication Handler
add_action('wp_ajax_nopriv_firebase_auth_handler', 'handle_firebase_auth');
add_action('wp_ajax_firebase_auth_handler', 'handle_firebase_auth');

function handle_firebase_auth() {
    $firebase_id = sanitize_text_field($_POST['firebase_id']);
    $email = sanitize_email($_POST['email']);
    $display_name = sanitize_text_field($_POST['display_name']);
    $phone_number = sanitize_text_field($_POST['phone_number']);

    // Check if user exists with this Firebase ID
    $existing_user = get_users(array(
        'meta_key' => 'firebase_user_id',
        'meta_value' => $firebase_id,
        'number' => 1
    ));

    if (!empty($existing_user)) {
        // User exists, log them in
        $user = $existing_user[0];
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        wp_send_json_success(array('message' => 'Logged in successfully'));
    } else {
        // Check if user exists with this email
        $user = get_user_by('email', $email);
        
        if (!$user) {
            // Create new user
            $username = sanitize_user(strstr($email, '@', true));
            $counter = 1;
            
            // Ensure username is unique
            while (username_exists($username)) {
                $username = sanitize_user(strstr($email, '@', true)) . $counter;
                $counter++;
            }

            $user_id = wp_create_user($username, wp_generate_password(), $email);
            
            if (is_wp_error($user_id)) {
                wp_send_json_error($user_id->get_error_message());
                return;
            }

            // Generate unique patient ID (AT = AttenTrack, followed by timestamp and random number)
            $unique_id = 'AT' . time() . rand(1000, 9999);

            // Update user meta
            wp_update_user(array(
                'ID' => $user_id,
                'display_name' => $display_name ?: $username,
                'nickname' => $display_name ?: $username
            ));

            update_user_meta($user_id, 'firebase_user_id', $firebase_id);
            update_user_meta($user_id, 'patient_id', $unique_id);
            if ($phone_number) {
                update_user_meta($user_id, 'phone_number', $phone_number);
            }

            // Set user role
            $user = new WP_User($user_id);
            $user->set_role('subscriber');

            // Log the user in
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
            wp_send_json_success(array('message' => 'User created and logged in successfully'));
        } else {
            // Link existing user with Firebase
            update_user_meta($user->ID, 'firebase_user_id', $firebase_id);
            if ($phone_number) {
                update_user_meta($user->ID, 'phone_number', $phone_number);
            }

            // Log the user in
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            wp_send_json_success(array('message' => 'User linked and logged in successfully'));
        }
    }
}

// Create the user details table
function create_user_details_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_details';
    
    // Check if table exists
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            full_name varchar(100),
            age int,
            gender varchar(20),
            medical_history text,
            symptoms text,
            diagnosis text,
            treatment_plan text,
            last_visit datetime,
            next_appointment datetime,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        error_log('User details table created successfully');
    }
}

// Create table when theme is activated
add_action('after_switch_theme', 'create_user_details_table');

// Also create table now if it doesn't exist
add_action('init', function() {
    create_user_details_table();
});

// Function to get user details
function get_user_details($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_details';
    
    // Check if table exists
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        create_user_details_table();
    }
    
    // Get user details
    $details = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d",
            $user_id
        ),
        ARRAY_A
    );
    
    // Return empty array if no details found
    return $details ?: array();
}

// Function to save user details
function save_user_details() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_details';
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'User not logged in']);
        return;
    }
    
    $user_id = get_current_user_id();
    
    // Prepare the data
    $data = [
        'user_id' => $user_id,
        'full_name' => sanitize_text_field($_POST['full_name'] ?? ''),
        'age' => intval($_POST['age'] ?? 0),
        'gender' => sanitize_text_field($_POST['gender'] ?? ''),
        'medical_history' => sanitize_textarea_field($_POST['medical_history'] ?? ''),
        'symptoms' => sanitize_textarea_field($_POST['symptoms'] ?? ''),
        'diagnosis' => sanitize_textarea_field($_POST['diagnosis'] ?? ''),
        'treatment_plan' => sanitize_textarea_field($_POST['treatment_plan'] ?? ''),
        'next_appointment' => sanitize_text_field($_POST['next_appointment'] ?? null)
    ];
    
    // Check if record exists
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE user_id = %d",
        $user_id
    ));
    
    if ($existing) {
        // Update
        $result = $wpdb->update(
            $table_name,
            $data,
            ['user_id' => $user_id]
        );
    } else {
        // Insert
        $result = $wpdb->insert($table_name, $data);
    }
    
    if ($result !== false) {
        wp_send_json_success([
            'message' => 'Data saved successfully',
            'data' => $data
        ]);
    } else {
        wp_send_json_error([
            'message' => 'Failed to save data',
            'error' => $wpdb->last_error
        ]);
    }
}

// Register AJAX actions
add_action('wp_ajax_save_user_details', 'save_user_details');
add_action('wp_ajax_nopriv_save_user_details', function() {
    wp_send_json_error(['message' => 'User not logged in']);
});

// Handle profile photo upload
function handle_profile_photo_upload() {
    check_ajax_referer('profile_photo_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'User not logged in']);
        return;
    }

    if (!isset($_FILES['profile_photo'])) {
        wp_send_json_error(['message' => 'No file uploaded']);
        return;
    }

    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');

    $user_id = get_current_user_id();
    $attachment_id = media_handle_upload('profile_photo', 0);

    if (is_wp_error($attachment_id)) {
        wp_send_json_error(['message' => $attachment_id->get_error_message()]);
        return;
    }

    // Get old photo ID and delete it
    $old_photo_id = get_user_meta($user_id, 'profile_photo_id', true);
    if ($old_photo_id) {
        wp_delete_attachment($old_photo_id, true);
    }

    // Save new photo ID
    update_user_meta($user_id, 'profile_photo_id', $attachment_id);
    $photo_url = wp_get_attachment_url($attachment_id);
    
    wp_send_json_success([
        'message' => 'Photo uploaded successfully',
        'photo_url' => $photo_url
    ]);
}
add_action('wp_ajax_handle_profile_photo', 'handle_profile_photo_upload');

// Handle account deletion
function handle_account_deletion() {
    check_ajax_referer('delete_account_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'User not logged in']);
        return;
    }

    $user_id = get_current_user_id();
    
    // Delete profile photo if exists
    $photo_id = get_user_meta($user_id, 'profile_photo_id', true);
    if ($photo_id) {
        wp_delete_attachment($photo_id, true);
    }
    
    // Delete user
    if (wp_delete_user($user_id)) {
        wp_send_json_success(['message' => 'Account deleted successfully']);
    } else {
        wp_send_json_error(['message' => 'Failed to delete account']);
    }
}
add_action('wp_ajax_delete_account', 'handle_account_deletion');

// Register REST API endpoints
function attentrack_register_rest_routes() {
    // Generate Test ID endpoint
    register_rest_route('attentrack/v1', '/generate-test-id', array(
        'methods' => 'GET',
        'callback' => 'generate_unique_test_id',
        'permission_callback' => function() {
            return is_user_logged_in();
        }
    ));

    // Save Patient Details endpoint
    register_rest_route('attentrack/v1', '/save-patient-details', array(
        'methods' => 'POST',
        'callback' => 'save_patient_details',
        'permission_callback' => function() {
            return is_user_logged_in();
        }
    ));
}
add_action('rest_api_init', 'attentrack_register_rest_routes');

// Generate unique test ID
function generate_unique_test_id($user_id) {
    $prefix = 'AT';
    $timestamp = time();
    $random = wp_rand(1000, 9999);
    return $prefix . $user_id . '_' . $timestamp . '_' . $random;
}

// Save patient details
function save_patient_details($request) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('not_logged_in', 'User must be logged in', array('status' => 401));
    }

    $parameters = $request->get_json_params();
    
    // Combine first and last name
    $full_name = '';
    if (!empty($parameters['firstName'])) {
        $full_name = sanitize_text_field($parameters['firstName']);
        if (!empty($parameters['lastName'])) {
            $full_name .= ' ' . sanitize_text_field($parameters['lastName']);
        }
    }

    // Calculate age from DOB
    $age = '';
    if (!empty($parameters['dob'])) {
        $dob = new DateTime($parameters['dob']);
        $today = new DateTime();
        $age = $dob->diff($today)->y;
    }
    
    // Required fields validation
    if (empty($parameters['patientId']) || empty($full_name) || empty($parameters['dob']) || 
        empty($parameters['gender']) || empty($parameters['phone']) || empty($parameters['email']) || 
        empty($parameters['address'])) {
        return new WP_Error('missing_field', 'Please fill in all required fields', array('status' => 400));
    }

    // Save patient details as user meta
    update_user_meta($user_id, 'patient_id', sanitize_text_field($parameters['patientId']));
    update_user_meta($user_id, 'patient_name', $full_name);
    update_user_meta($user_id, 'patient_dob', sanitize_text_field($parameters['dob']));
    update_user_meta($user_id, 'patient_age', $age);
    update_user_meta($user_id, 'patient_gender', sanitize_text_field($parameters['gender']));
    update_user_meta($user_id, 'patient_phone', sanitize_text_field($parameters['phone']));
    update_user_meta($user_id, 'patient_email', sanitize_email($parameters['email']));
    update_user_meta($user_id, 'patient_address', sanitize_textarea_field($parameters['address']));

    // Optional fields
    if (!empty($parameters['insuranceProvider'])) {
        update_user_meta($user_id, 'patient_city_state', sanitize_text_field($parameters['insuranceProvider']));
    }
    if (!empty($parameters['insuranceNumber'])) {
        update_user_meta($user_id, 'patient_nationality', sanitize_text_field($parameters['insuranceNumber']));
    }
    if (!empty($parameters['medicalHistory'])) {
        update_user_meta($user_id, 'patient_medical_history', sanitize_textarea_field($parameters['medicalHistory']));
    }

    return array(
        'success' => true,
        'message' => 'Patient details saved successfully'
    );
}

// Remove old test results functions
remove_action('wp_ajax_save_test_results', 'handle_save_test_results');
remove_action('init', 'register_test_result_post_type');
remove_action('after_switch_theme', 'create_test_results_table');

// Remove old verification endpoint
remove_action('wp_ajax_verify_test_results', 'verify_test_results');

// Create custom tables on theme activation
function attentrack_create_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Test Results Table
    $table_name = $wpdb->prefix . 'test_results';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        test_type varchar(50) NOT NULL,
        total_count int(11) NOT NULL,
        p_count int(11) NOT NULL,
        total_responses int(11) NOT NULL,
        correct_responses int(11) NOT NULL,
        accuracy float NOT NULL,
        avg_reaction_time float NOT NULL,
        missed_responses int(11) NOT NULL,
        false_alarms int(11) NOT NULL,
        score int(11) NOT NULL,
        test_date datetime DEFAULT CURRENT_TIMESTAMP,
        session_id varchar(50) NOT NULL,
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY test_type (test_type),
        KEY test_date (test_date)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $result = dbDelta($sql);
    
    if (empty($result)) {
        error_log('Failed to create/update test_results table');
        error_log('Last MySQL error: ' . $wpdb->last_error);
        return false;
    }
    
    error_log('Test results table created/updated successfully');
    error_log('dbDelta result: ' . print_r($result, true));
    
    return true;
}

// Run table creation on theme activation
add_action('after_switch_theme', 'attentrack_create_tables');

// Also run it now to ensure tables exist
attentrack_create_tables();

// Test Results Logging and Validation
function validate_test_results($data) {
    $errors = array();
    
    // Validate test type
    $valid_test_types = array('selective_attention', 'selective_attention_extended', 'alternative_attention', 'divided_attention');
    if (!isset($data['test_type']) || !in_array($data['test_type'], $valid_test_types)) {
        $errors[] = 'Invalid test type';
    }

    // Validate numeric values
    $numeric_fields = array(
        'score' => array(0, 100),
        'accuracy' => array(0, 100),
        'reaction_time' => array(0.1, 10),
        'total_letters' => array(1, 1000),
        'p_letters' => array(0, 1000),
        'missed_responses' => array(0, 1000),
        'false_alarms' => array(0, 1000)
    );

    foreach ($numeric_fields as $field => $range) {
        if (isset($data[$field])) {
            $value = floatval($data[$field]);
            if ($value < $range[0] || $value > $range[1]) {
                $errors[] = sprintf('%s must be between %s and %s', $field, $range[0], $range[1]);
            }
        }
    }

    // Validate letter counts
    if (isset($data['total_letters']) && isset($data['p_letters'])) {
        if ($data['p_letters'] > $data['total_letters']) {
            $errors[] = 'P letters count cannot exceed total letters';
        }
    }

    // Validate responses
    if (empty($data['responses'])) {
        $errors[] = 'Responses data is required';
    }

    return $errors;
}

function log_test_results($data, $result) {
    $log_dir = WP_CONTENT_DIR . '/test_logs';
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
    }

    $current_user = wp_get_current_user();
    $log_data = array(
        'timestamp' => current_time('mysql'),
        'user_id' => $current_user->ID,
        'test_id' => $data['test_id'],
        'test_type' => $data['test_type'],
        'test_phase' => $data['test_phase'],
        'metrics' => array(
            'total_letters' => $data['total_letters'],
            'p_letters' => $data['p_letters'],
            'score' => $data['score'],
            'accuracy' => $data['accuracy'],
            'reaction_time' => $data['reaction_time'],
            'missed_responses' => $data['missed_responses'],
            'false_alarms' => $data['false_alarms']
        ),
        'result' => $result ? 'success' : 'failure'
    );

    // Add debug information
    $log_data['debug'] = array(
        'request_time' => current_time('mysql'),
        'php_version' => PHP_VERSION,
        'wp_version' => get_bloginfo('version')
    );

    $log_file = $log_dir . '/' . date('Y-m') . '_test_results.log';
    $log_entry = json_encode($log_data, JSON_PRETTY_PRINT) . "\n---\n";
    
    if (file_put_contents($log_file, $log_entry, FILE_APPEND) === false) {
        error_log('Failed to write to test results log file: ' . $log_file);
        return false;
    }
    
    return true;
}

// AJAX handler to save test results
function save_test_results_handler() {
    error_log('Starting save_test_results_handler');
    
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'attentrack_test_nonce')) {
        error_log('Nonce verification failed');
        wp_send_json_error(['message' => 'Invalid nonce']);
        return;
    }

    // Get current user
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        error_log('No user logged in');
        wp_send_json_error(['message' => 'User not logged in']);
        return;
    }
    error_log('User ID: ' . $current_user->ID);

    // Get and validate required data
    $required_fields = ['test_id', 'test_phase', 'score', 'accuracy', 'reaction_time', 'missed_responses', 'false_alarms', 'responses'];
    $data = [];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field])) {
            error_log("Missing required field: $field");
            wp_send_json_error(['message' => "Missing required field: $field"]);
            return;
        }
        $data[$field] = $_POST[$field];
    }

    // Get optional fields with defaults
    $data['total_letters'] = isset($_POST['total_letters']) ? $_POST['total_letters'] : 0;
    $data['p_letters'] = isset($_POST['p_letters']) ? $_POST['p_letters'] : 0;

    error_log('Received test data: ' . print_r($data, true));

    // Validate data
    $validation_errors = validate_test_results($data);
    if (!empty($validation_errors)) {
        wp_send_json_error(array(
            'message' => 'Validation failed',
            'errors' => $validation_errors
        ));
        return;
    }

    // Add additional fields for specific test types
    if ($data['test_type'] === 'alternative_attention') {
        $data['switch_time'] = floatval($_POST['switch_time']);
        $data['completion_time'] = floatval($_POST['completion_time']);
        $data['errors'] = intval($_POST['errors']);
    } elseif ($data['test_type'] === 'divided_attention') {
        $data['task1_accuracy'] = floatval($_POST['task1_accuracy']);
        $data['task2_accuracy'] = floatval($_POST['task2_accuracy']);
        $data['dual_task_cost'] = floatval($_POST['dual_task_cost']);
        $data['response_time'] = floatval($_POST['response_time']);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'test_results';

    // Prepare data for insertion
    $insert_data = array_merge($data, array(
        'user_id' => $current_user->ID,
        'test_date' => current_time('mysql')
    ));

    $result = $wpdb->insert($table_name, $insert_data);

    // Log the test results
    log_test_results($data, $result);

    if ($result === false) {
        wp_send_json_error(array(
            'message' => 'Failed to save results',
            'db_error' => $wpdb->last_error
        ));
        return;
    }

    wp_send_json_success(array(
        'message' => 'Results saved successfully',
        'test_id' => $data['test_id']
    ));
}
add_action('wp_ajax_save_test_results', 'save_test_results_handler');
add_action('wp_ajax_nopriv_save_test_results', 'save_test_results_handler');

// Shortcode to display test results in dashboard
function display_test_results_shortcode() {
    if (!is_user_logged_in()) {
        return 'Please log in to view your test results.';
    }

    // Add custom CSS for test results display
    $output = '<style>
        .test-results-container {
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .test-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .phase-section {
            margin: 20px 0;
            padding: 15px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .phase-section h4 {
            color: #34495e;
            margin-bottom: 10px;
        }
        .combined-results {
            margin-top: 30px;
            padding: 20px;
            background: #e9ecef;
            border-radius: 8px;
        }
        .combined-results h4 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 15px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
            background-color: transparent;
        }
        .table th {
            background-color: #e9ecef;
            color: #2c3e50;
            font-weight: 600;
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #dee2e6;
        }
        .table td {
            padding: 12px;
            border-top: 1px solid #dee2e6;
            color: #495057;
        }
        .nav-tabs {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
            border-bottom: 2px solid #dee2e6;
        }
        .nav-tabs li {
            margin-right: 5px;
        }
        .nav-tabs a {
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            color: #495057;
            border-radius: 5px 5px 0 0;
            border: 1px solid transparent;
        }
        .nav-tabs a.active {
            color: #2c3e50;
            background: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
            margin-bottom: -2px;
        }
    </style>';

    global $wpdb;
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    $output .= '<div class="test-results-container">';
    $output .= '<h2>Test History</h2>';
    
    // Create tabs
    $output .= '<ul class="nav-tabs">';
    $output .= '<li><a href="#selective" class="tab-link active" data-test="selective">Selective Attention</a></li>';
    $output .= '<li><a href="#selective_sustained" class="tab-link" data-test="selective_sustained">Selective & Sustained Attention</a></li>';
    $output .= '<li><a href="#alternative" class="tab-link" data-test="alternative">Alternative Attention</a></li>';
    $output .= '<li><a href="#divided" class="tab-link" data-test="divided">Divided Attention</a></li>';
    $output .= '</ul>';

    // Selective Attention Section
    $output .= '<div id="selective" class="test-section" style="display: block;">';
    $output .= '<h3>Selective Attention Test Results</h3>';
    $selective_results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}test_results 
        WHERE user_id = %d AND test_type = 'selective_attention'
        ORDER BY test_date DESC", 
        $user_id
    ));
    if (!empty($selective_results)) {
        $output .= '<div class="table-responsive"><table class="table">';
        $output .= '<thead><tr>';
        $output .= '<th>Date</th>';
        $output .= '<th>Total Letters</th>';
        $output .= '<th>P Letters</th>';
        $output .= '<th>Correct P Responses</th>';
        $output .= '<th>Incorrect Responses</th>';
        $output .= '<th>Reaction Time (s)</th>';
        $output .= '<th>Score</th>';
        $output .= '</tr></thead><tbody>';
        foreach ($selective_results as $result) {
            $responses = json_decode($result->responses, true);
            $correct_p = 0;
            $incorrect = 0;
            foreach ($responses as $response) {
                if ($response['letter'] === 'p' && $response['correct']) $correct_p++;
                if (!$response['correct']) $incorrect++;
            }
            $output .= sprintf(
                '<tr><td>%s</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td><td>%.2f</td><td>%.2f</td></tr>',
                date('Y-m-d H:i', strtotime($result->test_date)),
                $result->total_letters,
                $result->p_letters,
                $correct_p,
                $incorrect,
                $result->reaction_time,
                $result->score
            );
        }
        $output .= '</tbody></table></div>';
    }
    $output .= '</div>';

    // Selective & Sustained Attention Section
    $output .= '<div id="selective_sustained" class="test-section" style="display: none;">';
    $output .= '<h3>Selective & Sustained Attention Test Results</h3>';
    
    // Get all test sessions
    $test_sessions = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT test_id, DATE_FORMAT(test_date, '%Y-%m-%d %H:%i') as test_date 
        FROM {$wpdb->prefix}test_results 
        WHERE user_id = %d AND test_type = 'selective_attention_extended'
        ORDER BY test_date DESC", 
        $user_id
    ));

    if (!empty($test_sessions)) {
        foreach ($test_sessions as $session) {
            $output .= '<div class="test-session">';
            $output .= "<h4>Test Date: {$session->test_date}</h4>";

            // Add charts container
            $output .= '<div class="performance-charts">';
            $output .= '<div class="chart-container"><canvas id="metrics_' . esc_attr($session->test_id) . '"></canvas></div>';
            $output .= '<div class="chart-container"><canvas id="accuracy_' . esc_attr($session->test_id) . '"></canvas></div>';
            $output .= '</div>';

            // Get individual phase results
            $phase_results = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}test_results 
                WHERE user_id = %d AND test_type = 'selective_attention_extended' 
                AND test_id = %s AND test_phase > 0
                ORDER BY test_phase ASC",
                $user_id, $session->test_id
            ));

            // Prepare chart data
            $chart_data = array(
                'phases' => array(),
                'scores' => array(),
                'reaction_times' => array(),
                'accuracy' => array(),
                'missed' => array(),
                'false_alarms' => array()
            );

            foreach ($phase_results as $result) {
                $chart_data['phases'][] = $result->test_phase;
                $chart_data['scores'][] = $result->score;
                $chart_data['reaction_times'][] = $result->reaction_time;
                $chart_data['accuracy'][] = $result->accuracy;
                $chart_data['missed'][] = $result->missed_responses;
                $chart_data['false_alarms'][] = $result->false_alarms;
            }

            // Initialize charts
            $output .= "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Performance Metrics Chart
                    new Chart(document.getElementById('metrics_" . esc_js($session->test_id) . "'), {
                        type: 'line',
                        data: {
                            labels: " . json_encode($chart_data['phases']) . ",
                            datasets: [{
                                label: 'Score',
                                data: " . json_encode($chart_data['scores']) . ",
                                borderColor: '#4CAF50',
                                tension: 0.1
                            }, {
                                label: 'Reaction Time (s)',
                                data: " . json_encode($chart_data['reaction_times']) . ",
                                borderColor: '#2196F3',
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                title: { display: true, text: 'Performance Metrics by Phase' }
                            }
                        }
                    });

                    // Accuracy Analysis Chart
                    new Chart(document.getElementById('accuracy_" . esc_js($session->test_id) . "'), {
                        type: 'bar',
                        data: {
                            labels: " . json_encode($chart_data['phases']) . ",
                            datasets: [{
                                label: 'Accuracy (%)',
                                data: " . json_encode($chart_data['accuracy']) . ",
                                backgroundColor: '#4CAF50'
                            }, {
                                label: 'Missed',
                                data: " . json_encode($chart_data['missed']) . ",
                                backgroundColor: '#FFC107'
                            }, {
                                label: 'False Alarms',
                                data: " . json_encode($chart_data['false_alarms']) . ",
                                backgroundColor: '#F44336'
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                title: { display: true, text: 'Response Accuracy Analysis' }
                            }
                        }
                    });
                });
            </script>";

            // Display individual phase results
            if (!empty($phase_results)) {
                foreach ($phase_results as $result) {
                    $responses = json_decode($result->responses, true);
                    $correct_p = 0;
                    $incorrect = 0;
                    foreach ($responses as $response) {
                        if ($response['letter'] === 'p' && $response['correct']) $correct_p++;
                        if (!$response['correct']) $incorrect++;
                    }

                    $output .= '<div class="phase-section">';
                    $output .= sprintf('<h5>Phase %d</h5>', $result->test_phase);
                    $output .= '<div class="table-responsive"><table class="table">';
                    $output .= '<thead><tr>';
                    $output .= '<th>Total Letters</th>';
                    $output .= '<th>P Letters</th>';
                    $output .= '<th>Correct P Responses</th>';
                    $output .= '<th>Incorrect Responses</th>';
                    $output .= '<th>Reaction Time (s)</th>';
                    $output .= '<th>Score</th>';
                    $output .= '</tr></thead>';
                    $output .= sprintf(
                        '<tbody><tr><td>%d</td><td>%d</td><td>%d</td><td>%d</td><td>%.2f</td><td>%.2f</td></tr></tbody>',
                        $result->total_letters,
                        $result->p_letters,
                        $correct_p,
                        $incorrect,
                        $result->reaction_time,
                        $result->score
                    );
                    $output .= '</table></div>';
                    $output .= '</div>';
                }
            }

            // Get combined results
            $combined_result = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}test_results 
                WHERE user_id = %d AND test_type = 'selective_attention_extended' 
                AND test_id = %s AND test_phase = 0",
                $user_id, $session->test_id
            ));

            if ($combined_result) {
                $output .= '<div class="combined-results">';
                $output .= '<h4>Combined Results</h4>';
                $output .= '<div class="table-responsive"><table class="table">';
                $output .= '<thead><tr>';
                $output .= '<th>Total Letters</th>';
                $output .= '<th>Total P Letters</th>';
                $output .= '<th>Overall Accuracy</th>';
                $output .= '<th>Avg Reaction Time</th>';
                $output .= '<th>Total Score</th>';
                $output .= '<th>Improvement</th>';
                $output .= '</tr></thead>';
                $output .= sprintf(
                    '<tbody><tr><td>%d</td><td>%d</td><td>%.2f%%</td><td>%.2fs</td><td>%.2f</td><td>%.2f%%</td></tr></tbody>',
                    $combined_result->total_letters,
                    $combined_result->p_letters,
                    $combined_result->accuracy,
                    $combined_result->reaction_time,
                    $combined_result->score,
                    $combined_result->improvement
                );
                $output .= '</table></div>';
                $output .= '</div>';
            }

            // Add trend analysis
            $trends = array(
                'score' => calculate_trend($chart_data['scores']),
                'reaction' => calculate_trend($chart_data['reaction_times']),
                'accuracy' => calculate_trend($chart_data['accuracy'])
            );

            $output .= '<div class="trend-analysis">';
            $output .= '<h5>Performance Trends</h5>';
            $output .= '<div class="trend-grid">';
            $output .= sprintf(
                '<div class="trend-item %s"><strong>Score:</strong> %s (%.1f%%)</div>',
                strtolower($trends['score']['direction']),
                $trends['score']['direction'],
                $trends['score']['percentage']
            );
            $output .= sprintf(
                '<div class="trend-item %s"><strong>Reaction Time:</strong> %s (%.1f%%)</div>',
                strtolower($trends['reaction']['direction']),
                $trends['reaction']['direction'],
                $trends['reaction']['percentage']
            );
            $output .= sprintf(
                '<div class="trend-item %s"><strong>Accuracy:</strong> %s (%.1f%%)</div>',
                strtolower($trends['accuracy']['direction']),
                $trends['accuracy']['direction'],
                $trends['accuracy']['percentage']
            );
            $output .= '</div></div>';

            // Add detailed phase statistics
            foreach ($phase_results as $result) {
                $responses = json_decode($result->responses, true);
                $response_times = array_map(function($r) { 
                    return $r['reactionTime']; 
                }, $responses);

                $output .= sprintf('<div class="phase-details" id="phase_%d">', $result->test_phase);
                $output .= sprintf('<h5>Phase %d Statistics</h5>', $result->test_phase);
                $output .= '<div class="stats-grid">';
                
                // Response Times
                $output .= '<div class="stat-box">';
                $output .= '<h6>Response Times</h6>';
                $output .= sprintf('<p>Min: %.3fs</p>', min($response_times) / 1000);
                $output .= sprintf('<p>Max: %.3fs</p>', max($response_times) / 1000);
                $output .= sprintf('<p>Avg: %.3fs</p>', array_sum($response_times) / count($response_times) / 1000);
                $output .= '</div>';

                // Accuracy
                $output .= '<div class="stat-box">';
                $output .= '<h6>Accuracy</h6>';
                $output .= sprintf('<p>Correct: %.1f%%</p>', $result->accuracy);
                $output .= sprintf('<p>Missed: %d</p>', $result->missed_responses);
                $output .= sprintf('<p>False Alarms: %d</p>', $result->false_alarms);
                $output .= '</div>';

                // Performance
                $output .= '<div class="stat-box">';
                $output .= '<h6>Performance</h6>';
                $output .= sprintf('<p>Score: %.2f</p>', $result->score);
                $output .= sprintf('<p>Consistency: %.1f%%</p>', 
                    100 - (stats_standard_deviation(array_column($responses, 'correct')) * 100)
                );
                $output .= '</div>';

                $output .= '</div></div>';
            }
        }
        $output .= '</div>'; // End test-session
    }
    $output .= '</div>';

    // Alternative Attention Section
    $output .= '<div id="alternative" class="test-section" style="display: none;">';
    $output .= '<h3>Alternative Attention Test Results</h3>';
    $alternative_results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}test_results 
        WHERE user_id = %d AND test_type = 'alternative_attention'
        ORDER BY test_date DESC", 
        $user_id
    ));
    if (!empty($alternative_results)) {
        $output .= '<div class="table-responsive"><table class="table">';
        $output .= '<thead><tr>';
        $output .= '<th>Date</th>';
        $output .= '<th>Letters Shown</th>';
        $output .= '<th>Correct Responses</th>';
        $output .= '<th>Incorrect Responses</th>';
        $output .= '<th>Reaction Time (s)</th>';
        $output .= '</tr></thead><tbody>';
        foreach ($alternative_results as $result) {
            $responses = json_decode($result->responses, true);
            $correct = array_sum(array_column($responses, 'correct'));
            $incorrect = count($responses) - $correct;
            $output .= sprintf(
                '<tr><td>%s</td><td>%d</td><td>%d</td><td>%d</td><td>%.2f</td></tr>',
                date('Y-m-d H:i', strtotime($result->test_date)),
                count($responses),
                $correct,
                $incorrect,
                $result->reaction_time
            );
        }
        $output .= '</tbody></table></div>';
    }
    $output .= '</div>';

    // Divided Attention Section
    $output .= '<div id="divided" class="test-section" style="display: none;">';
    $output .= '<h3>Divided Attention Test Results</h3>';
    $divided_results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}test_results 
        WHERE user_id = %d AND test_type = 'divided_attention'
        ORDER BY test_date DESC", 
        $user_id
    ));
    if (!empty($divided_results)) {
        $output .= '<div class="table-responsive"><table class="table">';
        $output .= '<thead><tr>';
        $output .= '<th>Date</th>';
        $output .= '<th>Color</th>';
        $output .= '<th>Times Shown</th>';
        $output .= '<th>Correct Responses</th>';
        $output .= '<th>Reaction Time</th>';
        $output .= '</tr></thead><tbody>';
        foreach ($divided_results as $result) {
            $responses = json_decode($result->responses, true);
            $colors = array();
            foreach ($responses as $response) {
                if (!isset($colors[$response['color']])) {
                    $colors[$response['color']] = array(
                        'shown' => 0,
                        'correct' => 0
                    );
                }
                $colors[$response['color']]['shown']++;
                if ($response['correct']) {
                    $colors[$response['color']]['correct']++;
                }
            }
            foreach ($colors as $color => $stats) {
                $output .= sprintf(
                    '<tr><td>%s</td><td>%s</td><td>%d</td><td>%d</td><td>%.2f</td></tr>',
                    date('Y-m-d H:i', strtotime($result->test_date)),
                    ucfirst($color),
                    $stats['shown'],
                    $stats['correct'],
                    $result->reaction_time
                );
            }
        }
        $output .= '</tbody></table></div>';
    }
    $output .= '</div>';

    $output .= '</div>';
    
    // Add JavaScript for tab switching
    $output .= '<script>
        jQuery(document).ready(function($) {
            $(".tab-link").click(function(e) {
                e.preventDefault();
                var testId = $(this).data("test");
                
                // Update active tab
                $(".tab-link").removeClass("active");
                $(this).addClass("active");
                
                // Show selected test section
                $(".test-section").hide();
                $("#" + testId).show();
            });
        });
    </script>';

    return $output;
}
add_shortcode('test_results', 'display_test_results_shortcode');

// Add admin menu for database management
function attentrack_admin_menu() {
    add_menu_page(
        'Verify Tests',
        'Verify Tests',
        'manage_options',
        'selective-attention-test-verify',
        'verify_selective_attention_test_page',
        'dashicons-clipboard',
        30
    );
}
add_action('admin_menu', 'attentrack_admin_menu');

// Admin page callback
function attentrack_db_page() {
    global $wpdb;
    $test_results_table = $wpdb->prefix . 'test_results';
    $test_sessions_table = $wpdb->prefix . 'test_sessions';
    
    // Create tables if requested
    if (isset($_POST['create_tables']) && check_admin_referer('attentrack_create_tables')) {
        attentrack_create_tables();
        echo '<div class="notice notice-success"><p>Tables have been created/updated successfully!</p></div>';
    }
    
    // Check if tables exist
    $test_results_exists = $wpdb->get_var("SHOW TABLES LIKE '$test_results_table'") === $test_results_table;
    $test_sessions_exists = $wpdb->get_var("SHOW TABLES LIKE '$test_sessions_table'") === $test_sessions_table;
    
    ?>
    <div class="wrap">
        <h1>AttenTrack Database Management</h1>
        
        <div class="card">
            <h2>Database Tables Status</h2>
            <p>Test Results Table: <strong><?php echo $test_results_exists ? 'Exists' : 'Missing'; ?></strong></p>
            <p>Test Sessions Table: <strong><?php echo $test_sessions_exists ? 'Exists' : 'Missing'; ?></strong></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('attentrack_create_tables'); ?>
                <p class="submit">
                    <input type="submit" name="create_tables" class="button button-primary" value="Create/Update Tables">
                </p>
            </form>
        </div>
        
        <?php if ($test_results_exists): ?>
        <div class="card">
            <h2>Test Results Table Structure</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Default</th>
                        <th>Extra</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $results = $wpdb->get_results("DESCRIBE $test_results_table");
                    foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo esc_html($row->Field); ?></td>
                        <td><?php echo esc_html($row->Type); ?></td>
                        <td><?php echo esc_html($row->Null); ?></td>
                        <td><?php echo esc_html($row->Key); ?></td>
                        <td><?php echo esc_html($row->Default); ?></td>
                        <td><?php echo esc_html($row->Extra); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if ($test_sessions_exists): ?>
        <div class="card">
            <h2>Test Sessions Table Structure</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Default</th>
                        <th>Extra</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $results = $wpdb->get_results("DESCRIBE $test_sessions_table");
                    foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo esc_html($row->Field); ?></td>
                        <td><?php echo esc_html($row->Type); ?></td>
                        <td><?php echo esc_html($row->Null); ?></td>
                        <td><?php echo esc_html($row->Key); ?></td>
                        <td><?php echo esc_html($row->Default); ?></td>
                        <td><?php echo esc_html($row->Extra); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

// Add admin styles
function attentrack_admin_styles() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'toplevel_page_selective-attention-test-verify') {
        ?>
        <style>
            .wrap {
                max-width: 1200px;
                margin: 20px auto;
            }
            .card {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .card h2 {
                margin-top: 0;
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
            }
            .widefat {
                margin-top: 15px;
                border-collapse: collapse;
                width: 100%;
            }
            .widefat th {
                background: #f8f9fa;
                border-bottom: 1px solid #ddd;
                padding: 10px;
                text-align: left;
            }
            .widefat td {
                padding: 10px;
                border-bottom: 1px solid #eee;
            }
            .widefat tr:hover td {
                background: #f8f9fa;
            }
            .notice {
                margin: 20px 0;
            }
            .button-primary {
                background: #2271b1;
                border-color: #2271b1;
                color: #fff;
                padding: 5px 15px;
                height: auto;
                line-height: 2;
            }
            .button-primary:hover {
                background: #135e96;
                border-color: #135e96;
            }
        </style>
        <?php
    }
}
add_action('admin_head', 'attentrack_admin_styles');

// Add submenu for viewing test results
function attentrack_admin_submenu() {
    add_submenu_page(
        'selective-attention-test-verify',
        'View Test Results',
        'View Results',
        'manage_options',
        'attentrack-results',
        'attentrack_results_page'
    );
}
add_action('admin_menu', 'attentrack_admin_submenu');

// Results page callback
function attentrack_results_page() {
    global $wpdb;
    $test_results_table = $wpdb->prefix . 'test_results';
    $test_sessions_table = $wpdb->prefix . 'test_sessions';
    
    // Get filter conditions
    list($where_clause, $values) = attentrack_results_filters();
    
    // Build the query
    $query = $wpdb->prepare("
        SELECT r.*, u.display_name, s.completion_status
        FROM $test_results_table r
        LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
        LEFT JOIN $test_sessions_table s ON r.user_id = s.user_id 
            AND r.test_date BETWEEN s.start_time AND DATE_ADD(s.start_time, INTERVAL 1 DAY)
        $where_clause
        ORDER BY r.test_date DESC
        LIMIT 50
    ", $values);
    
    $results = $wpdb->get_results($query);
    
    ?>
    <div class="wrap">
        <h1>Test Results Overview</h1>
        
        <div class="card">
            <h2>Recent Test Results</h2>
            <?php if ($results): ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Test Phase</th>
                        <th>Score</th>
                        <th>Accuracy</th>
                        <th>Reaction Time</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo esc_html($row->display_name); ?></td>
                        <td><?php echo esc_html($row->test_phase); ?></td>
                        <td><?php echo esc_html(number_format($row->score, 2)); ?></td>
                        <td><?php echo esc_html(number_format($row->accuracy, 1)) . '%'; ?></td>
                        <td><?php echo esc_html(number_format($row->reaction_time, 3)) . 's'; ?></td>
                        <td><?php echo esc_html(date('Y-m-d H:i', strtotime($row->test_date))); ?></td>
                        <td><?php echo esc_html($row->completion_status ?: 'In Progress'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No test results found matching the selected filters.</p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Statistics</h2>
            <?php
            // Update statistics query to use filters
            $stats_query = $wpdb->prepare("
                SELECT 
                    COUNT(DISTINCT r.user_id) as total_users,
                    COUNT(*) as total_tests,
                    AVG(r.accuracy) as avg_accuracy,
                    AVG(r.reaction_time) as avg_reaction_time
                FROM $test_results_table r
                $where_clause
            ", $values);
            
            $stats = $wpdb->get_row($stats_query);
            ?>
            <table class="widefat">
                <tr>
                    <th>Total Users</th>
                    <td><?php echo esc_html($stats->total_users); ?></td>
                </tr>
                <tr>
                    <th>Total Tests Taken</th>
                    <td><?php echo esc_html($stats->total_tests); ?></td>
                </tr>
                <tr>
                    <th>Average Accuracy</th>
                    <td><?php echo esc_html(number_format($stats->avg_accuracy, 1)) . '%'; ?></td>
                </tr>
                <tr>
                    <th>Average Reaction Time</th>
                    <td><?php echo esc_html(number_format($stats->avg_reaction_time, 3)) . 's'; ?></td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}

// Add filtering to results page
function attentrack_results_filters() {
    global $wpdb;
    $test_results_table = $wpdb->prefix . 'test_results';
    
    // Get filter values
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    $phase = isset($_GET['phase']) ? intval($_GET['phase']) : 0;
    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
    
    // Build WHERE clause
    $where = array();
    $values = array();
    
    if ($user_id) {
        $where[] = 'r.user_id = %d';
        $values[] = $user_id;
    }
    
    if ($phase) {
        $where[] = 'r.test_phase = %d';
        $values[] = $phase;
    }
    
    if ($date_from) {
        $where[] = 'r.test_date >= %s';
        $values[] = $date_from . ' 00:00:00';
    }
    
    if ($date_to) {
        $where[] = 'r.test_date <= %s';
        $values[] = $date_to . ' 23:59:59';
    }
    
    $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get users who have taken tests
    $users = $wpdb->get_results("
        SELECT DISTINCT u.ID, u.display_name 
        FROM $test_results_table r
        JOIN {$wpdb->users} u ON r.user_id = u.ID
        ORDER BY u.display_name
    ");
    
    // Get unique test phases
    $phases = $wpdb->get_col("SELECT DISTINCT test_phase FROM $test_results_table ORDER BY test_phase");
    
    ?>
    <div class="card">
        <h2>Filter Results</h2>
        <form method="get" action="">
            <input type="hidden" name="page" value="attentrack-results">
            <table class="form-table">
                <tr>
                    <th><label for="user_id">User</label></th>
                    <td>
                        <select name="user_id" id="user_id">
                            <option value="">All Users</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?php echo esc_attr($u->ID); ?>" <?php selected($user_id, $u->ID); ?>>
                                    <?php echo esc_html($u->display_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="phase">Test Phase</label></th>
                    <td>
                        <select name="phase" id="phase">
                            <option value="">All Phases</option>
                            <?php foreach ($phases as $p): ?>
                                <option value="<?php echo esc_attr($p); ?>" <?php selected($phase, $p); ?>>
                                    Phase <?php echo esc_html($p); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="date_from">Date Range</label></th>
                    <td>
                        <input type="date" name="date_from" id="date_from" value="<?php echo esc_attr($date_from); ?>">
                        to
                        <input type="date" name="date_to" id="date_to" value="<?php echo esc_attr($date_to); ?>">
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" class="button button-primary" value="Apply Filters">
                <a href="?page=attentrack-results" class="button">Reset</a>
            </p>
        </form>
    </div>
    <?php
    
    // Return the WHERE clause and values for the main query
    return array($where_clause, $values);
}

// Handle CSV export of test results
function attentrack_export_results() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['export_results']) && check_admin_referer('attentrack_export_results')) {
        global $wpdb;
        $test_results_table = $wpdb->prefix . 'test_results';
        $test_sessions_table = $wpdb->prefix . 'test_sessions';

        // Get filter conditions
        list($where_clause, $values) = attentrack_results_filters();

        // Get all results with user info using current filters
        $query = $wpdb->prepare("
            SELECT 
                r.*,
                u.display_name,
                s.completion_status,
                s.start_time as session_start
            FROM $test_results_table r
            LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
            LEFT JOIN $test_sessions_table s ON r.user_id = s.user_id 
                AND r.test_date BETWEEN s.start_time AND DATE_ADD(s.start_time, INTERVAL 1 DAY)
            $where_clause
            ORDER BY r.test_date DESC
        ", $values);

        $results = $wpdb->get_results($query);

        if ($results) {
            $filename = 'attentrack_results_' . date('Y-m-d_H-i') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($output, array(
                'User',
                'Test Phase',
                'Score',
                'Accuracy (%)',
                'Reaction Time',
                'Missed Responses',
                'False Alarms',
                'Test Date',
                'Session Start',
                'Status'
            ));
            
            // Add data rows
            foreach ($results as $row) {
                fputcsv($output, array(
                    $row->display_name,
                    $row->test_phase,
                    number_format($row->score, 2),
                    number_format($row->accuracy, 1),
                    number_format($row->reaction_time, 3),
                    $row->missed_responses,
                    $row->false_alarms,
                    $row->test_date,
                    $row->session_start,
                    $row->completion_status ?: 'In Progress'
                ));
            }
            
            fclose($output);
            exit();
        }
    }
}
add_action('admin_init', 'attentrack_export_results');

// Add export button to results page
function attentrack_add_export_button($content) {
    if (get_current_screen()->id === 'toplevel_page_selective-attention-test-verify') {
        ?>
        <div class="card" style="margin-top: 20px;">
            <h2>Export Data</h2>
            <p>Download test results as a CSV file. The export will respect your current filter settings.</p>
            <form method="post" action="">
                <?php wp_nonce_field('attentrack_export_results'); ?>
                <p class="submit">
                    <input type="submit" name="export_results" class="button button-primary" value="Export to CSV">
                </p>
            </form>
        </div>
        <?php
    }
}
add_action('in_admin_footer', 'attentrack_add_export_button');

// Add AJAX endpoint for chart data
function attentrack_get_chart_data() {
    check_ajax_referer('attentrack_chart_data', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
        return;
    }

    global $wpdb;
    $test_results_table = $wpdb->prefix . 'test_results';
    
    try {
        // Get last 30 days of data
        $chart_data = $wpdb->get_results($wpdb->prepare("
            SELECT 
                DATE(test_date) as date,
                AVG(accuracy) as avg_accuracy,
                AVG(reaction_time) as avg_reaction_time,
                COUNT(*) as test_count
            FROM $test_results_table
            WHERE test_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(test_date)
            ORDER BY date ASC
        "));
        
        wp_send_json_success($chart_data);
    } catch (Exception $e) {
        wp_send_json_error('Failed to fetch chart data');
    }
}
add_action('wp_ajax_attentrack_get_chart_data', 'attentrack_get_chart_data');

// Add performance trends section
function attentrack_add_performance_trends() {
    if (get_current_screen()->id === 'attentrack-db_page_attentrack-results') {
        ?>
        <div class="card">
            <h2>Performance Trends (Last 30 Days)</h2>
            <div class="chart-container" style="position: relative;">
                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <div style="flex: 1; min-height: 300px;">
                        <canvas id="accuracyChart"></canvas>
                    </div>
                    <div style="flex: 1; min-height: 300px;">
                        <canvas id="reactionTimeChart"></canvas>
                    </div>
                </div>
                <div style="min-height: 200px;">
                    <canvas id="testCountChart"></canvas>
                </div>
            </div>
            <div id="chartError" class="notice notice-error" style="display: none;">
                <p>Failed to load chart data. Please try refreshing the page.</p>
            </div>
        </div>
        <?php
        
        // Add nonce for AJAX
        wp_nonce_field('attentrack_chart_data', 'attentrack_chart_nonce');
    }
}
add_action('in_admin_footer', 'attentrack_add_performance_trends', 9);

// Add user comparison section
function attentrack_add_user_comparison() {
    if (!current_user_can('manage_options') || get_current_screen()->id !== 'attentrack-db_page_attentrack-results') {
        return;
    }

    global $wpdb;
    $test_results_table = $wpdb->prefix . 'test_results';
    
    try {
        // Get top performers
        $users = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT 
                u.ID, 
                u.display_name,
                COUNT(*) as total_tests,
                AVG(r.accuracy) as avg_accuracy,
                AVG(r.reaction_time) as avg_reaction_time,
                MAX(r.test_date) as last_test
            FROM {$wpdb->users} u
            JOIN $test_results_table r ON u.ID = r.user_id
            GROUP BY u.ID, u.display_name
            HAVING COUNT(*) >= 5
            ORDER BY avg_accuracy DESC
            LIMIT 10
        "));

        // Get performance distribution
        $distribution = $wpdb->get_results($wpdb->prepare("
            SELECT 
                CASE 
                    WHEN accuracy >= 90 THEN 'Excellent (90-100%%)'
                    WHEN accuracy >= 80 THEN 'Good (80-89%%)'
                    WHEN accuracy >= 70 THEN 'Average (70-79%%)'
                    ELSE 'Needs Improvement (<70%%)'
                END as performance_level,
                COUNT(*) as count,
                AVG(accuracy) as avg_accuracy
            FROM $test_results_table
            GROUP BY 
                CASE 
                    WHEN accuracy >= 90 THEN 'Excellent (90-100%%)'
                    WHEN accuracy >= 80 THEN 'Good (80-89%%)'
                    WHEN accuracy >= 70 THEN 'Average (70-79%%)'
                    ELSE 'Needs Improvement (<70%%)'
                END
            ORDER BY avg_accuracy DESC
        "));

        ?>
        <div class="card">
            <h2>Top Performers</h2>
            <p class="description">Users who have completed at least 5 tests, ranked by average accuracy.</p>
            
            <?php if ($users): ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>User</th>
                            <th>Tests Taken</th>
                            <th>Avg. Accuracy</th>
                            <th>Avg. Reaction Time</th>
                            <th>Last Test</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $user): ?>
                            <tr>
                                <td><?php echo esc_html($index + 1); ?></td>
                                <td><?php echo esc_html($user->display_name); ?></td>
                                <td><?php echo esc_html($user->total_tests); ?></td>
                                <td><?php echo esc_html(number_format($user->avg_accuracy, 1)) . '%'; ?></td>
                                <td><?php echo esc_html(number_format($user->avg_reaction_time, 3)) . 's'; ?></td>
                                <td><?php echo esc_html(human_time_diff(strtotime($user->last_test))) . ' ago'; ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['page' => 'attentrack-results', 'user_id' => $user->ID])); ?>" 
                                       class="button button-small">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users have completed enough tests yet.</p>
            <?php endif; ?>
        </div>

        <?php if ($distribution): ?>
        <div class="card">
            <h2>Performance Distribution</h2>
            <div style="display: flex; align-items: flex-start; gap: 20px;">
                <div style="flex: 1;">
                    <canvas id="distributionChart" style="max-height: 300px;"></canvas>
                </div>
                <div style="flex: 1;">
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>Performance Level</th>
                                <th>Count</th>
                                <th>Average Accuracy</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($distribution as $level): ?>
                                <tr>
                                    <td><?php echo esc_html($level->performance_level); ?></td>
                                    <td><?php echo esc_html($level->count); ?></td>
                                    <td><?php echo esc_html(number_format($level->avg_accuracy, 1)) . '%'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <script>
                jQuery(document).ready(function($) {
                    const distributionData = <?php echo json_encode($distribution); ?>;
                    new Chart(document.getElementById('distributionChart'), {
                        type: 'pie',
                        data: {
                            labels: distributionData.map(item => item.performance_level),
                            datasets: [{
                                data: distributionData.map(item => item.count),
                                backgroundColor: [
                                    '#4caf50',  // Excellent
                                    '#2196f3',  // Good
                                    '#ff9800',  // Average
                                    '#f44336'   // Needs Improvement
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Test Results Distribution'
                                },
                                legend: {
                                    position: 'right'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const item = distributionData[context.dataIndex];
                                            const percentage = ((item.count / distributionData.reduce((sum, d) => sum + parseInt(d.count), 0)) * 100).toFixed(1);
                                            return `${item.performance_level}: ${item.count} tests (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        </div>
        <?php endif; ?>
        <?php
    } catch (Exception $e) {
        ?>
        <div class="notice notice-error">
            <p>Error loading performance data. Please try refreshing the page.</p>
        </div>
        <?php
    }
}
add_action('in_admin_footer', 'attentrack_add_user_comparison', 8);

// Add Test Phase 0 Verification page to admin menu
function add_test_phase_0_verification_page() {
    add_submenu_page(
        'selective-attention-test-verify',
        'Test Phase 0 Verification',
        'Phase 0 Verification',
        'manage_options',
        'test-phase-0-verify',
        'display_test_phase_0_verification'
    );
}
add_action('admin_menu', 'add_test_phase_0_verification_page');

// Display the verification page
function display_test_phase_0_verification() {
    require_once get_template_directory() . '/admin/verify-test-phase-0.php';
}

// Add Database Test page to admin menu
function add_database_test_page() {
    add_submenu_page(
        'selective-attention-test-verify',
        'Database Test',
        'Database Test',
        'manage_options',
        'attentrack-db-test',
        'display_database_test_page'
    );
}
add_action('admin_menu', 'add_database_test_page');

// Display the database test page
function display_database_test_page() {
    include_once get_template_directory() . '/admin/test-database.php';
}

// Add a function to manually check and update database
function check_and_update_database() {
    global $wpdb;
    
    // Check if table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}test_results'");
    
    if (!$table_exists) {
        wp_die('Test results table does not exist. Please activate/deactivate the theme to create it.');
        return;
    }
    
    // Check if columns exist
    $columns = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}test_results");
    $column_names = array_map(function($col) { return $col->Field; }, $columns);
    
    $updates_needed = false;
    
    if (!in_array('total_letters', $column_names)) {
        $wpdb->query("ALTER TABLE {$wpdb->prefix}test_results ADD COLUMN total_letters int(11) NOT NULL DEFAULT 0");
        $updates_needed = true;
    }
    
    if (!in_array('p_letters', $column_names)) {
        $wpdb->query("ALTER TABLE {$wpdb->prefix}test_results ADD COLUMN p_letters int(11) NOT NULL DEFAULT 0");
        $updates_needed = true;
    }
    
    if ($updates_needed) {
        echo '<div class="notice notice-success"><p>Database structure has been updated successfully!</p></div>';
    } else {
        echo '<div class="notice notice-info"><p>Database structure is up to date.</p></div>';
    }
}

// Add admin menu for database management
function add_database_management_menu() {
    add_menu_page(
        'Database Management',
        'Database Management',
        'manage_options',
        'database-management',
        'display_database_management_page',
        'dashicons-database',
        30
    );
}
add_action('admin_menu', 'add_database_management_menu');

// Display the database management page
function display_database_management_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
    
    if (isset($_POST['check_db'])) {
        check_and_update_database();
    }
    
    ?>
    <div class="wrap">
        <h1>Database Management</h1>
        <form method="post" action="">
            <?php wp_nonce_field('check_db_nonce', 'check_db_nonce'); ?>
            <p>Click the button below to check and update the database structure:</p>
            <input type="submit" name="check_db" class="button button-primary" value="Check and Update Database">
        </form>
    </div>
    <?php
}

// AJAX handler for getting unique test ID
function get_unique_test_id_handler() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'attentrack_test_nonce')) {
        wp_send_json_error(array('message' => 'Invalid security token'));
        return;
    }

    // Get current user
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        wp_send_json_error(array('message' => 'User not logged in'));
        return;
    }

    // Generate unique test ID
    $test_id = uniqid('test_' . $current_user->ID . '_', true);
    
    // Store test ID in user meta
    update_user_meta($current_user->ID, 'current_test_id', $test_id);
    
    wp_send_json_success(array('test_id' => $test_id));
}
add_action('wp_ajax_get_unique_test_id', 'get_unique_test_id_handler');
add_action('wp_ajax_nopriv_get_unique_test_id', 'get_unique_test_id_handler');

// AJAX handler to create a test session
function create_test_session_handler() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'create_test_session')) {
        wp_send_json_error(array('message' => 'Invalid security token'));
    }

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Please log in to take the test'));
        return;
    }

    global $wpdb;
    $test_sessions_table = $wpdb->prefix . 'test_sessions';
    
    $test_id = sanitize_text_field($_POST['test_id']);
    $user_id = get_current_user_id();
    
    $session_data = array(
        'test_id' => $test_id,
        'user_id' => $user_id,
        'start_time' => current_time('mysql'),
        'status' => 'started'
    );
    
    $result = $wpdb->insert($test_sessions_table, $session_data);
    
    if ($result === false) {
        wp_send_json_error(array('message' => 'Failed to create test session'));
    }
    
    wp_send_json_success(array('session_id' => $wpdb->insert_id, 'test_id' => $test_id));
}
add_action('wp_ajax_create_test_session', 'create_test_session_handler');

// AJAX handler to complete a test session
function complete_test_session_handler() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'complete_test_session')) {
        wp_send_json_error(array('message' => 'Invalid security token'));
    }

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Please log in to complete the test'));
        return;
    }

    global $wpdb;
    $test_sessions_table = $wpdb->prefix . 'test_sessions';
    
    $session_id = intval($_POST['session_id']);
    
    // Verify the session belongs to the current user
    $session = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $test_sessions_table WHERE id = %d AND user_id = %d",
        $session_id,
        get_current_user_id()
    ));

    if (!$session) {
        wp_send_json_error(array('message' => 'Invalid test session'));
        return;
    }
    
    $result = $wpdb->update(
        $test_sessions_table,
        array('status' => 'completed'),
        array('id' => $session_id)
    );
    
    if ($result === false) {
        wp_send_json_error(array('message' => 'Failed to update test session'));
    }
    
    wp_send_json_success(array('message' => 'Test session completed successfully'));
}
add_action('wp_ajax_complete_test_session', 'complete_test_session_handler');

// AJAX handler for getting unique test ID
function get_unique_test_id() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'attentrack_test_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Get current user
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        wp_send_json_error('User not logged in');
        return;
    }

    // Generate unique test ID
    $test_id = uniqid('test_' . $current_user->ID . '_', true);
    
    // Store test ID in user meta
    update_user_meta($current_user->ID, 'current_test_id', $test_id);
    
    wp_send_json_success(array('test_id' => $test_id));
}
add_action('wp_ajax_get_unique_test_id', 'get_unique_test_id');

// AJAX handler for saving test results
function save_test_results() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'attentrack_test_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Get current user
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        wp_send_json_error('User not logged in');
        return;
    }

    // Validate test ID
    $test_id = isset($_POST['test_id']) ? sanitize_text_field($_POST['test_id']) : '';
    $stored_test_id = get_user_meta($current_user->ID, 'current_test_id', true);
    
    if (empty($test_id) || $test_id !== $stored_test_id) {
        wp_send_json_error('Invalid test ID');
        return;
    }

    // Save test results
    $results = isset($_POST['results']) ? $_POST['results'] : array();
    $test_type = isset($_POST['test_type']) ? sanitize_text_field($_POST['test_type']) : '';
    
    // Store results in user meta
    $all_results = get_user_meta($current_user->ID, 'test_results', true);
    if (!is_array($all_results)) {
        $all_results = array();
    }
    
    $all_results[$test_id] = array(
        'type' => $test_type,
        'data' => $results,
        'timestamp' => current_time('mysql')
    );
    
    update_user_meta($current_user->ID, 'test_results', $all_results);
    
    wp_send_json_success(array('message' => 'Results saved successfully'));
}
add_action('wp_ajax_save_test_results', 'save_test_results');

// Add function to delete test page
function delete_test_page() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'delete_page_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Get page by title
    $page = get_page_by_title('Selective and Sustained Attention Test Part 1', OBJECT, 'page');
    
    if ($page) {
        $result = wp_delete_post($page->ID, true);
        if ($result) {
            wp_send_json_success('Page deleted successfully');
        } else {
            wp_send_json_error('Failed to delete page');
        }
    } else {
        wp_send_json_error('Page not found');
    }
}
add_action('wp_ajax_delete_test_page', 'delete_test_page');

// Function to force delete test pages
function force_delete_test_pages() {
    global $wpdb;
    
    // Get all pages with this title (including duplicates)
    $pages = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT ID, post_title FROM {$wpdb->posts} 
            WHERE post_title LIKE %s 
            AND post_type = 'page'",
            'Selective and Sustained Attention Test Part 1'
        )
    );

    if ($pages) {
        foreach ($pages as $page) {
            // Delete all post meta
            $wpdb->delete($wpdb->postmeta, array('post_id' => $page->ID));
            
            // Delete the post/page
            $wpdb->delete($wpdb->posts, array('ID' => $page->ID));
            
            // Clean up any orphaned meta
            $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT id FROM {$wpdb->posts})");
        }
        return true;
    }
    return false;
}

// Add admin action to delete test pages
function delete_test_pages_init() {
    if (current_user_can('manage_options')) {
        force_delete_test_pages();
    }
}
add_action('admin_init', 'delete_test_pages_init');

// Helper function for trend calculation
function calculate_trend($data) {
    if (count($data) < 2) return array('direction' => 'N/A', 'percentage' => 0);
    $first = $data[0];
    $last = end($data);
    $change = (($last - $first) / $first) * 100;
    $direction = $change > 0 ? 'Improving' : ($change < 0 ? 'Declining' : 'Stable');
    return array('direction' => $direction, 'percentage' => abs($change));
}

// Helper function for standard deviation
function stats_standard_deviation($array) {
    $n = count($array);
    if ($n === 0) return 0;
    $mean = array_sum($array) / $n;
    $deviation = 0.0;
    foreach ($array as $x) {
        $deviation += pow($x - $mean, 2);
    }
    return sqrt($deviation / $n);
}

// Add AJAX handler for CSV export
add_action('wp_ajax_export_test_results', 'export_test_results_handler');
function export_test_results_handler() {
    check_ajax_referer('attentrack_test_nonce', 'nonce');
    
    $test_id = sanitize_text_field($_POST['test_id']);
    $user_id = get_current_user_id();
    
    global $wpdb;
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}test_results 
        WHERE user_id = %d AND test_id = %s 
        ORDER BY test_phase ASC",
        $user_id, $test_id
    ));
    
    $filename = "test_results_{$test_id}_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, array(
        'Phase', 'Score', 'Accuracy (%)', 'Reaction Time (s)',
        'Total Letters', 'P Letters', 'Missed Responses',
        'False Alarms', 'Consistency (%)'
    ));
    
    foreach ($results as $result) {
        $responses = json_decode($result->responses, true);
        $consistency = 100 - (stats_standard_deviation(array_column($responses, 'correct')) * 100);
        
        fputcsv($output, array(
            $result->test_phase,
            $result->score,
            $result->accuracy,
            $result->reaction_time,
            $result->total_letters,
            $result->p_letters,
            $result->missed_responses,
            $result->false_alarms,
            round($consistency, 1)
        ));
    }
    
    fclose($output);
    wp_die();
}

// Function to analyze error patterns
function analyze_error_patterns($responses) {
    $patterns = array(
        'consecutive_errors' => 0,
        'max_consecutive_errors' => 0,
        'error_clusters' => 0,
        'fatigue_errors' => 0,
        'early_errors' => 0,
        'late_errors' => 0
    );
    
    $consecutive = 0;
    $total_responses = count($responses);
    $third = floor($total_responses / 3);
    
    foreach ($responses as $i => $response) {
        // Track consecutive errors
        if (!$response['correct']) {
            $consecutive++;
            if ($consecutive > $patterns['max_consecutive_errors']) {
                $patterns['max_consecutive_errors'] = $consecutive;
            }
            
            // Check position of error
            if ($i < $third) {
                $patterns['early_errors']++;
            } elseif ($i >= ($total_responses - $third)) {
                $patterns['late_errors']++;
            }
            
            // Check for error clusters (3+ errors within 5 responses)
            if ($i >= 4) {
                $error_count = 0;
                for ($j = 0; $j < 5; $j++) {
                    if (!$responses[$i - $j]['correct']) {
                        $error_count++;
                    }
                }
                if ($error_count >= 3) {
                    $patterns['error_clusters']++;
                }
            }
        } else {
            $consecutive = 0;
        }
    }
    
    // Calculate fatigue errors (more errors in last third vs first third)
    $patterns['fatigue_errors'] = $patterns['late_errors'] - $patterns['early_errors'];
    
    return $patterns;
}

// Direct database access for test results
function save_test_results_direct($data) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'test_results';
    
    // Log the test results first
    $log_data = array(
        'test_id' => uniqid('test_'),
        'test_type' => 'selective_attention',
        'test_phase' => 0,
        'total_letters' => $data['total_count'],
        'p_letters' => $data['p_count'],
        'score' => $data['score'],
        'accuracy' => $data['accuracy'],
        'reaction_time' => $data['avg_reaction_time'],
        'missed_responses' => $data['missed_responses'],
        'false_alarms' => $data['false_alarms']
    );
    log_test_results($log_data, true);
    
    try {
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => get_current_user_id(),
                'test_type' => 'selective_attention',
                'total_count' => $data['total_count'],
                'p_count' => $data['p_count'],
                'total_responses' => $data['total_responses'],
                'correct_responses' => $data['correct_responses'],
                'accuracy' => $data['accuracy'],
                'avg_reaction_time' => $data['avg_reaction_time'],
                'missed_responses' => $data['missed_responses'],
                'false_alarms' => $data['false_alarms'],
                'score' => $data['score'],
                'test_date' => current_time('mysql'),
                'session_id' => uniqid('test_')
            ),
            array(
                '%d', // user_id
                '%s', // test_type
                '%d', // total_count
                '%d', // p_count
                '%d', // total_responses
                '%d', // correct_responses
                '%f', // accuracy
                '%f', // avg_reaction_time
                '%d', // missed_responses
                '%d', // false_alarms
                '%d', // score
                '%s', // test_date
                '%s'  // session_id
            )
        );

        if ($result === false) {
            error_log('Failed to insert test results: ' . $wpdb->last_error);
            return false;
        }

        return $wpdb->insert_id;
    } catch (Exception $e) {
        error_log('Exception while saving test results: ' . $e->getMessage());
        return false;
    }
}

// Register REST API endpoints for test functionality
add_action('rest_api_init', function() {
    // Save test results endpoint
    register_rest_route('attentrack/v1', '/save-test-results', array(
        'methods' => 'POST',
        'callback' => 'handle_save_test_results_rest',
        'permission_callback' => function() {
            return is_user_logged_in();
        },
        'args' => array(
            'total_count' => array(
                'required' => true,
                'type' => 'integer',
                'sanitize_callback' => 'absint'
            ),
            'p_count' => array(
                'required' => true,
                'type' => 'integer',
                'sanitize_callback' => 'absint'
            ),
            'total_responses' => array(
                'required' => true,
                'type' => 'integer',
                'sanitize_callback' => 'absint'
            ),
            'correct_responses' => array(
                'required' => true,
                'type' => 'integer',
                'sanitize_callback' => 'absint'
            ),
            'accuracy' => array(
                'required' => true,
                'type' => 'number',
                'sanitize_callback' => 'floatval'
            ),
            'avg_reaction_time' => array(
                'required' => true,
                'type' => 'number',
                'sanitize_callback' => 'floatval'
            ),
            'missed_responses' => array(
                'required' => true,
                'type' => 'integer',
                'sanitize_callback' => 'absint'
            ),
            'false_alarms' => array(
                'required' => true,
                'type' => 'integer',
                'sanitize_callback' => 'absint'
            ),
            'score' => array(
                'required' => true,
                'type' => 'integer',
                'sanitize_callback' => 'absint'
            ),
            'responses' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            )
        )
    ));

    // Check database status endpoint
    register_rest_route('attentrack/v1', '/check-database', array(
        'methods' => 'GET',
        'callback' => function() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'test_results';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
            
            return new WP_REST_Response(array(
                'status' => $table_exists ? 'ready' : 'not_found',
                'message' => $table_exists ? 'Database is ready' : 'Database table not found',
                'timestamp' => current_time('mysql')
            ), $table_exists ? 200 : 404);
        },
        'permission_callback' => '__return_true'
    ));

    // Get test results endpoint
    register_rest_route('attentrack/v1', '/test-results/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => function($request) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'test_results';
            $result_id = $request['id'];
            
            // Get the test result
            $result = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $result_id
            ));
            
            if (!$result) {
                return new WP_REST_Response(array(
                    'message' => 'Test result not found'
                ), 404);
            }
            
            // Check if user has permission to view this result
            if (!current_user_can('manage_options') && $result->user_id != get_current_user_id()) {
                return new WP_REST_Response(array(
                    'message' => 'You do not have permission to view this result'
                ), 403);
            }
            
            return new WP_REST_Response($result, 200);
        },
        'permission_callback' => function() {
            return is_user_logged_in();
        },
        'args' => array(
            'id' => array(
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            )
        )
    ));
});

function handle_save_test_results_rest($request) {
    global $wpdb;
    
    try {
        $params = $request->get_params();
        
        // Validate required parameters
        $required_fields = ['total_count', 'p_count', 'total_responses', 'correct_responses', 
                          'accuracy', 'avg_reaction_time', 'missed_responses', 'false_alarms', 'score'];
        
        foreach ($required_fields as $field) {
            if (!isset($params[$field])) {
                error_log("Missing required field: {$field}");
                return new WP_REST_Response(array(
                    'success' => false,
                    'message' => "Missing required field: {$field}"
                ), 400);
            }
        }
        
        // Log the incoming data
        error_log('Saving test results with data: ' . json_encode($params));
        
        // Ensure the test_results table exists
        $table_name = $wpdb->prefix . 'test_results';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
        
        if (!$table_exists) {
            error_log("Table {$table_name} does not exist");
            attentrack_create_tables(); // Try to create the table
            
            // Check again if table was created
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
            if (!$table_exists) {
                throw new Exception("Failed to create test_results table");
            }
        }
        
        $result_id = save_test_results_direct($params);
        
        if ($result_id === false) {
            error_log('Database error: ' . $wpdb->last_error);
            throw new Exception('Failed to save test results to database');
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'result_id' => $result_id
        ), 200);
        
    } catch (Exception $e) {
        error_log('Test results error: ' . $e->getMessage());
        error_log('Database last error: ' . $wpdb->last_error);
        
        return new WP_REST_Response(array(
            'success' => false,
            'message' => $e->getMessage(),
            'debug' => WP_DEBUG ? $wpdb->last_error : null
        ), 500);
    }
}

function check_test_results_table_direct() {
    $host = 'localhost';
    $port = '10005';
    $user = 'root';
    $pass = 'root';
    $db = 'local';

    try {
        $conn = new mysqli($host, $user, $pass, $db, $port);
        
        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            return false;
        }

        $result = $conn->query("SHOW TABLES LIKE 'wp_test_results'");
        $exists = $result->num_rows > 0;
        
        $conn->close();
        
        error_log("Table check result: " . ($exists ? "exists" : "not found"));
        return $exists;

    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

// Add REST API endpoint for table status check
add_action('rest_api_init', function() {
    register_rest_route('attentrack/v1', '/check-table-status', array(
        'methods' => 'GET',
        'callback' => 'check_test_results_table',
        'permission_callback' => function() {
            return true;
        }
    ));
});

function check_test_results_table() {
    $table_exists = check_test_results_table_direct();
    return new WP_REST_Response([
        'exists' => $table_exists,
        'checked_at' => current_time('mysql')
    ], 200);
}
