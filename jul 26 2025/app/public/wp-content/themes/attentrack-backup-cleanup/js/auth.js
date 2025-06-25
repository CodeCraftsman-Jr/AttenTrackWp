// Firebase Configuration
window.firebaseConfig = {
    apiKey: "AIzaSyDxwNXFliKJPC39UOweKnWNvpPipf7-PXc",
    authDomain: "innovproject-274c9.firebaseapp.com",
    projectId: "innovproject-274c9",
    storageBucket: "innovproject-274c9.appspot.com",
    messagingSenderId: "1033236935463",
    appId: "1:1033236935463:web:2b5d0d5a9e5e7c1a9e5e7c",
    measurementId: "G-MEASUREMENT_ID"
};

// Show message in alert container
function showMessage(message, isError = false) {
    const alertContainer = document.getElementById('alert-container');
    if (alertContainer) {
        alertContainer.innerHTML = `
            <div class="alert ${isError ? 'alert-danger' : 'alert-success'} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    } else {
        alert(message);
    }
}

// Handle AJAX errors
function handleAjaxError(error) {
    console.error('AJAX Error:', error);
    showMessage('An error occurred. Please try again.', true);
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
        data.append('action', 'send_otp');
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
        data.append('action', 'verify_otp');
        data.append('email_or_phone', emailOrPhone);
        data.append('otp', otp);
        data.append('account_type', accountType);
        data.append('_ajax_nonce', authData.nonce);
        
        xhr.send(data);
    });
}

// Handle successful authentication
function handleSuccessfulAuth(response) {
    if (response.data && response.data.redirect) {
        window.location.href = response.data.redirect;
    } else {
        showMessage('Authentication successful, but no redirect URL provided.');
    }
}

// Google Sign-in
function signInWithGoogle() {
    return new Promise((resolve, reject) => {
        if (!window.auth || !window.googleProvider) {
            console.error('Firebase Auth not initialized. Auth:', window.auth, 'GoogleProvider:', window.googleProvider);
            reject(new Error('Firebase Auth not initialized'));
            return;
        }
        
        console.log('Starting Google sign-in process');
        
        // Store account type in session storage to retrieve after redirect
        const accountType = getAccountType();
        sessionStorage.setItem('attentrack_account_type', accountType);
        console.log('Stored account type in session storage:', accountType);
        
        // Use popup method instead of redirect for more reliable authentication
        window.auth.signInWithPopup(window.googleProvider)
            .then((result) => {
                console.log('Google sign-in popup successful:', result);
                // Only process the user after the popup is closed and we have a result
                if (result && result.user) {
                    console.log('Processing user data for server:', result.user.email, result.user.uid);
                    
                    // Send token to server
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', authData.ajaxUrl, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    
                    // Add timeout handling
                    xhr.timeout = 30000; // 30 seconds
                    xhr.ontimeout = function() {
                        console.error('Server request timed out');
                        showMessage('Server request timed out. Please try again.', true);
                        reject(new Error('Server request timed out'));
                    };
                    
                    xhr.onload = function() {
                        console.log('Server response received:', xhr.status, xhr.responseText);
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                console.log('Parsed server response:', response);
                                
                                if (response.success) {
                                    console.log('Authentication successful:', response.data);
                                    resolve(response);
                                } else {
                                    // Handle role-based access errors
                                    console.error('Authentication error:', response.data);
                                    showMessage(response.data, true);
                                    reject(new Error(response.data));
                                }
                            } catch (e) {
                                console.error('Error parsing server response:', e);
                                showMessage('Error processing server response. Please try again.', true);
                                reject(e);
                            }
                        } else {
                            console.error('Server error response:', xhr.status, xhr.statusText, xhr.responseText);
                            showMessage('Server error. Please try again.', true);
                            reject({
                                status: xhr.status,
                                statusText: xhr.statusText
                            });
                        }
                    };
                    
                    xhr.onerror = function() {
                        console.error('AJAX error:', xhr.status, xhr.statusText);
                        showMessage('Network error. Please check your connection and try again.', true);
                        reject({
                            status: xhr.status,
                            statusText: xhr.statusText
                        });
                    };
                    
                    const data = new URLSearchParams();
                    data.append('action', 'verify_firebase_token');
                    data.append('token', result.user.uid);
                    data.append('provider', 'google');
                    data.append('email', result.user.email);
                    data.append('name', result.user.displayName);
                    data.append('account_type', accountType);
                    data.append('_ajax_nonce', authData.nonce);
                    
                    console.log('Sending data to server:', Object.fromEntries(data));
                    xhr.send(data);
                } else {
                    console.error('No user returned from Google sign-in');
                    showMessage('No user data returned from Google. Please try again.', true);
                    reject(new Error('No user returned from Google sign-in'));
                }
            })
            .catch((error) => {
                console.error('Google sign-in error:', error);
                showMessage('Google sign-in failed: ' + (error.message || 'Unknown error'), true);
                reject(error);
            });
    });
}

// Facebook Sign-in
function signInWithFacebook() {
    return new Promise((resolve, reject) => {
        if (!window.auth || !window.facebookProvider) {
            console.error('Firebase Auth not initialized. Auth:', window.auth, 'FacebookProvider:', window.facebookProvider);
            reject(new Error('Firebase Auth not initialized'));
            return;
        }
        
        console.log('Starting Facebook sign-in process');
        
        // Store account type in session storage to retrieve after redirect
        const accountType = getAccountType();
        sessionStorage.setItem('attentrack_account_type', accountType);
        console.log('Stored account type in session storage:', accountType);
        
        // Use popup method instead of redirect for more reliable authentication
        window.auth.signInWithPopup(window.facebookProvider)
            .then((result) => {
                console.log('Facebook sign-in popup successful:', result);
                // Only process the user after the popup is closed and we have a result
                if (result && result.user) {
                    console.log('Processing user data for server:', result.user.email, result.user.uid);
                    
                    // Send token to server
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', authData.ajaxUrl, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    
                    // Add timeout handling
                    xhr.timeout = 30000; // 30 seconds
                    xhr.ontimeout = function() {
                        console.error('Server request timed out');
                        showMessage('Server request timed out. Please try again.', true);
                        reject(new Error('Server request timed out'));
                    };
                    
                    xhr.onload = function() {
                        console.log('Server response received:', xhr.status, xhr.responseText);
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                console.log('Parsed server response:', response);
                                
                                if (response.success) {
                                    console.log('Authentication successful:', response.data);
                                    resolve(response);
                                } else {
                                    // Handle role-based access errors
                                    console.error('Authentication error:', response.data);
                                    showMessage(response.data, true);
                                    reject(new Error(response.data));
                                }
                            } catch (e) {
                                console.error('Error parsing server response:', e);
                                showMessage('Error processing server response. Please try again.', true);
                                reject(e);
                            }
                        } else {
                            console.error('Server error response:', xhr.status, xhr.statusText, xhr.responseText);
                            showMessage('Server error. Please try again.', true);
                            reject({
                                status: xhr.status,
                                statusText: xhr.statusText
                            });
                        }
                    };
                    
                    xhr.onerror = function() {
                        console.error('AJAX error:', xhr.status, xhr.statusText);
                        showMessage('Network error. Please check your connection and try again.', true);
                        reject({
                            status: xhr.status,
                            statusText: xhr.statusText
                        });
                    };
                    
                    const data = new URLSearchParams();
                    data.append('action', 'verify_firebase_token');
                    data.append('token', result.user.uid);
                    data.append('provider', 'facebook');
                    data.append('email', result.user.email);
                    data.append('name', result.user.displayName);
                    data.append('account_type', accountType);
                    data.append('_ajax_nonce', authData.nonce);
                    
                    console.log('Sending data to server:', Object.fromEntries(data));
                    xhr.send(data);
                } else {
                    console.error('No user returned from Facebook sign-in');
                    showMessage('No user data returned from Facebook. Please try again.', true);
                    reject(new Error('No user returned from Facebook sign-in'));
                }
            })
            .catch((error) => {
                console.error('Facebook sign-in error:', error);
                showMessage('Facebook sign-in failed: ' + (error.message || 'Unknown error'), true);
                reject(error);
            });
    });
}

// Sign out function
function signOut() {
    return new Promise((resolve, reject) => {
        if (!window.auth) {
            console.error('Firebase Auth not initialized');
            reject(new Error('Firebase Auth not initialized'));
            return;
        }
        
        window.auth.signOut()
            .then(() => {
                // Clear session storage
                sessionStorage.removeItem('attentrack_account_type');
                
                // Redirect to home page
                window.location.href = '/';
                resolve();
            })
            .catch((error) => {
                console.error('Sign out error:', error);
                reject(error);
            });
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
        console.error('Firebase Auth not initialized. Make sure firebase-config.js is loaded before auth.js');
        return;
    }
    
    // Get form elements
    const form = document.getElementById('auth-form');
    if (!form) return;
    
    const emailPhoneInput = document.getElementById('email-phone');
    const passwordInput = document.getElementById('password');
    const otpInput = document.getElementById('otp');
    const otpContainer = document.getElementById('otp-container');
    const sendOtpBtn = document.getElementById('send-otp-btn');
    const verifyOtpBtn = document.getElementById('verify-otp-btn');
    const googleBtn = document.getElementById('google-signin-btn');
    const facebookBtn = document.getElementById('facebook-signin-btn');
    const phoneAuthBtn = document.getElementById('phone-auth-btn');
    const signupTab = document.getElementById('signup-tab');
    const loginTab = document.getElementById('login-tab');
    
    // Add event listeners for social sign-in buttons
    if (googleBtn) {
        googleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            googleBtn.disabled = true;
            
            signInWithGoogle()
                .then(handleSuccessfulAuth)
                .catch(function(error) {
                    console.error('Google sign-in error:', error);
                    showMessage('Google sign-in failed: ' + (error.message || 'Unknown error'), true);
                })
                .finally(function() {
                    googleBtn.disabled = false;
                });
        });
    }
    
    if (facebookBtn) {
        facebookBtn.addEventListener('click', function(e) {
            e.preventDefault();
            facebookBtn.disabled = true;
            
            signInWithFacebook()
                .then(handleSuccessfulAuth)
                .catch(function(error) {
                    console.error('Facebook sign-in error:', error);
                    showMessage('Facebook sign-in failed: ' + (error.message || 'Unknown error'), true);
                })
                .finally(function() {
                    facebookBtn.disabled = false;
                });
        });
    }
    
    // Validate email/phone
    function validateInput(input) {
        const value = input.value.trim();
        const isEmail = value.includes('@');
        const isPhone = /^\+?[0-9]{10,15}$/.test(value);
        
        return isEmail || isPhone;
    }
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const isSignup = signupTab && signupTab.classList.contains('active');
        const accountType = getAccountType();
        
        // Handle OTP verification
        if (otpContainer && otpContainer.style.display !== 'none' && otpInput && otpInput.value) {
            const otp = otpInput.value.trim();
            const emailOrPhone = emailPhoneInput.value.trim();
            
            verifyLoginOtp(emailOrPhone, otp, accountType)
                .then(handleSuccessfulAuth)
                .catch(handleAjaxError);
            
            return;
        }
        
        // Handle signup or login with username/password
        if (isSignup) {
            // Signup logic
            const username = document.getElementById('username').value.trim();
            const email = emailPhoneInput.value.trim();
            const password = passwordInput.value;
            const phone = document.getElementById('phone') ? document.getElementById('phone').value.trim() : '';
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', authData.ajaxUrl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            handleSuccessfulAuth(response);
                        } else {
                            showMessage(response.data, true);
                        }
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
            data.append('action', 'register_user');
            data.append('username', username);
            data.append('email', email);
            data.append('password', password);
            data.append('account_type', accountType);
            if (phone) {
                data.append('phone', phone);
            }
            data.append('_ajax_nonce', authData.nonce);
            
            xhr.send(data);
        } else {
            // Login logic
            const username = emailPhoneInput.value.trim();
            const password = passwordInput.value;
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', authData.ajaxUrl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            handleSuccessfulAuth(response);
                        } else {
                            showMessage(response.data, true);
                        }
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
            data.append('action', 'login_with_username_password');
            data.append('username', username);
            data.append('password', password);
            data.append('account_type', accountType);
            data.append('_ajax_nonce', authData.nonce);
            
            xhr.send(data);
        }
    });
    
    // Handle OTP sending
    if (sendOtpBtn) {
        sendOtpBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (!emailPhoneInput || !validateInput(emailPhoneInput)) {
                showMessage('Please enter a valid email or phone number', true);
                return;
            }
            
            const emailOrPhone = emailPhoneInput.value.trim();
            const accountType = getAccountType();
            
            sendOtpBtn.disabled = true;
            
            sendLoginOtp(emailOrPhone, accountType)
                .then(function(response) {
                    if (response.success) {
                        showMessage('OTP sent successfully. Please check your email or phone.');
                        
                        // Show OTP input
                        if (otpContainer) {
                            otpContainer.style.display = 'block';
                        }
                        
                        // Hide password input
                        if (passwordInput) {
                            passwordInput.parentElement.style.display = 'none';
                        }
                        
                        // Show verify button
                        if (verifyOtpBtn) {
                            verifyOtpBtn.style.display = 'block';
                        }
                        
                        // Testing only - auto-fill OTP if it's in the response
                        if (response.data && response.data.otp && otpInput) {
                            otpInput.value = response.data.otp;
                        }
                    } else {
                        showMessage(response.data, true);
                    }
                })
                .catch(handleAjaxError)
                .finally(function() {
                    sendOtpBtn.disabled = false;
                });
        });
    }
    
    // Handle phone authentication
    if (phoneAuthBtn) {
        phoneAuthBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (!emailPhoneInput || !validateInput(emailPhoneInput)) {
                showMessage('Please enter a valid phone number', true);
                return;
            }
            
            const phoneNumber = emailPhoneInput.value.trim();
            const accountType = getAccountType();
            
            // Create reCAPTCHA verifier
            const recaptchaContainer = document.getElementById('recaptcha-container');
            if (!recaptchaContainer) {
                showMessage('reCAPTCHA container not found', true);
                return;
            }
            
            phoneAuthBtn.disabled = true;
            
            try {
                const recaptchaVerifier = new firebase.auth.RecaptchaVerifier(recaptchaContainer, {
                    'size': 'normal',
                    'callback': function(response) {
                        // reCAPTCHA solved, send verification code
                        sendVerificationCode(phoneNumber, recaptchaVerifier)
                            .then(function(confirmationResult) {
                                window.confirmationResult = confirmationResult;
                                showMessage('Verification code sent to your phone.');
                                
                                // Show OTP input
                                if (otpContainer) {
                                    otpContainer.style.display = 'block';
                                }
                                
                                // Hide password input
                                if (passwordInput) {
                                    passwordInput.parentElement.style.display = 'none';
                                }
                                
                                // Show verify button
                                if (verifyOtpBtn) {
                                    verifyOtpBtn.style.display = 'block';
                                }
                            })
                            .catch(function(error) {
                                console.error('Error sending verification code:', error);
                                showMessage('Error sending verification code: ' + error.message, true);
                            })
                            .finally(function() {
                                phoneAuthBtn.disabled = false;
                            });
                    },
                    'expired-callback': function() {
                        showMessage('reCAPTCHA expired. Please try again.', true);
                        phoneAuthBtn.disabled = false;
                    }
                });
                
                recaptchaVerifier.render().then(function(widgetId) {
                    window.recaptchaWidgetId = widgetId;
                });
            } catch (error) {
                console.error('Error creating reCAPTCHA:', error);
                showMessage('Error creating reCAPTCHA: ' + error.message, true);
                phoneAuthBtn.disabled = false;
            }
        });
    }
    
    // Handle OTP verification for phone authentication
    if (verifyOtpBtn) {
        verifyOtpBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (!otpInput || !otpInput.value) {
                showMessage('Please enter the verification code', true);
                return;
            }
            
            const otp = otpInput.value.trim();
            const accountType = getAccountType();
            
            verifyOtpBtn.disabled = true;
            
            // If using phone authentication with Firebase
            if (window.confirmationResult) {
                window.confirmationResult.confirm(otp)
                    .then(function(result) {
                        // User signed in successfully
                        const user = result.user;
                        
                        // Send token to server
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', authData.ajaxUrl, true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onload = function() {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.success) {
                                        handleSuccessfulAuth(response);
                                    } else {
                                        showMessage(response.data, true);
                                    }
                                } catch (e) {
                                    handleAjaxError(e);
                                }
                            } else {
                                handleAjaxError({
                                    status: xhr.status,
                                    statusText: xhr.statusText
                                });
                            }
                            verifyOtpBtn.disabled = false;
                        };
                        xhr.onerror = function() {
                            handleAjaxError({
                                status: xhr.status,
                                statusText: xhr.statusText
                            });
                            verifyOtpBtn.disabled = false;
                        };
                        
                        const data = new URLSearchParams();
                        data.append('action', 'verify_firebase_token');
                        data.append('token', user.accessToken);
                        data.append('provider', 'phone');
                        data.append('email', '');
                        data.append('name', user.phoneNumber);
                        data.append('phone', user.phoneNumber);
                        data.append('account_type', accountType);
                        data.append('_ajax_nonce', authData.nonce);
                        
                        xhr.send(data);
                    })
                    .catch(function(error) {
                        console.error('Error confirming verification code:', error);
                        showMessage('Error confirming verification code: ' + error.message, true);
                        verifyOtpBtn.disabled = false;
                    });
            } else {
                // If using custom OTP verification
                const emailOrPhone = emailPhoneInput.value.trim();
                
                verifyLoginOtp(emailOrPhone, otp, accountType)
                    .then(handleSuccessfulAuth)
                    .catch(handleAjaxError)
                    .finally(function() {
                        verifyOtpBtn.disabled = false;
                    });
            }
        });
    }
});
