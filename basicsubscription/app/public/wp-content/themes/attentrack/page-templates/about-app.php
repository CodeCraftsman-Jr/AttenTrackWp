<?php
/**
 * Template Name: About App Page
 * 
 * This is the template that displays the about app page.
 */

get_header(); ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4"><?php the_title(); ?></h1>
            <div class="content">
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <?php the_content(); ?>
                <?php endwhile; endif; ?>
                
                <div class="about-content mt-4">
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <img src="<?php echo get_theme_file_uri('assets/images/about-feature.jpg'); ?>" alt="About AttenTrack" class="img-fluid rounded shadow">
                        </div>
                        <div class="col-md-6">
                            <h2>Our Mission</h2>
                            <p>AttenTrack is dedicated to helping individuals understand and improve their attention levels through innovative assessment tools and personalized tracking solutions.</p>
                            <p>We believe that everyone deserves the opportunity to reach their full potential, and proper attention management is key to achieving this goal.</p>
                        </div>
                    </div>

                    <div class="features mt-5">
                        <h2 class="text-center mb-4">What We Offer</h2>
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <i class="fas fa-brain fa-3x text-primary mb-3"></i>
                                        <h3 class="card-title">Attention Assessment</h3>
                                        <p class="card-text">Advanced tests to measure various aspects of attention and focus.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                                        <h3 class="card-title">Progress Tracking</h3>
                                        <p class="card-text">Monitor your improvement over time with detailed analytics.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <i class="fas fa-user-md fa-3x text-primary mb-3"></i>
                                        <h3 class="card-title">Expert Insights</h3>
                                        <p class="card-text">Get professional recommendations based on your results.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cta-section text-center py-5 mt-5">
                    <h2>Ready to Start Your Journey?</h2>
                    <p class="lead">Take the first step towards better attention management today.</p>
                    <a href="<?php echo esc_url(get_permalink(get_page_by_path('contact-us'))); ?>" class="btn btn-primary btn-lg mt-3">Contact Us Now</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>
