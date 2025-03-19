<?php
// Load WordPress
require_once('wp-load.php');

// Create About App page
$about_page = array(
    'post_title'    => 'About App',
    'post_status'   => 'publish',
    'post_type'     => 'page',
    'post_content'  => '',
    'page_template' => 'page-templates/about-app.php'
);
$about_page_id = wp_insert_post($about_page);

// Create Contact Us page
$contact_page = array(
    'post_title'    => 'Contact Us',
    'post_status'   => 'publish',
    'post_type'     => 'page',
    'post_content'  => '',
    'page_template' => 'page-templates/contact-us.php'
);
$contact_page_id = wp_insert_post($contact_page);

echo "Pages created successfully!\n";
echo "About App page ID: " . $about_page_id . "\n";
echo "Contact Us page ID: " . $contact_page_id . "\n";
?>
