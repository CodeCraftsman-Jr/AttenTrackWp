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
                                <!-- OTP Login Form - Using div instead of form to prevent any form submission issues -->
                                <div id="login-form" class="needs-validation">
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
                                    
                                    <!-- OTP Container - Initially hidden -->
                                    <div class="mb-3 otp-section" id="otp-section" style="display: none;">
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
                                        <div class="form-text mt-2">
                                            A 6-digit verification code has been sent to your email/phone.
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" id="submit-btn" class="btn btn-primary btn-lg">
                                            Get Verification Code
                                        </button>
                                    </div>
                                    <input type="hidden" id="account-type" name="account_type" value="user">
                                </div>
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
    
    // Handle OTP-based login
    var otpForm = document.getElementById('login-form');
    var submitBtn = document.getElementById('submit-btn');
    var otpSection = document.getElementById('otp-section');
    var otpInput = document.getElementById('otp');
    var emailPhoneInput = document.getElementById('email-phone');
    var otpSent = false;
    
    // Initialize Firebase Phone Auth when the OTP tab is clicked
    var otpTab = document.getElementById('otp-tab');
    if (otpTab) {
        otpTab.addEventListener('click', function() {
            // Initialize Firebase Phone Auth UI
            if (typeof initFirebasePhoneAuth === 'function') {
                setTimeout(function() {
                    initFirebasePhoneAuth();
                    console.log('Firebase Phone Auth initialized');
                }, 500);
            } else {
                console.error('Firebase Phone Auth not available');
            }
        });
    }
    
    // Set up click handler for the submit button
    if (otpForm && submitBtn) {
        console.log('OTP login initialized');
        submitBtn.addEventListener('click', function(e) {
            
            var emailOrPhone = emailPhoneInput.value.trim();
            var accountType = document.getElementById('account-type').value;
            
            if (!emailOrPhone) {
                showMessage('Please enter your email or phone number', true);
                return;
            }
            
            // Clear previous errors
            hideMessage();
            
            if (!otpSent) {
                // Send verification code
                submitBtn.disabled = true;
                submitBtn.innerHTML = window.loadingSpinnerHtml + 'Sending verification code...';
                
                // Determine if input is email or phone
                const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailOrPhone);
                console.log('Sending verification to:', emailOrPhone, 'Type:', isEmail ? 'Email' : 'Phone', 'Account type:', accountType);
                
                if (isEmail) {
                    // Use Firebase Email Authentication for email addresses
                    if (typeof sendEmailVerification !== 'function') {
                        console.error('Firebase Email Auth not initialized');
                        submitBtn.innerHTML = 'Get Verification Code';
                        submitBtn.disabled = false;
                        showMessage('Firebase Email Auth not available. Please try again later.', true);
                        return;
                    }
                    
                    sendEmailVerification(emailOrPhone)
                        .then(function(response) {
                            console.log('Email verification response:', response);
                            if (response.success) {
                                // Hide the OTP section since we're using email link authentication
                                document.getElementById('otp-section').style.display = 'none';
                                submitBtn.innerHTML = 'Check Your Email';
                                submitBtn.disabled = true;
                                
                                // Show success message with instructions
                                const successHtml = `
                                    <div class="alert alert-success">
                                        <h5>Email Verification Link Sent!</h5>
                                        <p>We've sent a sign-in link to <strong>${emailOrPhone}</strong>.</p>
                                        <p>Please check your email and click the link to sign in.</p>
                                        <hr>
                                        <p class="mb-0"><small>The link will expire in 15 minutes.</small></p>
                                    </div>
                                `;
                                
                                // Replace the form with success message
                                const formContainer = document.getElementById('login-form').parentElement;
                                const originalForm = formContainer.innerHTML;
                                formContainer.innerHTML = successHtml;
                                
                                // Add a button to resend the email
                                const resendBtn = document.createElement('button');
                                resendBtn.className = 'btn btn-outline-primary mt-3';
                                resendBtn.innerHTML = 'Didn\'t receive the email? Send again';
                                resendBtn.onclick = function() {
                                    // Restore the original form
                                    formContainer.innerHTML = originalForm;
                                    // Re-initialize the event listeners
                                    initializeLoginForm();
                                };
                                formContainer.appendChild(resendBtn);
                            } else {
                                submitBtn.innerHTML = 'Get Verification Code';
                                submitBtn.disabled = false;
                                showMessage(response.message || 'Failed to send verification email', true);
                            }
                        })
                        .catch(function(error) {
                            console.error('Error sending email verification:', error);
                            submitBtn.innerHTML = 'Get Verification Code';
                            submitBtn.disabled = false;
                            showMessage('Error: ' + (error.message || 'Failed to send verification email'), true);
                        });
                } else {
                    // Use Firebase Phone Auth for phone numbers
                    try {
                        if (typeof sendPhoneVerificationCode !== 'function') {
                            throw new Error('Firebase Phone Auth not initialized');
                        }
                        
                        sendPhoneVerificationCode(emailOrPhone)
                            .then(function(response) {
                                console.log('Phone verification response:', response);
                                if (response.success) {
                                    otpSent = true;
                                    // Show the OTP section
                                    document.getElementById('otp-section').style.display = 'block';
                                    submitBtn.innerHTML = 'Verify Code';
                                    submitBtn.disabled = false;
                                    showMessage('Verification code sent! Please check your phone.', false);
                                    
                                    // Focus on OTP input
                                    otpInput.focus();
                                } else {
                                    submitBtn.innerHTML = 'Get Verification Code';
                                    submitBtn.disabled = false;
                                    showMessage(response.message || 'Failed to send verification code', true);
                                }
                            })
                            .catch(function(error) {
                                console.error('Error sending phone verification:', error);
                                submitBtn.innerHTML = 'Get Verification Code';
                                submitBtn.disabled = false;
                                showMessage('Error: ' + (error.message || 'Failed to send verification code'), true);
                            });
                    } catch (error) {
                        console.error('Firebase Phone Auth error:', error);
                        submitBtn.innerHTML = 'Get Verification Code';
                        submitBtn.disabled = false;
                        showMessage('Firebase Phone Auth not available. Please try again later.', true);
                    }
                }
            } else {
                // Verify code
                var otp = otpInput.value.trim();
                
                if (!otp) {
                    showMessage('Please enter the verification code', true);
                    return;
                }
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = window.loadingSpinnerHtml + 'Verifying...';
                
                // Determine if input is email or phone
                const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailOrPhone);
                
                if (isEmail) {
                // For email, we're now using Firebase Email Link Authentication
                // The verification happens when the user clicks the link in their email
                // This code should not be reached for email authentication
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Get Verification Code';
                showMessage('For email verification, please check your email and click the link we sent you.', true);
                
                // Reset the OTP sent flag to allow sending a new email
                otpSent = false;
                } else {
                    // Verify phone code using Firebase
                    try {
                        if (typeof verifyPhoneCode !== 'function') {
                            throw new Error('Firebase Phone Auth not initialized');
                        }
                        
                        verifyPhoneCode(otp)
                            .then(function(response) {
                                if (response.success) {
                                    showMessage('Verification successful! Redirecting...', false);
                                    window.location.href = response.data.redirect || '/dashboard';
                                } else {
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = 'Verify Code';
                                    showMessage(response.message || 'Invalid verification code', true);
                                }
                            })
                            .catch(function(error) {
                                console.error('Error verifying phone code:', error);
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = 'Verify Code';
                                showMessage('Error: ' + (error.message || 'Failed to verify code'), true);
                            });
                    } catch (error) {
                        console.error('Firebase Phone Auth error:', error);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Verify Code';
                        showMessage('Firebase Phone Auth not available. Please try again later.', true);
                    }
                }
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