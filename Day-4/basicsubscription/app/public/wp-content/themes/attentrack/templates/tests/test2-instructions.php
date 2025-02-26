<?php
/**
 * Template Name: Test 2 Instructions
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

get_header();
?>

<div class="instructions-container">
    <div class="instructions-content">
        <h1>Instructions</h1>
        <p>Welcome to the Alternate Attention Test! Please follow the instructions below:</p>
        
        <div class="instructions-list">
            <div class="instruction-item">
                <span class="instruction-number">1</span>
                <p>Every Alphabet from A to Z will be shown on screen with a number representing them</p>
            </div>
            
            <div class="instruction-item">
                <span class="instruction-number">2</span>
                <p>For Example: Below this __, blank line, if 8 is mentioned then you should write H in the blank space.</p>
            </div>
            
            <div class="instruction-item">
                <span class="instruction-number">3</span>
                <p>You can choose the response only once, correction is not allowed.</p>
            </div>
            
            <div class="instruction-item">
                <span class="instruction-number">4</span>
                <p>You have to attempt sequentially, do not skip any blank lines.</p>
            </div>
            
            <div class="instruction-item">
                <span class="instruction-number">5</span>
                <p>Maximum 50 blank lines will be displayed.</p>
            </div>
            
            <div class="instruction-item">
                <span class="instruction-number">6</span>
                <p>Total time: 1 minute</p>
            </div>
        </div>

        <div class="instructions-actions">
            <a href="<?php echo esc_url(home_url('/test2-trial')); ?>" class="btn btn-primary">Try Demo Version</a>
            <a href="<?php echo esc_url(home_url('/test2')); ?>" class="btn btn-secondary">Start Full Test</a>
        </div>
    </div>
</div>

<style>
.instructions-container {
    min-height: calc(100vh - 100px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: var(--light-bg);
}

.instructions-content {
    max-width: 800px;
    width: 100%;
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.instructions-content h1 {
    color: var(--secondary-color);
    text-align: center;
    margin-bottom: 30px;
    font-size: 2.5rem;
}

.instructions-content > p {
    text-align: center;
    color: var(--text-color);
    font-size: 1.2rem;
    margin-bottom: 40px;
}

.instructions-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 40px;
}

.instruction-item {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 20px;
    background: var(--light-bg);
    border-radius: 10px;
    transition: transform 0.3s ease;
}

.instruction-item:hover {
    transform: translateX(10px);
}

.instruction-number {
    width: 30px;
    height: 30px;
    background: var(--primary-color);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.instruction-item p {
    margin: 0;
    color: var(--text-color);
    font-size: 1.1rem;
    line-height: 1.5;
}

.instructions-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    margin-top: 40px;
}

.instructions-actions .btn {
    padding: 12px 30px;
    font-size: 1.1rem;
    border-radius: 25px;
    transition: all 0.3s ease;
}

.instructions-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
    border: none;
}

.btn-secondary {
    background-color: var(--secondary-color);
    color: white;
    border: none;
}

@media (max-width: 768px) {
    .instructions-content {
        padding: 20px;
    }

    .instructions-actions {
        flex-direction: column;
    }

    .instructions-actions .btn {
        width: 100%;
        text-align: center;
    }
}
</style>

<?php get_footer(); ?>
