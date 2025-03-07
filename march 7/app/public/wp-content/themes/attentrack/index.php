<?php get_header(); ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="overlay-container">
        <div class="hero-content text-center">
            <h1 class="hero-title">Know Your Attention Level!</h1>
            <div class="hero-quotes">
                <h3>"Strength doesn't come from what you can do, it comes from overcoming the things you once thought you couldn't."</h3>
                <h3>"Focus on progress, not perfection!"</h3>
                <h3>"Small steps lead to big leaps."</h3>
                <h3>"You're not lazy—you just think differently!"</h3>
                <h3>"Celebrate every small win—it's all progress!"</h3>
            </div>
        </div>
    </div>
</section>
<!-- Take Test Section -->
<section class="take-test-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 text-center text-lg-start">
                <h2 class="mb-4">Take Our Attention Assessment Test</h2>
                <p class="lead mb-4">Discover your attention patterns and get personalized insights to improve your focus.</p>
                <ul class="list-unstyled mb-4">
                    <li><i class="fas fa-check me-2"></i> Quick and easy to complete</li>
                    <li><i class="fas fa-check me-2"></i> Instant results and analysis</li>
                    <li><i class="fas fa-check me-2"></i> Professional recommendations</li>
                </ul>
            </div>
            <div class="col-lg-4 text-center">
                <a href="<?php echo esc_url(home_url('/home2-template')); ?>" class="btn btn-light btn-lg px-4 py-3">
                    <i class="fas fa-clipboard-check me-2"></i>Start Test Now
                </a>
            </div>
        </div>
    </div>
</section>
<!-- About Section -->
<section class="about-section">
    <div class="container mt-5">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12">
            <img src="<?php echo site_url(); ?>/wp-content/includes/images/aboutus.png" width="2000px" alt="<?php bloginfo('name'); ?>" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6 col-md-12">
                <h2 class="section-title">About AttenTrack</h2>
                <p class="section-text">
                    Our medical website is dedicated to providing comprehensive healthcare solutions for both doctors and patients.
                    We focus on delivering personalized care through advanced technology, expert medical professionals, and a user-friendly platform.
                </p>
                <a href="<?php echo esc_url(get_permalink(get_page_by_path('about-app'))); ?>" class="btn btn-primary mt-3">Read More</a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section bg-light py-5 mt-5">
    <div class="container">
        <h2 class="text-center mb-5">Our Features</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-brain fa-3x text-primary mb-3"></i>
                        <h3 class="card-title">Attention Assessment</h3>
                        <p class="card-text">Advanced tests to measure various aspects of attention and focus.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                        <h3 class="card-title">Progress Tracking</h3>
                        <p class="card-text">Monitor your improvement over time with detailed analytics.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-user-md fa-3x text-primary mb-3"></i>
                        <h3 class="card-title">Expert Insights</h3>
                        <p class="card-text">Get professional recommendations based on your results.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Call to Action -->
<section class="cta-section text-center py-5">
    <div class="container">
        <h2>Ready to Start Your Journey?</h2>
        <p class="lead">Take the first step towards better attention management today.</p>
        <a href="<?php echo esc_url(get_permalink(get_page_by_path('contact-us'))); ?>" class="btn btn-primary btn-lg mt-3">Get Started</a>
    </div>
</section>

<?php get_footer(); ?>