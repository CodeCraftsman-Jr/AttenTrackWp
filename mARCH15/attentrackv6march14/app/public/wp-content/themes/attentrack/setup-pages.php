<?php
// Load WordPress with absolute path
$wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
require_once($wp_load_path);

// Ensure we're in WordPress context
if (!defined('ABSPATH')) {
    die('Direct access not allowed');
}

echo "Starting setup...\n";

// Create About App page if it doesn't exist
if (!get_page_by_path('about-app')) {
    $about_page = array(
        'post_title'    => 'About App',
        'post_name'     => 'about-app',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_content'  => 'AttenTrack helps you understand and improve your attention levels.',
        'page_template' => 'page-about-app.php'
    );
    $about_id = wp_insert_post($about_page);
    update_post_meta($about_id, '_wp_page_template', 'page-about-app.php');
    echo "Created About App page\n";
}

// Create Contact Us page if it doesn't exist
if (!get_page_by_path('contact-us')) {
    $contact_page = array(
        'post_title'    => 'Contact Us',
        'post_name'     => 'contact-us',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_content'  => 'Get in touch with us.',
        'page_template' => 'page-contact-us.php'
    );
    $contact_id = wp_insert_post($contact_page);
    update_post_meta($contact_id, '_wp_page_template', 'page-contact-us.php');
    echo "Created Contact Us page\n";
}

// Create primary menu if it doesn't exist
$menu_name = 'Primary Menu';
$menu_exists = wp_get_nav_menu_object($menu_name);

if (!$menu_exists) {
    $menu_id = wp_create_nav_menu($menu_name);
    
    // Add menu items
    wp_update_nav_menu_item($menu_id, 0, array(
        'menu-item-title' => 'Home',
        'menu-item-url' => home_url('/'),
        'menu-item-status' => 'publish',
        'menu-item-type' => 'custom',
    ));
    
    wp_update_nav_menu_item($menu_id, 0, array(
        'menu-item-title' => 'About App',
        'menu-item-url' => home_url('/about-app'),
        'menu-item-status' => 'publish',
        'menu-item-type' => 'custom',
    ));
    
    wp_update_nav_menu_item($menu_id, 0, array(
        'menu-item-title' => 'About Us',
        'menu-item-url' => 'https://svcet.ac.in/',
        'menu-item-status' => 'publish',
        'menu-item-type' => 'custom',
    ));
    
    wp_update_nav_menu_item($menu_id, 0, array(
        'menu-item-title' => 'Contact Us',
        'menu-item-url' => home_url('/contact-us'),
        'menu-item-status' => 'publish',
        'menu-item-type' => 'custom',
    ));
    
    // Assign menu to primary location
    $locations = get_theme_mod('nav_menu_locations');
    $locations['primary'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
    
    echo "Created and set up primary menu\n";
}

// Create Test Data Insertion page
$test_data_page = array(
    'post_title'    => 'Test Data Insertion',
    'post_name'     => 'test-data-insertion',
    'post_status'   => 'publish',
    'post_type'     => 'page',
    'post_content'  => '',
    'post_author'   => 1,
    'ping_status'   => 'closed',
    'comment_status'=> 'closed'
);

$existing_page = get_page_by_path('test-data-insertion');
if ($existing_page) {
    $test_data_id = $existing_page->ID;
    echo "Test Data Insertion page already exists with ID: " . $test_data_id . "\n";
} else {
    $test_data_id = wp_insert_post($test_data_page);
    if (!is_wp_error($test_data_id)) {
        update_post_meta($test_data_id, '_wp_page_template', 'templates/test-data-insertion.php');
        echo "Test Data Insertion page created with ID: " . $test_data_id . "\n";
    } else {
        echo "Error creating Test Data Insertion page: " . $test_data_id->get_error_message() . "\n";
    }
}

// Create Test Phase 0 page
$test_phase_page = array(
    'post_title'    => 'Test Phase 0',
    'post_name'     => 'test-phase-0',
    'post_status'   => 'publish',
    'post_type'     => 'page',
    'post_content'  => '',
    'post_author'   => 1,
    'ping_status'   => 'closed',
    'comment_status'=> 'closed'
);

$existing_page = get_page_by_path('test-phase-0');
if ($existing_page) {
    $test_phase_id = $existing_page->ID;
    echo "Test Phase 0 page already exists with ID: " . $test_phase_id . "\n";
} else {
    $test_phase_id = wp_insert_post($test_phase_page);
    if (!is_wp_error($test_phase_id)) {
        update_post_meta($test_phase_id, '_wp_page_template', 'templates/test-phase-0-template.php');
        echo "Test Phase 0 page created with ID: " . $test_phase_id . "\n";
    } else {
        echo "Error creating Test Phase 0 page: " . $test_phase_id->get_error_message() . "\n";
    }
}

// Initialize test results table
global $wpdb;
$table_name = $wpdb->prefix . 'test_results';

echo "Creating/updating test results table...\n";

$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    test_id varchar(50) NOT NULL,
    test_type varchar(50) NOT NULL,
    test_phase int(11) NOT NULL,
    score float NOT NULL DEFAULT 0,
    accuracy float NOT NULL DEFAULT 0,
    reaction_time float NOT NULL DEFAULT 0,
    missed_responses int(11) NOT NULL DEFAULT 0,
    false_alarms int(11) NOT NULL DEFAULT 0,
    total_letters int(11) NOT NULL DEFAULT 0,
    p_letters int(11) NOT NULL DEFAULT 0,
    responses longtext,
    test_date datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY test_id (test_id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

// Set proper encoding for the responses column
$wpdb->query("ALTER TABLE $table_name MODIFY responses longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

// Verify table exists
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
if ($table_exists) {
    echo "Test results table created/updated successfully\n";
} else {
    echo "Error: Failed to create test results table\n";
}

echo "Setup complete!\n";
?>
