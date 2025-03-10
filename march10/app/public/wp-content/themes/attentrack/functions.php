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
    
    // Firebase scripts
    wp_enqueue_script('firebase-app', 'https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js', array(), null, true);
    wp_enqueue_script('firebase-auth', 'https://www.gstatic.com/firebasejs/8.10.0/firebase-auth.js', array('firebase-app'), null, true);
    
    // Add Firebase config and initialization
    wp_add_inline_script('firebase-auth', '
        const firebaseConfig = {
            apiKey: "AIzaSyDxwNXFliKJPC39UOweKnWNvpPipf7-PXc",
            authDomain: "innovproject-274c9.firebaseapp.com",
            projectId: "innovproject-274c9",
            storageBucket: "innovproject-274c9.firebasestorage.app",
            messagingSenderId: "111288496386",
            appId: "1:111288496386:web:38dd0ab7e126ebe93b521b"
        };

        // Initialize Firebase
        if (!window.firebase) {
            console.error("Firebase not loaded");
        } else {
            firebase.initializeApp(firebaseConfig);
            window.auth = firebase.auth();
            console.log("Firebase initialized successfully");
        }
    ', 'after');
    
    // Chart.js and its dependencies
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), null, true);
    wp_enqueue_script('date-fns', 'https://cdn.jsdelivr.net/npm/date-fns@2.29.3/index.min.js', array(), null, true);
    wp_enqueue_script('chartjs-adapter-date-fns', 'https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js', array('chart-js', 'date-fns'), null, true);
    
    // Test phase scripts
    wp_enqueue_script('test-phases', get_template_directory_uri() . '/js/test-phases.js', array('jquery'), '1.0.1', true);
    
    // Localize the script with AJAX URL and nonce
    wp_localize_script('test-phases', 'attentrack_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('save_test_results')
    ));
    
    // Main script
    wp_enqueue_script('attentrack-main', get_template_directory_uri() . '/js/main.js', array('jquery', 'firebase-auth', 'chartjs-adapter-date-fns'), '1.0.0', true);
    
    // Auth script
    wp_enqueue_script('attentrack-auth', get_template_directory_uri() . '/js/auth.js', array('jquery', 'firebase-auth'), '1.0.0', true);
    
    // Add AJAX URL and nonces
    wp_localize_script('attentrack-auth', 'attentrack_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'test_nonce' => wp_create_nonce('save_test_results')
    ));
    
    // Add Chart.js for dashboard
    if (is_page('dashboard')) {
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.4.1', true);
    }
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
    $test_results_table = $wpdb->prefix . 'test_results';
    $sql_results = "CREATE TABLE IF NOT EXISTS $test_results_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        test_id varchar(100) NOT NULL,
        test_phase int(11) NOT NULL,
        score float NOT NULL,
        accuracy float NOT NULL,
        reaction_time float NOT NULL,
        missed_responses int(11) NOT NULL,
        false_alarms int(11) NOT NULL,
        responses longtext NOT NULL,
        test_date datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY test_id (test_id),
        KEY test_phase (test_phase),
        KEY test_date (test_date)
    ) $charset_collate;";

    // Test Sessions Table
    $test_sessions_table = $wpdb->prefix . 'test_sessions';
    $sql_sessions = "CREATE TABLE IF NOT EXISTS $test_sessions_table (
        session_id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        start_time datetime DEFAULT CURRENT_TIMESTAMP,
        completion_status varchar(20) DEFAULT 'incomplete',
        total_phases_completed int(11) DEFAULT 0,
        notes text,
        PRIMARY KEY (session_id),
        KEY user_id (user_id),
        KEY start_time (start_time)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_results);
    dbDelta($sql_sessions);
}

// Run table creation on theme activation
add_action('after_switch_theme', 'attentrack_create_tables');

// Also run it now to ensure tables exist
attentrack_create_tables();

// AJAX handler to save test results
function save_test_results() {
    check_ajax_referer('save_test_results', 'nonce');
    
    global $wpdb;
    $results_table = $wpdb->prefix . 'test_results';
    $sessions_table = $wpdb->prefix . 'test_sessions';
    
    // Get current user ID
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(array('message' => 'User not logged in'));
        return;
    }
    
    // Get POST data
    $test_id = sanitize_text_field($_POST['test_id']);
    $test_phase = intval($_POST['test_phase']);
    $score = floatval($_POST['score']);
    $accuracy = floatval($_POST['accuracy']);
    $reaction_time = floatval($_POST['reaction_time']);
    $missed_responses = intval($_POST['missed_responses']);
    $false_alarms = intval($_POST['false_alarms']);
    $responses = sanitize_text_field($_POST['responses']);
    
    // Start transaction
    $wpdb->query('START TRANSACTION');
    
    try {
        // Update or create session
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT session_id, total_phases_completed FROM $sessions_table 
            WHERE user_id = %d AND completion_status = 'incomplete'
            ORDER BY start_time DESC LIMIT 1",
            $user_id
        ));

        if (!$session) {
            // Create new session
            $wpdb->insert(
                $sessions_table,
                array(
                    'user_id' => $user_id,
                    'total_phases_completed' => 1
                ),
                array('%d', '%d')
            );
            $session_id = $wpdb->insert_id;
        } else {
            $session_id = $session->session_id;
            // Update phases completed
            $wpdb->update(
                $sessions_table,
                array(
                    'total_phases_completed' => $session->total_phases_completed + 1,
                    'completion_status' => ($test_phase >= 2) ? 'complete' : 'incomplete'
                ),
                array('session_id' => $session_id),
                array('%d', '%s'),
                array('%d')
            );
        }

        // Save test results
        $result = $wpdb->insert(
            $results_table,
            array(
                'user_id' => $user_id,
                'test_id' => $test_id,
                'test_phase' => $test_phase,
                'score' => $score,
                'accuracy' => $accuracy,
                'reaction_time' => $reaction_time,
                'missed_responses' => $missed_responses,
                'false_alarms' => $false_alarms,
                'responses' => $responses,
                'test_date' => current_time('mysql')
            ),
            array(
                '%d', // user_id
                '%s', // test_id
                '%d', // test_phase
                '%f', // score
                '%f', // accuracy
                '%f', // reaction_time
                '%d', // missed_responses
                '%d', // false_alarms
                '%s', // responses
                '%s'  // test_date
            )
        );

        if ($result === false) {
            throw new Exception($wpdb->last_error);
        }

        $wpdb->query('COMMIT');
        wp_send_json_success(array(
            'message' => 'Results saved successfully',
            'session_id' => $session_id,
            'result_id' => $wpdb->insert_id
        ));
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        wp_send_json_error(array(
            'message' => 'Failed to save results: ' . $e->getMessage()
        ));
    }
}

// Add AJAX handlers
add_action('wp_ajax_save_test_results', 'save_test_results');

// Shortcode to display test results in dashboard
function display_test_results_shortcode() {
    if (!is_user_logged_in()) {
        return 'Please log in to view your test results.';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    
    // Get all completed sessions
    $sessions = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}test_sessions 
        WHERE user_id = %d AND completion_status = 'complete'
        ORDER BY start_time DESC",
        $user_id
    ));
    
    ob_start();
    ?>
    <div class="test-results-container">
        <h2>Your Test History</h2>
        
        <?php if (empty($sessions)): ?>
            <p>You haven't completed any tests yet. Start a new test to see your results here!</p>
        <?php else: ?>
            <?php foreach ($sessions as $session): ?>
                <div class="test-session">
                    <h3>Test Session - <?php echo date('F j, Y g:i a', strtotime($session->start_time)); ?></h3>
                    
                    <?php
                    // Get results for all phases in this session
                    $results = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}test_results 
                        WHERE user_id = %d 
                        AND test_date BETWEEN %s AND DATE_ADD(%s, INTERVAL 1 DAY)
                        ORDER BY test_phase ASC",
                        $user_id,
                        $session->start_time,
                        $session->start_time
                    ));
                    
                    foreach ($results as $result):
                    ?>
                        <div class="phase-results">
                            <h4>Phase <?php echo $result->test_phase; ?></h4>
                            <table class="results-table">
                                <tr>
                                    <td>Score:</td>
                                    <td><?php echo number_format($result->score, 1); ?></td>
                                </tr>
                                <tr>
                                    <td>Accuracy:</td>
                                    <td><?php echo number_format($result->accuracy, 1); ?>%</td>
                                </tr>
                                <tr>
                                    <td>Reaction Time:</td>
                                    <td><?php echo number_format($result->reaction_time, 3); ?>s</td>
                                </tr>
                                <tr>
                                    <td>Missed Responses:</td>
                                    <td><?php echo $result->missed_responses; ?></td>
                                </tr>
                                <tr>
                                    <td>False Alarms:</td>
                                    <td><?php echo $result->false_alarms; ?></td>
                                </tr>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <style>
    .test-results-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    .test-session {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .phase-results {
        background: #f5f5f5;
        border-radius: 8px;
        padding: 20px;
        margin: 15px 0;
    }
    .results-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .results-table td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
    }
    .results-table td:first-child {
        font-weight: bold;
        width: 40%;
        color: #333;
    }
    .results-table td:last-child {
        color: #666;
    }
    h3 {
        color: #2c3e50;
        margin-bottom: 20px;
    }
    h4 {
        color: #34495e;
        margin: 0 0 15px 0;
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('test_results', 'display_test_results_shortcode');
