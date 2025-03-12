<?php
/**
 * Template Name: Test Results
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// Get the test_id from URL parameter
$test_id = isset($_GET['test_id']) ? sanitize_text_field($_GET['test_id']) : '';

// Get the latest test results for the current user
$args = array(
    'post_type' => 'test_result',
    'posts_per_page' => 5,
    'author' => get_current_user_id(),
    'orderby' => 'date',
    'order' => 'DESC',
    'meta_query' => array(
        array(
            'key' => 'test_id',
            'value' => $test_id,
            'compare' => $test_id ? '=' : 'EXISTS'
        )
    )
);

$results = new WP_Query($args);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Results</title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="results-container">
    <h1 class="results-title">Test Results Summary</h1>
    
    <?php if ($results->have_posts()) : ?>
        <div class="results-grid">
            <?php 
            $phases = array();
            while ($results->have_posts()) : 
                $results->the_post();
                $phase = get_post_meta(get_the_ID(), 'test_phase', true);
                $phases[$phase] = array(
                    'total_responses' => get_post_meta(get_the_ID(), 'total_responses', true),
                    'correct_responses' => get_post_meta(get_the_ID(), 'correct_responses', true),
                    'accuracy' => get_post_meta(get_the_ID(), 'accuracy', true)
                );
            endwhile;
            wp_reset_postdata();

            // Calculate overall performance
            $total_accuracy = 0;
            $phase_count = count($phases);

            foreach ($phases as $phase_data) {
                $total_accuracy += floatval($phase_data['accuracy']);
            }

            $avg_accuracy = $phase_count > 0 ? $total_accuracy / $phase_count : 0;
            ?>

            <div class="overall-stats">
                <h2>Overall Performance</h2>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($avg_accuracy, 2); ?>%</div>
                    <div class="stat-label">Average Accuracy</div>
                </div>
            </div>

            <div class="phase-results">
                <h2>Phase-wise Performance</h2>
                <div class="phase-grid">
                    <?php foreach ($phases as $phase => $data) : ?>
                        <div class="phase-card">
                            <h3>Phase <?php echo esc_html($phase); ?></h3>
                            <div class="phase-stats">
                                <div class="stat">
                                    <span class="label">Total Responses:</span>
                                    <span class="value"><?php echo esc_html($data['total_responses']); ?></span>
                                </div>
                                <div class="stat">
                                    <span class="label">Correct Responses:</span>
                                    <span class="value"><?php echo esc_html($data['correct_responses']); ?></span>
                                </div>
                                <div class="stat">
                                    <span class="label">Accuracy:</span>
                                    <span class="value"><?php echo number_format($data['accuracy'], 2); ?>%</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="results-actions">
            <a href="<?php echo esc_url(home_url('/selectionpage')); ?>" class="btn btn-primary">Back to Home</a>
            <button class="btn btn-secondary" onclick="window.print()">Print Results</button>
        </div>

    <?php else : ?>
        <div class="no-results">
            <p>No test results found.</p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">Back to Home</a>
        </div>
    <?php endif; ?>
</div>

<style>
.results-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.results-title {
    text-align: center;
    color: var(--secondary-color);
    margin-bottom: 40px;
    font-size: 2.5rem;
}

.results-grid {
    display: flex;
    flex-direction: column;
    gap: 40px;
}

.overall-stats {
    text-align: center;
    padding: 30px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.overall-stats h2 {
    color: var(--secondary-color);
    margin-bottom: 30px;
}

.stat-card {
    display: inline-block;
    padding: 20px 40px;
    margin: 0 15px;
    background: var(--light-bg);
    border-radius: 8px;
    text-align: center;
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 10px;
}

.stat-label {
    color: var(--text-color);
    font-size: 1.1rem;
}

.phase-results {
    padding: 30px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.phase-results h2 {
    text-align: center;
    color: var(--secondary-color);
    margin-bottom: 30px;
}

.phase-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.phase-card {
    padding: 20px;
    background: var(--light-bg);
    border-radius: 8px;
}

.phase-card h3 {
    color: var(--primary-color);
    margin-bottom: 20px;
    text-align: center;
}

.phase-stats {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.stat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
}

.results-actions {
    margin-top: 40px;
    text-align: center;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    margin: 0 10px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    cursor: pointer;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-secondary {
    background: var(--secondary-color);
    color: white;
    border: none;
}

@media (max-width: 992px) {
    .phase-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .stat-card {
        display: block;
        margin: 15px 0;
    }

    .phase-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php wp_footer(); ?>
