<?php
/*
Template Name: Subscription Page
*/

// get_header();

global $wpdb;
$subscription_plans = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}subscription_plans ORDER BY price ASC");
$current_user_id = get_current_user_id();

// Get user's current subscription if any
$current_subscription = null;
if ($current_user_id) {
    $current_subscription = $wpdb->get_row($wpdb->prepare(
        "SELECT s.*, p.plan_name, p.access_limit 
        FROM {$wpdb->prefix}user_subscriptions s 
        JOIN {$wpdb->prefix}subscription_plans p ON s.plan_id = p.id 
        WHERE s.user_id = %d 
        AND s.status = 'active' 
        AND (s.end_date IS NULL OR s.end_date > NOW())
        ORDER BY s.id DESC 
        LIMIT 1",
        $current_user_id
    ));
}
?>

<div class="container my-5">
    <?php if (isset($_GET['subscription'])): ?>
        <?php if ($_GET['subscription'] === 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                Your subscription has been activated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($_GET['subscription'] === 'failed'): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                Payment failed. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <h1 class="text-center mb-5">Choose Your Subscription Plan</h1>

    <?php if ($current_subscription): ?>
    <div class="alert alert-info mb-4">
        <h4>Your Current Subscription</h4>
        <p>Plan: <?php echo esc_html($current_subscription->plan_name); ?></p>
        <p>Tests Available: <?php 
            if ($current_subscription->access_limit == -1) {
                echo 'Unlimited';
            } else {
                $remaining = $current_subscription->access_limit - $current_subscription->access_count;
                echo esc_html($remaining) . ' of ' . esc_html($current_subscription->access_limit);
            }
        ?></p>
        <?php if ($current_subscription->end_date): ?>
            <p>Valid until: <?php echo date('F j, Y', strtotime($current_subscription->end_date)); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php foreach ($subscription_plans as $plan): ?>
        <div class="col">
            <div class="card h-100">
                <div class="card-header text-center">
                    <h3 class="card-title"><?php echo esc_html($plan->plan_name); ?></h3>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="price text-center mb-4">
                        <span class="h1">₹<?php echo number_format($plan->price, 2); ?></span>
                    </div>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <?php echo $plan->access_limit == -1 ? 'Unlimited access' : esc_html($plan->access_limit) . ' test accesses'; ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Access to all test types
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Detailed results
                        </li>
                        <?php if ($plan->access_limit > 1): ?>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Share with others
                        </li>
                        <?php endif; ?>
                    </ul>
                    <div class="mt-auto">
                        <button type="button" class="btn btn-primary btn-lg w-100 subscribe-button" 
                                data-plan-id="<?php echo esc_attr($plan->id); ?>"
                                data-plan-name="<?php echo esc_attr($plan->plan_name); ?>"
                                data-plan-price="<?php echo esc_attr($plan->price); ?>">
                            Subscribe Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Razorpay script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
jQuery(document).ready(function($) {
    $('.subscribe-button').on('click', function() {
        const planId = $(this).data('plan-id');
        const planName = $(this).data('plan-name');
        const planPrice = $(this).data('plan-price');
        
        // Show loading state
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
        
        // Create order
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'purchase_subscription',
                plan_id: planId,
                _wpnonce: '<?php echo wp_create_nonce('purchase_subscription_' . $plan->id); ?>'
            },
            success: function(response) {
                if (response.success) {
                    const options = response.data;
                    
                    // Initialize Razorpay checkout
                    const rzp = new Razorpay(options);
                    
                    rzp.on('payment.failed', function(response) {
                        alert('Payment failed. Please try again.');
                        location.reload();
                    });
                    
                    rzp.open();
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            },
            complete: function() {
                // Reset button state
                $('.subscribe-button').prop('disabled', false).html('Subscribe Now');
            }
        });
    });
});
</script>

<style>
.card {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: none;
    padding: 2rem 1rem;
}

.price {
    color: #0d6efd;
}

.btn-primary {
    padding: 0.8rem 2rem;
}

.list-unstyled li {
    padding: 0.5rem 0;
}

.alert {
    border-radius: 10px;
    padding: 1.5rem;
}

.spinner-border {
    margin-right: 0.5rem;
}
</style>

<!-- <?php get_footer(); ?> -->
