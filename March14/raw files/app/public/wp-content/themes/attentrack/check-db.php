<?php
/*
Template Name: Database Check Template
*/

// Ensure only administrators can access this page
if (!current_user_can('administrator')) {
    wp_die('Access denied');
}

global $wpdb;

// Check usermeta table structure
$usermeta_structure = $wpdb->get_results("DESCRIBE {$wpdb->usermeta}");
echo "<h2>User Meta Table Structure</h2>";
echo "<pre>";
print_r($usermeta_structure);
echo "</pre>";

// Check if we have any patient data
$patient_meta = $wpdb->get_results("
    SELECT user_id, meta_key, meta_value 
    FROM {$wpdb->usermeta} 
    WHERE meta_key LIKE 'patient_%'
");

echo "<h2>Existing Patient Data</h2>";
echo "<pre>";
print_r($patient_meta);
echo "</pre>";

// Check WordPress options
$patient_options = $wpdb->get_results("
    SELECT option_name, option_value 
    FROM {$wpdb->options} 
    WHERE option_name LIKE '%patient%'
");

echo "<h2>Patient-related Options</h2>";
echo "<pre>";
print_r($patient_options);
echo "</pre>";
?>
