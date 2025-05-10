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
                    <h1 class="display-4 fw-bold text-primary mb-3">AttenTrack</h1>
                    <h2 class="h3">Welcome Back</h2>
                    <p class="text-muted">Sign in to continue to AttenTrack</p>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <!-- Alert Messages -->
                        <div id="alert-container"></div>
                        
                        <!-- Account Type Selection -->
                        <div class="account-type-selection mb-4">
                            <label class="form-label">I am signing in as:</label>
                            <div class="d-flex">
                                <div class="form-check form-check-inline flex-grow-1">
                                    <input class="form-check-input" type="radio" name="accountType" id="userAccount" value="user" checked>
                                    <label class="form-check-label w-100 py-2 px-3 border rounded text-center" for="userAccount">
                                        <i class="fas fa-user mb-2 d-block" style="font-size: 24px;"></i>
                                        User
                                    </label>
                                </div>
                                <div class="form-check form-check-inline flex-grow-1">
                                    <input class="form-check-input" type="radio" name="accountType" id="institutionAccount" value="institution">
                                    <label class="form-check-label w-100 py-2 px-3 border rounded text-center" for="institutionAccount">
                                        <i class="fas fa-building mb-2 d-block" style="font-size: 24px;"></i>
                                        Institution
                                    </label>
                                </div>
                            </div>
                        </div>
                        
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
                                or continue with
                            </span>
                        </div>
                        
                        <!-- Authentication Method Tabs -->
                        <ul class="nav nav-tabs mb-4" id="authTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="password-tab" data-bs-toggle="tab" data-bs-target="#password-content" 
                                        type="button" role="tab" aria-controls="password-content" aria-selected="true">
                                    Username/Password
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="otp-tab" data-bs-toggle="tab" data-bs-target="#otp-content" 
                                        type="button" role="tab" aria-controls="otp-content" aria-selected="false">
                                    Phone/Email OTP
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="authTabsContent">
                            <!-- Password Login Form -->
                            <div class="tab-pane fade show active" id="password-content" role="tabpanel" aria-labelledby="password-tab">
                                <form id="password-login-form" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="username-email" class="form-label">Username or Email</label>
                                        <input type="text" 
                                               class="form-control form-control-lg" 
                                               id="username-email" 
                                               name="username_email"
                                               required
                                               placeholder="Enter username or email">
                                        <div class="invalid-feedback">
                                            Please provide a valid username or email
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" 
                                               class="form-control form-control-lg" 
                                               id="password" 
                                               name="password"
                                               required
                                               placeholder="Enter your password">
                                        <div class="invalid-feedback">
                                            Please enter your password
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="remember-me" name="remember">
                                        <label class="form-check-label" for="remember-me">Remember me</label>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" id="password-submit-btn" class="btn btn-primary btn-lg">
                                            Sign In
                                        </button>
                                    </div>
                                    <input type="hidden" id="password-account-type" name="account_type" value="user">
                                    
                                    <div class="text-center mt-3">
                                        <a href="#" class="text-decoration-none small">Forgot password?</a>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- OTP Login Form -->
                            <div class="tab-pane fade" id="otp-content" role="tabpanel" aria-labelledby="otp-tab">
                                <form id="login-form" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="email-phone" class="form-label">Email or Phone Number</label>
                                        <input type="text" 
                                               class="form-control form-control-lg" 
                                               id="email-phone" 
                                               name="email_phone"
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
                                    <input type="hidden" id="account-type" name="account_type" value="user">
                                </form>
                            </div>
                        </div>
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

/* Account type selection styles */
.form-check-inline {
    margin-right: 10px;
}
.form-check-input {
    position: absolute;
    opacity: 0;
}
.form-check-label {
    cursor: pointer;
    transition: all 0.2s ease;
}
.form-check-input:checked + .form-check-label {
    border-color: #0d6efd !important;
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}
</style>

<!-- Custom Scripts -->
<script src="<?php echo get_template_directory_uri(); ?>/js/firebase-config.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/auth.js"></script>

<script>
// Define global auth data if not already defined
if (typeof authData === 'undefined') {
    var authData = {
        ajaxUrl: '<?php echo admin_url("admin-ajax.php"); ?>',
        nonce: '<?php echo wp_create_nonce("auth-nonce"); ?>',
        logoutUrl: '<?php echo wp_logout_url(home_url()); ?>'
    };
}

// Define loading spinner HTML if not already defined
if (typeof window.loadingSpinnerHtml === 'undefined') {
    window.loadingSpinnerHtml = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>';
}

document.addEventListener('DOMContentLoaded', function() {
    // Update hidden account type field when selection changes
    var accountTypeRadios = document.querySelectorAll('input[name="accountType"]');
    var accountTypeField = document.getElementById('account-type');
    var passwordAccountTypeField = document.getElementById('password-account-type');
    
    accountTypeRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            accountTypeField.value = this.value;
            passwordAccountTypeField.value = this.value;
        });
    });
    
    // Add event listeners for social login buttons
    var googleSignInBtn = document.getElementById('googleSignIn');
    var facebookSignInBtn = document.getElementById('facebookSignIn');
    
    if (googleSignInBtn) {
        googleSignInBtn.addEventListener('click', function() {
            var originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = window.loadingSpinnerHtml + 'Connecting to Google...';
            
            // Clear previous messages
            hideMessage();
            
            // Check if Firebase is properly initialized
            if (typeof firebase === 'undefined' || !firebase.apps.length) {
                showMessage('Firebase is not initialized. Please refresh the page and try again.', true);
                this.disabled = false;
                this.innerHTML = originalText;
                return;
            }
            
            try {
                signInWithGoogle()
                    .then(function(response) {
                        if (response.success) {
                            showMessage('Login successful! Redirecting...', false);
                            window.location.href = response.data.redirect_url || '/dashboard';
                        } else {
                            showMessage(response.data || 'Error during Google sign-in', true);
                            googleSignInBtn.disabled = false;
                            googleSignInBtn.innerHTML = originalText;
                        }
                    })
                    .catch(function(error) {
                        console.error('Google sign-in error:', error);
                        var errorMessage = 'Error signing in with Google';
                        
                        if (error.code === 'auth/network-request-failed') {
                            errorMessage = 'Network error. Please check your internet connection and try again.';
                        } else if (error.code === 'auth/popup-closed-by-user') {
                            errorMessage = 'Sign-in popup was closed. Please try again.';
                        } else if (error.code === 'auth/cancelled-popup-request') {
                            errorMessage = 'Sign-in was cancelled. Please try again.';
                        } else if (error.code === 'auth/popup-blocked') {
                            errorMessage = 'Sign-in popup was blocked. Please allow popups for this site and try again.';
                        } else if (error.message) {
                            errorMessage = error.message;
                        }
                        
                        showMessage(errorMessage, true);
                        googleSignInBtn.disabled = false;
                        googleSignInBtn.innerHTML = originalText;
                    });
            } catch (e) {
                console.error('Unexpected error during Google sign-in:', e);
                showMessage('An unexpected error occurred. Please try again later.', true);
                googleSignInBtn.disabled = false;
                googleSignInBtn.innerHTML = originalText;
            }
        });
    }
    
    if (facebookSignInBtn) {
        facebookSignInBtn.addEventListener('click', function() {
            var originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = window.loadingSpinnerHtml + 'Connecting to Facebook...';
            
            // Clear previous messages
            hideMessage();
            
            // Check if Firebase is properly initialized
            if (typeof firebase === 'undefined' || !firebase.apps.length) {
                showMessage('Firebase is not initialized. Please refresh the page and try again.', true);
                this.disabled = false;
                this.innerHTML = originalText;
                return;
            }
            
            try {
                signInWithFacebook()
                    .then(function(response) {
                        if (response.success) {
                            showMessage('Login successful! Redirecting...', false);
                            window.location.href = response.data.redirect_url || '/dashboard';
                        } else {
                            showMessage(response.data || 'Error during Facebook sign-in', true);
                            facebookSignInBtn.disabled = false;
                            facebookSignInBtn.innerHTML = originalText;
                        }
                    })
                    .catch(function(error) {
                        console.error('Facebook sign-in error:', error);
                        var errorMessage = 'Error signing in with Facebook';
                        
                        if (error.code === 'auth/network-request-failed') {
                            errorMessage = 'Network error. Please check your internet connection and try again.';
                        } else if (error.code === 'auth/popup-closed-by-user') {
                            errorMessage = 'Sign-in popup was closed. Please try again.';
                        } else if (error.code === 'auth/cancelled-popup-request') {
                            errorMessage = 'Sign-in was cancelled. Please try again.';
                        } else if (error.code === 'auth/popup-blocked') {
                            errorMessage = 'Sign-in popup was blocked. Please allow popups for this site and try again.';
                        } else if (error.message) {
                            errorMessage = error.message;
                        }
                        
                        showMessage(errorMessage, true);
                        facebookSignInBtn.disabled = false;
                        facebookSignInBtn.innerHTML = originalText;
                    });
            } catch (e) {
                console.error('Unexpected error during Facebook sign-in:', e);
                showMessage('An unexpected error occurred. Please try again later.', true);
                facebookSignInBtn.disabled = false;
                facebookSignInBtn.innerHTML = originalText;
            }
        });
    }
    
    // Handle password-based login form submission
    var passwordForm = document.getElementById('password-login-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var email = document.getElementById('username-email').value.trim();
            var password = document.getElementById('password').value.trim();
            var accountType = document.getElementById('password-account-type').value;
            
            if (!email || !password) {
                showMessage('Please enter your email and password', true);
                return;
            }
            
            var submitBtn = document.getElementById('password-submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = window.loadingSpinnerHtml + 'Signing in...';
            
            // Clear previous errors
            hideMessage();
            
            // Use Firebase Email/Password Authentication
            window.auth.signInWithEmailAndPassword(email, password)
                .then(function(userCredential) {
                    // Signed in
                    var user = userCredential.user;
                    showMessage('Login successful! Verifying...', false);
                    
                    // Get ID token to send to server
                    return user.getIdToken();
                })
                .then(function(idToken) {
                    // Send token to server
                    var data = new URLSearchParams();
                    data.append('action', 'verify_firebase_token');
                    data.append('token', idToken);
                    data.append('provider', 'password');
                    data.append('email', email);
                    data.append('account_type', accountType);
                    data.append('_ajax_nonce', authData.nonce);
                    
                    // Send AJAX request
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', authData.ajaxUrl, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    
                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    showMessage('Login successful! Redirecting...', false);
                                    window.location.href = response.data.redirect_url || '/dashboard';
                                } else {
                                    showMessage(response.data || 'Error processing your request', true);
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = 'Sign In';
                                }
                            } catch (e) {
                                showMessage('Error processing server response', true);
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = 'Sign In';
                            }
                        } else {
                            showMessage('Server error. Please try again.', true);
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Sign In';
                        }
                    };
                    
                    xhr.onerror = function() {
                        showMessage('Network error. Please try again.', true);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Sign In';
                    };
                    
                    xhr.send(data);
                })
                .catch(function(error) {
                    // Handle Errors here
                    var errorCode = error.code;
                    var errorMessage = error.message;
                    console.error('Firebase auth error:', errorCode, errorMessage);
                    
                    // Show user-friendly error message
                    if (errorCode === 'auth/user-not-found' || errorCode === 'auth/wrong-password') {
                        showMessage('Invalid email or password. Please try again.', true);
                    } else if (errorCode === 'auth/invalid-email') {
                        showMessage('Please enter a valid email address.', true);
                    } else if (errorCode === 'auth/too-many-requests') {
                        showMessage('Too many failed login attempts. Please try again later.', true);
                    } else {
                        showMessage(errorMessage, true);
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Sign In';
                });
        });
    }
});

// Helper function to show messages
function showMessage(message, isError) {
    var alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;
    
    var alertClass = isError ? 'danger' : 'success';
    alertContainer.innerHTML = '<div class="alert alert-' + alertClass + ' alert-dismissible fade show" role="alert">' +
                              message +
                              '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                              '</div>';
}

// Helper function to hide messages
function hideMessage() {
    var alertContainer = document.getElementById('alert-container');
    if (alertContainer) {
        alertContainer.innerHTML = '';
    }
}
</script>

<?php get_footer(); ?>