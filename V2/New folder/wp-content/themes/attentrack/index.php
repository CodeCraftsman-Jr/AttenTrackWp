<?php get_header(); ?>

<div class="overlay-container">
    <div class="container text-center text-white">
        <h1 class="display-4 mb-4">Welcome to AttenTrack</h1>
        <p class="lead mb-4">Advanced Attention Assessment Platform</p>
        <?php if (!is_user_logged_in()) : ?>
            <div class="d-flex justify-content-center gap-3">
                <a href="<?php echo esc_url(wp_login_url()); ?>" class="btn btn-primary btn-lg">Login</a>
                <a href="<?php echo esc_url(wp_registration_url()); ?>" class="btn btn-outline-light btn-lg">Sign Up</a>
            </div>
        <?php else : ?>
            <a href="<?php echo esc_url(home_url('/dashboard')); ?>" class="btn btn-primary btn-lg">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-brain fa-3x mb-3 text-primary"></i>
                    <h3 class="card-title">Attention Assessment</h3>
                    <p class="card-text">Comprehensive tests to evaluate attention span and cognitive performance.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-3x mb-3 text-primary"></i>
                    <h3 class="card-title">Track Progress</h3>
                    <p class="card-text">Monitor improvement over time with detailed analytics and reports.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user-md fa-3x mb-3 text-primary"></i>
                    <h3 class="card-title">Professional Support</h3>
                    <p class="card-text">Get expert guidance and recommendations based on your results.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
