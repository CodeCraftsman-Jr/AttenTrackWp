<?php
// Include database setup functions
require_once get_template_directory() . '/inc/database-setup.php';

if (!defined('ABSPATH')) exit;

// Include essential files
require_once get_template_directory() . '/includes/class-bootstrap-walker-nav-menu.php';
require_once get_template_directory() . '/inc/authentication.php';

// Include database setup and API endpoints
require_once get_template_directory() . '/inc/database-setup.php';
require_once get_template_directory() . '/inc/api-endpoints.php';

// Theme Setup
function attentrack_setup() {
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

// Create required pages on theme activation
function attentrack_create_pages() {
    $pages = array(
        'signin' => array(
            'title' => 'Sign In',
            'template' => 'page-signin.php'
        ),
        'signup' => array(
            'title' => 'Sign Up',
            'template' => 'page-signup.php'
        ),
        'selection-page' => array(
            'title' => 'Test Selection',
            'template' => 'selection-page.php'
        )
    );

    foreach ($pages as $slug => $page) {
        $existing_page = get_page_by_path($slug);
        if (!$existing_page) {
            $page_id = wp_insert_post(array(
                'post_title' => $page['title'],
                'post_name' => $slug,
                'post_status' => 'publish',
                'post_type' => 'page',
                'page_template' => $page['template']
            ));
            
            if (!is_wp_error($page_id)) {
                update_post_meta($page_id, '_wp_page_template', $page['template']);
            }
        }
    }
    
    update_option('attentrack_pages_created', true);
}

// Run page creation immediately and on theme activation
add_action('after_switch_theme', 'attentrack_create_pages');
if (!get_option('attentrack_pages_created')) {
    attentrack_create_pages();
}

// Firebase configuration
define('FIREBASE_PUBLIC_KEY', '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4ZwN7HLKvX+ZpP0GHgmR
0Vgl/ZD3K4p9ZLvL4ZwN7HLKvX+ZpP0GHgmR0Vgl/ZD3K4p9ZLvL4ZwN7HLKvX+Z
pP0GHgmR0Vgl/ZD3K4p9ZLvL4ZwN7HLKvX+ZpP0GHgmR0Vgl/ZD3K4p9ZLvL4ZwN
7HLKvX+ZpP0GHgmR0Vgl/ZD3K4p9ZLvL4ZwN7HLKvX+ZpP0GHgmR0Vgl/ZD3K4p9
ZLvL4ZwN7HLKvX+ZpP0GHgmR0Vgl/ZD3K4p9ZLvL
-----END PUBLIC KEY-----');

// Enqueue scripts and styles
function attentrack_enqueue_scripts() {
    // Bootstrap CSS and JS
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
    
    // Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    
    // Theme styles
    wp_enqueue_style('attentrack-style', get_stylesheet_uri());
    
    // Custom styles
    $custom_css = "
        .navbar { padding: 1rem 0; }
        .navbar-brand img { max-height: 40px; width: auto; }
        .dropdown-toggle::after { margin-left: 0.5rem; }
        .dropdown-menu { min-width: 200px; }
        .dropdown-item i { width: 20px; }
        .user-avatar { width: 32px; height: 32px; object-fit: cover; }
    ";
    wp_add_inline_style('attentrack-style', $custom_css);
    
    // jQuery (make sure it's loaded)
    wp_enqueue_script('jquery');
    
    // Firebase SDK
    wp_enqueue_script('firebase-app', 'https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js', array(), null, true);
    wp_enqueue_script('firebase-auth', 'https://www.gstatic.com/firebasejs/9.6.1/firebase-auth-compat.js', array('firebase-app'), null, true);
    
    // Auth script
    wp_enqueue_script('auth-js', get_template_directory_uri() . '/js/auth.js', array('jquery', 'firebase-app', 'firebase-auth'), null, true);
    
    // Localize script with AJAX URL and nonce
    wp_localize_script('auth-js', 'authData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'homeUrl' => home_url(),
        'nonce' => wp_create_nonce('auth-nonce')
    ));
}
add_action('wp_enqueue_scripts', 'attentrack_enqueue_scripts');

// Create database tables on theme activation
function attentrack_create_tables() {
    require_once get_template_directory() . '/inc/user-management.php';
    create_custom_user_tables();
}
add_action('after_switch_theme', 'attentrack_create_tables');

// Create pages and menu
function setup_attentrack_pages_and_menu() {
    $pages = [
        'about-app' => ['title' => 'About App', 'template' => 'page-templates/about-app.php'],
        'contact-us' => ['title' => 'Contact Us', 'template' => 'page-templates/contact-us.php'],
        'signin' => ['title' => 'Sign In', 'template' => 'page-signin.php'],
        'signup' => ['title' => 'Sign Up', 'template' => 'page-signup.php'],
        'patient-details-form' => ['title' => 'Patient Details Form', 'template' => 'patientdetailsform-template.php'],
        'selection-page' => ['title' => 'Selection Page', 'template' => 'selectionpage2-template.php'],
        'alternative-attention-test' => ['title' => 'Alternative Attention Test', 'template' => 'alternative-attention-test.php'],
        'divided-attention-test' => ['title' => 'Divided Attention Test', 'template' => 'divided-attention-test.php'],
        'selective-attention-test' => ['title' => 'Selective Test', 'template' => 'selective-attention-test.php']
    ];
    
    foreach ($pages as $slug => $data) {
        if (!get_page_by_path($slug)) {
            $page_id = wp_insert_post([
                'post_title' => $data['title'],
                'post_name' => $slug,
                'post_status' => 'publish',
                'post_type' => 'page'
            ]);
            if ($page_id) update_post_meta($page_id, '_wp_page_template', $data['template']);
        }
    }
    
    // Create menu
    $menu_name = 'Primary Menu';
    $menu_exists = wp_get_nav_menu_object($menu_name);
    if (!$menu_exists) {
        $menu_id = wp_create_nav_menu($menu_name);
        
        // Only add About App and Contact Us to the menu
        $menu_items = [
            'about-app' => 'About App',
            'contact-us' => 'Contact Us'
        ];
        
        foreach ($menu_items as $slug => $title) {
            $page = get_page_by_path($slug);
            if ($page) {
                wp_update_nav_menu_item($menu_id, 0, [
                    'menu-item-title' => $title,
                    'menu-item-object' => 'page',
                    'menu-item-object-id' => $page->ID,
                    'menu-item-type' => 'post_type',
                    'menu-item-status' => 'publish'
                ]);
            }
        }
        
        set_theme_mod('nav_menu_locations', ['primary' => $menu_id]);
    }
}

// Run setup on theme activation and manual trigger
add_action('after_switch_theme', 'setup_attentrack_pages_and_menu');
if (isset($_GET['setup_pages']) && current_user_can('manage_options')) {
    setup_attentrack_pages_and_menu();
}

// Handle auth page access
function handle_auth_page_access() {
    global $post;
    
    if (!$post) return;
    
    // If user is logged in and trying to access auth pages, redirect to home
    if (is_user_logged_in() && in_array($post->post_name, ['signin', 'signup'])) {
        wp_redirect(home_url());
        exit;
    }
    
    // If user is not logged in and trying to access protected pages, redirect to signin
    if (!is_user_logged_in() && in_array($post->post_name, ['selection-page', 'dashboard'])) {
        wp_redirect(home_url('/signin'));
        exit;
    }
}
add_action('template_redirect', 'handle_auth_page_access', 1);

// Add AJAX endpoint for getting home URL
function get_home_url_ajax() {
    wp_send_json_success(home_url());
}
add_action('wp_ajax_get_home_url', 'get_home_url_ajax');
add_action('wp_ajax_nopriv_get_home_url', 'get_home_url_ajax');

// Function to ensure a user has a test ID and profile ID
function ensure_user_ids($user_id) {
    // Get existing IDs
    $test_id = get_user_meta($user_id, 'test_id', true);
    $profile_id = get_user_meta($user_id, 'profile_id', true);
    
    // Generate test ID if not exists
    if (empty($test_id)) {
        $test_id = 'T-' . $user_id . '-' . time();
        update_user_meta($user_id, 'test_id', $test_id);
        error_log("Generated new test ID for user {$user_id}: {$test_id}");
    }
    
    // Generate profile ID if not exists
    if (empty($profile_id)) {
        $profile_id = 'P-' . $user_id;
        update_user_meta($user_id, 'profile_id', $profile_id);
        error_log("Generated new profile ID for user {$user_id}: {$profile_id}");
    }
    
    return array(
        'test_id' => $test_id,
        'profile_id' => $profile_id
    );
}

// Add AJAX endpoint for saving test results
function save_test_results_ajax() {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
        return;
    }
    
    // Get current user ID
    $user_id = get_current_user_id();
    
    // Ensure user has test ID and profile ID
    $ids = ensure_user_ids($user_id);
    
    // Get test type and results from POST data
    $test_type = isset($_POST['test_type']) ? sanitize_text_field($_POST['test_type']) : '';
    $results = isset($_POST['results']) ? $_POST['results'] : array();
    
    // Validate test type
    if (empty($test_type)) {
        wp_send_json_error('Test type is required');
        return;
    }
    
    // Validate results
    if (empty($results)) {
        wp_send_json_error('Results are required');
        return;
    }
    
    // Log the received data for debugging
    error_log('Received test results for ' . $test_type . ': ' . print_r($results, true));
    error_log('User IDs: ' . print_r($ids, true));
    
    // Process results based on test type
    switch ($test_type) {
        case 'selective_attention_extended':
            // Save overall results
            $total_correct = 0;
            $total_incorrect = 0;
            $total_reaction_time = 0;
            
            // Get test ID and profile ID
            $test_id = $ids['test_id'];
            $profile_id = $ids['profile_id'];
            
            // Save individual phase results
            for ($i = 1; $i <= 4; $i++) {
                if (isset($results['phases'][$i-1])) {
                    $phase = $results['phases'][$i-1];
                    
                    // Log the phase data for debugging
                    error_log("Processing phase {$i} data: " . print_r($phase, true));
                    
                    // Ensure phase data is correct
                    $phase_num = isset($phase['phase']) ? intval($phase['phase']) : $i;
                    
                    update_user_meta($user_id, "test_selective_attention_extended_phase{$phase_num}_total_letters", sanitize_text_field($phase['totalLetters']));
                    update_user_meta($user_id, "test_selective_attention_extended_phase{$phase_num}_p_letters", sanitize_text_field($phase['pLetters']));
                    update_user_meta($user_id, "test_selective_attention_extended_phase{$phase_num}_correct", sanitize_text_field($phase['correctResponses']));
                    update_user_meta($user_id, "test_selective_attention_extended_phase{$phase_num}_incorrect", sanitize_text_field($phase['incorrectResponses']));
                    update_user_meta($user_id, "test_selective_attention_extended_phase{$phase_num}_time", sanitize_text_field($phase['reactionTime']));
                    update_user_meta($user_id, "test_selective_attention_extended_phase{$phase_num}_score", sanitize_text_field($phase['score']));
                    
                    $total_correct += intval($phase['correctResponses']);
                    $total_incorrect += intval($phase['incorrectResponses']);
                    $total_reaction_time += floatval($phase['reactionTime']);
                    
                    // Save to custom database table
                    if (function_exists('save_extended_results')) {
                        $extended_data = array(
                            'test_id' => $test_id,
                            'profile_id' => $profile_id,
                            'phase' => $phase_num,
                            'total_letters' => $phase['totalLetters'],
                            'p_letters' => $phase['pLetters'],
                            'correct_responses' => $phase['correctResponses'],
                            'incorrect_responses' => $phase['incorrectResponses'],
                            'reaction_time' => $phase['reactionTime']
                        );
                        
                        // Debug log
                        error_log('Saving extended results for phase ' . $phase_num . ': ' . print_r($extended_data, true));
                        
                        $result = save_extended_results($extended_data);
                        
                        if ($result === false) {
                            global $wpdb;
                            error_log('Database error: ' . $wpdb->last_error);
                        } else {
                            error_log('Successfully saved data to extended results table for phase ' . $phase_num . '. Result: ' . $result);
                        }
                    } else {
                        error_log('save_extended_results function not found!');
                    }
                } else {
                    error_log("Phase {$i} data not found in results array");
                }
            }
            
            // Save combined results
            update_user_meta($user_id, 'test_selective_attention_extended_total_correct', $total_correct);
            update_user_meta($user_id, 'test_selective_attention_extended_total_incorrect', $total_incorrect);
            update_user_meta($user_id, 'test_selective_attention_extended_avg_time', $total_reaction_time / 4);
            update_user_meta($user_id, 'test_selective_attention_extended_total_score', sanitize_text_field($results['totalScore']));
            break;
            
        case 'divided_attention':
            // Extract test ID and profile ID from results or use the generated ones
            $test_id = isset($results['testId']) ? sanitize_text_field($results['testId']) : $ids['test_id'];
            $profile_id = isset($results['profileId']) ? sanitize_text_field($results['profileId']) : $ids['profile_id'];
            
            // Ensure IDs are set
            if (empty($test_id)) $test_id = $ids['test_id'];
            if (empty($profile_id)) $profile_id = $ids['profile_id'];
            
            // Log the IDs
            error_log("Using test ID: {$test_id} and profile ID: {$profile_id} for divided attention test");
            
            // Save test results
            update_user_meta($user_id, 'test_divided_attention_correct', sanitize_text_field($results['correctResponses']));
            update_user_meta($user_id, 'test_divided_attention_incorrect', sanitize_text_field($results['incorrectResponses']));
            update_user_meta($user_id, 'test_divided_attention_reaction_time', sanitize_text_field($results['reactionTime']));
            update_user_meta($user_id, 'test_divided_attention_score', sanitize_text_field($results['score']));
            
            // Save to custom database table if function exists
            if (function_exists('save_divided_attention_results')) {
                $divided_data = array(
                    'test_id' => $test_id,
                    'profile_id' => $profile_id,
                    'correct_responses' => $results['correctResponses'],
                    'incorrect_responses' => $results['incorrectResponses'],
                    'reaction_time' => $results['reactionTime'],
                    'score' => $results['score']
                );
                
                $result = save_divided_attention_results($divided_data);
                
                if ($result === false) {
                    global $wpdb;
                    error_log('Database error saving divided attention results: ' . $wpdb->last_error);
                } else {
                    error_log('Successfully saved divided attention results to database. Result: ' . $result);
                }
            }
            break;
            
        // Add other test types here
        default:
            wp_send_json_error('Invalid test type');
            return;
    }
    
    wp_send_json_success('Test results saved successfully');
}
add_action('wp_ajax_save_test_results', 'save_test_results_ajax');

// AJAX endpoint to get user IDs
function get_user_ids_ajax() {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
        return;
    }
    
    // Get current user ID
    $user_id = get_current_user_id();
    
    // Ensure user has test ID and profile ID
    $ids = ensure_user_ids($user_id);
    
    // Return the IDs
    wp_send_json_success($ids);
}
add_action('wp_ajax_get_user_ids', 'get_user_ids_ajax');
add_action('wp_ajax_nopriv_get_user_ids', 'get_user_ids_ajax');

// Add AJAX handler for saving test results
add_action('wp_ajax_save_test_results', 'handle_save_test_results');
add_action('wp_ajax_nopriv_save_test_results', 'handle_save_test_results');

function handle_save_test_results() {
    // Verify nonce
    check_ajax_referer('save_test_results', 'nonce');

    // Get the data
    $test_type = sanitize_text_field($_POST['test_type']);
    $results = json_decode(stripslashes($_POST['results']), true);
    
    // Validate data
    if (!$results || !is_array($results)) {
        wp_send_json_error('Invalid results data');
        return;
    }

    // Get current user ID
    $user_id = get_current_user_id();
    
    // Validate test type
    $valid_test_types = array('selective_attention_basic', 'selective_attention_extended', 'divided_attention');
    if (!in_array($test_type, $valid_test_types)) {
        wp_send_json_error('Invalid test type: ' . $test_type);
        return;
    }

    // Prepare data for database
    $data = array(
        'post_title'    => 'Test Results - ' . $test_type . ' - ' . date('Y-m-d H:i:s'),
        'post_type'     => 'test_result',
        'post_status'   => 'publish',
        'post_author'   => $user_id
    );

    // Insert the post
    $post_id = wp_insert_post($data);

    if ($post_id) {
        // Save test results as post meta
        update_post_meta($post_id, 'test_type', $test_type);
        update_post_meta($post_id, 'test_results', $results);
        update_post_meta($post_id, 'test_score', $results['score']);
        update_post_meta($post_id, 'test_accuracy', $results['accuracy']);
        update_post_meta($post_id, 'test_reaction_time', $results['reactionTime']);
        update_post_meta($post_id, 'test_timestamp', $results['timestamp']);
        update_post_meta($post_id, 'test_id', $results['testId']);
        
        wp_send_json_success(array('message' => 'Results saved successfully', 'post_id' => $post_id));
    } else {
        wp_send_json_error('Failed to save results');
    }
}

// Register Test Results post type
add_action('init', 'register_test_results_post_type');

function register_test_results_post_type() {
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

function save_divided_attention_results($data) {
    global $wpdb;
    
    // Validate required fields
    if (empty($data['test_id']) || empty($data['profile_id'])) {
        error_log('Missing required fields for divided attention results');
        return false;
    }
    
    // Prepare data for insertion
    $insert_data = array(
        'test_id' => sanitize_text_field($data['test_id']),
        'profile_id' => sanitize_text_field($data['profile_id']),
        'correct_responses' => intval($data['correct_responses']),
        'incorrect_responses' => intval($data['incorrect_responses']),
        'reaction_time' => floatval($data['reaction_time']),
        'score' => intval($data['score']),
        'test_date' => current_time('mysql')
    );
    
    // Insert data into the divided attention results table
    $result = $wpdb->insert(
        $wpdb->prefix . 'divided_attention_results',
        $insert_data,
        array('%s', '%s', '%d', '%d', '%f', '%d', '%s')
    );
    
    if ($result === false) {
        error_log('Failed to save divided attention results: ' . $wpdb->last_error);
        return false;
    }
    
    return $wpdb->insert_id;
}

// Create divided attention results table if it doesn't exist
function create_divided_attention_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'divided_attention_results';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        test_id varchar(50) NOT NULL,
        profile_id varchar(50) NOT NULL,
        correct_responses int(11) NOT NULL,
        incorrect_responses int(11) NOT NULL,
        reaction_time float NOT NULL,
        score int(11) NOT NULL,
        test_date datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY test_id (test_id),
        KEY profile_id (profile_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Run table creation on theme activation
add_action('after_switch_theme', 'create_divided_attention_table');
