<?php get_header(); ?>

<!-- Hero Section - Enhanced Design -->
<section class="hero-section">
    <div class="container">
        <div class="overlay-container">
            <div class="wel2-text">
                <h1 class="wel-text">Discover Your Attention Potential</h1>
                <p class="hero-subtitle lead text-white mb-4">
                    Unlock insights into your cognitive abilities with our scientifically-backed attention assessment platform
                </p>
                <div class="hero-quotes">
                    <h3>"Strength doesn't come from what you can do, it comes from overcoming the things you once thought you couldn't."</h3>
                    <h3>"Focus on progress, not perfection!"</h3>
                    <h3>"Small steps lead to big leaps."</h3>
                    <h3>"You're not lazy—you just think differently!"</h3>
                    <h3>"Celebrate every small win—it's all progress!"</h3>
                </div>
                <div class="hero-cta">
                    <a href="<?php
                        $home2_page = get_page_by_path('home2');
                        echo $home2_page ? esc_url(get_permalink($home2_page->ID)) : '#';
                    ?>" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-brain me-2"></i>Start Assessment
                    </a>
                    <a href="<?php echo esc_url(get_permalink(get_page_by_path('about-app'))); ?>" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-info-circle me-2"></i>Learn More
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Assessment Overview Section -->
<section class="assessment-section py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="assessment-content">
                    <div class="section-badge mb-3">
                        <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill">
                            <i class="fas fa-brain me-2"></i>Assessment Platform
                        </span>
                    </div>
                    <h2 class="section-title mb-4">Professional Attention Assessment</h2>
                    <p class="lead mb-4">
                        Our scientifically-validated assessment tools provide comprehensive insights into your cognitive attention patterns, helping you understand and optimize your mental performance.
                    </p>
                    <div class="feature-list mb-4">
                        <div class="feature-item d-flex align-items-center mb-3">
                            <div class="feature-icon me-3">
                                <i class="fas fa-clock text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Quick & Efficient</h6>
                                <p class="text-muted mb-0">Complete in 15-20 minutes</p>
                            </div>
                        </div>
                        <div class="feature-item d-flex align-items-center mb-3">
                            <div class="feature-icon me-3">
                                <i class="fas fa-chart-line text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Instant Analysis</h6>
                                <p class="text-muted mb-0">Real-time results and insights</p>
                            </div>
                        </div>
                        <div class="feature-item d-flex align-items-center mb-3">
                            <div class="feature-icon me-3">
                                <i class="fas fa-user-md text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Expert Recommendations</h6>
                                <p class="text-muted mb-0">Professional guidance and tips</p>
                            </div>
                        </div>
                    </div>
                    <div class="cta-buttons">
                        <a href="<?php
                            $home2_page = get_page_by_path('home2');
                            echo $home2_page ? esc_url(get_permalink($home2_page->ID)) : '#';
                        ?>" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-play me-2"></i>Start Assessment
                        </a>
                        <a href="#features" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-info-circle me-2"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="assessment-visual">
                    <div class="visual-card">
                        <div class="cognitive-assessment-card">
                            <div class="text-center">
                                <div class="assessment-icon mb-3">
                                    <i class="fas fa-brain fa-4x text-primary"></i>
                                </div>
                                <h4 class="mb-3">Cognitive Assessment</h4>
                                <p class="text-muted mb-4">Advanced algorithms analyze your attention patterns across multiple dimensions</p>
                                <div class="progress-demo">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="small">Assessment Progress</span>
                                        <span class="small">85%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" style="width: 85%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about-section py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="about-image position-relative">
                    <div class="image-wrapper">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/aboutus.png'); ?>"
                             alt="<?php bloginfo('name'); ?>"
                             class="img-fluid rounded-3 shadow-lg">
                        <div class="image-overlay"></div>
                    </div>
                    <div class="floating-stats">
                        <div class="stat-card">
                            <div class="stat-number">10K+</div>
                            <div class="stat-label">Assessments Completed</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-content">
                    <div class="section-badge mb-3">
                        <span class="badge bg-accent-light text-accent px-3 py-2 rounded-pill">
                            <i class="fas fa-heart me-2"></i>About AttenTrack
                        </span>
                    </div>
                    <h2 class="section-title mb-4">Empowering Cognitive Wellness Through Technology</h2>
                    <p class="lead mb-4">
                        AttenTrack combines cutting-edge neuroscience research with intuitive technology to provide comprehensive attention assessment solutions for healthcare professionals and individuals.
                    </p>
                    <p class="text-muted mb-4">
                        Our platform bridges the gap between clinical assessment and practical application, offering evidence-based insights that help users understand and optimize their cognitive performance in real-world settings.
                    </p>
                    <div class="about-highlights mb-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="highlight-item">
                                    <i class="fas fa-microscope text-primary me-2"></i>
                                    <span class="small">Research-Based</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="highlight-item">
                                    <i class="fas fa-shield-alt text-primary me-2"></i>
                                    <span class="small">HIPAA Compliant</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="highlight-item">
                                    <i class="fas fa-users text-primary me-2"></i>
                                    <span class="small">Multi-User Support</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="highlight-item">
                                    <i class="fas fa-mobile-alt text-primary me-2"></i>
                                    <span class="small">Mobile Friendly</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="about-cta">
                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('about-app'))); ?>" class="btn btn-primary me-3">
                            <i class="fas fa-arrow-right me-2"></i>Learn More
                        </a>
                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('contact-us'))); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="features-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-badge mb-3">
                <span class="badge bg-secondary-light text-secondary px-3 py-2 rounded-pill">
                    <i class="fas fa-star me-2"></i>Platform Features
                </span>
            </div>
            <h2 class="section-title mb-4">Comprehensive Assessment Tools</h2>
            <p class="lead text-muted max-width-lg mx-auto">
                Our platform combines cutting-edge technology with proven psychological assessment methods to deliver accurate, actionable insights.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon mb-4">
                                <div class="icon-wrapper bg-primary-light rounded-circle d-inline-flex align-items-center justify-content-center">
                                    <i class="fas fa-brain fa-2x text-primary"></i>
                                </div>
                            </div>
                            <h4 class="card-title mb-3">Multi-Domain Assessment</h4>
                            <p class="card-text text-muted">
                                Comprehensive evaluation across selective, divided, and sustained attention domains with scientifically validated protocols.
                            </p>
                            <div class="feature-highlights mt-3">
                                <small class="text-primary">
                                    <i class="fas fa-check me-1"></i>Selective Attention
                                </small><br>
                                <small class="text-primary">
                                    <i class="fas fa-check me-1"></i>Divided Attention
                                </small><br>
                                <small class="text-primary">
                                    <i class="fas fa-check me-1"></i>Sustained Focus
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon mb-4">
                                <div class="icon-wrapper bg-secondary-light rounded-circle d-inline-flex align-items-center justify-content-center">
                                    <i class="fas fa-chart-line fa-2x text-secondary"></i>
                                </div>
                            </div>
                            <h4 class="card-title mb-3">Real-Time Analytics</h4>
                            <p class="card-text text-muted">
                                Advanced data visualization and progress tracking with detailed performance metrics and trend analysis over time.
                            </p>
                            <div class="feature-highlights mt-3">
                                <small class="text-secondary">
                                    <i class="fas fa-check me-1"></i>Performance Metrics
                                </small><br>
                                <small class="text-secondary">
                                    <i class="fas fa-check me-1"></i>Progress Tracking
                                </small><br>
                                <small class="text-secondary">
                                    <i class="fas fa-check me-1"></i>Trend Analysis
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon mb-4">
                                <div class="icon-wrapper bg-accent-light rounded-circle d-inline-flex align-items-center justify-content-center">
                                    <i class="fas fa-user-md fa-2x text-accent"></i>
                                </div>
                            </div>
                            <h4 class="card-title mb-3">Professional Insights</h4>
                            <p class="card-text text-muted">
                                Expert-level recommendations and personalized improvement strategies based on your unique cognitive profile.
                            </p>
                            <div class="feature-highlights mt-3">
                                <small class="text-accent">
                                    <i class="fas fa-check me-1"></i>Expert Analysis
                                </small><br>
                                <small class="text-accent">
                                    <i class="fas fa-check me-1"></i>Custom Strategies
                                </small><br>
                                <small class="text-accent">
                                    <i class="fas fa-check me-1"></i>Improvement Plans
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section py-5">
    <div class="container">
        <div class="cta-wrapper">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="cta-content">
                        <h2 class="cta-title mb-3">Ready to Unlock Your Cognitive Potential?</h2>
                        <p class="cta-subtitle mb-4">
                            Join thousands of users who have already discovered their attention patterns and improved their cognitive performance with AttenTrack.
                        </p>
                        <div class="cta-features">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="cta-feature">
                                        <i class="fas fa-rocket text-white me-2"></i>
                                        <span>Quick Setup</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="cta-feature">
                                        <i class="fas fa-chart-bar text-white me-2"></i>
                                        <span>Instant Results</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="cta-feature">
                                        <i class="fas fa-lock text-white me-2"></i>
                                        <span>Secure & Private</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="cta-actions">
                        <a href="<?php
                            $home2_page = get_page_by_path('home2');
                            echo $home2_page ? esc_url(get_permalink($home2_page->ID)) : '#';
                        ?>" class="btn btn-light btn-lg mb-3 w-100">
                            <i class="fas fa-play me-2"></i>Start Free Assessment
                        </a>
                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('contact-us'))); ?>" class="btn btn-outline-light btn-lg w-100">
                            <i class="fas fa-phone me-2"></i>Schedule Demo
                        </a>
                        <p class="small text-white-50 mt-3 mb-0">
                            <i class="fas fa-shield-alt me-1"></i>
                            No credit card required • HIPAA compliant
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>