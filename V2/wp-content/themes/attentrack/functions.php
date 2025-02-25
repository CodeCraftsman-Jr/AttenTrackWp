// Include the Bootstrap Nav Walker
require_once get_template_directory() . '/includes/class-bootstrap-walker-nav-menu.php';

<?php
if (!defined('ABSPATH')) exit;

function attentrack_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));

    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'attentrack'),
        'footer' => __('Footer Menu', 'attentrack'),
    ));
}
add_action('after_setup_theme', 'attentrack_setup');

// Add Bootstrap classes to menu items
function attentrack_menu_classes($classes, $item, $args) {
    if ($args->theme_location == 'primary') {
        $classes[] = 'nav-item';
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'attentrack_menu_classes', 10, 3);

// Add Bootstrap classes to menu links
function attentrack_menu_link_classes($atts, $item, $args) {
    if ($args->theme_location == 'primary') {
        $atts['class'] = 'nav-link';
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'attentrack_menu_link_classes', 10, 3);

function attentrack_enqueue_scripts() {
    // Enqueue styles
    wp_enqueue_style('attentrack-style', get_stylesheet_uri());
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
    wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap');

    // Enqueue scripts
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
    wp_enqueue_script('attentrack-main', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0', true);
    
    // Enqueue test handler script only on test pages
    if (is_page_template('page-test.php') || is_singular('attention_test')) {
        wp_enqueue_script('attentrack-test-handler', get_template_directory_uri() . '/js/test-handler.js', array('jquery'), '1.0', true);
    }
}
add_action('wp_enqueue_scripts', 'attentrack_enqueue_scripts');

// Register Custom Post Types
function attentrack_register_post_types() {
    // Tests Post Type
    register_post_type('test', array(
        'labels' => array(
            'name' => __('Tests', 'attentrack'),
            'singular_name' => __('Test', 'attentrack'),
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-clipboard',
        'supports' => array('title', 'editor', 'thumbnail'),
        'rewrite' => array('slug' => 'tests'),
    ));

    // Results Post Type
    register_post_type('result', array(
        'labels' => array(
            'name' => __('Results', 'attentrack'),
            'singular_name' => __('Result', 'attentrack'),
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-chart-bar',
        'supports' => array('title', 'editor'),
        'rewrite' => array('slug' => 'results'),
    ));
}
add_action('init', 'attentrack_register_post_types');

// Add custom roles and capabilities
function attentrack_add_roles() {
    add_role('patient', 'Patient', array(
        'read' => true,
        'take_tests' => true,
        'view_results' => true,
    ));
}
add_action('init', 'attentrack_add_roles');

// AJAX handlers for test functionality
function attentrack_get_unique_test_id() {
    check_ajax_referer('get_unique_test_id');
    
    $test_id = uniqid('test_');
    wp_send_json_success(array('test_id' => $test_id));
}
add_action('wp_ajax_get_unique_test_id', 'attentrack_get_unique_test_id');

function attentrack_save_test_results() {
    check_ajax_referer('save_test_results');
    
    $test_id = sanitize_text_field($_POST['test_id']);
    $phase = intval($_POST['phase']);
    $responses = json_decode(stripslashes($_POST['responses']), true);
    
    if (!$responses || !is_array($responses)) {
        wp_send_json_error(array('message' => 'Invalid response data'));
        return;
    }
    
    $total_responses = count($responses);
    $correct_responses = array_filter($responses, function($r) { return $r['correct']; });
    $accuracy = ($total_responses > 0) ? (count($correct_responses) / $total_responses) * 100 : 0;
    $avg_response_time = array_reduce($responses, function($carry, $r) { return $carry + $r['responseTime']; }, 0) / $total_responses;
    
    $post_data = array(
        'post_title' => sprintf('Test Results - Phase %d - %s', $phase, current_time('mysql')),
        'post_type' => 'test_result',
        'post_status' => 'publish',
        'post_author' => get_current_user_id()
    );
    
    $post_id = wp_insert_post($post_data);
    
    if ($post_id) {
        update_post_meta($post_id, 'test_id', $test_id);
        update_post_meta($post_id, 'test_phase', $phase);
        update_post_meta($post_id, 'responses', $responses);
        update_post_meta($post_id, 'total_responses', $total_responses);
        update_post_meta($post_id, 'correct_responses', count($correct_responses));
        update_post_meta($post_id, 'accuracy', $accuracy);
        update_post_meta($post_id, 'avg_response_time', $avg_response_time);
        
        wp_send_json_success(array('post_id' => $post_id));
    } else {
        wp_send_json_error(array('message' => 'Failed to save results'));
    }
}
add_action('wp_ajax_save_test_results', 'attentrack_save_test_results');
