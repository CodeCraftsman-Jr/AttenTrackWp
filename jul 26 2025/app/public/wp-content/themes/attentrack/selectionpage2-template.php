<?php
/*
Template Name: Selection Page 2
*/

// Start session if not already started
if (!session_id()) {
    session_start();
}

// Check subscription status
$current_user_id = get_current_user_id();
$subscription = attentrack_get_subscription_status($current_user_id);

// Get plan type from subscription
$plan_type = $subscription['plan_type'] ?? 'free';

// Initialize error message
$error_message = '';

// Set test parameters based on subscription status and plan type
if ($subscription['status'] === 'active' && in_array($plan_type, ['basic', 'premium'])) {
    // Paid active subscription - full access
    $_SESSION['test_time_limit'] = 300; // 5 minutes
    $_SESSION['disable_db_storage'] = false;
    
    // Get full test pages for paid users
    $selective_page = get_page_by_path('selective-attention-test');
    $divided_page = get_page_by_path('divided-attention-test');
    $alternative_page = get_page_by_path('alternative-attention-test');
    $extended_page = get_page_by_path('extended-attention-test');

    // Check if all pages exist
    if (!$selective_page || !$divided_page || !$alternative_page || !$extended_page) {
        // Create missing pages
        if (!$selective_page) {
            wp_insert_post([
                'post_title' => 'Selective Attention Test',
                'post_name' => 'selective-attention-test',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '',
            ]);
        }
        if (!$divided_page) {
            wp_insert_post([
                'post_title' => 'Divided Attention Test',
                'post_name' => 'divided-attention-test',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '',
            ]);
        }
        if (!$alternative_page) {
            wp_insert_post([
                'post_title' => 'Alternative Attention Test',
                'post_name' => 'alternative-attention-test',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '',
            ]);
        }
        if (!$extended_page) {
            wp_insert_post([
                'post_title' => 'Extended Attention Test',
                'post_name' => 'extended-attention-test',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '',
            ]);
        }
        
        // Refresh page objects
        $selective_page = get_page_by_path('selective-attention-test');
        $divided_page = get_page_by_path('divided-attention-test');
        $alternative_page = get_page_by_path('alternative-attention-test');
        $extended_page = get_page_by_path('extended-attention-test');
    }
} else {
    // Free trial or expired subscription
    $_SESSION['test_time_limit'] = 15; // 15 seconds
    $_SESSION['disable_db_storage'] = true;
    
    // Get demo pages for free users
    $selective_page = get_page_by_path('demo-selective-test');
    $divided_page = get_page_by_path('demo-divided-test');
    $alternative_page = get_page_by_path('demo-alternative-test');
    $extended_page = get_page_by_path('demo-extended-test');

    // Check if all demo pages exist
    if (!$selective_page || !$divided_page || !$alternative_page || !$extended_page) {
        // Create missing demo pages
        if (!$selective_page) {
            wp_insert_post([
                'post_title' => 'Demo Selective Test',
                'post_name' => 'demo-selective-test',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '',
            ]);
        }
        if (!$divided_page) {
            wp_insert_post([
                'post_title' => 'Demo Divided Test',
                'post_name' => 'demo-divided-test',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '',
            ]);
        }
        if (!$alternative_page) {
            wp_insert_post([
                'post_title' => 'Demo Alternative Test',
                'post_name' => 'demo-alternative-test',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '',
            ]);
        }
        if (!$extended_page) {
            wp_insert_post([
                'post_title' => 'Demo Extended Test',
                'post_name' => 'demo-extended-test',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '',
            ]);
        }
        
        // Refresh page objects
        $selective_page = get_page_by_path('demo-selective-test');
        $divided_page = get_page_by_path('demo-divided-test');
        $alternative_page = get_page_by_path('demo-alternative-test');
        $extended_page = get_page_by_path('demo-extended-test');
    }
    
    // If subscription is expired, update plan type to free
    if ($subscription['status'] === 'expired') {
        $plan_type = 'free';
    }
}

get_header();

// Test descriptions
$test_descriptions = [
    'selective' => 'Focus on specific visual elements while ignoring distractions. Test your ability to concentrate on what matters.',
    'divided' => 'Handle multiple tasks simultaneously. Measure your capacity to split attention effectively.',
    'alternative' => 'Switch between different tasks smoothly. Evaluate your cognitive flexibility and adaptation skills.',
    'extended' => 'Maintain focus over an extended period. Assess your sustained attention capabilities.'
];
?>

<style>
.selection-container {
    min-height: 70vh;
    background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
    background-size: 400% 400%;
    animation: gradient 15s ease infinite;
    padding: 40px 0;
}

@keyframes gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
}

.page-title {
    text-align: center;
    color: white;
    margin-bottom: 50px;
    font-size: 2.8rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.test-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
    padding: 0 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.test-card {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 15px;
    padding: 30px 20px;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    min-height: 380px;
}

.test-icon {
    font-size: 3.5rem;
    margin-bottom: 20px;
    color: #4a4a4a;
    background: linear-gradient(45deg, #12c2e9, #c471ed, #f64f59);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.test-title {
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}

.test-description {
    font-size: 0.95rem;
    color: #666;
    line-height: 1.5;
    margin-bottom: 25px;
    flex-grow: 1;
}

.btn-group {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: auto;
}

.btn-test {
    padding: 10px 20px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 120px;
}

.btn-start {
    background: linear-gradient(45deg, #12c2e9, #c471ed);
    color: white;
}

.btn-instructions {
    background: linear-gradient(45deg, #f64f59, #c471ed);
    color: white;
}

.btn-test:hover {
    transform: translateY(-2px);
    color: white;
    text-decoration: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.btn-test i {
    margin-left: 8px;
}

.free-trial-notice {
    background: rgba(255, 255, 255, 0.95);
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 30px;
    text-align: center;
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

@media (max-width: 1200px) {
    .test-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .test-card {
        min-height: 350px;
    }
}

@media (max-width: 768px) {
    .container {
        padding: 20px;
    }
    
    .test-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .test-card {
        min-height: auto;
    }
    
    .page-title {
        font-size: 2rem;
        margin-bottom: 30px;
    }
}
</style>

<div class="selection-container">
    <div class="container">
        <h1 class="page-title">Choose Your Attention Test</h1>
        
        <?php if ($plan_type === 'free'): ?>
        <div class="free-trial-notice">
            <strong>Free Trial Mode:</strong> Tests are limited to 15 seconds and results won't be saved.
            <a href="<?php echo home_url('/subscription-plans'); ?>" class="btn btn-sm btn-primary ms-3">Upgrade Now</a>
        </div>
        <?php endif; ?>

        <?php if (!$selective_page || !$divided_page || !$alternative_page || !$extended_page): ?>
        <div class="alert alert-warning">
            <strong>Note:</strong> Some test pages are being set up. Please refresh the page.
            <?php if (current_user_can('administrator')): ?>
            <br>
            <small>Admin Note: Please ensure all test page templates exist and are properly configured.</small>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="test-grid">
            <?php if ($selective_page): ?>
            <div class="test-card">
                <div class="test-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h3 class="test-title">Selective Attention Test</h3>
                <p class="test-description"><?php echo esc_html($test_descriptions['selective']); ?></p>
                <div class="btn-group">
                    <a href="<?php echo get_permalink($selective_page->ID); ?>" class="btn-test btn-start">
                        Start Test <i class="fas fa-play ms-2"></i>
                    </a>
                    <a href="<?php echo home_url('/selective-test-instructions'); ?>" class="btn-test btn-instructions">
                        Instructions <i class="fas fa-info-circle ms-2"></i>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($divided_page): ?>
            <div class="test-card">
                <div class="test-icon">
                    <i class="fas fa-random"></i>
                </div>
                <h3 class="test-title">Divided Attention Test</h3>
                <p class="test-description"><?php echo esc_html($test_descriptions['divided']); ?></p>
                <div class="btn-group">
                    <a href="<?php echo get_permalink($divided_page->ID); ?>" class="btn-test btn-start">
                        Start Test <i class="fas fa-play ms-2"></i>
                    </a>
                    <a href="<?php echo home_url('/divided-test-instructions'); ?>" class="btn-test btn-instructions">
                        Instructions <i class="fas fa-info-circle ms-2"></i>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($alternative_page): ?>
            <div class="test-card">
                <div class="test-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3 class="test-title">Alternative Attention Test</h3>
                <p class="test-description"><?php echo esc_html($test_descriptions['alternative']); ?></p>
                <div class="btn-group">
                    <a href="<?php echo get_permalink($alternative_page->ID); ?>" class="btn-test btn-start">
                        Start Test <i class="fas fa-play ms-2"></i>
                    </a>
                    <a href="<?php echo home_url('/alternative-test-instructions'); ?>" class="btn-test btn-instructions">
                        Instructions <i class="fas fa-info-circle ms-2"></i>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($extended_page): ?>
            <div class="test-card">
                <div class="test-icon">
                    <i class="fas fa-expand-arrows-alt"></i>
                </div>
                <h3 class="test-title">Extended Attention Test</h3>
                <p class="test-description"><?php echo esc_html($test_descriptions['extended']); ?></p>
                <div class="btn-group">
                    <a href="<?php echo get_permalink($extended_page->ID); ?>" class="btn-test btn-start">
                        Start Test <i class="fas fa-play ms-2"></i>
                    </a>
                    <a href="<?php echo home_url('/extended-test-instructions'); ?>" class="btn-test btn-instructions">
                        Instructions <i class="fas fa-info-circle ms-2"></i>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!$selective_page || !$divided_page || !$alternative_page || !$extended_page): ?>
        <div class="alert alert-info mt-4 text-center">
            <strong>Note:</strong> Some test pages may not be available. Please contact the administrator.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
