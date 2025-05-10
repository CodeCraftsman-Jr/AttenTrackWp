<?php
require_once get_template_directory() . '/vendor/autoload.php';
require_once get_template_directory() . '/includes/utilities.php';

// Prevent direct class loading of Requests
if (!class_exists('WpOrg\Requests\Requests')) {
    class_alias('Requests', 'WpOrg\Requests\Requests');
}

use Razorpay\Api\Api;

// Handle free plan activation
function handle_activate_free_plan() {
    write_log("Starting free plan activation process");
    write_log("GET data: " . json_encode($_GET));
    
    // Get user ID and profile ID
    $user_id = get_current_user_id();
    $profile_id = get_user_meta($user_id, 'profile_id', true);
    write_log("Processing free plan for user ID: {$user_id}, Profile ID: {$profile_id}");
    
    if (!$user_id || !$profile_id) {
        write_log("No user logged in or missing profile ID", 'warning');
        wp_redirect(home_url('/login'));
        exit;
    }

    // Free tier has no end date
    $start_date = current_time('mysql');
    $end_date = null; // No end date for free tier
    write_log("Plan dates - Start: $start_date, End: Unlimited");

    try {
        global $wpdb;

        // Check if user already has an active subscription
        $existing_subscription = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}attentrack_subscriptions 
            WHERE user_id = %d AND profile_id = %s AND status = 'active'",
            $user_id,
            $profile_id
        ));

        if ($existing_subscription) {
            write_log("User already has an active subscription", 'warning');
            wp_redirect(home_url('/selection-page'));
            exit;
        }

        // Insert subscription record
        $result = $wpdb->insert(
            $wpdb->prefix . 'attentrack_subscriptions',
            array(
                'user_id' => $user_id,
                'profile_id' => $profile_id,
                'plan_name' => 'small_free',
                'plan_group' => 'small_scale',
                'amount' => 0.00,
                'duration_months' => 0, // Unlimited
                'member_limit' => 1,
                'days_limit' => 0, // Unlimited
                'payment_id' => 'FREE_' . time(),
                'order_id' => 'FREE_ORDER_' . time(),
                'status' => 'active',
                'start_date' => $start_date,
                'end_date' => $end_date,
                'created_at' => current_time('mysql')
            ),
            array(
                '%d',    // user_id
                '%s',    // profile_id
                '%s',    // plan_name
                '%s',    // plan_group
                '%f',    // amount
                '%d',    // duration_months
                '%d',    // member_limit
                '%d',    // days_limit
                '%s',    // payment_id
                '%s',    // order_id
                '%s',    // status
                '%s',    // start_date
                '%s',    // end_date
                '%s'     // created_at
            )
        );

        if ($result === false) {
            throw new Exception($wpdb->last_error);
        }

        write_log("Successfully inserted subscription record. Insert ID: " . $wpdb->insert_id);

        // Update user meta
        update_user_meta($user_id, 'subscription_status', 'active');
        update_user_meta($user_id, 'subscription_plan_type', 'small_free');
        update_user_meta($user_id, 'subscription_plan_group', 'small_scale');
        update_user_meta($user_id, 'subscription_start_date', $start_date);
        update_user_meta($user_id, 'subscription_end_date', '');
        update_user_meta($user_id, 'subscription_member_limit', 1);
        update_user_meta($user_id, 'subscription_days_limit', 0);

        // Check if user is an institution and update institution tables
        if (user_can($user_id, 'institution')) {
            // Include institution functions if not already included
            if (!function_exists('attentrack_create_or_update_institution')) {
                require_once get_template_directory() . '/inc/institution-functions.php';
            }
            
            // Create or update institution record
            $user_data = get_userdata($user_id);
            $institution_data = array(
                'institution_name' => $user_data->display_name,
                'contact_email' => $user_data->user_email,
                'member_limit' => 1, // Free plan has 1 member limit
                'status' => 'active'
            );
            
            $institution_id = attentrack_create_or_update_institution($user_id, $institution_data);
            write_log("Institution record created/updated with ID: $institution_id for free plan");
        }

        write_log("Free plan activation completed successfully. Redirecting to dashboard.");
        
        // Redirect to dashboard
        wp_redirect(home_url('/dashboard'));
        exit;

    } catch (Exception $e) {
        write_log("Error in free plan activation: " . $e->getMessage(), 'error');
        write_log("Last query: " . $wpdb->last_query, 'error');
        wp_die('Error activating free plan: ' . $e->getMessage());
    }
}

// Make sure both logged-in and non-logged-in users can access this
add_action('admin_post_activate_free_plan', 'handle_activate_free_plan');
add_action('admin_post_nopriv_activate_free_plan', 'handle_activate_free_plan');

// Create Razorpay order for paid plans
function handle_create_razorpay_order() {
    if (!isset($_POST['plan_type']) || !wp_verify_nonce($_POST['_wpnonce'], 'create_razorpay_order')) {
        wp_die('Invalid request');
    }

    $plan_type = sanitize_text_field($_POST['plan_type']);
    $user_id = get_current_user_id();

    if (!$user_id) {
        wp_redirect(home_url('/login'));
        exit;
    }

    try {
        // Get plan details
        $selected_plan = attentrack_get_plan_by_type($plan_type);

        if (!$selected_plan) {
            throw new Exception('Invalid plan type');
        }

        // Initialize Razorpay API
        $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

        // Create order
        $order = $api->order->create([
            'amount' => $selected_plan['price'] * 100, // Amount in paise
            'currency' => 'INR',
            'payment_capture' => 1
        ]);

        // Store order details in user meta
        update_user_meta($user_id, 'pending_subscription_order', [
            'order_id' => $order->id,
            'plan_type' => $plan_type,
            'plan_group' => $selected_plan['group'],
            'amount' => $selected_plan['price'],
            'member_limit' => $selected_plan['member_limit'],
            'days_limit' => $selected_plan['days_limit'],
            'duration_months' => $selected_plan['duration']
        ]);

        // Redirect to payment page
        wp_redirect(home_url('/payment-page?order_id=' . $order->id));
        exit;

    } catch (Exception $e) {
        write_log('Razorpay order creation error: ' . $e->getMessage());
        wp_die('Error creating payment order: ' . $e->getMessage());
    }
}
add_action('admin_post_create_razorpay_order', 'handle_create_razorpay_order');
add_action('admin_post_nopriv_create_razorpay_order', 'handle_create_razorpay_order');

// Handle successful payment and activate subscription
function handle_successful_payment($payment_id, $order_id, $signature) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        write_log("No user logged in for successful payment", 'warning');
        return false;
    }

    $profile_id = get_user_meta($user_id, 'profile_id', true);
    if (!$profile_id) {
        write_log("Missing profile ID for successful payment", 'error');
        return false;
    }

    $pending_order = get_user_meta($user_id, 'pending_subscription_order', true);
    if (!$pending_order || $pending_order['order_id'] !== $order_id) {
        write_log("Invalid pending order for successful payment", 'error');
        return false;
    }

    $plan_type = $pending_order['plan_type'];
    $plan_group = $pending_order['plan_group'];
    $member_limit = $pending_order['member_limit'];
    $days_limit = $pending_order['days_limit'];
    $duration_months = $pending_order['duration_months'];
    $start_date = current_time('mysql');
    
    // Calculate end date based on duration
    $end_date = null;
    if ($duration_months > 0) {
        $end_date = date('Y-m-d H:i:s', strtotime("+{$duration_months} months", strtotime($start_date)));
    }

    // Update user subscription
    update_user_meta($user_id, 'subscription_status', 'active');
    update_user_meta($user_id, 'subscription_plan_type', $plan_type);
    update_user_meta($user_id, 'subscription_plan_group', $plan_group);
    update_user_meta($user_id, 'subscription_start_date', $start_date);
    update_user_meta($user_id, 'subscription_end_date', $end_date);
    update_user_meta($user_id, 'subscription_member_limit', $member_limit);
    update_user_meta($user_id, 'subscription_days_limit', $days_limit);

    // Save payment details
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'attentrack_payments',
        array(
            'user_id' => $user_id,
            'profile_id' => $profile_id,
            'payment_id' => $payment_id,
            'order_id' => $order_id,
            'amount' => $pending_order['amount'],
            'status' => 'success',
            'payment_date' => current_time('mysql'),
            'created_at' => current_time('mysql')
        )
    );

    // Create subscription record
    write_log("Creating subscription record for user: {$user_id}, plan: {$plan_type}");
    
    // Check if the subscriptions table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}attentrack_subscriptions'");
    if (!$table_exists) {
        write_log("Subscriptions table does not exist! Creating it now.", 'error');
        
        // Create the subscriptions table
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$wpdb->prefix}attentrack_subscriptions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            profile_id varchar(20) NOT NULL,
            plan_name varchar(50) NOT NULL,
            plan_group enum('small_scale','large_scale') NOT NULL,
            amount decimal(10,2) NOT NULL,
            duration_months int NOT NULL,
            member_limit int NOT NULL DEFAULT 0,
            days_limit int NOT NULL DEFAULT 30,
            payment_id varchar(100) NOT NULL,
            order_id varchar(100) NOT NULL,
            status varchar(20) NOT NULL,
            start_date datetime NOT NULL,
            end_date datetime NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY profile_id (profile_id),
            KEY payment_id (payment_id),
            KEY order_id (order_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    // Prepare subscription data
    $subscription_data = array(
        'user_id' => $user_id,
        'profile_id' => $profile_id,
        'plan_name' => $plan_type,
        'plan_group' => $plan_group,
        'amount' => $pending_order['amount'],
        'duration_months' => $duration_months,
        'member_limit' => $member_limit,
        'days_limit' => $days_limit,
        'payment_id' => $payment_id,
        'order_id' => $order_id,
        'status' => 'active',
        'start_date' => $start_date,
        'end_date' => $end_date ? $end_date : date('Y-m-d H:i:s', strtotime('+100 years')), // Use far future date if no end date
        'created_at' => current_time('mysql')
    );
    
    write_log("Subscription data: " . json_encode($subscription_data));
    
    // Insert subscription record
    $result = $wpdb->insert(
        $wpdb->prefix . 'attentrack_subscriptions',
        $subscription_data
    );
    
    if ($result === false) {
        write_log("Failed to insert subscription record: " . $wpdb->last_error, 'error');
    } else {
        $subscription_id = $wpdb->insert_id;
        write_log("Subscription record created successfully with ID: {$subscription_id}");
    }

    // Check if user is an institution and update institution tables
    if (user_can($user_id, 'institution')) {
        // Include institution functions if not already included
        if (!function_exists('attentrack_create_or_update_institution')) {
            require_once get_template_directory() . '/inc/institution-functions.php';
        }
        
        // Create or update institution record
        $user_data = get_userdata($user_id);
        $institution_data = array(
            'institution_name' => $user_data->display_name,
            'contact_email' => $user_data->user_email,
            'member_limit' => $member_limit,
            'status' => 'active'
        );
        
        $institution_id = attentrack_create_or_update_institution($user_id, $institution_data);
        write_log("Institution record created/updated with ID: $institution_id");
    }

    // Clear pending order
    delete_user_meta($user_id, 'pending_subscription_order');

    return true;
}

// Handle failed payment
function handle_failed_payment($order_id) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        write_log("No user logged in for failed payment", 'warning');
        return false;
    }

    $profile_id = get_user_meta($user_id, 'profile_id', true);
    if (!$profile_id) {
        write_log("Missing profile ID for failed payment", 'error');
        return false;
    }

    $pending_order = get_user_meta($user_id, 'pending_subscription_order', true);
    if (!$pending_order || $pending_order['order_id'] !== $order_id) {
        write_log("Invalid pending order for failed payment", 'error');
        return false;
    }

    // Log failed payment
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'attentrack_payments',
        array(
            'user_id' => $user_id,
            'profile_id' => $profile_id,
            'order_id' => $order_id,
            'amount' => $pending_order['amount'],
            'plan_type' => $pending_order['plan_type'],
            'status' => 'failed',
            'created_at' => current_time('mysql')
        ),
        array('%d', '%s', '%s', '%f', '%s', '%s', '%s')
    );

    // Clear pending order
    delete_user_meta($user_id, 'pending_subscription_order');

    return true;
}

// Handle plan change request
function handle_plan_change() {
    if (!isset($_GET['plan'])) {
        wp_die('Invalid request - missing plan');
    }

    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'change_plan_' . $_GET['plan'])) {
        wp_die('Security check failed');
    }

    $plan_type = sanitize_text_field($_GET['plan']);
    $user_id = get_current_user_id();

    if (!$user_id) {
        wp_redirect(home_url('/login'));
        exit;
    }

    // If changing to free plan
    if ($plan_type === 'small_free') {
        handle_activate_free_plan();
        exit;
    }

    // For paid plans, create Razorpay order
    try {
        // Get plan details
        $plans = attentrack_get_subscription_plans();
        $selected_plan = null;
        
        // Find the selected plan
        foreach ($plans as $group => $group_plans) {
            foreach ($group_plans as $plan) {
                if ($plan['type'] === $plan_type) {
                    $selected_plan = $plan;
                    break 2;
                }
            }
        }

        if (!$selected_plan) {
            throw new Exception('Invalid plan type: ' . $plan_type);
        }

        // Initialize Razorpay API
        $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

        // Create order
        $order = $api->order->create([
            'amount' => $selected_plan['price'] * 100, // Amount in paise
            'currency' => 'INR',
            'payment_capture' => 1
        ]);

        // Store order details in user meta
        update_user_meta($user_id, 'pending_subscription_order', [
            'order_id' => $order->id,
            'plan_type' => $plan_type,
            'plan_group' => $selected_plan['group'],
            'amount' => $selected_plan['price'],
            'member_limit' => $selected_plan['member_limit'],
            'days_limit' => $selected_plan['days_limit'],
            'duration_months' => isset($selected_plan['duration']) ? $selected_plan['duration'] : 1
        ]);

        // Redirect to payment page
        wp_redirect(home_url('/payment-page?order_id=' . $order->id));
        exit;

    } catch (Exception $e) {
        write_log('Razorpay order creation error: ' . $e->getMessage());
        wp_die('Error creating payment order: ' . $e->getMessage());
    }
}
add_action('admin_post_change_plan', 'handle_plan_change');
add_action('admin_post_nopriv_change_plan', 'handle_plan_change');

// Handle Razorpay payment callback
function handle_razorpay_payment() {
    if (!isset($_POST['razorpay_payment_id']) || 
        !isset($_POST['razorpay_order_id']) || 
        !isset($_POST['razorpay_signature']) ||
        !wp_verify_nonce($_POST['_wpnonce'], 'handle_razorpay_payment')) {
        wp_redirect(add_query_arg(['status' => 'error', 'message' => 'invalid_request'], home_url('/subscription-plans')));
        exit;
    }

    $payment_id = sanitize_text_field($_POST['razorpay_payment_id']);
    $order_id = sanitize_text_field($_POST['razorpay_order_id']);
    $signature = sanitize_text_field($_POST['razorpay_signature']);

    try {
        $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
        
        // Verify signature
        $attributes = [
            'razorpay_order_id' => $order_id,
            'razorpay_payment_id' => $payment_id,
            'razorpay_signature' => $signature
        ];
        
        $api->utility->verifyPaymentSignature($attributes);
        
        // If signature verification passes, process the payment
        if (handle_successful_payment($payment_id, $order_id, $signature)) {
            wp_redirect(add_query_arg(['status' => 'success'], home_url('/dashboard')));
            exit;
        }
        
        wp_redirect(add_query_arg(['status' => 'error', 'message' => 'payment_failed'], home_url('/subscription-plans')));
        exit;

    } catch (Exception $e) {
        write_log('Payment signature verification failed: ' . $e->getMessage());
        wp_redirect(add_query_arg(['status' => 'error', 'message' => 'invalid_signature'], home_url('/subscription-plans')));
        exit;
    }
}

add_action('admin_post_handle_razorpay_payment', 'handle_razorpay_payment');
add_action('admin_post_nopriv_handle_razorpay_payment', 'handle_razorpay_payment');
