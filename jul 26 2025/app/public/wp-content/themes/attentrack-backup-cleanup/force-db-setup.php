<?php
/**
 * Template Name: Force DB Setup
 */

// Only allow administrators to run this script
if (!current_user_can('administrator')) {
    wp_die('You do not have permission to access this page.');
}

get_header();

// Include database setup file
require_once get_template_directory() . '/inc/database-setup.php';

// Force table creation
create_attentrack_tables();

// Test insertion into extended results table
$test_id = 'TEST-' . time();
$profile_id = 'P-TEST-' . time();

$test_data = array(
    'test_id' => $test_id,
    'profile_id' => $profile_id,
    'phase' => 1,
    'total_letters' => 100,
    'p_letters' => 20,
    'correct_responses' => 18,
    'incorrect_responses' => 2,
    'reaction_time' => 0.75
);

$result = save_extended_results($test_data);

// Get database prefix
global $wpdb;
$table_name = $wpdb->prefix . 'attentrack_extended_results';

// Check if the table exists
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3>Database Setup Results</h3>
                </div>
                <div class="card-body">
                    <h4>Table Creation</h4>
                    <p>Database tables have been created or updated.</p>
                    
                    <h4>Table Status</h4>
                    <p>Extended Results Table Exists: <strong><?php echo $table_exists ? 'Yes' : 'No'; ?></strong></p>
                    
                    <h4>Test Insertion</h4>
                    <p>Test ID: <?php echo $test_id; ?></p>
                    <p>Profile ID: <?php echo $profile_id; ?></p>
                    <p>Insertion Result: <strong><?php echo $result !== false ? 'Success' : 'Failed - ' . $wpdb->last_error; ?></strong></p>
                    
                    <h4>Table Structure</h4>
                    <pre><?php
                    if ($table_exists) {
                        $structure = $wpdb->get_results("DESCRIBE {$table_name}");
                        print_r($structure);
                    } else {
                        echo "Table does not exist";
                    }
                    ?></pre>
                    
                    <h4>Table Contents</h4>
                    <pre><?php
                    if ($table_exists) {
                        $contents = $wpdb->get_results("SELECT * FROM {$table_name} LIMIT 10");
                        print_r($contents);
                    } else {
                        echo "Table does not exist";
                    }
                    ?></pre>
                </div>
                <div class="card-footer">
                    <a href="<?php echo home_url('/dashboard'); ?>" class="btn btn-primary">Return to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
