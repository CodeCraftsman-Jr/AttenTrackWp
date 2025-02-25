// Include the Bootstrap Nav Walker
require_once get_template_directory() . '/includes/class-bootstrap-walker-nav-menu.php';

<?php
if (!defined('ABSPATH')) exit;

function attentrack_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));

    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'attentrack'),
        'footer' => __('Footer Menu', 'attentrack'),
    ));
}
add_action('after_setup_theme', 'attentrack_setup');

// Add Bootstrap classes to menu items
function attentrack_menu_classes($classes, $item, $args) {
    if ($args->theme_location == 'primary') {
        $classes[] = 'nav-item';
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'attentrack_menu_classes', 10, 3);

// Add Bootstrap classes to menu links
function attentrack_menu_link_classes($atts, $item, $args) {
    if ($args->theme_location == 'primary') {
        $atts['class'] = 'nav-link';
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'attentrack_menu_link_classes', 10, 3);

function attentrack_enqueue_scripts() {
    // Enqueue styles
    wp_enqueue_style('attentrack-style', get_stylesheet_uri());
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
    wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap');

    // Enqueue scripts
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
    wp_enqueue_script('attentrack-main', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0', true);
    
    // Enqueue test handler script only on test pages
    if (is_page_template('page-test.php') || is_singular('attention_test')) {
        wp_enqueue_script('attentrack-test-handler', get_template_directory_uri() . '/js/test-handler.js', array('jquery'), '1.0', true);
    }
}
add_action('wp_enqueue_scripts', 'attentrack_enqueue_scripts');

// Register Custom Post Types
function attentrack_register_post_types() {
    // Tests Post Type
    register_post_type('test', array(
        'labels' => array(
            'name' => __('Tests', 'attentrack'),
            'singular_name' => __('Test', 'attentrack'),
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-clipboard',
        'supports' => array('title', 'editor', 'thumbnail'),
        'rewrite' => array('slug' => 'tests'),
    ));

    // Results Post Type
    register_post_type('result', array(
        'labels' => array(
            'name' => __('Results', 'attentrack'),
            'singular_name' => __('Result', 'attentrack'),
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-chart-bar',
        'supports' => array('title', 'editor'),
        'rewrite' => array('slug' => 'results'),
    ));
}
add_action('init', 'attentrack_register_post_types');

// Add custom roles and capabilities
function attentrack_add_roles() {
    add_role('patient', 'Patient', array(
        'read' => true,
        'take_tests' => true,
        'view_results' => true,
    ));
}
add_action('init', 'attentrack_add_roles');

// AJAX handlers for test functionality
function attentrack_get_unique_test_id() {
    check_ajax_referer('get_unique_test_id');
    
    $test_id = uniqid('test_');
    wp_send_json_success(array('test_id' => $test_id));
}
add_action('wp_ajax_get_unique_test_id', 'attentrack_get_unique_test_id');

function attentrack_save_test_results() {
    check_ajax_referer('save_test_results');
    
    $test_id = sanitize_text_field($_POST['test_id']);
    $phase = intval($_POST['phase']);
    $responses = json_decode(stripslashes($_POST['responses']), true);
    
    if (!$responses || !is_array($responses)) {
        wp_send_json_error(array('message' => 'Invalid response data'));
        return;
    }
    
    $total_responses = count($responses);
    $correct_responses = array_filter($responses, function($r) { return $r['correct']; });
    $accuracy = ($total_responses > 0) ? (count($correct_responses) / $total_responses) * 100 : 0;
    $avg_response_time = array_reduce($responses, function($carry, $r) { return $carry + $r['responseTime']; }, 0) / $total_responses;
    
    $post_data = array(
        'post_title' => sprintf('Test Results - Phase %d - %s', $phase, current_time('mysql')),
        'post_type' => 'test_result',
        'post_status' => 'publish',
        'post_author' => get_current_user_id()
    );
    
    $post_id = wp_insert_post($post_data);
    
    if ($post_id) {
        update_post_meta($post_id, 'test_id', $test_id);
        update_post_meta($post_id, 'test_phase', $phase);
        update_post_meta($post_id, 'responses', $responses);
        update_post_meta($post_id, 'total_responses', $total_responses);
        update_post_meta($post_id, 'correct_responses', count($correct_responses));
        update_post_meta($post_id, 'accuracy', $accuracy);
        update_post_meta($post_id, 'avg_response_time', $avg_response_time);
        
        wp_send_json_success(array('post_id' => $post_id));
    } else {
        wp_send_json_error(array('message' => 'Failed to save results'));
    }
}
add_action('wp_ajax_save_test_results', 'attentrack_save_test_results');

// Database connection details
define('ATTENTRACK_DB_HOST', 'sql210.infinityfree.com');
define('ATTENTRACK_DB_USER', 'if0_37517154');
define('ATTENTRACK_DB_PASS', '1slLMeZbqe7IyKy');
define('ATTENTRACK_DB_NAME', 'if0_37517154_useriddb');

// Register REST API endpoints for test ID management
function attentrack_register_rest_routes() {
    register_rest_route('attentrack/v1', '/generate-test-id', array(
        'methods' => 'POST',
        'callback' => 'attentrack_generate_test_id',
        'permission_callback' => function() {
            return true; // Allow any logged-in user to generate test ID
        }
    ));

    register_rest_route('attentrack/v1', '/get-last-test-id', array(
        'methods' => 'GET',
        'callback' => 'attentrack_get_last_test_id',
        'permission_callback' => function() {
            return true; // Allow any logged-in user to get last test ID
        }
    ));
}
add_action('rest_api_init', 'attentrack_register_rest_routes');

// Function to establish database connection
function attentrack_db_connect() {
    $conn = new mysqli(ATTENTRACK_DB_HOST, ATTENTRACK_DB_USER, ATTENTRACK_DB_PASS, ATTENTRACK_DB_NAME);
    
    if ($conn->connect_error) {
        return new WP_Error('db_connection_error', 'Database connection failed: ' . $conn->connect_error);
    }
    
    return $conn;
}

// Generate unique test ID
function attentrack_generate_unique_test_id($conn) {
    $isUnique = false;
    $uniqueID = '';

    while (!$isUnique) {
        $uniqueID = mt_rand(100000, 999999);
        $query = "SELECT test_id FROM test_results WHERE test_id = '$uniqueID'";
        $result = $conn->query($query);

        if ($result->num_rows == 0) {
            $isUnique = true;
        }
    }

    return $uniqueID;
}

// REST API callback for generating test ID
function attentrack_generate_test_id($request) {
    $conn = attentrack_db_connect();
    
    if (is_wp_error($conn)) {
        return new WP_REST_Response(array(
            'error' => $conn->get_error_message()
        ), 500);
    }

    $uniqueTestID = attentrack_generate_unique_test_id($conn);
    $stmt = $conn->prepare("INSERT INTO test_results (test_id) VALUES (?)");
    $stmt->bind_param("s", $uniqueTestID);

    if ($stmt->execute()) {
        $response = array('test_id' => $uniqueTestID);
        $status = 200;
    } else {
        $response = array('error' => 'Error storing test ID: ' . $stmt->error);
        $status = 500;
    }

    $stmt->close();
    $conn->close();

    return new WP_REST_Response($response, $status);
}

// REST API callback for getting last test ID
function attentrack_get_last_test_id($request) {
    $conn = attentrack_db_connect();
    
    if (is_wp_error($conn)) {
        return new WP_REST_Response(array(
            'error' => $conn->get_error_message()
        ), 500);
    }

    $sql = "SELECT test_id FROM test_results ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = array('test_id' => $row['test_id']);
        $status = 200;
    } else {
        $response = array('test_id' => null);
        $status = 404;
    }

    $conn->close();
    return new WP_REST_Response($response, $status);
}

// Subscription Plans
define('SUBSCRIPTION_STAGE_1', 'one_time');
define('SUBSCRIPTION_STAGE_2', 'two_person');
define('SUBSCRIPTION_STAGE_3', 'three_person');
define('SUBSCRIPTION_STAGE_4', 'unlimited');

// Create subscription tables on theme activation
function attentrack_create_subscription_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Subscription Plans Table
    $sql_plans = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}subscription_plans (
        id int(11) NOT NULL AUTO_INCREMENT,
        plan_name varchar(50) NOT NULL,
        access_limit int(11) NOT NULL,
        price decimal(10,2) NOT NULL,
        description text,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // User Subscriptions Table
    $sql_subscriptions = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}user_subscriptions (
        id int(11) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        plan_id int(11) NOT NULL,
        start_date datetime NOT NULL,
        end_date datetime,
        access_count int(11) DEFAULT 0,
        status varchar(20) NOT NULL,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY plan_id (plan_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_plans);
    dbDelta($sql_subscriptions);

    // Insert default subscription plans if they don't exist
    $existing_plans = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}subscription_plans");
    if (empty($existing_plans)) {
        $default_plans = array(
            array(
                'plan_name' => 'Stage 1 - One Time Access',
                'access_limit' => 1,
                'price' => 99.00,
                'description' => 'Single-use access to all tests'
            ),
            array(
                'plan_name' => 'Stage 2 - Two Person Access',
                'access_limit' => 2,
                'price' => 179.00,
                'description' => 'Access for two people to all tests'
            ),
            array(
                'plan_name' => 'Stage 3 - Three Person Access',
                'access_limit' => 3,
                'price' => 249.00,
                'description' => 'Access for three people to all tests'
            ),
            array(
                'plan_name' => 'Stage 4 - Unlimited Access',
                'access_limit' => -1,
                'price' => 499.00,
                'description' => 'Unlimited access to all tests'
            )
        );

        foreach ($default_plans as $plan) {
            $wpdb->insert("{$wpdb->prefix}subscription_plans", $plan);
        }
    }
}
add_action('after_switch_theme', 'attentrack_create_subscription_tables');

// Check if user has active subscription
function attentrack_check_subscription_access() {
    if (!is_user_logged_in()) {
        return false;
    }

    global $wpdb;
    $user_id = get_current_user_id();
    
    $subscription = $wpdb->get_row($wpdb->prepare(
        "SELECT s.*, p.access_limit 
        FROM {$wpdb->prefix}user_subscriptions s 
        JOIN {$wpdb->prefix}subscription_plans p ON s.plan_id = p.id 
        WHERE s.user_id = %d 
        AND s.status = 'active' 
        AND (s.end_date IS NULL OR s.end_date > NOW())
        ORDER BY s.id DESC 
        LIMIT 1",
        $user_id
    ));

    if (!$subscription) {
        return false;
    }

    // Unlimited access
    if ($subscription->access_limit == -1) {
        return true;
    }

    // Check access count
    return $subscription->access_count < $subscription->access_limit;
}

// Increment access count for user
function attentrack_increment_access_count($user_id) {
    global $wpdb;
    
    return $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->prefix}user_subscriptions 
        SET access_count = access_count + 1 
        WHERE user_id = %d 
        AND status = 'active' 
        AND (end_date IS NULL OR end_date > NOW())
        ORDER BY id DESC 
        LIMIT 1",
        $user_id
    ));
}

// Add subscription check to test pages
function attentrack_check_test_access() {
    if (is_page_template(array(
        'templates/tests/test-phase-0.php',
        'templates/tests/test-phase-1.php',
        'templates/tests/test-phase-2.php',
        'templates/tests/test-phase-3.php',
        'templates/tests/test-phase-4.php',
        'templates/tests/test2.php',
        'templates/tests/test3.php',
        'templates/tests/trialtest0.php',
        'templates/tests/trialtest1.php',
        'templates/tests/trialtest2.php',
        'templates/tests/trialtest3.php'
    ))) {
        if (!attentrack_check_subscription_access()) {
            wp_redirect(home_url('/subscription'));
            exit;
        }
    }
}
add_action('template_redirect', 'attentrack_check_test_access');

// Include Razorpay configuration
require_once get_template_directory() . '/includes/razorpay-config.php';

// Create payment logs table on theme activation
function attentrack_create_payment_logs_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}subscription_payment_logs (
        id int(11) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        plan_id int(11) NOT NULL,
        razorpay_order_id varchar(100),
        razorpay_payment_id varchar(100),
        amount decimal(10,2) NOT NULL,
        status varchar(20) NOT NULL,
        error_message text,
        created_at datetime NOT NULL,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY plan_id (plan_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'attentrack_create_payment_logs_table');

// Handle subscription purchase with Razorpay
function attentrack_handle_subscription_purchase() {
    if (!isset($_POST['plan_id']) || !wp_verify_nonce($_POST['_wpnonce'], 'purchase_subscription_' . $_POST['plan_id'])) {
        wp_die('Invalid request');
    }

    if (!is_user_logged_in()) {
        wp_redirect(wp_login_url(home_url('/subscription')));
        exit;
    }

    global $wpdb;
    $plan_id = intval($_POST['plan_id']);
    $user_id = get_current_user_id();

    // Get plan details
    $plan = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}subscription_plans WHERE id = %d",
        $plan_id
    ));

    if (!$plan) {
        wp_die('Invalid subscription plan');
    }

    try {
        // Create Razorpay order
        $order = attentrack_create_razorpay_order($plan->price);
        if (!$order) {
            throw new Exception('Failed to create payment order');
        }

        // Store order details in session
        $_SESSION['razorpay_order'] = array(
            'order_id' => $order->id,
            'plan_id' => $plan_id,
            'amount' => $plan->price
        );

        // Prepare payment data for Razorpay checkout
        $payment_data = array(
            'key' => RAZORPAY_KEY_ID,
            'amount' => $plan->price * 100,
            'currency' => 'INR',
            'name' => get_bloginfo('name'),
            'description' => $plan->plan_name,
            'order_id' => $order->id,
            'callback_url' => home_url('/wp-json/attentrack/v1/razorpay-callback'),
            'prefill' => array(
                'name' => wp_get_current_user()->display_name,
                'email' => wp_get_current_user()->user_email
            )
        );

        // Log the payment attempt
        $wpdb->insert(
            $wpdb->prefix . 'subscription_payment_logs',
            array(
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'razorpay_order_id' => $order->id,
                'amount' => $plan->price,
                'status' => 'pending',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%f', '%s', '%s')
        );

        // Return payment data for frontend processing
        wp_send_json_success($payment_data);

    } catch (Exception $e) {
        attentrack_handle_failed_payment($user_id, $plan_id, $e->getMessage());
        wp_send_json_error(array('message' => $e->getMessage()));
    }
}
add_action('wp_ajax_purchase_subscription', 'attentrack_handle_subscription_purchase');

// Handle Razorpay payment callback
function attentrack_handle_razorpay_callback($request) {
    $payment_id = $request->get_param('razorpay_payment_id');
    $order_id = $request->get_param('razorpay_order_id');
    $signature = $request->get_param('razorpay_signature');

    if (!$payment_id || !$order_id || !$signature) {
        return new WP_Error('invalid_payment', 'Invalid payment data');
    }

    try {
        // Verify payment signature
        if (!attentrack_verify_razorpay_payment($payment_id, $order_id, $signature)) {
            throw new Exception('Payment signature verification failed');
        }

        global $wpdb;
        
        // Get payment log
        $payment_log = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}subscription_payment_logs 
            WHERE razorpay_order_id = %s",
            $order_id
        ));

        if (!$payment_log) {
            throw new Exception('Payment record not found');
        }

        // Update payment log
        $wpdb->update(
            $wpdb->prefix . 'subscription_payment_logs',
            array(
                'razorpay_payment_id' => $payment_id,
                'status' => 'completed'
            ),
            array('id' => $payment_log->id),
            array('%s', '%s'),
            array('%d')
        );

        // Activate subscription
        attentrack_activate_subscription($payment_log->user_id, $payment_log->plan_id);

        wp_redirect(add_query_arg('subscription', 'success', home_url('/subscription')));
        exit;

    } catch (Exception $e) {
        attentrack_handle_failed_payment($payment_log->user_id, $payment_log->plan_id, $e->getMessage());
        wp_redirect(add_query_arg('subscription', 'failed', home_url('/subscription')));
        exit;
    }
}

// Register Razorpay callback endpoint
add_action('rest_api_init', function () {
    register_rest_route('attentrack/v1', '/razorpay-callback', array(
        'methods' => 'POST',
        'callback' => 'attentrack_handle_razorpay_callback',
        'permission_callback' => '__return_true'
    ));
});

// Check for subscription renewals daily
function attentrack_check_all_subscriptions() {
    global $wpdb;
    
    $users = get_users(array('fields' => 'ID'));
    foreach ($users as $user_id) {
        $subscription = attentrack_check_subscription_renewal($user_id);
        if ($subscription) {
            attentrack_send_renewal_notification($user_id, $subscription);
        }
    }
}
add_action('attentrack_daily_subscription_check', 'attentrack_check_all_subscriptions');

// Schedule daily subscription check
if (!wp_next_scheduled('attentrack_daily_subscription_check')) {
    wp_schedule_event(time(), 'daily', 'attentrack_daily_subscription_check');
}

// Handle subscription purchase
function attentrack_handle_subscription_purchase() {
    if (!isset($_POST['plan_id']) || !wp_verify_nonce($_POST['_wpnonce'], 'purchase_subscription_' . $_POST['plan_id'])) {
        wp_die('Invalid request');
    }

    if (!is_user_logged_in()) {
        wp_redirect(wp_login_url(home_url('/subscription')));
        exit;
    }

    global $wpdb;
    $plan_id = intval($_POST['plan_id']);
    $user_id = get_current_user_id();

    // Get plan details
    $plan = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}subscription_plans WHERE id = %d",
        $plan_id
    ));

    if (!$plan) {
        wp_die('Invalid subscription plan');
    }

    // For now, we'll simulate a successful payment
    // In production, integrate with a payment gateway here
    $payment_successful = true;

    if ($payment_successful) {
        // Deactivate any existing subscriptions
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}user_subscriptions 
            SET status = 'inactive', end_date = NOW() 
            WHERE user_id = %d AND status = 'active'",
            $user_id
        ));

        // Create new subscription
        $wpdb->insert(
            $wpdb->prefix . 'user_subscriptions',
            array(
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'start_date' => current_time('mysql'),
                'end_date' => null, // No end date for now
                'access_count' => 0,
                'status' => 'active'
            ),
            array('%d', '%d', '%s', '%s', '%d', '%s')
        );

        // Redirect to success page
        wp_redirect(add_query_arg('subscription', 'success', home_url('/subscription')));
        exit;
    } else {
        // Handle payment failure
        wp_redirect(add_query_arg('subscription', 'failed', home_url('/subscription')));
        exit;
    }
}
add_action('admin_post_purchase_subscription', 'attentrack_handle_subscription_purchase');

// Display subscription notices
function attentrack_subscription_notices() {
    if (!isset($_GET['subscription'])) {
        return;
    }

    $status = $_GET['subscription'];
    $message = '';
    $type = '';

    switch ($status) {
        case 'success':
            $message = 'Your subscription has been activated successfully!';
            $type = 'success';
            break;
        case 'failed':
            $message = 'Payment failed. Please try again.';
            $type = 'error';
            break;
    }

    if ($message) {
        echo '<div class="alert alert-' . ($type === 'success' ? 'success' : 'danger') . ' alert-dismissible fade show" role="alert">
            ' . esc_html($message) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}
add_action('wp_body_open', 'attentrack_subscription_notices');

// Add subscription status to user profile
function attentrack_add_subscription_info($user) {
    global $wpdb;
    
    $subscription = $wpdb->get_row($wpdb->prepare(
        "SELECT s.*, p.plan_name, p.access_limit 
        FROM {$wpdb->prefix}user_subscriptions s 
        JOIN {$wpdb->prefix}subscription_plans p ON s.plan_id = p.id 
        WHERE s.user_id = %d 
        AND s.status = 'active' 
        AND (s.end_date IS NULL OR s.end_date > NOW())
        ORDER BY s.id DESC 
        LIMIT 1",
        $user->ID
    ));
    ?>
    <h3>Subscription Status</h3>
    <table class="form-table">
        <tr>
            <th>Current Plan</th>
            <td><?php echo $subscription ? esc_html($subscription->plan_name) : 'No active subscription'; ?></td>
        </tr>
        <?php if ($subscription): ?>
        <tr>
            <th>Tests Available</th>
            <td><?php 
                if ($subscription->access_limit == -1) {
                    echo 'Unlimited';
                } else {
                    $remaining = $subscription->access_limit - $subscription->access_count;
                    echo esc_html($remaining) . ' of ' . esc_html($subscription->access_limit);
                }
            ?></td>
        </tr>
        <?php if ($subscription->end_date): ?>
        <tr>
            <th>Valid Until</th>
            <td><?php echo date('F j, Y', strtotime($subscription->end_date)); ?></td>
        </tr>
        <?php endif; ?>
        <?php endif; ?>
    </table>
    <?php
}
add_action('show_user_profile', 'attentrack_add_subscription_info');
add_action('edit_user_profile', 'attentrack_add_subscription_info');

// Include social login configuration
require_once get_template_directory() . '/includes/social-login-config.php';

// Handle Google login callback
function handle_google_callback() {
    if (isset($_GET['code']) && isset($_GET['state'])) {
        $client = get_google_client();
        
        try {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            $client->setAccessToken($token);
            
            $google_oauth = new Google_Service_Oauth2($client);
            $user_info = $google_oauth->userinfo->get();
            
            if (handle_social_login($user_info->email, $user_info->name, $user_info->id, 'google')) {
                wp_redirect(home_url());
                exit;
            }
        } catch (Exception $e) {
            error_log('Google Login Error: ' . $e->getMessage());
        }
    }
    
    wp_redirect(home_url('/login?error=google'));
    exit;
}
add_action('template_redirect', function() {
    if (strpos($_SERVER['REQUEST_URI'], '/google-callback') !== false) {
        handle_google_callback();
    }
});

// Handle Facebook login callback
function handle_facebook_callback() {
    $helper = get_facebook_helper();
    
    try {
        $accessToken = $helper->getAccessToken();
        
        if ($accessToken) {
            $fb = new Facebook\Facebook([
                'app_id' => FACEBOOK_APP_ID,
                'app_secret' => FACEBOOK_APP_SECRET,
                'default_graph_version' => 'v12.0',
            ]);
            
            $response = $fb->get('/me?fields=id,name,email', $accessToken);
            $user = $response->getGraphUser();
            
            if (handle_social_login($user->getEmail(), $user->getName(), $user->getId(), 'facebook')) {
                wp_redirect(home_url());
                exit;
            }
        }
    } catch (Exception $e) {
        error_log('Facebook Login Error: ' . $e->getMessage());
    }
    
    wp_redirect(home_url('/login?error=facebook'));
    exit;
}
add_action('template_redirect', function() {
    if (strpos($_SERVER['REQUEST_URI'], '/facebook-callback') !== false) {
        handle_facebook_callback();
    }
});

// Handle phone login verification
add_action('wp_ajax_nopriv_verify_phone', 'handle_phone_verification');
add_action('wp_ajax_verify_phone', 'handle_phone_verification');

function handle_phone_verification() {
    if (!isset($_POST['phone']) || !isset($_POST['uid'])) {
        wp_send_json_error('Invalid request');
    }
    
    $phone = sanitize_text_field($_POST['phone']);
    $uid = sanitize_text_field($_POST['uid']);
    
    if (handle_phone_login($phone, $uid)) {
        wp_send_json_success(['redirect' => home_url()]);
    } else {
        wp_send_json_error('Login failed');
    }
}
