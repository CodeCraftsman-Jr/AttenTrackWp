<?php
if (!defined('ABSPATH')) exit;

// Include the Bootstrap Nav Walker
require_once get_template_directory() . '/includes/class-bootstrap-walker-nav-menu.php';

// Include authentication pages creation
require_once get_template_directory() . '/inc/create-auth-pages.php';

// Include authentication functionality
require_once get_template_directory() . '/inc/authentication.php';

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
    // Register the template
    add_filter('theme_page_templates', function($templates) {
        $templates['templates/selective-and-sustained-attention-test-part-1.php'] = 'Selective and Sustained Attention Test Part 1';
        return $templates;
    });

    // Create the page if it doesn't exist
    $page = get_page_by_path('selective-and-sustained-attention-test-part-1');
    if (!$page) {
        $page_data = array(
            'post_title'    => 'Selective and Sustained Attention Test Part 1',
            'post_name'     => 'selective-and-sustained-attention-test-part-1',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_content'  => '',
        );
        $page_id = wp_insert_post($page_data);
        if (!is_wp_error($page_id)) {
            update_post_meta($page_id, '_wp_page_template', 'templates/selective-and-sustained-attention-test-part-1.php');
        }
    }
}
add_action('after_switch_theme', 'attentrack_register_test_templates');
add_action('init', 'attentrack_register_test_templates');

// Template loader
function attentrack_template_include($template) {
    if (is_page()) {
        $page_template = get_post_meta(get_the_ID(), '_wp_page_template', true);
        if ($page_template === 'templates/selective-and-sustained-attention-test-part-1.php') {
            $new_template = locate_template(array('templates/selective-and-sustained-attention-test-part-1.php'));
            if (!empty($new_template)) {
                return $new_template;
            }
        }
    }
    return $template;
}
add_filter('template_include', 'attentrack_template_include');

// Remove the old template registration functions
remove_action('theme_page_templates', 'attentrack_add_page_templates');

// Add Bootstrap classes to menu items
function attentrack_menu_classes($classes, $item, $args) {
    if ($args->theme_location == 'primary') {
        $classes[] = 'nav-item';
        if ($item->current || $item->current_item_ancestor || $item->current_item_parent) {
            $classes[] = 'active';
        }
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'attentrack_menu_classes', 10, 3);

// Add Bootstrap classes to menu links
function attentrack_menu_link_classes($atts, $item, $args) {
    if ($args->theme_location == 'primary') {
        $atts['class'] = 'nav-link';
        if ($item->current || $item->current_item_ancestor || $item->current_item_parent) {
            $atts['class'] .= ' active';
            $atts['aria-current'] = 'page';
        }
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'attentrack_menu_link_classes', 10, 3);

// Enqueue scripts and styles for the theme
function attentrack_enqueue_scripts() {
    // Enqueue jQuery first
    wp_enqueue_script('jquery');

    // Enqueue test phases script
    wp_enqueue_script(
        'attentrack-test-phases',
        get_template_directory_uri() . '/js/test-phases.js',
        array('jquery'),
        '1.0',
        true
    );

    // Localize script with AJAX data
    wp_localize_script(
        'jquery',
        'attentrack_ajax',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('attentrack_test_nonce'),
            'login_url' => wp_login_url()
        )
    );

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
    
    // Enqueue selective attention test part 1 script
    wp_enqueue_script(
        'selective-attention-test-part1',
        get_template_directory_uri() . '/js/selective-attention-test-part1.js',
        array('jquery'),
        '1.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'attentrack_enqueue_scripts');

// Enqueue Chart.js and admin scripts
function attentrack_admin_scripts($hook) {
    if ($hook === 'attentrack-db_page_attentrack-results') {
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.7.0', true);
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

    // Test Results table
    $test_results_table = $wpdb->prefix . 'test_results';
    $test_sessions_table = $wpdb->prefix . 'test_sessions';

    // Create test_sessions table if it doesn't exist
    $sql_sessions = "CREATE TABLE IF NOT EXISTS $test_sessions_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        test_id varchar(50) NOT NULL,
        user_id bigint(20) NOT NULL,
        start_time datetime NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'started',
        PRIMARY KEY  (id),
        KEY test_id (test_id),
        KEY user_id (user_id)
    ) $charset_collate;";

    // Create test_results table if it doesn't exist
    $sql_results = "CREATE TABLE IF NOT EXISTS $test_results_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        test_id varchar(50) NOT NULL,
        user_id bigint(20) NOT NULL,
        test_phase int(11) NOT NULL,
        score int(11) NOT NULL,
        accuracy decimal(5,2) NOT NULL,
        reaction_time decimal(10,3) NOT NULL,
        missed_responses int(11) NOT NULL,
        false_alarms int(11) NOT NULL,
        responses longtext NOT NULL,
        total_letters int(11) NOT NULL DEFAULT 0,
        p_letters int(11) NOT NULL DEFAULT 0,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY test_id (test_id),
        KEY user_id (user_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_sessions);
    dbDelta($sql_results);

    // Log any errors
    if (!empty($wpdb->last_error)) {
        error_log('Error creating tables: ' . $wpdb->last_error);
    }
}

// Run table creation on theme activation
add_action('after_switch_theme', 'attentrack_create_tables');

// Also run it now to ensure tables exist
attentrack_create_tables();

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

    global $wpdb;
    $table_name = $wpdb->prefix . 'test_results';

    // Prepare data for insertion
    $insert_data = [
        'user_id' => $current_user->ID,
        'test_id' => sanitize_text_field($data['test_id']),
        'test_phase' => intval($data['test_phase']),
        'score' => floatval($data['score']),
        'accuracy' => floatval($data['accuracy']),
        'reaction_time' => floatval($data['reaction_time']),
        'missed_responses' => intval($data['missed_responses']),
        'false_alarms' => intval($data['false_alarms']),
        'responses' => sanitize_text_field($data['responses']),
        'total_letters' => intval($data['total_letters']),
        'p_letters' => intval($data['p_letters']),
        'test_date' => current_time('mysql')
    ];

    error_log('Prepared data for insertion: ' . print_r($insert_data, true));

    // Insert the data
    $result = $wpdb->insert($table_name, $insert_data);

    if ($result === false) {
        error_log('Database error: ' . $wpdb->last_error);
        wp_send_json_error(['message' => 'Failed to save test results: ' . $wpdb->last_error]);
        return;
    }

    error_log('Test results saved successfully. Insert ID: ' . $wpdb->insert_id);
    wp_send_json_success(['message' => 'Test results saved successfully']);
}
add_action('wp_ajax_save_test_results', 'save_test_results_handler');
add_action('wp_ajax_nopriv_save_test_results', 'save_test_results_handler');

// Shortcode to display test results in dashboard
function display_test_results_shortcode() {
    if (!is_user_logged_in()) {
        return 'Please log in to view your test results.';
    }

    // Add custom CSS for test results table
    $output = '<style>
        .test-results-container {
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .test-results-container h2 {
            color: #333;
            margin-bottom: 5px;
            font-size: 24px;
        }
        .test-results-container h3 {
            color: #666;
            margin-bottom: 20px;
            font-size: 18px;
        }
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
            background-color: transparent;
        }
        .table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 600;
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #dee2e6;
        }
        .table td {
            padding: 12px;
            border-top: 1px solid #dee2e6;
            color: #666;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,.02);
        }
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        @media (max-width: 768px) {
            .test-results-container {
                padding: 10px;
            }
            .table th, .table td {
                padding: 8px;
                font-size: 14px;
            }
        }
    </style>';

    global $wpdb;
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    
    $results_table = $wpdb->prefix . 'test_results';
    $query = $wpdb->prepare(
        "SELECT test_date, test_phase, score, accuracy, reaction_time, missed_responses, false_alarms, responses, total_letters, p_letters 
         FROM $results_table 
         WHERE user_id = %d 
         ORDER BY test_date DESC",
        $user_id
    );
    
    $results = $wpdb->get_results($query);

    if ($wpdb->last_error) {
        return 'Error retrieving test results.';
    }

    if (empty($results)) {
        return '<div class="alert alert-info">No test results found. Complete a test to see your results here!</div>';
    }

    $output .= '<div class="test-results-container">';
    $output .= '<h2>Test History</h2>';
    $output .= '<h3>Your Test Results</h3>';
    $output .= '<div class="table-responsive">';
    $output .= '<table class="table table-striped">';
    $output .= '<thead><tr>';
    $output .= '<th>Test Date</th>';
    $output .= '<th>Test Phase</th>';
    $output .= '<th>Total Letters</th>';
    $output .= '<th>P Letters</th>';
    $output .= '<th>Score</th>';
    $output .= '<th>Accuracy</th>';
    $output .= '<th>Reaction Time</th>';
    $output .= '<th>Correct Responses</th>';
    $output .= '<th>Missed Responses</th>';
    $output .= '<th>False Alarms</th>';
    $output .= '</tr></thead><tbody>';

    foreach ($results as $result) {
        // Calculate correct responses and totals from stored responses data
        $responses = json_decode($result->responses, true);
        $correct_responses = 0;
        $total_letters = $result->total_letters;
        $p_letters = $result->p_letters;

        if (is_array($responses)) {
            foreach ($responses as $response) {
                if (isset($response['correct']) && $response['correct'] === true) {
                    $correct_responses++;
                }
            }
        }

        $output .= '<tr>';
        $output .= '<td>' . date('Y-m-d H:i:s', strtotime($result->test_date)) . '</td>';
        
        // Map test phase numbers to descriptive names
        $test_name = '';
        switch($result->test_phase) {
            case 0:
                $test_name = 'Selective Attention Test';
                break;
            case 1:
                $test_name = 'Selective and Sustained Attention Test Part 1';
                break;
            case 2:
                $test_name = 'Selective and Sustained Attention Test Part 2';
                break;
            case 3:
                $test_name = 'Selective and Sustained Attention Test Part 3';
                break;
            case 4:
                $test_name = 'Selective and Sustained Attention Test Part 4';
                break;
            case 5:
                $test_name = 'Alternative Attention Test';
                break;
            case 6:
                $test_name = 'Divided Attention Test';
                break;
            default:
                $test_name = 'Test Phase ' . esc_html($result->test_phase);
        }
        
        $output .= '<td>' . $test_name . '</td>';
        $output .= '<td>' . $total_letters . '</td>';
        $output .= '<td>' . $p_letters . '</td>';
        $output .= '<td>' . number_format($result->score, 2) . '</td>';
        $output .= '<td>' . number_format($result->accuracy, 1) . '%</td>';
        $output .= '<td>' . number_format($result->reaction_time, 2) . 's</td>';
        $output .= '<td>' . $correct_responses . '</td>';
        $output .= '<td>' . $result->missed_responses . '</td>';
        $output .= '<td>' . $result->false_alarms . '</td>';
        $output .= '</tr>';
    }

    $output .= '</tbody></table>';
    $output .= '</div></div>';
    
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
    $test_results_table = $wpdb->prefix . 'test_results';
    
    // Check if table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$test_results_table'");
    
    if (!$table_exists) {
        wp_die('Test results table does not exist. Please activate/deactivate the theme to create it.');
        return;
    }
    
    // Check if columns exist
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $test_results_table");
    $column_names = array_map(function($col) { return $col->Field; }, $columns);
    
    $updates_needed = false;
    
    if (!in_array('total_letters', $column_names)) {
        $wpdb->query("ALTER TABLE $test_results_table ADD COLUMN total_letters int(11) NOT NULL DEFAULT 0");
        $updates_needed = true;
    }
    
    if (!in_array('p_letters', $column_names)) {
        $wpdb->query("ALTER TABLE $test_results_table ADD COLUMN p_letters int(11) NOT NULL DEFAULT 0");
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
    error_log('get_unique_test_id_handler called');
    
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'attentrack_test_nonce')) {
        error_log('Nonce verification failed');
        wp_send_json_error(['message' => 'Invalid security token']);
        return;
    }

    // Get current user
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        error_log('User not logged in');
        wp_send_json_error(['message' => 'User not logged in']);
        return;
    }

    // Generate unique test ID
    $test_id = generate_unique_test_id($current_user->ID);
    error_log('Generated test ID: ' . $test_id);
    
    // Create test session
    global $wpdb;
    $test_sessions_table = $wpdb->prefix . 'test_sessions';
    
    $result = $wpdb->insert(
        $test_sessions_table,
        array(
            'test_id' => $test_id,
            'user_id' => $current_user->ID,
            'start_time' => current_time('mysql'),
            'status' => 'started'
        ),
        array('%s', '%d', '%s', '%s')
    );

    if ($result === false) {
        error_log('Error creating test session: ' . $wpdb->last_error);
        wp_send_json_error(['message' => 'Error creating test session']);
        return;
    }

    wp_send_json_success(['test_id' => $test_id]);
}
add_action('wp_ajax_get_unique_test_id', 'get_unique_test_id_handler');
add_action('wp_ajax_nopriv_get_unique_test_id', 'get_unique_test_id_handler');

// AJAX handler to create a test session
function create_test_session_handler() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'create_test_session')) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Please log in to take the test']);
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
        wp_send_json_error(['message' => 'Failed to create test session']);
    }
    
    wp_send_json_success(['session_id' => $wpdb->insert_id, 'test_id' => $test_id]);
}
add_action('wp_ajax_create_test_session', 'create_test_session_handler');

// AJAX handler to complete a test session
function complete_test_session_handler() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'complete_test_session')) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Please log in to complete the test']);
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
        wp_send_json_error(['message' => 'Invalid test session']);
        return;
    }
    
    $result = $wpdb->update(
        $test_sessions_table,
        array('status' => 'completed'),
        array('id' => $session_id)
    );
    
    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to update test session']);
    }
    
    wp_send_json_success(['message' => 'Test session completed successfully']);
}
add_action('wp_ajax_complete_test_session', 'complete_test_session_handler');
