// Firebase Phone Authentication
function initializePhoneAuth() {
    // Only initialize on sign-in page
    if (!document.getElementById('phoneStep')) {
        return; // Not on sign-in page
    }

    try {
        // Check if Firebase is initialized
        if (typeof firebase === 'undefined') {
            console.error('Firebase not loaded');
            showMessage('Authentication service not available. Please try again later.', true);
            return;
        }

        // Get auth instance
        const auth = firebase.auth();
        if (!auth) {
            console.error('Firebase auth not initialized');
            showMessage('Authentication service not ready. Please try again later.', true);
            return;
        }

        const recaptchaContainer = document.getElementById('recaptcha-container');
        if (!recaptchaContainer) {
            console.error('Recaptcha container not found');
            return;
        }

        // Initialize reCAPTCHA
        window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
            'size': 'normal',
            'callback': (response) => {
                // Enable the Send Code button if phone number is valid
                validatePhoneNumber();
            },
            'expired-callback': () => {
                // Disable the Send Code button
                const sendCodeBtn = document.getElementById('sendCode');
                if (sendCodeBtn) {
                    sendCodeBtn.setAttribute('disabled', 'disabled');
                }
                // Reset reCAPTCHA
                if (window.recaptchaVerifier) {
                    window.recaptchaVerifier.clear();
                    window.recaptchaVerifier = null;
                    // Reinitialize
                    setTimeout(initializePhoneAuth, 1000);
                }
            }
        });

        // Add phone number input listener
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', validatePhoneNumber);
            // Set initial placeholder
            phoneInput.placeholder = '98765 43210';
        }

        // Render the reCAPTCHA
        window.recaptchaVerifier.render().then(() => {
            console.log('reCAPTCHA rendered successfully');
        }).catch(error => {
            console.error('Error rendering reCAPTCHA:', error);
            showMessage('Error setting up phone verification. Please try again.', true);
            
            // Clear and retry
            if (window.recaptchaVerifier) {
                window.recaptchaVerifier.clear();
                window.recaptchaVerifier = null;
            }
        });
    } catch (error) {
        console.error('Error in initializePhoneAuth:', error);
        showMessage('Error setting up phone verification. Please try again.', true);
    }
}

// Validate phone number format
function validatePhoneNumber() {
    const phoneInput = document.getElementById('phone');
    const sendCodeBtn = document.getElementById('sendCode');
    
    if (!phoneInput || !sendCodeBtn) return;

    let phoneNumber = phoneInput.value.trim();
    
    // Remove any non-digit characters
    phoneNumber = phoneNumber.replace(/\D/g, '');
    
    // Format the number with spaces for readability (only if we have enough digits)
    if (phoneNumber.length > 5) {
        // Add first space after 5 digits
        phoneNumber = phoneNumber.slice(0, 5) + ' ' + phoneNumber.slice(5);
        // Add second space after 4 more digits if we have them
        if (phoneNumber.replace(/\D/g, '').length > 5) {
            const parts = phoneNumber.split(' ');
            if (parts[1] && parts[1].length > 4) {
                phoneNumber = parts[0] + ' ' + parts[1].slice(0, 4) + ' ' + parts[1].slice(4);
            }
        }
    }
    
    // Limit to 10 digits
    const digitsOnly = phoneNumber.replace(/\D/g, '');
    if (digitsOnly.length > 10) {
        const truncated = digitsOnly.slice(0, 10);
        // Reformat with the truncated number
        phoneNumber = truncated.slice(0, 5) + ' ' + truncated.slice(5, 9) + ' ' + truncated.slice(9);
    }
    
    // Update input value with formatted number
    phoneInput.value = phoneNumber;
    
    // Add +91 prefix for Firebase validation
    const fullNumber = '+91' + phoneNumber.replace(/\s/g, '');
    
    // Validate: should be exactly 10 digits
    const isValid = phoneNumber.replace(/\s/g, '').length === 10;
    
    // Enable/disable send button based on validation
    if (isValid && window.recaptchaVerifier) {
        sendCodeBtn.removeAttribute('disabled');
    } else {
        sendCodeBtn.setAttribute('disabled', 'disabled');
    }
    
    return isValid ? fullNumber : null;
}

// Send verification code
function sendVerificationCode() {
    try {
        // Get full phone number with country code
        const fullNumber = validatePhoneNumber();
        if (!fullNumber) {
            showMessage('Please enter a valid 10-digit phone number', true);
            return;
        }

        // Disable send button and show loading
        const sendCodeBtn = document.getElementById('sendCode');
        if (sendCodeBtn) {
            sendCodeBtn.setAttribute('disabled', 'disabled');
            sendCodeBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
        }

        // Get the reCAPTCHA verifier
        if (!window.recaptchaVerifier) {
            showMessage('Phone verification not initialized. Please refresh the page.', true);
            return;
        }

        // Send verification code
        firebase.auth().signInWithPhoneNumber(fullNumber, window.recaptchaVerifier)
            .then((confirmationResult) => {
                window.confirmationResult = confirmationResult;
                
                // Show verification code section
                document.getElementById('verification-code-container').style.display = 'block';
                document.getElementById('phoneStep').style.display = 'none';
                
                // Show success message
                showMessage('Verification code sent!', false);
            })
            .catch((error) => {
                console.error('Error sending verification code:', error);
                
                // Handle specific error cases
                let errorMessage = 'Error sending verification code. ';
                switch (error.code) {
                    case 'auth/billing-not-enabled':
                        errorMessage = 'Phone authentication is temporarily unavailable. Please try again later or contact support.';
                        break;
                    case 'auth/invalid-phone-number':
                        errorMessage = 'Please enter a valid 10-digit phone number';
                        break;
                    case 'auth/quota-exceeded':
                        errorMessage = 'Too many attempts. Please try again later.';
                        break;
                    case 'auth/user-disabled':
                        errorMessage = 'This phone number has been disabled. Please contact support.';
                        break;
                    case 'auth/operation-not-allowed':
                        errorMessage = 'Phone authentication is not enabled. Please contact support.';
                        break;
                    default:
                        errorMessage += error.message;
                }
                
                showMessage(errorMessage, true);
                
                // Reset button
                if (sendCodeBtn) {
                    sendCodeBtn.removeAttribute('disabled');
                    sendCodeBtn.innerHTML = 'Send Code';
                }
                
                // Reset reCAPTCHA
                if (window.recaptchaVerifier) {
                    window.recaptchaVerifier.clear();
                    window.recaptchaVerifier = null;
                    setTimeout(initializePhoneAuth, 1000);
                }
            });
    } catch (error) {
        console.error('Error in sendVerificationCode:', error);
        showMessage('Error sending verification code. Please try again.', true);
    }
}

// Verify the code
function verifyCode() {
    const code = document.getElementById('verification-code').value;
    const verifyButton = document.getElementById('verifyCode');
    
    // Disable verify button and show loading
    verifyButton.setAttribute('disabled', 'disabled');
    verifyButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...';

    window.confirmationResult.confirm(code)
        .then((result) => {
            // User signed in successfully
            const user = result.user;
            
            // Get the user's phone number
            const phoneNumber = user.phoneNumber;
            
            // Create WordPress user
            createWordPressUser(phoneNumber);
        })
        .catch((error) => {
            console.error('Error verifying code:', error);
            showMessage('Error verifying code: ' + error.message, true);
            
            // Reset the button
            verifyButton.removeAttribute('disabled');
            verifyButton.innerHTML = 'Verify Code';
        });
}

// Create WordPress user
function createWordPressUser(phoneNumber) {
    // Get the form data
    const formData = new FormData();
    formData.append('action', 'register_user');
    formData.append('phone', phoneNumber);
    formData.append('security', attentrack_ajax.nonce);

    // Send request to WordPress
    fetch(attentrack_ajax.ajax_url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Successfully signed up!', false);
            // Redirect to dashboard after successful signup
            setTimeout(() => {
                window.location.href = data.redirect_url;
            }, 1500);
        } else {
            throw new Error(data.data.message || 'Error creating WordPress user');
        }
    })
    .catch(error => {
        console.error('Error creating WordPress user:', error);
        showMessage('Error creating account: ' + error.message, true);
        
        // Reset verify button
        document.getElementById('verifyCode').removeAttribute('disabled');
        document.getElementById('verifyCode').innerHTML = 'Verify Code';
    });
}

// Show message helper function
function showMessage(message, isError) {
    const messageDiv = document.getElementById(isError ? 'errorMessage' : 'successMessage');
    if (messageDiv) {
        messageDiv.textContent = message;
        messageDiv.style.display = 'block';
        
        // Hide the message after 5 seconds
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 5000);
    }
}

// Initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    // Wait for Firebase to be ready
    if (typeof firebase !== 'undefined') {
        setTimeout(initializePhoneAuth, 1000);
    } else {
        console.error('Firebase not loaded');
        showMessage('Error: Authentication service not available.', true);
    }
});
