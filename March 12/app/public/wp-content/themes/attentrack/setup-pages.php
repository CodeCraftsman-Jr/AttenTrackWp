<?php
// Bootstrap WordPress
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

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

echo "Setup complete!\n";
?>
