<?php
if (!defined('ABSPATH')) exit;

class AttenTrack_Reporting {
    private static $instance = null;
    private $charts_loaded = false;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'register_endpoints'));
        add_action('admin_menu', array($this, 'add_reports_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_get_report_data', array($this, 'ajax_get_report_data'));
        add_action('wp_ajax_export_report', array($this, 'ajax_export_report'));
    }

    public function register_endpoints() {
        register_rest_route('attentrack/v1', '/reports/(?P<user_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_user_reports'),
            'permission_callback' => array($this, 'check_report_permissions'),
            'args' => array(
                'user_id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
            ),
        ));
    }

    public function check_report_permissions($request) {
        $user_id = $request['user_id'];
        $current_user = wp_get_current_user();

        // Admin can access all reports
        if (current_user_can('view_all_results')) {
            return true;
        }

        // Therapists can access their patients' reports
        if (current_user_can('view_assigned_results')) {
            $assigned_therapist = get_user_meta($user_id, 'assigned_therapist', true);
            if ($assigned_therapist == $current_user->ID) {
                return true;
            }
        }

        // Patients can access their own reports
        if ($user_id == $current_user->ID && current_user_can('view_own_results')) {
            return true;
        }

        // Guardians can access their dependents' reports
        if (current_user_can('view_dependent_results')) {
            $dependents = get_user_meta($current_user->ID, 'dependent_patients', true);
            if (is_array($dependents) && in_array($user_id, $dependents)) {
                return true;
            }
        }

        return false;
    }

    public function add_reports_menu() {
        add_menu_page(
            __('AttenTrack Reports', 'attentrack'),
            __('Reports', 'attentrack'),
            'read',
            'attentrack-reports',
            array($this, 'render_reports_page'),
            'dashicons-chart-area',
            30
        );
    }

    public function enqueue_assets() {
        if (!$this->charts_loaded) {
            wp_enqueue_script(
                'chart-js',
                'https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js',
                array(),
                '3.7.0',
                true
            );
            $this->charts_loaded = true;
        }

        wp_enqueue_script(
            'attentrack-reports',
            plugins_url('js/reports.js', dirname(__FILE__)),
            array('jquery', 'chart-js'),
            ATTENTRACK_VERSION,
            true
        );

        wp_enqueue_style(
            'attentrack-reports',
            plugins_url('css/reports.css', dirname(__FILE__)),
            array(),
            ATTENTRACK_VERSION
        );

        wp_localize_script('attentrack-reports', 'attentrackReports', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('attentrack_reports'),
            'i18n' => array(
                'loading' => __('Loading...', 'attentrack'),
                'error' => __('Error loading data', 'attentrack'),
                'noData' => __('No data available', 'attentrack'),
            )
        ));
    }

    public function enqueue_admin_assets($hook) {
        if ('toplevel_page_attentrack-reports' !== $hook) {
            return;
        }
        $this->enqueue_assets();
    }

    public function render_reports_page() {
        if (!current_user_can('read')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $current_user = wp_get_current_user();
        $user_role = $current_user->roles[0];
        ?>
        <div class="wrap attentrack-reports">
            <h1><?php _e('AttenTrack Reports', 'attentrack'); ?></h1>

            <?php if (current_user_can('view_all_results') || current_user_can('view_assigned_results')): ?>
            <div class="user-selector">
                <label for="user-select"><?php _e('Select User:', 'attentrack'); ?></label>
                <select id="user-select" class="widefat">
                    <?php
                    if (current_user_can('view_all_results')) {
                        $users = get_users(array('role' => 'patient'));
                    } else {
                        $users = AttenTrack_User_Roles::get_instance()->get_therapist_patients($current_user->ID);
                    }
                    foreach ($users as $user) {
                        echo sprintf(
                            '<option value="%d">%s (%s)</option>',
                            $user->ID,
                            esc_html($user->display_name),
                            esc_html(get_user_meta($user->ID, 'patient_id', true))
                        );
                    }
                    ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="report-controls">
                <div class="date-range">
                    <label for="date-from"><?php _e('From:', 'attentrack'); ?></label>
                    <input type="date" id="date-from" name="date-from">
                    
                    <label for="date-to"><?php _e('To:', 'attentrack'); ?></label>
                    <input type="date" id="date-to" name="date-to">
                </div>

                <div class="test-type">
                    <label for="test-type"><?php _e('Test Type:', 'attentrack'); ?></label>
                    <select id="test-type" name="test-type">
                        <option value="all"><?php _e('All Tests', 'attentrack'); ?></option>
                        <option value="sustained"><?php _e('Sustained Attention', 'attentrack'); ?></option>
                        <option value="selective"><?php _e('Selective Attention', 'attentrack'); ?></option>
                        <option value="divided"><?php _e('Divided Attention', 'attentrack'); ?></option>
                        <option value="switching"><?php _e('Attention Switching', 'attentrack'); ?></option>
                    </select>
                </div>

                <button id="update-report" class="button button-primary">
                    <?php _e('Update Report', 'attentrack'); ?>
                </button>

                <button id="export-report" class="button">
                    <?php _e('Export Data', 'attentrack'); ?>
                </button>
            </div>

            <div class="report-container">
                <div class="report-section performance-trends">
                    <h2><?php _e('Performance Trends', 'attentrack'); ?></h2>
                    <canvas id="performance-chart"></canvas>
                </div>

                <div class="report-section response-times">
                    <h2><?php _e('Response Times', 'attentrack'); ?></h2>
                    <canvas id="response-chart"></canvas>
                </div>

                <div class="report-section error-analysis">
                    <h2><?php _e('Error Analysis', 'attentrack'); ?></h2>
                    <canvas id="error-chart"></canvas>
                </div>

                <div class="report-section test-summary">
                    <h2><?php _e('Test Summary', 'attentrack'); ?></h2>
                    <div id="summary-table"></div>
                </div>
            </div>
        </div>
        <?php
    }

    public function get_user_reports($request) {
        $user_id = $request['user_id'];
        $from_date = $request->get_param('from_date');
        $to_date = $request->get_param('to_date');
        $test_type = $request->get_param('test_type');

        global $wpdb;

        // Get test results
        $results_table = $wpdb->prefix . 'attentrack_test_results';
        $phases_table = $wpdb->prefix . 'attentrack_phase_results';

        $query = $wpdb->prepare(
            "SELECT r.*, p.* 
            FROM $results_table r 
            LEFT JOIN $phases_table p ON r.id = p.result_id 
            WHERE r.user_id = %d",
            $user_id
        );

        if ($from_date) {
            $query .= $wpdb->prepare(" AND r.test_date >= %s", $from_date);
        }
        if ($to_date) {
            $query .= $wpdb->prepare(" AND r.test_date <= %s", $to_date);
        }
        if ($test_type && $test_type !== 'all') {
            $query .= $wpdb->prepare(" AND r.test_type = %s", $test_type);
        }

        $query .= " ORDER BY r.test_date ASC";
        $results = $wpdb->get_results($query);

        if (empty($results)) {
            return new WP_REST_Response(array(
                'message' => __('No test results found for the specified criteria.', 'attentrack')
            ), 404);
        }

        // Process results
        $processed_data = $this->process_test_results($results);

        return new WP_REST_Response($processed_data, 200);
    }

    private function process_test_results($results) {
        $performance_data = array();
        $response_times = array();
        $error_data = array();
        $summary = array();

        foreach ($results as $result) {
            $date = date('Y-m-d', strtotime($result->test_date));
            
            // Performance data
            $performance_data['dates'][] = $date;
            $performance_data['accuracy'][] = $result->accuracy * 100;
            $performance_data['completion_rate'][] = $result->completion_rate * 100;

            // Response times
            $response_times['dates'][] = $date;
            $response_times['average'][] = $result->avg_response_time;
            $response_times['minimum'][] = $result->min_response_time;
            $response_times['maximum'][] = $result->max_response_time;

            // Error analysis
            $error_data['dates'][] = $date;
            $error_data['commission'][] = $result->commission_errors;
            $error_data['omission'][] = $result->omission_errors;

            // Summary data
            $summary[] = array(
                'date' => $date,
                'test_type' => $result->test_type,
                'duration' => $result->duration,
                'accuracy' => $result->accuracy * 100,
                'completion_rate' => $result->completion_rate * 100,
                'avg_response_time' => $result->avg_response_time,
                'total_errors' => $result->commission_errors + $result->omission_errors
            );
        }

        return array(
            'performance' => $performance_data,
            'response_times' => $response_times,
            'errors' => $error_data,
            'summary' => $summary
        );
    }

    public function ajax_get_report_data() {
        check_ajax_referer('attentrack_reports', 'nonce');

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        if (!$this->check_report_permissions(array('user_id' => $user_id))) {
            wp_send_json_error(__('You do not have permission to view these reports.', 'attentrack'));
        }

        $request = new WP_REST_Request('GET', '/attentrack/v1/reports/' . $user_id);
        $request->set_param('from_date', $_POST['from_date']);
        $request->set_param('to_date', $_POST['to_date']);
        $request->set_param('test_type', $_POST['test_type']);

        $response = $this->get_user_reports($request);
        
        if ($response->is_error()) {
            wp_send_json_error($response->get_data());
        } else {
            wp_send_json_success($response->get_data());
        }
    }

    public function ajax_export_report() {
        check_ajax_referer('attentrack_reports', 'nonce');

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        if (!$this->check_report_permissions(array('user_id' => $user_id))) {
            wp_send_json_error(__('You do not have permission to export these reports.', 'attentrack'));
        }

        $request = new WP_REST_Request('GET', '/attentrack/v1/reports/' . $user_id);
        $request->set_param('from_date', $_POST['from_date']);
        $request->set_param('to_date', $_POST['to_date']);
        $request->set_param('test_type', $_POST['test_type']);

        $response = $this->get_user_reports($request);
        
        if ($response->is_error()) {
            wp_send_json_error($response->get_data());
            return;
        }

        $data = $response->get_data();
        $csv_data = $this->generate_csv($data);

        wp_send_json_success(array(
            'csv' => $csv_data,
            'filename' => sprintf(
                'attentrack-report-%s-%s.csv',
                get_userdata($user_id)->user_login,
                date('Y-m-d')
            )
        ));
    }

    private function generate_csv($data) {
        $csv = array();
        
        // Headers
        $csv[] = array(
            'Date',
            'Test Type',
            'Duration (s)',
            'Accuracy (%)',
            'Completion Rate (%)',
            'Avg Response Time (ms)',
            'Commission Errors',
            'Omission Errors'
        );

        // Data rows
        foreach ($data['summary'] as $row) {
            $csv[] = array(
                $row['date'],
                $row['test_type'],
                $row['duration'],
                number_format($row['accuracy'], 2),
                number_format($row['completion_rate'], 2),
                number_format($row['avg_response_time'], 2),
                $row['total_errors']
            );
        }

        // Convert to CSV string
        $output = fopen('php://temp', 'r+');
        foreach ($csv as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv_string = stream_get_contents($output);
        fclose($output);

        return $csv_string;
    }
}

// Initialize the reporting class
AttenTrack_Reporting::get_instance();
