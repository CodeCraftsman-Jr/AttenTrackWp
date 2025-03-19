<?php
/**
 * Template Name: Database Test
 * Description: A template to test database connectivity and functionality
 */

get_header(); ?>

<div class="container">
    <div class="test-section">
        <h2>Database Connection Test</h2>
        
        <?php
        // Test database connection
        global $wpdb;
        $test_results_table = $wpdb->prefix . 'test_results';
        $test_sessions_table = $wpdb->prefix . 'test_sessions';
        
        echo '<div class="status-section">';
        
        // Check if tables exist
        $results_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$test_results_table'") === $test_results_table;
        $sessions_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$test_sessions_table'") === $test_sessions_table;
        
        if ($results_table_exists) {
            echo '<div class="status-success">✓ Test Results table exists</div>';
        } else {
            echo '<div class="status-error">✗ Test Results table is missing</div>';
            // Try to create the table
            require_once get_template_directory() . '/inc/database-setup.php';
            initialize_database_tables();
            
            // Check again
            if ($wpdb->get_var("SHOW TABLES LIKE '$test_results_table'") === $test_results_table) {
                echo '<div class="status-success">✓ Test Results table created successfully</div>';
            } else {
                echo '<div class="status-error">✗ Failed to create Test Results table</div>';
                echo '<div class="error-details">Error: ' . $wpdb->last_error . '</div>';
            }
        }
        
        if ($sessions_table_exists) {
            echo '<div class="status-success">✓ Test Sessions table exists</div>';
        } else {
            echo '<div class="status-error">✗ Test Sessions table is missing</div>';
        }
        
        echo '</div>'; // End status-section
        ?>
        
        <div class="test-sections">
            <div class="test-section">
                <h3>Database Status</h3>
                <button id="check-db" class="button">Check Database Status</button>
                <div id="db-status" class="status-display"></div>
            </div>

            <div class="test-section">
                <h3>Save Test Results</h3>
                <button id="save-test" class="button">Test Save Results</button>
                <div id="save-status" class="status-display"></div>
            </div>

            <div class="test-section">
                <h3>Retrieved Results</h3>
                <div id="retrieve-status" class="status-display"></div>
            </div>
        </div>
    </div>
</div>

<style>
.test-section {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.test-sections {
    margin-top: 30px;
}

.status-section {
    margin: 20px 0;
}

.status-success {
    color: #155724;
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    padding: 15px;
    border-radius: 4px;
    margin-top: 10px;
}

.status-error {
    color: #721c24;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    padding: 15px;
    border-radius: 4px;
    margin-top: 10px;
}

.status-info {
    padding: 10px;
    margin: 5px 0;
    background: #e2e3e5;
    color: #383d41;
    border-radius: 4px;
}

.error-details {
    margin: 5px 20px;
    padding: 10px;
    background: #f8f9fa;
    border-left: 3px solid #dc3545;
    font-family: monospace;
}

.button {
    background: #007bff;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.button:hover {
    background: #0056b3;
}

.status-display {
    margin-top: 15px;
    padding: 15px;
    border-radius: 4px;
    background: #fff;
    min-height: 50px;
}

pre {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
    overflow-x: auto;
    margin-top: 10px;
    font-size: 13px;
    line-height: 1.4;
    white-space: pre-wrap;
}

h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #2c3e50;
}
</style>

<?php get_footer(); ?>
