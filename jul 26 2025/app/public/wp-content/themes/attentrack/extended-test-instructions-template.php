<?php
/*
Template Name: Extended Test Instructions
*/

get_header();
?>

<style>
.instructions-container {
    min-height: 100vh;
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

.instruction-card {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    max-width: 800px;
    margin: 0 auto;
}

.page-title {
    text-align: center;
    color: white;
    margin-bottom: 40px;
    font-size: 2.5rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.instruction-list {
    list-style-type: none;
    padding: 0;
    margin: 0;
}

.instruction-list li {
    margin-bottom: 15px;
    padding-left: 30px;
    position: relative;
}

.instruction-list li:before {
    content: "•";
    color: #23a6d5;
    font-size: 24px;
    position: absolute;
    left: 0;
    top: -2px;
}

.btn-group {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    justify-content: center;
}

.btn-test {
    padding: 12px 25px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    color: white;
}

.btn-start {
    background: linear-gradient(45deg, #12c2e9, #c471ed);
}

.btn-back {
    background: linear-gradient(45deg, #f64f59, #c471ed);
}

.btn-test:hover {
    transform: scale(1.05);
    color: white;
    text-decoration: none;
}

.example-image {
    max-width: 100%;
    border-radius: 10px;
    margin: 20px 0;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}
</style>

<div class="instructions-container">
    <div class="container">
        <h1 class="page-title">Extended Attention Test Instructions</h1>
        
        <div class="instruction-card">
            <h2 class="mb-4">How to Take the Test</h2>
            
            <ul class="instruction-list">
                <li>This test measures your ability to maintain focus over an extended period:</li>
                <li>Test Duration:
                    <ul>
                        <li>The test runs for a longer duration than other tests</li>
                        <li>You'll need to maintain consistent focus throughout</li>
                        <li>Regular breaks are built into the test</li>
                    </ul>
                </li>
                <li>Your Tasks:
                    <ul>
                        <li>Monitor the screen for specific patterns or changes</li>
                        <li>Respond to target stimuli quickly and accurately</li>
                        <li>Maintain concentration despite fatigue</li>
                        <li>Use break periods effectively</li>
                    </ul>
                </li>
                <li>Performance Evaluation:
                    <ul>
                        <li>Response accuracy over time</li>
                        <li>Consistency in performance</li>
                        <li>Fatigue resistance</li>
                        <li>Recovery after breaks</li>
                    </ul>
                </li>
            </ul>

            <div class="alert alert-info mt-4">
                <strong>Tips:</strong>
                <ul class="mb-0">
                    <li>Ensure you're well-rested before starting</li>
                    <li>Take advantage of break periods</li>
                    <li>Stay hydrated during the test</li>
                    <li>Maintain good posture to prevent fatigue</li>
                </ul>
            </div>

            <div class="btn-group">
                <a href="<?php echo home_url('/selection-page'); ?>" class="btn-test btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Back to Tests
                </a>
                <?php 
                $test_page = get_page_by_path('demo-extended-test');
                if ($test_page): 
                ?>
                <a href="<?php echo get_permalink($test_page->ID); ?>" class="btn-test btn-start">
                    Start Test <i class="fas fa-play ms-2"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
