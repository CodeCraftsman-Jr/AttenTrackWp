/**
 * Firebase Phone Authentication
 * This file handles phone number verification using Firebase Authentication
 */

// Initialize the Firebase Phone Auth UI
function initFirebasePhoneAuth() {
    // Check if Firebase is initialized
    if (typeof firebase === 'undefined' || !firebase.apps.length) {
        console.error('Firebase is not initialized. Please refresh the page and try again.');
        return;
    }

    // Get the account type
    const accountType = getAccountType();
    
    // Set up the reCAPTCHA verifier
    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
        'size': 'normal',
        'callback': (response) => {
            // reCAPTCHA solved, enable the Send Code button
            document.getElementById('submit-btn').disabled = false;
        },
        'expired-callback': () => {
            // Reset reCAPTCHA
            document.getElementById('submit-btn').disabled = true;
            window.recaptchaVerifier.render().then(function(widgetId) {
                grecaptcha.reset(widgetId);
            });
        }
    });
    
    // Render the reCAPTCHA
    window.recaptchaVerifier.render().then(function(widgetId) {
        window.recaptchaWidgetId = widgetId;
    });
}

// Send verification code to phone number
function sendPhoneVerificationCode(phoneNumber) {
    return new Promise((resolve, reject) => {
        if (!window.recaptchaVerifier) {
            reject(new Error('reCAPTCHA not initialized'));
            return;
        }
        
        // Format the phone number if needed
        if (!phoneNumber.startsWith('+')) {
            // Add country code if not present (default to +1 for US)
            phoneNumber = '+1' + phoneNumber;
        }
        
        // Send verification code
        firebase.auth().signInWithPhoneNumber(phoneNumber, window.recaptchaVerifier)
            .then((confirmationResult) => {
                // SMS sent. Save the confirmation result for later use
                window.confirmationResult = confirmationResult;
                resolve({ success: true, message: 'Verification code sent successfully' });
            })
            .catch((error) => {
                console.error('Error sending verification code:', error);
                // Reset reCAPTCHA
                if (window.recaptchaWidgetId) {
                    grecaptcha.reset(window.recaptchaWidgetId);
                }
                reject(error);
            });
    });
}

// Verify the code entered by the user
function verifyPhoneCode(code) {
    return new Promise((resolve, reject) => {
        if (!window.confirmationResult) {
            reject(new Error('No verification code was sent'));
            return;
        }
        
        // Get the account type
        const accountType = getAccountType();
        
        // Verify the code
        window.confirmationResult.confirm(code)
            .then((result) => {
                // User signed in successfully
                const user = result.user;
                
                // Send the user data to the server
                const xhr = new XMLHttpRequest();
                xhr.open('POST', authData.ajaxUrl, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                resolve(response);
                            } else {
                                reject(new Error(response.data || 'Server error'));
                            }
                        } catch (e) {
                            reject(e);
                        }
                    } else {
                        reject(new Error('Server error'));
                    }
                };
                
                xhr.onerror = function() {
                    reject(new Error('Network error'));
                };
                
                const data = new URLSearchParams();
                data.append('action', 'verify_firebase_token');
                data.append('token', user.uid);
                data.append('provider', 'phone');
                data.append('email', user.phoneNumber); // Use phone number as identifier
                data.append('name', user.displayName || 'Phone User');
                data.append('account_type', accountType);
                data.append('_ajax_nonce', authData.nonce);
                
                xhr.send(data);
            })
            .catch((error) => {
                console.error('Error verifying code:', error);
                reject(error);
            });
    });
}

// Handle email OTP (custom implementation)
function sendEmailOTP(email, accountType) {
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
        data.append('email_or_phone', email);
        data.append('account_type', accountType);
        data.append('_ajax_nonce', authData.nonce);
        
        xhr.send(data);
    });
}

// Verify email OTP (custom implementation)
function verifyEmailOTP(email, otp, accountType) {
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
        data.append('email_or_phone', email);
        data.append('otp', otp);
        data.append('account_type', accountType);
        data.append('_ajax_nonce', authData.nonce);
        
        xhr.send(data);
    });
}

// Detect if the input is an email or phone number
function isEmail(input) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(input);
}

// Send verification code based on input type (email or phone)
function sendVerificationCode(input, accountType) {
    if (isEmail(input)) {
        // Send email OTP
        return sendEmailOTP(input, accountType);
    } else {
        // Send phone verification code
        return sendPhoneVerificationCode(input);
    }
}

// Verify code based on input type (email or phone)
function verifyCode(input, code, accountType) {
    if (isEmail(input)) {
        // Verify email OTP
        return verifyEmailOTP(input, code, accountType);
    } else {
        // Verify phone code
        return verifyPhoneCode(code);
    }
}
