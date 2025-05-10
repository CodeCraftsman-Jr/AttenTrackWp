// Firebase Configuration
window.firebaseConfig = {
    apiKey: "AIzaSyDxwNXFliKJPC39UOweKnWNvpPipf7-PXc",
    authDomain: "innovproject-274c9.firebaseapp.com",
    projectId: "innovproject-274c9",
    storageBucket: "innovproject-274c9.firebasestorage.app",
    messagingSenderId: "111288496386",
    appId: "1:111288496386:web:38dd0ab7e126ebe93b521b"
};

// Common UI Elements - use window to avoid redeclaration
window.loadingSpinnerHtml = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ';

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

// Get account type value
function getAccountType() {
    const accountTypeField = document.getElementById('account-type');
    return accountTypeField ? accountTypeField.value || 'user' : 'user';
}

// Send OTP
function sendLoginOtp(emailOrPhone, accountType) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', authData.ajaxUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch (e) {
                    reject(e);
                }
            } else {
                reject({
                    status: xhr.status,
                    statusText: xhr.statusText
                });
            }
        };
        xhr.onerror = function() {
            reject({
                status: xhr.status,
                statusText: xhr.statusText
            });
        };
        
        const data = new URLSearchParams();
        data.append('action', 'send_login_otp');
        data.append('email_or_phone', emailOrPhone);
        data.append('account_type', accountType);
        data.append('_ajax_nonce', authData.nonce);
        
        xhr.send(data);
    });
}

// Verify OTP
function verifyLoginOtp(emailOrPhone, otp, accountType) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', authData.ajaxUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch (e) {
                    reject(e);
                }
            } else {
                reject({
                    status: xhr.status,
                    statusText: xhr.statusText
                });
            }
        };
        xhr.onerror = function() {
            reject({
                status: xhr.status,
                statusText: xhr.statusText
            });
        };
        
        const data = new URLSearchParams();
        data.append('action', 'verify_login_otp');
        data.append('email_or_phone', emailOrPhone);
        data.append('otp', otp);
        data.append('account_type', accountType);
        data.append('_ajax_nonce', authData.nonce);
        
        xhr.send(data);
    });
}

// Handle successful authentication
function handleSuccessfulAuth(response) {
    if (response.data && response.data.redirect_url) {
        window.location.href = response.data.redirect_url;
    }
}

// Google Sign-in
function signInWithGoogle() {
    return new Promise((resolve, reject) => {
        if (!window.auth || !window.googleProvider) {
            reject(new Error('Firebase Auth not initialized'));
            return;
        }
        
        // Store account type in session storage to retrieve after redirect
        const accountType = getAccountType();
        sessionStorage.setItem('attentrack_account_type', accountType);
        
        // Set up auth state change listener to handle redirect result
        const unsubscribe = window.auth.onAuthStateChanged((user) => {
            if (user) {
                // User is signed in, send token to server
                unsubscribe(); // Unsubscribe to avoid multiple calls
                
                const xhr = new XMLHttpRequest();
                xhr.open('POST', authData.ajaxUrl, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            resolve(response);
                        } catch (e) {
                            reject(e);
                        }
                    } else {
                        reject({
                            status: xhr.status,
                            statusText: xhr.statusText
                        });
                    }
                };
                xhr.onerror = function() {
                    reject({
                        status: xhr.status,
                        statusText: xhr.statusText
                    });
                };
                
                const data = new URLSearchParams();
                data.append('action', 'verify_firebase_token');
                data.append('token', user.uid);
                data.append('provider', 'google');
                data.append('email', user.email);
                data.append('name', user.displayName);
                data.append('account_type', accountType);
                data.append('_ajax_nonce', authData.nonce);
                
                xhr.send(data);
            }
        });
        
        // Use redirect method instead of popup to avoid COOP issues
        window.auth.signInWithRedirect(window.googleProvider)
            .catch((error) => {
                unsubscribe(); // Unsubscribe on error
                console.error('Google sign-in error:', error);
                reject(error);
            });
    });
}

// Facebook Sign-in
function signInWithFacebook() {
    return new Promise((resolve, reject) => {
        if (!window.auth || !window.facebookProvider) {
            reject(new Error('Firebase Auth not initialized'));
            return;
        }
        
        // Store account type in session storage to retrieve after redirect
        const accountType = getAccountType();
        sessionStorage.setItem('attentrack_account_type', accountType);
        
        // Set up auth state change listener to handle redirect result
        const unsubscribe = window.auth.onAuthStateChanged((user) => {
            if (user) {
                // User is signed in, send token to server
                unsubscribe(); // Unsubscribe to avoid multiple calls
                
                const xhr = new XMLHttpRequest();
                xhr.open('POST', authData.ajaxUrl, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            resolve(response);
                        } catch (e) {
                            reject(e);
                        }
                    } else {
                        reject({
                            status: xhr.status,
                            statusText: xhr.statusText
                        });
                    }
                };
                xhr.onerror = function() {
                    reject({
                        status: xhr.status,
                        statusText: xhr.statusText
                    });
                };
                
                const data = new URLSearchParams();
                data.append('action', 'verify_firebase_token');
                data.append('token', user.uid);
                data.append('provider', 'facebook');
                data.append('email', user.email);
                data.append('name', user.displayName);
                data.append('account_type', accountType);
                data.append('_ajax_nonce', authData.nonce);
                
                xhr.send(data);
            }
        });
        
        // Use redirect method instead of popup to avoid COOP issues
        window.auth.signInWithRedirect(window.facebookProvider)
            .catch((error) => {
                unsubscribe(); // Unsubscribe on error
                console.error('Facebook sign-in error:', error);
                reject(error);
            });
    });
}

// Sign out function
function signOut() {
    return new Promise((resolve, reject) => {
        try {
            // Check if Firebase is properly initialized
            if (typeof firebase === 'undefined') {
                console.warn('Firebase is not defined, redirecting to WordPress logout');
                window.location.href = authData.logoutUrl || '/';
                return;
            }
            
            // Check if auth is available
            if (!firebase.apps.length) {
                console.warn('No Firebase apps initialized, redirecting to WordPress logout');
                window.location.href = authData.logoutUrl || '/';
                return;
            }
            
            // Get auth instance safely
            const auth = firebase.auth();
            if (!auth) {
                console.warn('Firebase Auth not available, redirecting to WordPress logout');
                window.location.href = authData.logoutUrl || '/';
                return;
            }
            
            // Now try to sign out
            auth.signOut()
                .then(() => {
                    console.log('Firebase sign-out successful');
                    
                    // Send logout request to WordPress
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', authData.ajaxUrl, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                console.log('WordPress logout successful');
                                window.location.href = authData.logoutUrl || '/';
                                resolve(response);
                            } catch (e) {
                                console.error('Error parsing logout response:', e);
                                window.location.href = authData.logoutUrl || '/';
                                reject(e);
                            }
                        } else {
                            console.error('WordPress logout failed:', xhr.status, xhr.statusText);
                            window.location.href = authData.logoutUrl || '/';
                            reject({
                                status: xhr.status,
                                statusText: xhr.statusText
                            });
                        }
                    };
                    xhr.onerror = function() {
                        console.error('Network error during logout');
                        window.location.href = authData.logoutUrl || '/';
                        reject({
                            status: 0,
                            statusText: 'Unknown Error'
                        });
                    };
                    
                    const data = new URLSearchParams();
                    data.append('action', 'logout');
                    data.append('_ajax_nonce', authData.nonce);
                    xhr.send(data);
                })
                .catch((error) => {
                    console.error('Firebase sign-out error:', error);
                    window.location.href = authData.logoutUrl || '/';
                    reject(error);
                });
        } catch (e) {
            console.error('Unexpected error during sign-out:', e);
            window.location.href = authData.logoutUrl || '/';
            reject(e);
        }
    });
}

// Send verification code for phone authentication
function sendVerificationCode(phoneNumber, recaptchaVerifier) {
    return window.auth.signInWithPhoneNumber(phoneNumber, recaptchaVerifier);
}

// Initialize on document ready
document.addEventListener('DOMContentLoaded', function() {
    // Wait for firebase-config.js to initialize Firebase first
    if (typeof window.auth === 'undefined') {
        console.log('Waiting for Firebase to be initialized by firebase-config.js');
        return;
    }
    
    // Initialize reCAPTCHA
    if (document.getElementById('recaptcha-container')) {
        try {
            window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                'size': 'invisible',
                'callback': (response) => {
                    // reCAPTCHA solved, allow signInWithPhoneNumber.
                    console.log('reCAPTCHA verified');
                },
                'expired-callback': () => {
                    // Response expired. Ask user to solve reCAPTCHA again.
                    console.log('reCAPTCHA expired');
                    grecaptcha.reset(window.recaptchaWidgetId);
                }
            });
            
            window.recaptchaWidgetId = window.recaptchaVerifier.render();
        } catch (error) {
            console.error('reCAPTCHA initialization error:', error);
        }
    }
    
    // Setup login form handlers
    const form = document.getElementById('login-form');
    if (!form) return;
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const emailPhoneInput = document.getElementById('email-phone');
    const otpContainer = document.getElementById('otp-container');
    const otpInput = document.getElementById('otp');
    let isOtpSent = false;
    
    // Validate email/phone
    function validateInput(input) {
        const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input);
        const isPhone = /^\d{10}$/.test(input.replace(/[^0-9]/g, ''));
        return isEmail || isPhone;
    }
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const emailPhone = emailPhoneInput.value.trim();
        if (!validateInput(emailPhone)) {
            showMessage('Please enter a valid email or phone number', true);
            return;
        }
        
        submitBtn.disabled = true;
        const loadingText = isOtpSent ? 'Verifying...' : 'Sending...';
        submitBtn.innerHTML = window.loadingSpinnerHtml + loadingText;

        const isPhone = /^\d{10}$/.test(emailPhone.replace(/[^0-9]/g, ''));
        const accountType = getAccountType();
        
        if (isPhone) {
            // Handle phone authentication with Firebase
            const phoneNumber = '+91' + emailPhone.replace(/[^0-9]/g, '');
            if (!isOtpSent) {
                const appVerifier = window.recaptchaVerifier;
                sendVerificationCode(phoneNumber, appVerifier)
                    .then(function(confirmationResult) {
                        window.confirmationResult = confirmationResult;
                        isOtpSent = true;
                        otpContainer.style.display = 'block';
                        submitBtn.innerHTML = 'Verify Code';
                        showMessage('Verification code sent!', false);
                    })
                    .catch(function(error) {
                        console.error('Phone auth error:', error);
                        showMessage('Failed to send verification code: ' + error.message, true);
                        if (window.recaptchaWidgetId) {
                            grecaptcha.reset(window.recaptchaWidgetId);
                        }
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                    });
            } else {
                const code = otpInput.value.trim();
                if (!window.confirmationResult) {
                    showMessage('Please request a new code', true);
                    return;
                }
                window.confirmationResult.confirm(code)
                    .then((result) => {
                        const user = result.user;
                        // Send token to WordPress
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', authData.ajaxUrl, true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onload = function() {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    handleSuccessfulAuth(response);
                                } catch (e) {
                                    handleAjaxError(e);
                                }
                            } else {
                                handleAjaxError({
                                    status: xhr.status,
                                    statusText: xhr.statusText
                                });
                            }
                        };
                        xhr.onerror = function() {
                            handleAjaxError({
                                status: xhr.status,
                                statusText: xhr.statusText
                            });
                        };
                        
                        const data = new URLSearchParams();
                        data.append('action', 'verify_firebase_token');
                        data.append('token', user.accessToken);
                        data.append('provider', 'phone');
                        data.append('phone', phoneNumber);
                        data.append('account_type', accountType);
                        data.append('_ajax_nonce', authData.nonce);
                        
                        xhr.send(data);
                    })
                    .catch((error) => {
                        showMessage('Invalid verification code', true);
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Verify Code';
                    });
            }
        } else {
            // Handle email authentication with WordPress
            if (isOtpSent) {
                const otp = otpInput.value.trim();
                verifyLoginOtp(emailPhone, otp, accountType)
                    .then(function(response) {
                        if (response.success) {
                            handleSuccessfulAuth(response);
                        } else {
                            showMessage(response.data, true);
                        }
                    })
                    .catch(handleAjaxError)
                    .finally(function() {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Verify Code';
                    });
            } else {
                sendLoginOtp(emailPhone, accountType)
                    .then(function(response) {
                        if (response.success) {
                            isOtpSent = true;
                            otpContainer.style.display = 'block';
                            submitBtn.innerHTML = 'Verify Code';
                            showMessage('Verification code sent!', false);
                            // For development only
                            if (response.data.otp) {
                                otpInput.value = response.data.otp;
                            }
                        } else {
                            showMessage(response.data, true);
                        }
                    })
                    .catch(handleAjaxError)
                    .finally(function() {
                        submitBtn.disabled = false;
                    });
            }
        }
    });
});
