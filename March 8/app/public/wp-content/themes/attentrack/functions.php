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

// Enqueue scripts and styles
function attentrack_enqueue_scripts() {
    // Enqueue styles
    wp_enqueue_style('attentrack-style', get_stylesheet_uri(), array(), '1.0.0');
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
    
    // Enqueue scripts
    wp_enqueue_script('jquery');
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.3', true);
    
    // Firebase Scripts
    wp_enqueue_script('firebase-app', 'https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js', array(), '9.6.1', true);
    wp_enqueue_script('firebase-auth', 'https://www.gstatic.com/firebasejs/9.6.1/firebase-auth-compat.js', array('firebase-app'), '9.6.1', true);
    
    // Add Firebase config and initialization
    wp_add_inline_script('firebase-app', '
        if (typeof window.firebaseConfig === "undefined") {
            window.firebaseConfig = {
                apiKey: "AIzaSyDxwNXFliKJPC39UOweKnWNvpPipf7-PXc",
                authDomain: "innovproject-274c9.firebaseapp.com",
                projectId: "innovproject-274c9",
                storageBucket: "innovproject-274c9.firebasestorage.app",
                messagingSenderId: "111288496386",
                appId: "1:111288496386:web:38dd0ab7e126ebe93b521b"
            };
            // Initialize Firebase
            if (!firebase.apps.length) {
                firebase.initializeApp(window.firebaseConfig);
            }
            // Initialize auth
            window.auth = firebase.auth();
        }
    ');
    
    // Main script
    wp_enqueue_script('attentrack-main', get_template_directory_uri() . '/js/main.js', array('jquery', 'firebase-app', 'firebase-auth'), '1.0.0', true);
    
    // Add AJAX URL and nonces for all pages
    wp_localize_script('jquery', 'attentrack_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'test_nonce' => wp_create_nonce('save_test_results')
    ));

    // Add inline script to define ajaxurl globally
    wp_add_inline_script('jquery', 'var ajaxurl = "' . admin_url('admin-ajax.php') . '";', 'before');
}
add_action('wp_enqueue_scripts', 'attentrack_enqueue_scripts');

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
function generate_unique_test_id() {
    $prefix = 'AT';
    $timestamp = time();
    $random = wp_rand(1000, 9999);
    $test_id = $prefix . $timestamp . $random;
    
    return array(
        'success' => true,
        'testId' => $test_id
    );
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

// Handle test results submission
add_action('wp_ajax_save_test_results', 'handle_save_test_results');
function handle_save_test_results() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'save_test_results')) {
        wp_send_json_error('Invalid nonce');
    }

    $current_user = wp_get_current_user();
    
    // Create a new test result post
    $post_data = array(
        'post_title'   => 'Test Result - ' . current_time('mysql'),
        'post_type'    => 'test_result',
        'post_status'  => 'publish',
        'post_author'  => $current_user->ID
    );

    $post_id = wp_insert_post($post_data);

    if (is_wp_error($post_id)) {
        wp_send_json_error('Failed to create test result');
    }

    // Save test metadata
    update_post_meta($post_id, 'test_id', sanitize_text_field($_POST['test_id']));
    update_post_meta($post_id, 'test_phase', sanitize_text_field($_POST['test_phase']));
    update_post_meta($post_id, 'total_responses', intval($_POST['total_responses']));
    update_post_meta($post_id, 'correct_responses', intval($_POST['correct_responses']));
    update_post_meta($post_id, 'accuracy', floatval($_POST['accuracy']));

    // Clear the current test ID from user meta after saving
    delete_user_meta($current_user->ID, 'current_test_id');

    wp_send_json_success(array('post_id' => $post_id));
}

// Register test_result custom post type
add_action('init', 'register_test_result_post_type');
function register_test_result_post_type() {
    register_post_type('test_result', array(
        'labels' => array(
            'name' => 'Test Results',
            'singular_name' => 'Test Result'
        ),
        'public' => false,
        'show_ui' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => array('slug' => 'test-result'),
        'supports' => array('title', 'author')
    ));
}

// Create test results table on theme activation
function create_test_results_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'test_results';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        test_id varchar(50) NOT NULL,
        test_phase int(11) NOT NULL,
        score int(11) NOT NULL,
        accuracy float NOT NULL,
        reaction_time float NOT NULL,
        missed_responses int(11) NOT NULL,
        false_alarms int(11) NOT NULL,
        test_date datetime DEFAULT CURRENT_TIMESTAMP,
        raw_data longtext,
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY test_id (test_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'create_test_results_table');

// AJAX handler to save test results
function save_test_results() {
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
        return;
    }

    // Verify nonce
    check_ajax_referer('save_test_results', '_ajax_nonce');

    $user_id = get_current_user_id();
    $test_id = sanitize_text_field($_POST['test_id']);
    $test_phase = intval($_POST['test_phase']);
    $score = intval($_POST['score']);
    $accuracy = floatval($_POST['accuracy']);
    $reaction_time = floatval($_POST['reaction_time']);
    $missed_responses = intval($_POST['missed_responses']);
    $false_alarms = intval($_POST['false_alarms']);
    $raw_data = sanitize_text_field($_POST['raw_data']);

    global $wpdb;
    $table_name = $wpdb->prefix . 'test_results';

    $result = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'test_id' => $test_id,
            'test_phase' => $test_phase,
            'score' => $score,
            'accuracy' => $accuracy,
            'reaction_time' => $reaction_time,
            'missed_responses' => $missed_responses,
            'false_alarms' => $false_alarms,
            'raw_data' => $raw_data
        ),
        array('%d', '%s', '%d', '%d', '%f', '%f', '%d', '%d', '%s')
    );

    if ($result) {
        wp_send_json_success('Results saved successfully');
    } else {
        wp_send_json_error('Failed to save results: ' . $wpdb->last_error);
    }
}
add_action('wp_ajax_save_test_results', 'save_test_results');

// Shortcode to display test results in dashboard
function display_test_results_shortcode() {
    if (!is_user_logged_in()) {
        return 'Please log in to view your test results.';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'test_results';
    $user_id = get_current_user_id();

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d ORDER BY test_date DESC",
        $user_id
    ));

    ob_start();
    ?>
    <div class="test-results-dashboard">
        <h2>Your Test Results</h2>
        <table class="test-results-table">
            <thead>
                <tr>
                    <th>Test Phase</th>
                    <th>Score</th>
                    <th>Accuracy</th>
                    <th>Reaction Time</th>
                    <th>Missed</th>
                    <th>False Alarms</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                <tr>
                    <td>Phase <?php echo esc_html($result->test_phase); ?></td>
                    <td><?php echo esc_html($result->score); ?></td>
                    <td><?php echo number_format($result->accuracy, 1); ?>%</td>
                    <td><?php echo number_format($result->reaction_time, 3); ?>s</td>
                    <td><?php echo esc_html($result->missed_responses); ?></td>
                    <td><?php echo esc_html($result->false_alarms); ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($result->test_date)); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <style>
    .test-results-dashboard {
        margin: 20px 0;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .test-results-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .test-results-table th,
    .test-results-table td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #eee;
    }
    .test-results-table th {
        background: #f5f5f5;
        font-weight: bold;
    }
    .test-results-table tr:hover {
        background: #f9f9f9;
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('test_results', 'display_test_results_shortcode');
