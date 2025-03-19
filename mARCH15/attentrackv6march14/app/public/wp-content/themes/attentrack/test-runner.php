<?php
require_once('../../../wp-load.php');

// Ensure only administrators can access this
if (!current_user_can('administrator')) {
    wp_die('Access denied');
}

// Create a test user if it doesn't exist
$test_user_email = 'test.user@example.com';
$test_user = get_user_by('email', $test_user_email);

if (!$test_user) {
    $user_id = wp_create_user(
        'testuser',
        wp_generate_password(),
        $test_user_email
    );
    
    if (!is_wp_error($user_id)) {
        // Generate unique patient ID
        $patient_id = 'AT' . time() . rand(1000, 9999);
        update_user_meta($user_id, 'patient_id', $patient_id);
        echo "Test user created with ID: $user_id and patient ID: $patient_id<br>";
    } else {
        echo "Error creating test user: " . $user_id->get_error_message() . "<br>";
    }
} else {
    echo "Using existing test user with ID: " . $test_user->ID . "<br>";
}

// Get the test phase 0 page URL
$test_page = get_page_by_path('test-phase-0');
if ($test_page) {
    $test_url = get_permalink($test_page->ID);
    echo "Test URL: <a href='$test_url' target='_blank'>$test_url</a><br>";
    echo "<br>Instructions:<br>";
    echo "1. Click the link above to open the test page<br>";
    echo "2. Complete the test by responding to 'p' letters<br>";
    echo "3. After completion, check the results in the admin dashboard under 'AttenTrack DB' -> 'Phase 0 Verification'<br>";
} else {
    echo "Error: Test page not found. Please ensure the 'test-phase-0' page exists.";
}
?>
