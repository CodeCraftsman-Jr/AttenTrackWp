<?php
/*
Template Name: Checkout
*/

// Start output buffering to prevent headers already sent error
ob_start();

// Get plan from query parameters
$plan = isset($_GET['plan']) ? sanitize_text_field($_GET['plan']) : '';
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

// Get plan details
$plans = attentrack_get_subscription_plans();
$selected_plan = null;

foreach ($plans as $p) {
    if ($p['type'] === $plan) {
        $selected_plan = $p;
        break;
    }
}

// Validate plan and user before any output
if (!$selected_plan || $action !== 'change_plan') {
    wp_safe_redirect(home_url('/subscription-plans'));
    exit;
}

// Get user details and validate
$user_id = get_current_user_id();
if (!$user_id) {
    wp_safe_redirect(home_url('/login'));
    exit;
}

$user = get_userdata($user_id);

// Now we can safely output content
get_header();

// Get user details
?>

<style>
    .checkout-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 20px;
    }

    .checkout-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .checkout-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    .order-summary {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .payment-section {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .plan-details {
        margin: 20px 0;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .price-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }

    .price-row:last-child {
        border-bottom: none;
        font-weight: bold;
    }

    .payment-button {
        display: block;
        width: 100%;
        padding: 15px;
        background: #00e6e6;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 18px;
        cursor: pointer;
        transition: background 0.3s;
        text-align: center;
        text-decoration: none;
        margin-top: 20px;
    }

    .payment-button:hover {
        background: #00b3b3;
    }

    @media (max-width: 768px) {
        .checkout-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="checkout-container">
    <div class="checkout-header">
        <h1>Checkout</h1>
        <p>Complete your subscription to <?php echo esc_html($selected_plan['name']); ?></p>
    </div>

    <div class="checkout-grid">
        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="plan-details">
                <h3><?php echo esc_html($selected_plan['name']); ?></h3>
                <p><?php echo esc_html($selected_plan['description']); ?></p>
                <ul>
                    <?php foreach ($selected_plan['features'] as $feature): ?>
                        <li><?php echo esc_html($feature); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="price-breakdown">
                <div class="price-row">
                    <span>Subscription Price</span>
                    <span>₹<?php echo number_format($selected_plan['price']); ?>/month</span>
                </div>
                <div class="price-row">
                    <span>Total</span>
                    <span>₹<?php echo number_format($selected_plan['price']); ?></span>
                </div>
            </div>
        </div>

        <div class="payment-section">
            <h2>Payment Details</h2>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="create_razorpay_order">
                <input type="hidden" name="plan_type" value="<?php echo esc_attr($plan); ?>">
                <?php wp_nonce_field('create_razorpay_order'); ?>
                <button type="submit" class="payment-button">Proceed to Payment</button>
            </form>
        </div>
    </div>
</div>

<?php 
get_footer();

// Flush output buffer
ob_end_flush();
?>
