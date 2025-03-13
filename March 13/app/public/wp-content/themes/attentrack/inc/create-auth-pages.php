<?php
function create_authentication_pages() {
    // Create Sign In page
    $signin_page = array(
        'post_title'    => 'Sign In',
        'post_content'  => '',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_name'     => 'sign-in'
    );
    
    // Only create the page if it doesn't exist
    if (!get_page_by_path('sign-in')) {
        wp_insert_post($signin_page);
    }

    // Create Sign Up page
    $signup_page = array(
        'post_title'    => 'Sign Up',
        'post_content'  => '',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_name'     => 'sign-up'
    );
    
    // Only create the page if it doesn't exist
    if (!get_page_by_path('sign-up')) {
        wp_insert_post($signup_page);
    }

    // Create Dashboard page
    $dashboard_page = array(
        'post_title'    => 'Dashboard',
        'post_content'  => '',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_name'     => 'dashboard'
    );
    
    // Only create the page if it doesn't exist
    if (!get_page_by_path('dashboard')) {
        wp_insert_post($dashboard_page);
    }
}

// Run the function when the theme is activated
add_action('after_switch_theme', 'create_authentication_pages');
?>
