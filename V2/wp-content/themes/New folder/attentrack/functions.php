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

function attentrack_enqueue_scripts() {
    // Enqueue styles
    wp_enqueue_style('attentrack-style', get_stylesheet_uri());
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
    wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap');

    // Enqueue scripts
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
    wp_enqueue_script('attentrack-main', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0', true);
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
