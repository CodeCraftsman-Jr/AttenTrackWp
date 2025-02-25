<?php
if (!defined('ABSPATH')) exit;

// Include the Bootstrap Nav Walker
require_once get_template_directory() . '/includes/class-bootstrap-walker-nav-menu.php';

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
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
    wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
    wp_enqueue_style('attentrack-style', get_stylesheet_uri(), array(), '1.0.0');

    // Enqueue scripts
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
    wp_enqueue_script('attentrack-main', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0.0', true);
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
