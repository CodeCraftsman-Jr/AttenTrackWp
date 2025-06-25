<?php
/*
Template Name: Payment Page
*/

if (!isset($_GET['order_id'])) {
    wp_redirect(home_url('/subscription-plans'));
    exit;
}

$user_id = get_current_user_id();
if (!$user_id) {
    wp_redirect(home_url('/login'));
    exit;
}

$pending_order = get_user_meta($user_id, 'pending_subscription_order', true);
if (!$pending_order || $pending_order['order_id'] !== $_GET['order_id']) {
    wp_redirect(home_url('/subscription-plans'));
    exit;
}

get_header();
?>

<style>
    .payment-container {
        max-width: 600px;
        margin: 40px auto;
        padding: 30px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .payment-details {
        margin: 20px 0;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .payment-button {
        display: block;
        width: 100%;
        padding: 15px;
        background: #3498db;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 18px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .payment-button:hover {
        background: #2980b9;
    }

    .amount {
        font-size: 24px;
        color: #2c3e50;
        font-weight: bold;
    }
</style>

<div class="payment-container">
    <h1>Complete Your Payment</h1>
    
    <div class="payment-details">
        <p>Order ID: <?php echo esc_html($pending_order['order_id']); ?></p>
        <p>Plan Type: <?php echo esc_html(ucfirst($pending_order['plan_type'])); ?> Plan</p>
        <p>Amount: <span class="amount">₹<?php echo number_format($pending_order['amount'], 2); ?></span></p>
    </div>

    <button id="rzp-button" class="payment-button">Pay Now</button>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
    "key": "<?php echo RAZORPAY_KEY_ID; ?>",
    "amount": "<?php echo $pending_order['amount'] * 100; ?>",
    "currency": "INR",
    "name": "AttenTrack",
    "description": "<?php echo esc_html(ucfirst($pending_order['plan_type'])); ?> Plan Subscription",
    "order_id": "<?php echo esc_js($pending_order['order_id']); ?>",
    "handler": function (response) {
        // Send payment details to server
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo admin_url('admin-post.php'); ?>';

        var fields = {
            'action': 'handle_razorpay_payment',
            'razorpay_payment_id': response.razorpay_payment_id,
            'razorpay_order_id': response.razorpay_order_id,
            'razorpay_signature': response.razorpay_signature,
            '_wpnonce': '<?php echo wp_create_nonce('handle_razorpay_payment'); ?>'
        };

        for (var key in fields) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = fields[key];
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    },
    "prefill": {
        "name": "<?php echo esc_js(wp_get_current_user()->display_name); ?>",
        "email": "<?php echo esc_js(wp_get_current_user()->user_email); ?>"
    },
    "theme": {
        "color": "#3498db"
    }
};

document.getElementById('rzp-button').onclick = function(e) {
    var rzp = new Razorpay(options);
    rzp.open();
    e.preventDefault();
}
</script>

<?php get_footer(); ?>
