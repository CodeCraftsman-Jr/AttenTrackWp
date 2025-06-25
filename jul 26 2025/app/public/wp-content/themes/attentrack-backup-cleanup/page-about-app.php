<?php
/**
 * Template Name: About App
 */

get_header();
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="text-primary mb-4">About AttenTrack</h1>
                    
                    <div class="mb-5">
                        <h2 class="h4 mb-3">What is AttenTrack?</h2>
                        <p>AttenTrack is a comprehensive attention assessment platform designed to help individuals understand and measure their attention capabilities through various scientifically designed tests.</p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h4 mb-3">Our Tests</h2>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h3 class="h5 mb-3">Selective Attention Test</h3>
                                        <p class="mb-0">Measures your ability to focus on specific stimuli while ignoring others.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h3 class="h5 mb-3">Extended Attention Test</h3>
                                        <p class="mb-0">Evaluates your ability to maintain focus over an extended period.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h3 class="h5 mb-3">Divided Attention Test</h3>
                                        <p class="mb-0">Tests your ability to respond to multiple tasks simultaneously.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h3 class="h5 mb-3">Alternative Attention Test</h3>
                                        <p class="mb-0">Assesses your ability to switch between different tasks efficiently.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h2 class="h4 mb-3">Features</h2>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="fas fa-chart-line text-primary me-2"></i>
                                Detailed performance analytics and progress tracking
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-history text-primary me-2"></i>
                                Historical test results and improvement metrics
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-file-alt text-primary me-2"></i>
                                Comprehensive reports and insights
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-users text-primary me-2"></i>
                                Family plans for multiple users
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h2 class="h4 mb-3">Get Started</h2>
                        <p>Ready to begin your attention assessment journey? Sign up now and take your first test!</p>
                        <a href="<?php echo esc_url(home_url('/signup')); ?>" class="btn btn-primary">
                            Sign Up Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
