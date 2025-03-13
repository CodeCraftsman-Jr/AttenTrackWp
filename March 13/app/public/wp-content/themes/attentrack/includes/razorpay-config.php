<?php
if (!defined('ABSPATH')) exit;

// Razorpay Configuration
define('RAZORPAY_KEY_ID', 'rzp_test_YOUR_KEY_ID'); // Replace with your actual key
define('RAZORPAY_KEY_SECRET', 'YOUR_KEY_SECRET'); // Replace with your actual secret key

// Initialize Razorpay
require_once get_template_directory() . '/vendor/autoload.php';
use Razorpay\Api\Api;

function attentrack_get_razorpay_api() {
    return new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
}

// Create Razorpay Order
function attentrack_create_razorpay_order($amount, $currency = 'INR') {
    try {
        $api = attentrack_get_razorpay_api();
        $order = $api->order->create([
            'amount' => $amount * 100, // Convert to paise
            'currency' => $currency,
            'payment_capture' => 1
        ]);
        return $order;
    } catch (Exception $e) {
        error_log('Razorpay Order Creation Error: ' . $e->getMessage());
        return false;
    }
}

// Verify Razorpay Payment
function attentrack_verify_razorpay_payment($payment_id, $order_id, $signature) {
    try {
        $api = attentrack_get_razorpay_api();
        $attributes = array(
            'razorpay_payment_id' => $payment_id,
            'razorpay_order_id' => $order_id,
            'razorpay_signature' => $signature
        );
        
        $api->utility->verifyPaymentSignature($attributes);
        return true;
    } catch (Exception $e) {
        error_log('Razorpay Payment Verification Error: ' . $e->getMessage());
        return false;
    }
}

// Handle Failed Payments
function attentrack_handle_failed_payment($user_id, $plan_id, $error_message) {
    global $wpdb;
    
    // Log the failed payment
    $wpdb->insert(
        $wpdb->prefix . 'subscription_payment_logs',
        array(
            'user_id' => $user_id,
            'plan_id' => $plan_id,
            'status' => 'failed',
            'error_message' => $error_message,
            'created_at' => current_time('mysql')
        ),
        array('%d', '%d', '%s', '%s', '%s')
    );
    
    // Send email notification
    $user = get_user_by('id', $user_id);
    $plan = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}subscription_plans WHERE id = %d",
        $plan_id
    ));
    
    $to = $user->user_email;
    $subject = 'Payment Failed - ' . get_bloginfo('name');
    $message = sprintf(
        "Dear %s,\n\nYour payment for the subscription plan '%s' has failed.\nError: %s\n\nPlease try again or contact support if you need assistance.\n\nBest regards,\n%s",
        $user->display_name,
        $plan->plan_name,
        $error_message,
        get_bloginfo('name')
    );
    
    wp_mail($to, $subject, $message);
}

// Check for subscription renewal
function attentrack_check_subscription_renewal($user_id) {
    global $wpdb;
    
    $subscription = $wpdb->get_row($wpdb->prepare(
        "SELECT s.*, p.plan_name, p.access_limit 
        FROM {$wpdb->prefix}user_subscriptions s 
        JOIN {$wpdb->prefix}subscription_plans p ON s.plan_id = p.id 
        WHERE s.user_id = %d 
        AND s.status = 'active'
        ORDER BY s.id DESC 
        LIMIT 1",
        $user_id
    ));
    
    if (!$subscription) {
        return false;
    }
    
    // Check if subscription needs renewal (7 days before expiry or when access limit is nearly reached)
    $needs_renewal = false;
    
    if ($subscription->end_date) {
        $days_until_expiry = (strtotime($subscription->end_date) - time()) / (60 * 60 * 24);
        if ($days_until_expiry <= 7) {
            $needs_renewal = true;
        }
    }
    
    if ($subscription->access_limit > 0) {
        $remaining_access = $subscription->access_limit - $subscription->access_count;
        if ($remaining_access <= 2) {
            $needs_renewal = true;
        }
    }
    
    return $needs_renewal ? $subscription : false;
}

// Send renewal notification
function attentrack_send_renewal_notification($user_id, $subscription) {
    $user = get_user_by('id', $user_id);
    
    $to = $user->user_email;
    $subject = 'Subscription Renewal Reminder - ' . get_bloginfo('name');
    $message = sprintf(
        "Dear %s,\n\nYour subscription plan '%s' will need to be renewed soon.\n\n",
        $user->display_name,
        $subscription->plan_name
    );
    
    if ($subscription->end_date) {
        $message .= sprintf(
            "Your subscription will expire on %s.\n",
            date('F j, Y', strtotime($subscription->end_date))
        );
    }
    
    if ($subscription->access_limit > 0) {
        $remaining_access = $subscription->access_limit - $subscription->access_count;
        $message .= sprintf(
            "You have %d test accesses remaining.\n",
            $remaining_access
        );
    }
    
    $message .= sprintf(
        "\nPlease visit %s to renew your subscription.\n\nBest regards,\n%s",
        home_url('/subscription'),
        get_bloginfo('name')
    );
    
    wp_mail($to, $subject, $message);
}
