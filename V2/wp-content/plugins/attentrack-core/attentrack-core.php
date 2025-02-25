<?php
/*
Plugin Name: AttenTrack Core
Plugin URI: http://attentrack.com
Description: Core functionality for the AttenTrack attention assessment platform
Version: 1.0
Author: AttenTrack Team
Author URI: http://attentrack.com
License: GPL v2 or later
*/

if (!defined('ABSPATH')) exit;

class AttenTrack_Core {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'register_test_post_types'));
        add_action('init', array($this, 'register_custom_endpoints'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('activate_plugin', array($this, 'create_database_tables'));
        add_action('admin_init', array($this, 'check_database_tables'));
    }

    public function create_database_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Test Results Table
        $test_results_table = $wpdb->prefix . 'attentrack_test_results';
        $sql_test_results = "CREATE TABLE IF NOT EXISTS $test_results_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            test_id bigint(20) NOT NULL,
            test_date datetime DEFAULT CURRENT_TIMESTAMP,
            total_score decimal(5,2) NOT NULL,
            duration int NOT NULL,
            accuracy decimal(5,2) NOT NULL,
            avg_response_time decimal(10,2) NOT NULL,
            meta_data longtext,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY test_id (test_id)
        ) $charset_collate;";

        // Phase Results Table
        $phase_results_table = $wpdb->prefix . 'attentrack_phase_results';
        $sql_phase_results = "CREATE TABLE IF NOT EXISTS $phase_results_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            test_result_id bigint(20) NOT NULL,
            phase_number int NOT NULL,
            phase_type varchar(50) NOT NULL,
            score decimal(5,2) NOT NULL,
            accuracy decimal(5,2) NOT NULL,
            response_time decimal(10,2) NOT NULL,
            responses longtext,
            PRIMARY KEY  (id),
            KEY test_result_id (test_result_id)
        ) $charset_collate;";

        // User Progress Table
        $user_progress_table = $wpdb->prefix . 'attentrack_user_progress';
        $sql_user_progress = "CREATE TABLE IF NOT EXISTS $user_progress_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            test_type varchar(50) NOT NULL,
            total_tests int NOT NULL DEFAULT 0,
            avg_score decimal(5,2) NOT NULL DEFAULT 0,
            last_test_date datetime DEFAULT NULL,
            improvement_rate decimal(5,2) DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_test_type (user_id, test_type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_test_results);
        dbDelta($sql_phase_results);
        dbDelta($sql_user_progress);
    }

    public function check_database_tables() {
        if (get_option('attentrack_db_version') !== '1.0') {
            $this->create_database_tables();
            update_option('attentrack_db_version', '1.0');
        }
    }

    public function register_test_post_types() {
        register_post_type('attention_test', array(
            'labels' => array(
                'name' => __('Attention Tests'),
                'singular_name' => __('Attention Test'),
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'custom-fields'),
            'menu_icon' => 'dashicons-clipboard',
        ));

        register_post_type('test_result', array(
            'labels' => array(
                'name' => __('Test Results'),
                'singular_name' => __('Test Result'),
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => array('title', 'custom-fields'),
            'menu_icon' => 'dashicons-chart-bar',
        ));
    }

    public function register_custom_endpoints() {
        add_rewrite_rule(
            'test/([^/]+)/?$',
            'index.php?attention_test=$matches[1]',
            'top'
        );
        add_rewrite_rule(
            'results/([^/]+)/?$',
            'index.php?test_result=$matches[1]',
            'top'
        );
    }

    public function register_rest_routes() {
        // Get Test Configuration
        register_rest_route('attentrack/v1', '/test/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_test_configuration'),
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ));

        // Save Test Result
        register_rest_route('attentrack/v1', '/save-result', array(
            'methods' => 'POST',
            'callback' => array($this, 'save_test_result'),
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ));

        // Get User Progress
        register_rest_route('attentrack/v1', '/user-progress', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_user_progress'),
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ));
    }

    public function get_test_configuration($request) {
        $test_id = $request['id'];
        $test_post = get_post($test_id);
        
        if (!$test_post || $test_post->post_type !== 'attention_test') {
            return new WP_Error('invalid_test', 'Invalid test ID', array('status' => 404));
        }

        $config = array(
            'id' => $test_id,
            'title' => $test_post->post_title,
            'type' => get_post_meta($test_id, 'test_type', true),
            'duration' => (int)get_post_meta($test_id, 'test_duration', true),
            'phases' => get_post_meta($test_id, 'test_phases', true),
        );

        return new WP_REST_Response($config, 200);
    }

    public function save_test_result($request) {
        global $wpdb;
        $user_id = get_current_user_id();
        $data = $request->get_json_params();
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Insert main test result
            $test_result = $wpdb->insert(
                $wpdb->prefix . 'attentrack_test_results',
                array(
                    'user_id' => $user_id,
                    'test_id' => $data['testId'],
                    'total_score' => $data['results']['overallAccuracy'],
                    'duration' => $data['duration'],
                    'accuracy' => $data['results']['overallAccuracy'],
                    'avg_response_time' => $data['results']['overallResponseTime'],
                    'meta_data' => json_encode($data['results'])
                ),
                array('%d', '%d', '%f', '%d', '%f', '%f', '%s')
            );

            if ($test_result === false) {
                throw new Exception('Failed to save test result');
            }

            $test_result_id = $wpdb->insert_id;

            // Insert phase results
            foreach ($data['results']['phaseResults'] as $phase) {
                $phase_result = $wpdb->insert(
                    $wpdb->prefix . 'attentrack_phase_results',
                    array(
                        'test_result_id' => $test_result_id,
                        'phase_number' => $phase['phaseNumber'],
                        'phase_type' => $phase['title'],
                        'score' => $phase['accuracy'],
                        'accuracy' => $phase['accuracy'],
                        'response_time' => $phase['averageResponseTime'],
                        'responses' => json_encode($phase['responses'])
                    ),
                    array('%d', '%d', '%s', '%f', '%f', '%f', '%s')
                );

                if ($phase_result === false) {
                    throw new Exception('Failed to save phase result');
                }
            }

            // Update user progress
            $test_type = get_post_meta($data['testId'], 'test_type', true);
            $this->update_user_progress($user_id, $test_type, $data['results']['overallAccuracy']);

            $wpdb->query('COMMIT');

            return new WP_REST_Response(array(
                'success' => true,
                'result_id' => $test_result_id
            ), 200);

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('save_failed', $e->getMessage(), array('status' => 500));
        }
    }

    private function update_user_progress($user_id, $test_type, $score) {
        global $wpdb;
        $table = $wpdb->prefix . 'attentrack_user_progress';

        // Get current progress
        $progress = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d AND test_type = %s",
            $user_id,
            $test_type
        ));

        if ($progress) {
            // Calculate new average and improvement
            $new_total = $progress->total_tests + 1;
            $new_avg = (($progress->avg_score * $progress->total_tests) + $score) / $new_total;
            $improvement = $score - $progress->avg_score;

            // Update existing record
            $wpdb->update(
                $table,
                array(
                    'total_tests' => $new_total,
                    'avg_score' => $new_avg,
                    'last_test_date' => current_time('mysql'),
                    'improvement_rate' => $improvement
                ),
                array('id' => $progress->id),
                array('%d', '%f', '%s', '%f'),
                array('%d')
            );
        } else {
            // Insert new record
            $wpdb->insert(
                $table,
                array(
                    'user_id' => $user_id,
                    'test_type' => $test_type,
                    'total_tests' => 1,
                    'avg_score' => $score,
                    'last_test_date' => current_time('mysql'),
                    'improvement_rate' => 0
                ),
                array('%d', '%s', '%d', '%f', '%s', '%f')
            );
        }
    }

    public function get_user_progress($request) {
        global $wpdb;
        $user_id = get_current_user_id();
        
        $progress = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}attentrack_user_progress WHERE user_id = %d",
            $user_id
        ));

        return new WP_REST_Response($progress, 200);
    }
}

// Initialize the plugin
AttenTrack_Core::get_instance();
