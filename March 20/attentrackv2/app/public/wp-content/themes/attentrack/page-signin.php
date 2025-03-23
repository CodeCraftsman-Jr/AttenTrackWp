<?php
/**
 * Template Name: Sign In
 */

// If user is already logged in, redirect to dashboard
if (is_user_logged_in()) {
    wp_safe_redirect(home_url('/dashboard'));
    exit;
}

get_header();
?>

<div class="signin-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="text-center mb-4">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.png" alt="AttenTrack" height="60" class="mb-4">
                    <h1 class="h3">Welcome Back</h1>
                    <p class="text-muted">Sign in to continue to AttenTrack</p>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <!-- Alert Messages -->
                        <div id="alert-container"></div>
                        
                        <!-- Social Login Buttons -->
                        <div class="social-login mb-4">
                            <button type="button" id="googleSignIn" 
                                    class="btn w-100 mb-3 d-flex align-items-center justify-content-center" 
                                    style="background-color: #fff; border: 1px solid #ddd; color: #444; height: 48px;">
                                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" class="me-3" style="width: 20px;">
                                Continue with Google
                            </button>
                            
                            <button type="button" id="facebookSignIn" 
                                    class="btn w-100 d-flex align-items-center justify-content-center" 
                                    style="background-color: #1877f2; border: none; color: #fff; height: 48px;">
                                <svg class="me-3" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#fff">
                                    <path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm3 8h-1.35c-.538 0-.65.221-.65.778v1.222h2l-.209 2h-1.791v7h-3v-7h-2v-2h2v-2.308c0-1.769.931-2.692 3.029-2.692h1.971v3z"/>
                                </svg>
                                Continue with Facebook
                            </button>
                        </div>

                        <!-- Divider -->
                        <div class="position-relative my-4">
                            <hr>
                            <span class="position-absolute top-50 start-50 translate-middle px-3 bg-white text-muted small">
                                or continue with phone/email
                            </span>
                        </div>
                        
                        <!-- Phone/Email Form -->
                        <form id="login-form" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="email-phone" class="form-label">Email or Phone Number</label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="email-phone" 
                                       name="email_or_phone"
                                       required
                                       placeholder="Enter email or phone number">
                                <div class="invalid-feedback">
                                    Please provide a valid email or phone number
                                </div>
                            </div>
                            
                            <!-- reCAPTCHA container -->
                            <div id="recaptcha-container" class="mb-3"></div>
                            
                            <div id="otp-container" style="display: none;">
                                <div class="mb-3">
                                    <label for="otp" class="form-label">Verification Code</label>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="otp" 
                                           name="otp"
                                           maxlength="6"
                                           placeholder="Enter 6-digit code">
                                    <div class="invalid-feedback">
                                        Please enter the verification code
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" id="submit-btn" class="btn btn-primary btn-lg">
                                    Get Verification Code
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted">New to AttenTrack? <a href="<?php echo esc_url(home_url('/signup')); ?>" class="text-primary text-decoration-none">Create an account</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.signin-page {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: calc(100vh - 76px);
}

.card {
    border-radius: 1rem;
}

.form-control {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn-lg {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
}

#googleSignIn:hover {
    background-color: #f8f9fa;
}

#facebookSignIn:hover {
    background-color: #1666d8;
}

.social-login .btn {
    transition: all 0.2s ease;
    border-radius: 0.5rem;
    font-weight: 500;
}

.invalid-feedback {
    font-size: 0.875rem;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}
</style>

<!-- Firebase SDK -->
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-auth-compat.js"></script>

<?php get_footer(); ?>