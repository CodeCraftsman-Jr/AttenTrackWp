<?php
/**
 * Pre-deployment check script
 * Run this before deploying to production
 */

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('PHP 7.4 or higher required. Current version: ' . PHP_VERSION);
}

// Check MySQL version
$mysql_version = mysqli_get_client_info();
if (version_compare($mysql_version, '5.7.0', '<')) {
    die('MySQL 5.7 or higher required. Current version: ' . $mysql_version);
}

// Check required PHP extensions
$required_extensions = [
    'mysqli',
    'curl',
    'gd',
    'json',
    'mbstring',
    'xml',
    'zip'
];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        die("Required PHP extension not found: $ext");
    }
}

// Check file permissions
$theme_dir = __DIR__;
$writable_dirs = [
    $theme_dir . '/assets',
    $theme_dir . '/assets/images',
    WP_CONTENT_DIR . '/uploads'
];

foreach ($writable_dirs as $dir) {
    if (!is_writable($dir)) {
        die("Directory not writable: $dir");
    }
}

// Check for development URLs
$theme_files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($theme_dir)
);

$dev_urls = [
    'localhost',
    '.local',
    '127.0.0.1',
    'attentrackv2.local'
];

foreach ($theme_files as $file) {
    if ($file->isFile() && in_array($file->getExtension(), ['php', 'js', 'css'])) {
        $content = file_get_contents($file->getPathname());
        foreach ($dev_urls as $url) {
            if (stripos($content, $url) !== false) {
                echo "WARNING: Found development URL '$url' in " . $file->getPathname() . "\n";
            }
        }
    }
}

// Check database tables
$required_tables = [
    'wp_attentrack_patient_details',
    'wp_attentrack_selective_results',
    'wp_attentrack_divided_results',
    'wp_attentrack_alternative_results',
    'wp_attentrack_extended_results'
];

global $wpdb;
foreach ($required_tables as $table) {
    $table_name = $wpdb->prefix . str_replace('wp_', '', $table);
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        die("Required table not found: $table_name");
    }
}

echo "Pre-deployment checks completed successfully!\n";
echo "Please review the deployment guide for final steps.\n";
