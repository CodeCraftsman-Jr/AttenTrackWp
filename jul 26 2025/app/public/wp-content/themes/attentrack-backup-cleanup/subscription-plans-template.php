<?php
/*
Template Name: Subscription Plans
*/

// Start output buffering to prevent headers already sent error
ob_start();

// Process any redirects or headers first
if (isset($_GET['status']) && $_GET['status'] === 'error') {
    $message = isset($_GET['message']) ? $_GET['message'] : 'unknown_error';
    // Handle error messages through WordPress admin notices instead
    add_action('wp_notices', function() use ($message) {
        $error_messages = [
            'invalid_request' => 'Invalid payment request. Please try again.',
            'invalid_signature' => 'Payment verification failed. Please contact support if this persists.',
            'payment_failed' => 'Payment processing failed. Please try again.',
            'unknown_error' => 'An unknown error occurred. Please try again.'
        ];
        $error_message = isset($error_messages[$message]) ? $error_messages[$message] : $error_messages['unknown_error'];
        echo '<div class="notice notice-error"><p>' . esc_html($error_message) . '</p></div>';
    });
}

get_header();

$current_user_id = get_current_user_id();
$current_plan = get_user_meta($current_user_id, 'subscription_plan_type', true);

// Get all subscription plans
$plans = attentrack_get_subscription_plans();

// Generate nonce for free plan activation
$free_plan_nonce = wp_create_nonce('activate_free_plan');
?>

<style>
    :root {
        --primary-color: #4f46e5;
        --primary-light: #818cf8;
        --primary-dark: #3730a3;
        --secondary-color: #10b981;
        --accent-color: #f59e0b;
        --text-dark: #1f2937;
        --text-light: #6b7280;
        --bg-light: #f9fafb;
        --bg-white: #ffffff;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --radius-sm: 0.375rem;
        --radius-md: 0.5rem;
        --radius-lg: 1rem;
    }

    .subscription-hero {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        padding: 80px 20px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .subscription-hero::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('data:image/svg+xml;utf8,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="M0 0 L50 50 L100 0 L100 100 L0 100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
        background-size: 100px 100px;
        opacity: 0.2;
    }

    .subscription-hero h1 {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 1rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .subscription-hero p {
        font-size: 1.25rem;
        max-width: 700px;
        margin: 0 auto 2rem;
        opacity: 0.9;
    }

    .plans-container {
        max-width: 1200px;
        margin: -60px auto 80px;
        padding: 0 20px;
        position: relative;
        z-index: 10;
    }

    .plans-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
        gap: 30px;
    }

    .plan-card {
        background: var(--bg-white);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .plan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .plan-header {
        padding: 30px;
        background: var(--bg-light);
        border-bottom: 1px solid rgba(0,0,0,0.05);
        text-align: center;
    }

    .plan-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 10px;
    }

    .plan-description {
        color: var(--text-light);
        font-size: 1rem;
        margin-bottom: 0;
    }

    .plan-price {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--primary-color);
        margin: 20px 0 10px;
    }

    .plan-price .currency {
        font-size: 1.5rem;
        font-weight: 600;
        vertical-align: top;
        margin-right: 4px;
    }

    .plan-price .period {
        font-size: 1rem;
        color: var(--text-light);
        font-weight: 400;
    }

    .plan-body {
        padding: 30px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .plan-features {
        list-style: none;
        padding: 0;
        margin: 0 0 30px;
        flex-grow: 1;
    }

    .plan-features li {
        padding: 12px 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        color: var(--text-dark);
        display: flex;
        align-items: center;
    }

    .plan-features li:last-child {
        border-bottom: none;
    }

    .plan-features li::before {
        content: "✓";
        color: var(--secondary-color);
        font-weight: bold;
        margin-right: 10px;
    }

    .plan-features .highlight {
        font-weight: 600;
        color: var(--primary-color);
    }

    .plan-cta {
        text-align: center;
        margin-top: auto;
    }

    .plan-btn {
        display: inline-block;
        padding: 12px 30px;
        background: var(--primary-color);
        color: white;
        text-decoration: none;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        width: 100%;
    }

    .plan-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .plan-btn.secondary {
        background: transparent;
        color: var(--primary-color);
        border: 2px solid var(--primary-color);
    }

    .plan-btn.secondary:hover {
        background: rgba(79, 70, 229, 0.1);
        color: var(--primary-dark);
    }

    .plan-badge {
        display: inline-block;
        padding: 5px 12px;
        background: var(--accent-color);
        color: white;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .plan-card.popular {
        transform: scale(1.05);
        border: 2px solid var(--primary-color);
    }

    .plan-card.popular .plan-header {
        background: var(--primary-color);
        color: white;
    }

    .plan-card.popular .plan-name,
    .plan-card.popular .plan-description {
        color: white;
    }

    .plan-card.popular .plan-price {
        color: white;
    }

    .plan-card.popular .plan-price .period {
        color: rgba(255,255,255,0.8);
    }

    .plan-card.popular .plan-badge {
        background: white;
        color: var(--primary-color);
    }

    .plan-card.popular .plan-btn {
        background: white;
        color: var(--primary-color);
    }

    .plan-card.popular .plan-btn:hover {
        background: rgba(255,255,255,0.9);
    }

    .faq-section {
        max-width: 800px;
        margin: 80px auto;
        padding: 0 20px;
    }

    .faq-title {
        text-align: center;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 40px;
        color: var(--text-dark);
    }

    .faq-item {
        margin-bottom: 20px;
        border-radius: var(--radius-md);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        background: var(--bg-white);
    }

    .faq-question {
        padding: 20px;
        background: var(--bg-light);
        font-weight: 600;
        color: var(--text-dark);
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .faq-question::after {
        content: "+";
        font-size: 1.5rem;
        transition: transform 0.3s ease;
    }

    .faq-item[open] .faq-question::after {
        content: "−";
    }

    .faq-answer {
        padding: 20px;
        color: var(--text-light);
    }

    .testimonials {
        background: var(--bg-light);
        padding: 80px 20px;
        text-align: center;
    }

    .testimonials-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 40px;
        color: var(--text-dark);
    }

    .testimonial-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .testimonial-card {
        background: var(--bg-white);
        border-radius: var(--radius-md);
        padding: 30px;
        box-shadow: var(--shadow-md);
        text-align: left;
    }

    .testimonial-text {
        font-style: italic;
        color: var(--text-dark);
        margin-bottom: 20px;
        position: relative;
    }

    .testimonial-text::before,
    .testimonial-text::after {
        content: '"';
        font-size: 2rem;
        color: var(--primary-light);
        position: absolute;
    }

    .testimonial-text::before {
        top: -10px;
        left: -15px;
    }

    .testimonial-text::after {
        bottom: -30px;
        right: -15px;
    }

    .testimonial-author {
        display: flex;
        align-items: center;
    }

    .testimonial-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin-right: 15px;
        background: var(--primary-light);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
    }

    .testimonial-info {
        display: flex;
        flex-direction: column;
    }

    .testimonial-name {
        font-weight: 600;
        color: var(--text-dark);
    }

    .testimonial-role {
        font-size: 0.875rem;
        color: var(--text-light);
    }

    .cta-section {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        padding: 80px 20px;
        text-align: center;
        margin-top: 80px;
    }

    .cta-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .cta-text {
        font-size: 1.125rem;
        max-width: 600px;
        margin: 0 auto 30px;
        opacity: 0.9;
    }

    .cta-btn {
        display: inline-block;
        padding: 15px 40px;
        background: white;
        color: var(--primary-color);
        text-decoration: none;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .cta-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }

    @media (max-width: 768px) {
        .subscription-hero h1 {
            font-size: 2rem;
        }
        
        .plans-grid {
            grid-template-columns: 1fr;
        }
        
        .plan-card.popular {
            transform: scale(1);
        }
    }
    
    .plan-group-header {
        grid-column: 1 / -1;
        text-align: center;
        margin: 30px 0;
        padding-top: 30px;
        border-top: 1px solid rgba(0,0,0,0.1);
    }
    
    .plan-group-header:first-child {
        border-top: none;
        padding-top: 0;
    }
    
    .plan-group-header h2 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 10px;
    }
    
    .plan-group-header p {
        font-size: 1.125rem;
        color: var(--text-light);
        max-width: 600px;
        margin: 0 auto;
    }
</style>

<div class="subscription-hero">
    <h1>Choose the Perfect Plan for Your Needs</h1>
    <p>Unlock the full potential of attention assessment with our flexible subscription plans. Whether you're an individual or a large organization, we have the right solution for you.</p>
</div>

<div class="plans-container">
    <div class="plans-grid">
        <?php
        // Get all subscription plans
        
        // Small Scale Plans
        if (isset($plans['small_scale']) && is_array($plans['small_scale'])) {
            echo '<div class="plan-group-header"><h2>Small Scale Plans</h2><p>Ideal for individuals and small teams</p></div>';
            
            foreach ($plans['small_scale'] as $plan) {
                $is_popular = ($plan['type'] === 'small_60'); // Mark the 60 members plan as popular
                $is_free = ($plan['price'] === 0);
                $period_text = ($plan['days_limit'] > 0) ? "/{$plan['days_limit']} days" : "/month";
                $member_text = ($plan['member_limit'] > 0) ? "{$plan['member_limit']} members" : "Unlimited members";
                
                // Check if this is the current plan
                $is_current = ($current_plan === $plan['type']);
                
                ?>
                <div class="plan-card <?php echo $is_popular ? 'popular' : ''; ?>">
                    <div class="plan-header">
                        <div class="plan-badge"><?php echo $is_popular ? 'Most Popular' : 'Small Scale'; ?></div>
                        <h2 class="plan-name"><?php echo esc_html($plan['name']); ?></h2>
                        <p class="plan-description"><?php echo esc_html($plan['description']); ?></p>
                        <div class="plan-price">
                            <span class="currency">₹</span><?php echo esc_html($plan['price']); ?>
                            <span class="period"><?php echo esc_html($period_text); ?></span>
                        </div>
                    </div>
                    <div class="plan-body">
                        <ul class="plan-features">
                            <?php if ($plan['member_limit'] > 0) : ?>
                                <li><span class="highlight"><?php echo esc_html($member_text); ?></span> capacity</li>
                            <?php else : ?>
                                <li><span class="highlight">Unlimited members</span></li>
                            <?php endif; ?>
                            
                            <?php foreach ($plan['features'] as $feature) : ?>
                                <li><?php echo esc_html($feature); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="plan-cta">
                            <?php if ($is_current) : ?>
                                <button class="plan-btn" disabled>Current Plan</button>
                            <?php else : ?>
                                <a href="<?php echo esc_url(add_query_arg(array(
                                    'action' => 'change_plan',
                                    'plan' => $plan['type'],
                                    '_wpnonce' => wp_create_nonce('change_plan_' . $plan['type'])
                                ), admin_url('admin-post.php'))); ?>" class="plan-btn">
                                    <?php echo $is_free ? 'Get Started' : 'Subscribe Now'; ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        
        // Large Scale Plans
        if (isset($plans['large_scale']) && is_array($plans['large_scale'])) {
            echo '<div class="plan-group-header"><h2>Large Scale Plans</h2><p>For organizations with advanced needs</p></div>';
            
            foreach ($plans['large_scale'] as $plan) {
                $period_text = ($plan['days_limit'] > 0) ? "/{$plan['days_limit']} days" : "/month";
                $member_text = ($plan['member_limit'] > 0) ? "{$plan['member_limit']} members" : "Unlimited members";
                
                // Check if this is the current plan
                $is_current = ($current_plan === $plan['type']);
                
                ?>
                <div class="plan-card">
                    <div class="plan-header">
                        <div class="plan-badge">Large Scale</div>
                        <h2 class="plan-name"><?php echo esc_html($plan['name']); ?></h2>
                        <p class="plan-description"><?php echo esc_html($plan['description']); ?></p>
                        <div class="plan-price">
                            <span class="currency">₹</span><?php echo esc_html($plan['price']); ?>
                            <span class="period"><?php echo esc_html($period_text); ?></span>
                        </div>
                    </div>
                    <div class="plan-body">
                        <ul class="plan-features">
                            <?php if ($plan['member_limit'] > 0) : ?>
                                <li><span class="highlight"><?php echo esc_html($member_text); ?></span> capacity</li>
                            <?php else : ?>
                                <li><span class="highlight">Unlimited members</span></li>
                            <?php endif; ?>
                            
                            <?php foreach ($plan['features'] as $feature) : ?>
                                <li><?php echo esc_html($feature); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="plan-cta">
                            <?php if ($is_current) : ?>
                                <button class="plan-btn" disabled>Current Plan</button>
                            <?php else : ?>
                                <a href="<?php echo esc_url(add_query_arg(array(
                                    'action' => 'change_plan',
                                    'plan' => $plan['type'],
                                    '_wpnonce' => wp_create_nonce('change_plan_' . $plan['type'])
                                ), admin_url('admin-post.php'))); ?>" class="plan-btn">Subscribe Now</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>

<div class="faq-section">
    <h2 class="faq-title">Frequently Asked Questions</h2>
    
    <details class="faq-item">
        <summary class="faq-question">What is included in the Free Tier?</summary>
        <div class="faq-answer">
            <p>Our Free Tier includes basic attention assessment tools, limited reporting capabilities, and standard email support. It's perfect for individuals who want to explore our platform before committing to a paid subscription.</p>
        </div>
    </details>
    
    <details class="faq-item">
        <summary class="faq-question">Can I upgrade or downgrade my plan?</summary>
        <div class="faq-answer">
            <p>Yes, you can upgrade or downgrade your subscription plan at any time. When upgrading, you'll be charged the prorated difference for the remainder of your billing cycle. When downgrading, the changes will take effect at the start of your next billing cycle.</p>
        </div>
    </details>
    
    <details class="faq-item">
        <summary class="faq-question">How does the member capacity work?</summary>
        <div class="faq-answer">
            <p>The member capacity refers to the maximum number of users you can add to your organization's account. Each member can access the platform and utilize the assessment tools based on the permissions you assign to them.</p>
        </div>
    </details>
    
    <details class="faq-item">
        <summary class="faq-question">Do you offer custom enterprise solutions?</summary>
        <div class="faq-answer">
            <p>Yes, we offer custom enterprise solutions for organizations with specific requirements. Our enterprise plans include custom integrations, dedicated account management, and tailored assessment tools. Please contact our sales team for more information.</p>
        </div>
    </details>
    
    <details class="faq-item">
        <summary class="faq-question">What payment methods do you accept?</summary>
        <div class="faq-answer">
            <p>We accept all major credit cards, debit cards, and net banking options. For enterprise plans, we also offer invoice-based payments with terms. All payments are processed securely through our payment gateway.</p>
        </div>
    </details>
</div>

<div class="testimonials">
    <h2 class="testimonials-title">What Our Customers Say</h2>
    
    <div class="testimonial-grid">
        <div class="testimonial-card">
            <p class="testimonial-text">AttenTrack has transformed how we assess and improve attention skills in our school. The comprehensive analytics have helped us identify areas where students need additional support.</p>
            <div class="testimonial-author">
                <div class="testimonial-avatar">RP</div>
                <div class="testimonial-info">
                    <div class="testimonial-name">Rajesh Patel</div>
                    <div class="testimonial-role">School Principal</div>
                </div>
            </div>
        </div>
        
        <div class="testimonial-card">
            <p class="testimonial-text">The 60 Members Plan has been perfect for our growing therapy center. The assessment tools are intuitive, and the support team is always responsive to our questions.</p>
            <div class="testimonial-author">
                <div class="testimonial-avatar">AS</div>
                <div class="testimonial-info">
                    <div class="testimonial-name">Anita Sharma</div>
                    <div class="testimonial-role">Therapy Center Director</div>
                </div>
            </div>
        </div>
        
        <div class="testimonial-card">
            <p class="testimonial-text">We started with the Free Tier to test the platform and quickly upgraded to the 30 Members Plan. The transition was seamless, and the additional features have been invaluable for our small practice.</p>
            <div class="testimonial-author">
                <div class="testimonial-avatar">VK</div>
                <div class="testimonial-info">
                    <div class="testimonial-name">Vikram Kumar</div>
                    <div class="testimonial-role">Child Psychologist</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="cta-section">
    <h2 class="cta-title">Ready to Get Started?</h2>
    <p class="cta-text">Choose the plan that suits your needs and start improving attention assessment today. Our team is ready to help you every step of the way.</p>
    <a href="#" class="cta-btn">View Plans</a>
</div>

<?php get_footer(); ?>

<?php
// End output buffering and flush
ob_end_flush();
?>
