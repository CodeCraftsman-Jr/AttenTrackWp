<?php
/**
 * Template Name: Sign Up
 */

get_header();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Create Account</h2>
                    
                    <!-- Success and Error Messages -->
                    <div id="successMessage" class="alert alert-success" style="display: none;"></div>
                    <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>

                    <!-- Social Login Buttons -->
                    <div class="mb-4">
                        <button type="button" id="googleSignIn" onclick="signInWithGoogle()" 
                                class="btn w-100 mb-3 d-flex align-items-center justify-content-center" 
                                style="background-color: #fff; border: 1px solid #ddd; color: #444; height: 44px;">
                            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" class="me-2" style="width: 18px;">
                            Continue with Google
                        </button>
                        
                        <button type="button" id="facebookSignIn" onclick="signInWithFacebook()" 
                                class="btn w-100 d-flex align-items-center justify-content-center" 
                                style="background-color: #1877f2; border: none; color: #fff; height: 44px;">
                            <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="#fff">
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
                        <!-- Password Registration Form -->
                        <div class="tab-pane fade show active" id="password-content" role="tabpanel" aria-labelledby="password-tab">
                            <form id="password-signup-form" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="username" 
                                           name="username"
                                           required
                                           placeholder="Choose a username">
                                    <div class="invalid-feedback">
                                        Please choose a valid username
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" 
                                           class="form-control form-control-lg" 
                                           id="email" 
                                           name="email"
                                           required
                                           placeholder="Enter your email">
                                    <div class="invalid-feedback">
                                        Please provide a valid email
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" 
                                           class="form-control form-control-lg" 
                                           id="password" 
                                           name="password"
                                           required
                                           placeholder="Create a password">
                                    <div class="invalid-feedback">
                                        Please enter a password (minimum 8 characters)
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm-password" class="form-label">Confirm Password</label>
                                    <input type="password" 
                                           class="form-control form-control-lg" 
                                           id="confirm-password" 
                                           name="confirm_password"
                                           required
                                           placeholder="Confirm your password">
                                    <div class="invalid-feedback">
                                        Passwords do not match
                                    </div>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">I agree to the <a href="#">Terms and Conditions</a></label>
                                    <div class="invalid-feedback">
                                        You must agree to the terms and conditions
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" id="password-submit-btn" class="btn btn-primary btn-lg">
                                        Create Account
                                    </button>
                                </div>
                                <input type="hidden" id="password-account-type" name="account_type" value="user">
                            </form>
                        </div>
                        
                        <!-- OTP Registration Form -->
                        <div class="tab-pane fade" id="otp-content" role="tabpanel" aria-labelledby="otp-tab">
                            <!-- OTP Registration Form - Using div instead of form to prevent any form submission issues -->
                            <div id="signup-form" class="needs-validation">
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
                            </div>
                        </div>
                    </div>

                    <!-- Phone Number Signup Form -->
                    <div id="phoneStep">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">+91</span>
                                <input type="tel" class="form-control" id="phone" placeholder="98765 43210" maxlength="10" required>
                            </div>
                            <div class="form-text">Enter your 10-digit phone number without country code</div>
                        </div>
                        
                        <!-- reCAPTCHA container -->
                        <div id="recaptcha-container" class="mb-3"></div>
                        
                        <button type="button" id="sendCode" class="btn btn-primary w-100" 
                                onclick="handleSendCode()"
                                style="background-color: #40E0D0; border: none;"
                                data-original-text="SEND CODE">
                            SEND CODE
                        </button>

                        <div class="mt-3 text-center">
                            <p class="mb-0">Already have an account? <a href="<?php echo esc_url(home_url('/signin')); ?>" style="color: #40E0D0; text-decoration: none;">Sign in here</a></p>
                        </div>
                    </div>

                    <!-- Verification Code Input (Initially Hidden) -->
                    <div id="verification-code-container" style="display: none;">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="verification-code" placeholder="Enter 6-digit code" maxlength="6" required>
                            <div class="form-text">Enter the verification code sent to your phone</div>
                        </div>
                        
                        <button type="button" id="verifyCode" class="btn btn-primary w-100" 
                                onclick="handleVerifyCode()"
                                style="background-color: #40E0D0; border: none;"
                                data-original-text="VERIFY CODE">
                            VERIFY CODE
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Scripts -->
<script src="<?php echo get_template_directory_uri(); ?>/js/firebase-config.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/auth.js"></script>

<style>
body {
    font-family: 'Arial', sans-serif;
    background: linear-gradient(135deg, rgba(44,69,93,0.85), rgba(0,1,44,0.85));
}

.btn-primary {
    text-transform: uppercase;
    font-weight: 500;
    padding: 12px;
    background-color: #40E0D0 !important;
    border: none !important;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #3BC7B9 !important;
    transform: translateY(-2px);
    box-shadow: 0 7px 14px rgba(64, 224, 208, 0.2);
}

.input-group-text {
    background-color: #fff;
    border-right: none;
}

.form-control {
    border-left: none;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #40E0D0;
    box-shadow: 0 0 10px rgba(64, 224, 208, 0.2);
}

.card {
    border: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

button {
    font-family: 'Google Sans', Roboto, Arial, sans-serif;
}

#googleSignIn {
    transition: all 0.3s ease;
}

#googleSignIn:hover {
    background-color: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

#facebookSignIn {
    transition: all 0.3s ease;
}

#facebookSignIn:hover {
    background-color: #166fe5;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(24,119,242,0.2);
}

a {
    color: #40E0D0 !important;
    transition: all 0.3s ease;
}

a:hover {
    color: #3BC7B9 !important;
    text-decoration: none;
}
</style>

<script>
// Define helper functions
function showMessage(message, isError = false) {
    const alertContainer = document.getElementById('alert-container') || 
                         document.getElementById('successMessage').parentElement;
    if (alertContainer) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${isError ? 'alert-danger' : 'alert-success'} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Clear any existing alerts
        const existingAlerts = alertContainer.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Add the new alert
        alertContainer.appendChild(alertDiv);
    } else {
        alert(message);
    }
}

function hideMessage() {
    const alertContainer = document.getElementById('alert-container');
    if (alertContainer) {
        alertContainer.innerHTML = '';
    }
    
    // Also hide the legacy message elements
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');
    
    if (successMessage) successMessage.style.display = 'none';
    if (errorMessage) errorMessage.style.display = 'none';
}

function showError(message) {
    const errorMessage = document.getElementById('errorMessage');
    if (errorMessage) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
    } else {
        showMessage(message, true);
    }
}

function hideError() {
    const errorMessage = document.getElementById('errorMessage');
    if (errorMessage) {
        errorMessage.style.display = 'none';
    }
}

function showSuccess(message) {
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
        successMessage.textContent = message;
        successMessage.style.display = 'block';
    } else {
        showMessage(message, false);
    }
}

// Initialize Firebase Phone Auth when the OTP tab is clicked
var otpTab = document.getElementById('otp-tab');
if (otpTab) {
    otpTab.addEventListener('click', function() {
        // Initialize Firebase Phone Auth UI
        if (typeof initFirebasePhoneAuth === 'function') {
            setTimeout(function() {
                initFirebasePhoneAuth();
                console.log('Firebase Phone Auth initialized for signup');
            }, 500);
        } else {
            console.error('Firebase Phone Auth not available');
        }
    });
}

// Handle OTP-based signup
var signupForm = document.getElementById('signup-form');
var submitBtn = document.getElementById('submit-btn');
var otpSection = document.getElementById('otp-container');
var otpInput = document.getElementById('otp');
var emailPhoneInput = document.getElementById('email-phone');
var otpSent = false;

// Set up click handler for the submit button
if (signupForm && submitBtn) {
    console.log('OTP signup initialized');
    submitBtn.addEventListener('click', function(e) {
        var emailOrPhone = emailPhoneInput.value.trim();
        var accountType = document.getElementById('account-type').value;
        
        if (!emailOrPhone) {
            showMessage('Please enter your email or phone number', true);
            return;
        }
        
        // Clear previous messages
        hideMessage();
        
        if (!otpSent) {
            // Send verification code
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending verification code...';
            
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
                            document.getElementById('otp-container').style.display = 'none';
                            submitBtn.innerHTML = 'Check Your Email';
                            submitBtn.disabled = true;
                            
                            // Show success message with instructions
                            const successHtml = `
                                <div class="alert alert-success">
                                    <h5>Email Verification Link Sent!</h5>
                                    <p>We've sent a sign-in link to <strong>${emailOrPhone}</strong>.</p>
                                    <p>Please check your email and click the link to sign up.</p>
                                    <hr>
                                    <p class="mb-0"><small>The link will expire in 15 minutes.</small></p>
                                </div>
                            `;
                            
                            // Replace the form with success message
                            const formContainer = document.getElementById('signup-form').parentElement;
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
                                initializeSignupForm();
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
                                document.getElementById('otp-container').style.display = 'block';
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
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Verifying...';
            
            // Determine if input is email or phone
            const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailOrPhone);
            
            if (isEmail) {
                // Verify email OTP
                verifyEmailOTP(emailOrPhone, otp, accountType)
                    .then(function(response) {
                        if (response.success) {
                            showMessage('Verification successful! Redirecting...', false);
                            window.location.href = response.data.redirect || '/dashboard';
                        } else {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Verify Code';
                            showMessage(response.data || 'Invalid verification code', true);
                        }
                    })
                    .catch(function(error) {
                        console.error('Error verifying email OTP:', error);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Verify Code';
                        showMessage('Error verifying code. Please try again.', true);
                    });
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

// Legacy code for phone input handling
document.getElementById('phone').addEventListener('input', function(e) {
    // Remove any non-digit characters
    this.value = this.value.replace(/\D/g, '');
    
    // Enable/disable send code button
    document.getElementById('sendCode').disabled = this.value.length !== 10;
});

// Helper functions for sending and verifying OTP
function sendEmailOTP(emailOrPhone, accountType) {
    return new Promise(function(resolve, reject) {
        jQuery.ajax({
            url: attentrack_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'send_otp',
                email_phone: emailOrPhone,
                account_type: accountType,
                is_signup: true // This indicates it's a signup request
            },
            success: function(response) {
                resolve(response);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                reject(error);
            }
        });
    });
}

function verifyEmailOTP(emailOrPhone, otp, accountType) {
    return new Promise(function(resolve, reject) {
        jQuery.ajax({
            url: attentrack_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'verify_otp',
                email_phone: emailOrPhone,
                otp: otp,
                account_type: accountType,
                is_signup: true // This indicates it's a signup request
            },
            success: function(response) {
                resolve(response);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                reject(error);
            }
        });
    });
}

// Handle sending verification code
function handleSendCode() {
    const phoneInput = document.getElementById('phone');
    const phoneNumber = '+91' + phoneInput.value.trim().replace(/\D/g, '');
    const sendCodeBtn = document.getElementById('sendCode');
    
    // Validate phone number
    if (phoneInput.value.length !== 10) {
        showError('Please enter a valid 10-digit phone number');
        return;
    }
    
    // Disable button and show loading state
    sendCodeBtn.disabled = true;
    const originalText = sendCodeBtn.dataset.originalText;
    sendCodeBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
    
    // Clear previous errors
    hideError();
    
    // Initialize recaptcha if needed
    if (!window.recaptchaVerifier) {
        try {
            window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                'size': 'invisible',
                'callback': (response) => {
                    console.log('reCAPTCHA verified');
                }
            });
        } catch (error) {
            console.error('reCAPTCHA initialization error:', error);
            showError('Error initializing verification. Please try again.');
            sendCodeBtn.disabled = false;
            sendCodeBtn.innerHTML = originalText;
            return;
        }
    }
    
    // Send verification code
    sendVerificationCode(phoneNumber, window.recaptchaVerifier)
        .then(function(confirmationResult) {
            window.confirmationResult = confirmationResult;
            // Show verification code input
            document.getElementById('phoneStep').style.display = 'none';
            document.getElementById('verification-code-container').style.display = 'block';
            showSuccess('Verification code sent! Please check your phone.');
        })
        .catch(function(error) {
            console.error('Error sending verification code:', error);
            showError(error.message || 'Failed to send verification code. Please try again.');
            // Reset reCAPTCHA
            if (window.recaptchaVerifier) {
                window.recaptchaVerifier.render().then(function(widgetId) {
                    grecaptcha.reset(widgetId);
                });
            }
        })
        .finally(function() {
            // Reset button
            sendCodeBtn.disabled = false;
            sendCodeBtn.innerHTML = originalText;
        });
}

// Handle verifying code
function handleVerifyCode() {
    const codeInput = document.getElementById('verification-code');
    const code = codeInput.value.trim();
    const verifyBtn = document.getElementById('verifyCode');
    
    // Validate code
    if (code.length !== 6) {
        showError('Please enter a valid 6-digit verification code');
        return;
    }
    
    // Disable button and show loading state
    verifyBtn.disabled = true;
    const originalText = verifyBtn.dataset.originalText;
    verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...';
    
    // Clear previous errors
    hideError();
    
    // Verify code
    if (!window.confirmationResult) {
        showError('Session expired. Please request a new code.');
        verifyBtn.disabled = false;
        verifyBtn.innerHTML = originalText;
        return;
    }
    
    window.confirmationResult.confirm(code)
        .then(function(result) {
            // User signed in successfully
            const user = result.user;
            showSuccess('Phone number verified successfully! Redirecting...');
            
            // Get phone number
            const phoneInput = document.getElementById('phone');
            const phoneNumber = '+91' + phoneInput.value.trim().replace(/\D/g, '');
            
            // Send token to server
            const data = new URLSearchParams();
            data.append('action', 'verify_firebase_token');
            data.append('token', user.uid);
            data.append('provider', 'phone');
            data.append('phone', phoneNumber);
            data.append('name', 'Phone User'); // Default name
            data.append('account_type', 'user');
            data.append('_ajax_nonce', authData.nonce);
            
            // Send AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', authData.ajaxUrl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Redirect to dashboard or selection page
                            window.location.href = response.data.redirect_url || '/dashboard';
                        } else {
                            showError(response.data || 'Error processing your request');
                            verifyBtn.disabled = false;
                            verifyBtn.innerHTML = originalText;
                        }
                    } catch (e) {
                        showError('Error processing server response');
                        verifyBtn.disabled = false;
                        verifyBtn.innerHTML = originalText;
                    }
                } else {
                    showError('Server error. Please try again.');
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = originalText;
                }
            };
            
            xhr.onerror = function() {
                showError('Network error. Please try again.');
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = originalText;
            };
            
            xhr.send(data);
        })
        .catch(function(error) {
            console.error('Error verifying code:', error);
            showError(error.message || 'Invalid verification code. Please try again.');
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = originalText;
        });
}

// Handle password-based registration
document.addEventListener('DOMContentLoaded', function() {
    // Update account type for both forms
    var accountTypeRadios = document.querySelectorAll('input[name="accountType"]');
    var passwordAccountTypeField = document.getElementById('password-account-type');
    
    if (accountTypeRadios && passwordAccountTypeField) {
        accountTypeRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                passwordAccountTypeField.value = this.value;
            });
        });
    }
    
    // Handle password registration form
    var passwordForm = document.getElementById('password-signup-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!passwordForm.checkValidity()) {
                e.stopPropagation();
                passwordForm.classList.add('was-validated');
                return;
            }
            
            var username = document.getElementById('username').value.trim();
            var email = document.getElementById('email').value.trim();
            var password = document.getElementById('password').value.trim();
            var confirmPassword = document.getElementById('confirm-password').value.trim();
            var terms = document.getElementById('terms').checked;
            var accountType = document.getElementById('password-account-type').value;
            
            // Additional validation
            if (password !== confirmPassword) {
                showError('Passwords do not match');
                return;
            }
            
            if (!terms) {
                showError('You must agree to the terms and conditions');
                return;
            }
            
            var submitBtn = document.getElementById('password-submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating account...';
            
            // Clear previous errors
            hideError();
            
            // Use Firebase Email/Password Authentication to create account
            window.auth.createUserWithEmailAndPassword(email, password)
                .then(function(userCredential) {
                    // Signed in 
                    var user = userCredential.user;
                    
                    // Update profile with username
                    return user.updateProfile({
                        displayName: username
                    }).then(function() {
                        return user.getIdToken();
                    });
                })
                .then(function(idToken) {
                    // Send token to server
                    var data = new URLSearchParams();
                    data.append('action', 'verify_firebase_token');
                    data.append('token', idToken);
                    data.append('provider', 'password');
                    data.append('email', email);
                    data.append('name', username);
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
                                    showSuccess('Account created successfully! Redirecting...');
                                    window.location.href = response.data.redirect_url || '/dashboard';
                                } else {
                                    showError(response.data || 'Error processing your request');
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = 'Create Account';
                                }
                            } catch (e) {
                                showError('Error processing server response');
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = 'Create Account';
                            }
                        } else {
                            showError('Server error. Please try again.');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Create Account';
                        }
                    };
                    
                    xhr.onerror = function() {
                        showError('Network error. Please try again.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Create Account';
                    };
                    
                    xhr.send(data);
                })
                .catch(function(error) {
                    // Handle Errors here
                    var errorCode = error.code;
                    var errorMessage = error.message;
                    console.error('Firebase auth error:', errorCode, errorMessage);
                    
                    // Show user-friendly error message
                    if (errorCode === 'auth/email-already-in-use') {
                        showError('This email is already in use. Please use a different email or sign in.');
                    } else if (errorCode === 'auth/invalid-email') {
                        showError('Please enter a valid email address.');
                    } else if (errorCode === 'auth/weak-password') {
                        showError('Password is too weak. Please use a stronger password.');
                    } else {
                        showError(errorMessage);
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Create Account';
                });
        });
    }
});

// Helper functions for showing messages
function showError(message) {
    const errorElement = document.getElementById('errorMessage');
    errorElement.textContent = message;
    errorElement.style.display = 'block';
    
    const successElement = document.getElementById('successMessage');
    successElement.style.display = 'none';
}

function showSuccess(message) {
    const successElement = document.getElementById('successMessage');
    successElement.textContent = message;
    successElement.style.display = 'block';
    
    const errorElement = document.getElementById('errorMessage');
    errorElement.style.display = 'none';
}

function hideError() {
    const errorElement = document.getElementById('errorMessage');
    errorElement.style.display = 'none';
}
</script>

<?php get_footer(); ?>