// Common UI Elements
const loadingSpinner = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';

// Phone number validation
function validatePhoneNumber(phone) {
    // Remove all non-digit characters
    const cleanPhone = phone.replace(/\D/g, '');
    
    // Check if it's a valid Indian phone number
    const phoneRegex = /^(?:(?:\+|0{0,2})91(\s*[\-]\s*)?|[0]?)?[6789]\d{9}$/;
    return phoneRegex.test(cleanPhone);
}

// Loading state management
function setLoading(buttonId, isLoading) {
    const button = document.getElementById(buttonId);
    if (!button) return;

    if (isLoading) {
        button.disabled = true;
        button.innerHTML = `${loadingSpinner} Processing...`;
    } else {
        button.disabled = false;
        button.innerHTML = button.getAttribute('data-original-text') || button.innerHTML;
    }
}

// Google Sign-in
function signInWithGoogle() {
    if (!auth || !googleProvider) {
        showMessage('Google authentication not initialized', true);
        return;
    }

    const button = document.getElementById('googleSignIn');
    button.disabled = true;
    button.innerHTML = `<div class="spinner-border spinner-border-sm me-2" role="status"></div>Connecting...`;

    auth.signInWithPopup(googleProvider)
        .then((result) => {
            const user = result.user;
            handleSuccessfulAuth(user);
        })
        .catch((error) => {
            showMessage(error.message, true);
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = `<img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" class="me-2" style="width: 18px;">Continue with Google`;
        });
}

// Facebook Sign-in
function signInWithFacebook() {
    if (!auth || !facebookProvider) {
        showMessage('Facebook authentication not initialized', true);
        return;
    }

    const button = document.getElementById('facebookSignIn');
    button.disabled = true;
    button.innerHTML = `<div class="spinner-border spinner-border-sm me-2" role="status"></div>Connecting...`;

    auth.signInWithPopup(facebookProvider)
        .then((result) => {
            const user = result.user;
            handleSuccessfulAuth(user);
        })
        .catch((error) => {
            showMessage(error.message, true);
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = `<svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="#fff">
                <path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm3 8h-1.35c-.538 0-.65.221-.65.778v1.222h2l-.209 2h-1.791v7h-3v-7h-2v-2h2v-2.308c0-1.769.931-2.692 3.029-2.692h1.971v3z"/>
            </svg>Continue with Facebook`;
        });
}

// Email/Password Sign-up
function signUpWithEmail(email, password) {
    if (!auth) {
        showMessage('Firebase authentication not initialized', true);
        return;
    }

    const button = document.getElementById('emailSignUp');
    button.disabled = true;
    button.innerHTML = `<div class="spinner-border spinner-border-sm me-2" role="status"></div>Creating Account...`;

    auth.createUserWithEmailAndPassword(email, password)
        .then((userCredential) => {
            const user = userCredential.user;
            handleSuccessfulAuth(user);
        })
        .catch((error) => {
            showMessage(error.message, true);
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = 'Create Account';
        });
}

// Email/Password Sign-in
function signInWithEmail(email, password) {
    if (!auth) {
        showMessage('Firebase authentication not initialized', true);
        return;
    }

    const button = document.getElementById('emailSignIn');
    button.disabled = true;
    button.innerHTML = `<div class="spinner-border spinner-border-sm me-2" role="status"></div>Signing In...`;

    auth.signInWithEmailAndPassword(email, password)
        .then((userCredential) => {
            const user = userCredential.user;
            handleSuccessfulAuth(user);
        })
        .catch((error) => {
            showMessage(error.message, true);
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = 'Sign In';
        });
}

// Password Reset
function resetPassword(email) {
    if (!auth) {
        showMessage('Firebase authentication not initialized', true);
        return;
    }

    const button = document.getElementById('resetPassword');
    button.disabled = true;
    button.innerHTML = `<div class="spinner-border spinner-border-sm me-2" role="status"></div>Sending...`;

    auth.sendPasswordResetEmail(email)
        .then(() => {
            showMessage('Password reset email sent! Check your inbox.');
        })
        .catch((error) => {
            showMessage(error.message, true);
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = 'Reset Password';
        });
}

// Phone Number Authentication
function sendVerificationCode() {
    if (!auth) {
        showMessage('Firebase authentication not initialized', true);
        return;
    }

    const phoneInput = document.getElementById('phone');
    const phone = '+91' + phoneInput.value;

    if (!/^\+91\d{10}$/.test(phone)) {
        showMessage('Please enter a valid 10-digit phone number', true);
        return;
    }

    const button = document.getElementById('sendCode');
    button.disabled = true;
    button.innerHTML = `<div class="spinner-border spinner-border-sm me-2" role="status"></div>Sending...`;

    const appVerifier = window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
        'size': 'normal',
        'callback': () => {
            document.getElementById('sendCode').disabled = false;
        }
    });

    auth.signInWithPhoneNumber(phone, appVerifier)
        .then((confirmationResult) => {
            window.confirmationResult = confirmationResult;
            document.getElementById('phoneStep').style.display = 'none';
            document.getElementById('verification-code-container').style.display = 'block';
            showMessage('Verification code sent successfully!');
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
            button.disabled = false;
            button.innerHTML = 'SEND CODE';
        });
}

function verifyCode() {
    if (!auth || !window.confirmationResult) {
        showMessage('Verification process not initialized properly', true);
        return;
    }

    const code = document.getElementById('verification-code').value;
    
    if (!/^\d{6}$/.test(code)) {
        showMessage('Please enter a valid 6-digit code', true);
        return;
    }

    const button = document.getElementById('verifyCode');
    button.disabled = true;
    button.innerHTML = `<div class="spinner-border spinner-border-sm me-2" role="status"></div>Verifying...`;

    window.confirmationResult.confirm(code)
        .then((result) => {
            const user = result.user;
            showMessage('Phone number verified successfully!');
            handleSuccessfulAuth(user);
        })
        .catch((error) => {
            showMessage(error.message, true);
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = 'VERIFY CODE';
        });
}

// Common success handler
function handleSuccessfulAuth(user) {
    const data = {
        action: 'firebase_auth_handler',
        firebase_id: user.uid,
        email: user.email || null,
        display_name: user.displayName || null,
        phone_number: user.phoneNumber || null,
        photo_url: user.photoURL || null,
        provider_id: user.providerData[0]?.providerId || 'phone'
    };

    const formData = new URLSearchParams(data);

    fetch(attentrack_ajax.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            window.location.href = '/dashboard';
        } else {
            throw new Error(data.data || 'Authentication failed');
        }
    })
    .catch(error => {
        showMessage('Error: ' + error.message, true);
    });
}

// Helper function to show messages
function showMessage(message, isError = false) {
    const successDiv = document.getElementById('successMessage');
    const errorDiv = document.getElementById('errorMessage');
    
    if (isError) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        successDiv.style.display = 'none';
    } else {
        successDiv.textContent = message;
        successDiv.style.display = 'block';
        errorDiv.style.display = 'none';
    }

    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (isError) {
            errorDiv.style.display = 'none';
        } else {
            successDiv.style.display = 'none';
        }
    }, 5000);
}

// Forgot Password
function forgotPassword() {
    const email = document.getElementById('email').value;
    if (!email) {
        showMessage('Please enter your email address first', true);
        return;
    }

    auth.sendPasswordResetEmail(email)
        .then(() => {
            showMessage('Password reset email sent! Please check your inbox.');
        })
        .catch((error) => {
            showMessage('Error sending password reset email: ' + error.message, true);
        });
}

// Resend timer functionality
function startResendTimer() {
    let timeLeft = 60;
    const timerDisplay = document.getElementById('timer');
    const resendButton = document.getElementById('resendCode');
    
    resendButton.disabled = true;
    
    const timer = setInterval(() => {
        if (timeLeft <= 0) {
            clearInterval(timer);
            resendButton.disabled = false;
            timerDisplay.textContent = '';
        } else {
            timerDisplay.textContent = `Resend code in ${timeLeft} seconds`;
            timeLeft--;
        }
    }, 1000);
}
