<?php
require_once('wp-load.php');

// Array of pages to create
$instruction_pages = array(
    array(
        'title' => 'Selective Attention Test Instructions',
        'slug' => 'selective-test-instructions',
        'template' => 'selective-test-instructions-template.php'
    ),
    array(
        'title' => 'Divided Attention Test Instructions',
        'slug' => 'divided-test-instructions',
        'template' => 'divided-test-instructions-template.php'
    ),
    array(
        'title' => 'Alternative Attention Test Instructions',
        'slug' => 'alternative-test-instructions',
        'template' => 'alternative-test-instructions-template.php'
    ),
    array(
        'title' => 'Extended Attention Test Instructions',
        'slug' => 'extended-test-instructions',
        'template' => 'extended-test-instructions-template.php'
    )
);

foreach ($instruction_pages as $page) {
    // Check if page already exists
    $existing_page = get_page_by_path($page['slug']);
    
    if (!$existing_page) {
        // Create the page
        $page_data = array(
            'post_title' => $page['title'],
            'post_name' => $page['slug'],
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '',
        );
        
        $page_id = wp_insert_post($page_data);
        
        if ($page_id) {
            // Set the page template
            update_post_meta($page_id, '_wp_page_template', $page['template']);
            echo "Created page: " . $page['title'] . "\n";
        }
    } else {
        // Update existing page template
        update_post_meta($existing_page->ID, '_wp_page_template', $page['template']);
        echo "Updated template for existing page: " . $page['title'] . "\n";
    }
}

echo "Done creating/updating instruction pages.\n";
