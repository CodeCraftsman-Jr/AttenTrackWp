// Initialize Firebase
const firebaseConfig = {
    apiKey: "AIzaSyDxwNXFliKJPC39UOweKnWNvpPipf7-PXc",
    authDomain: "innovproject-274c9.firebaseapp.com",
    projectId: "innovproject-274c9",
    storageBucket: "innovproject-274c9.firebasestorage.app",
    messagingSenderId: "111288496386",
    appId: "1:111288496386:web:38dd0ab7e126ebe93b521b"
};

// Initialize Firebase
if (!window.firebase) {
    console.error('Firebase SDK not loaded');
} else if (!window.firebaseInitialized) {
    firebase.initializeApp(firebaseConfig);
    window.firebaseInitialized = true;
}

// Get Firebase Auth instance
const auth = firebase.auth();

// Configure providers
const googleProvider = new firebase.auth.GoogleAuthProvider();
const facebookProvider = new firebase.auth.FacebookAuthProvider();

// Common UI Elements
const loadingSpinnerHtml = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ';

// Show message in alert container
function showMessage(message, isError = false) {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;

    const alertHtml = `
        <div class="alert alert-${isError ? 'danger' : 'success'} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    alertContainer.innerHTML = alertHtml;
}

// Handle AJAX errors
function handleAjaxError(error) {
    console.error('AJAX Error:', error);
    showMessage('Something went wrong. Please try again.', true);
}

// Send OTP
function sendLoginOtp(emailOrPhone) {
    return jQuery.ajax({
        url: authData.ajaxUrl,
        type: 'POST',
        data: {
            action: 'send_login_otp',
            email_or_phone: emailOrPhone,
            _ajax_nonce: authData.nonce
        }
    });
}

// Verify OTP
function verifyLoginOtp(emailOrPhone, otp) {
    return jQuery.ajax({
        url: authData.ajaxUrl,
        type: 'POST',
        data: {
            action: 'verify_login_otp',
            email_or_phone: emailOrPhone,
            otp: otp,
            _ajax_nonce: authData.nonce
        }
    });
}

// Handle successful authentication
function handleSuccessfulAuth(response) {
    showMessage('Login successful! Redirecting...', false);
    window.location.href = response.data.redirect_url || authData.homeUrl;
}

// Google Sign-in
function signInWithGoogle() {
    const button = document.getElementById('googleSignIn');
    if (button) {
        button.disabled = true;
        button.innerHTML = loadingSpinnerHtml + 'Signing in with Google...';
    }

    auth.signInWithPopup(googleProvider)
        .then((result) => {
            // Get token from credential
            const token = result.credential.accessToken;
            const user = result.user;
            
            // Send token to WordPress
            jQuery.ajax({
                url: authData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'verify_firebase_token',
                    token: token,
                    provider: 'google',
                    email: user.email,
                    name: user.displayName,
                    _ajax_nonce: authData.nonce
                }
            })
            .done(handleSuccessfulAuth)
            .fail(handleAjaxError);
        })
        .catch((error) => {
            console.error('Google Sign In Error:', error);
            showMessage(error.message, true);
        })
        .finally(() => {
            if (button) {
                button.disabled = false;
                button.innerHTML = '<img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" class="me-2" style="width: 18px;">Continue with Google';
            }
        });
}

// Facebook Sign-in
function signInWithFacebook() {
    const button = document.getElementById('facebookSignIn');
    if (button) {
        button.disabled = true;
        button.innerHTML = loadingSpinnerHtml + 'Signing in with Facebook...';
    }

    auth.signInWithPopup(facebookProvider)
        .then((result) => {
            // Get token from credential
            const token = result.credential.accessToken;
            const user = result.user;
            
            // Send token to WordPress
            jQuery.ajax({
                url: authData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'verify_firebase_token',
                    token: token,
                    provider: 'facebook',
                    email: user.email,
                    name: user.displayName,
                    _ajax_nonce: authData.nonce
                }
            })
            .done(handleSuccessfulAuth)
            .fail(handleAjaxError);
        })
        .catch((error) => {
            console.error('Facebook Sign In Error:', error);
            showMessage(error.message, true);
        })
        .finally(() => {
            if (button) {
                button.disabled = false;
                button.innerHTML = '<svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="#fff"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm3 8h-1.35c-.538 0-.65.221-.65.778v1.222h2l-.209 2h-1.791v7h-3v-7h-2v-2h2v-2.308c0-1.769.931-2.692 3.029-2.692h1.971v3z"/></svg>Continue with Facebook';
            }
        });
}

// Sign out function
function signOut() {
    // Sign out from Firebase
    if (firebase.auth().currentUser) {
        firebase.auth().signOut();
    }
    
    // Sign out from WordPress
    jQuery.post(authData.ajaxUrl, {
        action: 'user_logout',
        _ajax_nonce: authData.nonce
    }, function(response) {
        if (response.success) {
            window.location.href = authData.homeUrl;
        }
    });
}

// Initialize on document ready
jQuery(document).ready(function($) {
    // Initialize reCAPTCHA
    if (document.getElementById('recaptcha-container')) {
        window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
            'size': 'normal',
            'callback': (response) => {
                // reCAPTCHA solved
                $('#submit-btn').prop('disabled', false);
            },
            'expired-callback': () => {
                $('#submit-btn').prop('disabled', true);
            }
        });
        window.recaptchaVerifier.render();
    }

    // Attach click handlers to social buttons
    $('#googleSignIn').on('click', signInWithGoogle);
    $('#facebookSignIn').on('click', signInWithFacebook);

    // Handle phone/email form
    const form = $('#login-form');
    if (!form.length) return;

    const submitBtn = $('#submit-btn');
    const emailPhoneInput = $('#email-phone');
    const otpContainer = $('#otp-container');
    const otpInput = $('#otp');
    let isOtpSent = false;

    // Validate email/phone
    function validateInput(input) {
        const value = input.trim();
        const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        const isPhone = /^\d{10}$/.test(value.replace(/[^0-9]/g, ''));
        return isEmail || isPhone;
    }

    // Handle form submission
    form.on('submit', function(e) {
        e.preventDefault();
        
        const emailPhone = emailPhoneInput.val().trim();
        if (!validateInput(emailPhone)) {
            showMessage('Please enter a valid email or phone number', true);
            return;
        }

        submitBtn.prop('disabled', true);
        const loadingText = isOtpSent ? 'Verifying...' : 'Sending Code...';
        submitBtn.html(loadingSpinnerHtml + loadingText);

        const isPhone = /^\d{10}$/.test(emailPhone.replace(/[^0-9]/g, ''));
        
        if (isPhone) {
            // Handle phone authentication with Firebase
            const phoneNumber = '+91' + emailPhone.replace(/[^0-9]/g, '');
            if (!isOtpSent) {
                const appVerifier = window.recaptchaVerifier;
                auth.signInWithPhoneNumber(phoneNumber, appVerifier)
                    .then((confirmationResult) => {
                        window.confirmationResult = confirmationResult;
                        isOtpSent = true;
                        otpContainer.slideDown();
                        submitBtn.html('Verify Code');
                        showMessage('Verification code sent!', false);
                    })
                    .catch((error) => {
                        showMessage(error.message, true);
                        if (window.recaptchaVerifier) {
                            window.recaptchaVerifier.render().then(widgetId => {
                                grecaptcha.reset(widgetId);
                            });
                        }
                    })
                    .finally(() => {
                        submitBtn.prop('disabled', false);
                    });
            } else {
                const code = otpInput.val().trim();
                if (!window.confirmationResult) {
                    showMessage('Please request a new code', true);
                    return;
                }
                window.confirmationResult.confirm(code)
                    .then((result) => {
                        const user = result.user;
                        // Send token to WordPress
                        jQuery.ajax({
                            url: authData.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'verify_firebase_token',
                                token: user.accessToken,
                                provider: 'phone',
                                phone: phoneNumber,
                                _ajax_nonce: authData.nonce
                            }
                        })
                        .done(handleSuccessfulAuth)
                        .fail(handleAjaxError);
                    })
                    .catch((error) => {
                        showMessage('Invalid verification code', true);
                    })
                    .finally(() => {
                        submitBtn.prop('disabled', false);
                        submitBtn.html('Verify Code');
                    });
            }
        } else {
            // Handle email authentication with WordPress
            if (isOtpSent) {
                const otp = otpInput.val().trim();
                verifyLoginOtp(emailPhone, otp)
                    .done(function(response) {
                        if (response.success) {
                            handleSuccessfulAuth(response);
                        } else {
                            showMessage(response.data, true);
                        }
                    })
                    .fail(handleAjaxError)
                    .always(function() {
                        submitBtn.prop('disabled', false);
                        submitBtn.html('Verify Code');
                    });
            } else {
                sendLoginOtp(emailPhone)
                    .done(function(response) {
                        if (response.success) {
                            isOtpSent = true;
                            otpContainer.slideDown();
                            submitBtn.html('Verify Code');
                            showMessage('Verification code sent!', false);
                            // For development only
                            if (response.data.otp) {
                                otpInput.val(response.data.otp);
                            }
                        } else {
                            showMessage(response.data, true);
                        }
                    })
                    .fail(handleAjaxError)
                    .always(function() {
                        submitBtn.prop('disabled', false);
                    });
            }
        }
    });
});
