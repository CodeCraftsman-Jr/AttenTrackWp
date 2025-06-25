<?php
/*
Template Name: Selective Test Instructions
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
        <h1 class="page-title">Selective Attention Test Instructions</h1>
        
        <div class="instruction-card">
            <h2 class="mb-4">How to Take the Test</h2>
            
            <ul class="instruction-list">
                <li>You will be shown a series of visual elements on the screen.</li>
                <li>Your task is to focus on specific target elements while ignoring distractions.</li>
                <li>Click or tap on the target elements as quickly as possible when they appear.</li>
                <li>The test measures your ability to:
                    <ul>
                        <li>Identify target elements accurately</li>
                        <li>Maintain focus on relevant information</li>
                        <li>Filter out distractions</li>
                        <li>Respond quickly and precisely</li>
                    </ul>
                </li>
                <li>Your performance will be evaluated based on:
                    <ul>
                        <li>Response accuracy (correct vs. incorrect clicks)</li>
                        <li>Reaction time (speed of response)</li>
                        <li>Consistency in maintaining attention</li>
                    </ul>
                </li>
            </ul>

            <div class="alert alert-info mt-4">
                <strong>Tips:</strong>
                <ul class="mb-0">
                    <li>Find a quiet, well-lit environment</li>
                    <li>Maintain a comfortable sitting position</li>
                    <li>Stay focused throughout the test duration</li>
                    <li>Respond as quickly and accurately as possible</li>
                </ul>
            </div>

            <div class="btn-group">
                <a href="<?php echo home_url('/selection-page'); ?>" class="btn-test btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Back to Tests
                </a>
                <?php 
                $test_page = get_page_by_path('demo-selective-test');
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
