<?php get_header(); ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="overlay-container">
        <div class="wel2-text">
            <h1 class="wel-text">Know Your Attention Level!</h1>
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
<section class="take-test-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="mb-4">Take Our Attention Assessment Test</h2>
                <p class="lead mb-4">Discover your attention patterns and get personalized insights to improve your focus.</p>
                <ul class="feature-list">
                    <li><i class="fas fa-check"></i> Quick and easy to complete</li>
                    <li><i class="fas fa-check"></i> Instant results and analysis</li>
                    <li><i class="fas fa-check"></i> Professional recommendations</li>
                </ul>
            </div>
            <div class="col-lg-4 text-center">
                <a href="<?php echo esc_url(home_url('/home2-template')); ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-clipboard-check me-2"></i>Start Test Now
                </a>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="about-image">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/aboutus.png'); ?>" alt="<?php bloginfo('name'); ?>" class="img-fluid rounded">
                </div>
            </div>
            <div class="col-lg-6">
                <h2 class="section-title">About AttenTrack</h2>
                <p class="section-text">
                    Our medical website is dedicated to providing comprehensive healthcare solutions for both doctors and patients.
                    We focus on delivering personalized care through advanced technology, expert medical professionals, and a user-friendly platform.
                </p>
                <a href="<?php echo esc_url(get_permalink(get_page_by_path('about-app'))); ?>" class="btn btn-primary mt-4">Learn More</a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5">
    <div class="container">
        <h2 class="text-center mb-5">Our Features</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3>Attention Assessment</h3>
                    <p>Advanced tests to measure various aspects of attention and focus.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Progress Tracking</h3>
                    <p>Monitor your improvement over time with detailed analytics.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h3>Expert Insights</h3>
                    <p>Get professional recommendations based on your results.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section py-5">
    <div class="container text-center">
        <h2>Ready to Start Your Journey?</h2>
        <p class="lead mb-4">Take the first step towards better attention management today.</p>
        <a href="<?php echo esc_url(get_permalink(get_page_by_path('contact-us'))); ?>" class="btn btn-primary btn-lg">Get Started</a>
    </div>
</section>

<?php get_footer(); ?>