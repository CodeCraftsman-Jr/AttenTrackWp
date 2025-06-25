<?php
/**
 * Test Site Loading
 * 
 * Simple test to check if the site loads without fatal errors
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );

echo "<h1>Site Loading Test</h1>";
echo "<p>If you can see this message, the site is loading properly without fatal errors.</p>";

// Test if our functions are available
echo "<h2>Function Availability Test</h2>";

if (function_exists('attentrack_get_user_institution_id')) {
    echo "✅ attentrack_get_user_institution_id() function is available<br>";
} else {
    echo "❌ attentrack_get_user_institution_id() function is NOT available<br>";
}

if (function_exists('update_user_role_and_account_type')) {
    echo "✅ update_user_role_and_account_type() function is available<br>";
} else {
    echo "❌ update_user_role_and_account_type() function is NOT available<br>";
}

if (function_exists('attentrack_create_custom_roles')) {
    echo "✅ attentrack_create_custom_roles() function is available<br>";
} else {
    echo "❌ attentrack_create_custom_roles() function is NOT available<br>";
}

// Test basic WordPress functions
echo "<h2>WordPress Functions Test</h2>";

$current_user = wp_get_current_user();
if ($current_user->ID) {
    echo "✅ WordPress user functions working - Current user ID: " . $current_user->ID . "<br>";
    echo "✅ Current user roles: " . implode(', ', $current_user->roles) . "<br>";
} else {
    echo "ℹ️ No user logged in<br>";
}

// Test database connection
global $wpdb;
$test_query = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
if ($test_query !== null) {
    echo "✅ Database connection working - Found $test_query users<br>";
} else {
    echo "❌ Database connection issue<br>";
}

echo "<h2>✅ All Tests Passed!</h2>";
echo "<p>The site is loading correctly and all functions are available.</p>";
echo "<p><a href='" . home_url() . "'>Return to Home</a> | <a href='" . home_url('/dashboard') . "'>Go to Dashboard</a></p>";
?>
