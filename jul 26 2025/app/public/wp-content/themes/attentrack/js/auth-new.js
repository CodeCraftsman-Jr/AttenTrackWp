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
                                    
                                    // Extract the error message from the response data object
                                    let errorMessage = 'Authentication failed. Please try again.';
                                    if (response.data && typeof response.data === 'object' && response.data.message) {
                                        errorMessage = response.data.message;
                                    } else if (typeof response.data === 'string') {
                                        errorMessage = response.data;
                                    }
                                    
                                    showMessage(errorMessage, true);
                                    reject(new Error(errorMessage));
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
                    
                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                
                                if (response.success) {
                                    resolve(response);
                                } else {
                                    // Handle role-based access errors
                                    console.error('Authentication error:', response.data);
                                    
                                    // Extract the error message from the response data object
                                    let errorMessage = 'Authentication failed. Please try again.';
                                    if (response.data && typeof response.data === 'object' && response.data.message) {
                                        errorMessage = response.data.message;
                                    } else if (typeof response.data === 'string') {
                                        errorMessage = response.data;
                                    }
                                    
                                    showMessage(errorMessage, true);
                                    reject(new Error(errorMessage));
                                }
                            } catch (e) {
                                console.error('Error parsing server response:', e);
                                showMessage('Error processing server response. Please try again.', true);
                                reject(e);
                            }
                        } else {
                            console.error('Server error response:', xhr.status, xhr.statusText);
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
                    
                    xhr.send(data);
                } else {
                    console.error('No user returned from Facebook sign-in');
                    showMessage('No user data returned from Facebook. Please try again.', true);
                    reject(new Error('No user returned from Facebook sign-in'));
                }
            })
            .catch((error) => {
                console.error('Facebook sign-in error:', error);
                showMessage('Facebook sign-in failed: ' + error.message, true);
                reject(error);
            });
    });
}

// Login with username and password
function loginWithUsernamePassword(username, password, accountType) {
    return new Promise((resolve, reject) => {
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
                        // Handle role-based access errors
                        console.error('Authentication error:', response.data);
                        
                        // Extract the error message from the response data object
                        let errorMessage = 'Authentication failed. Please try again.';
                        if (response.data && typeof response.data === 'object' && response.data.message) {
                            errorMessage = response.data.message;
                        } else if (typeof response.data === 'string') {
                            errorMessage = response.data;
                        }
                        
                        showMessage(errorMessage, true);
                        reject(new Error(errorMessage));
                    }
                } catch (e) {
                    console.error('Error parsing server response:', e);
                    showMessage('Error processing server response. Please try again.', true);
                    reject(e);
                }
            } else {
                console.error('Server error response:', xhr.status, xhr.statusText);
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
        data.append('action', 'login_with_username_password');
        data.append('username', username);
        data.append('password', password);
        data.append('account_type', accountType);
        data.append('_ajax_nonce', authData.nonce);
        
        xhr.send(data);
    });
}

// Register user
function registerUser(userData) {
    return new Promise((resolve, reject) => {
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
                        // Handle registration errors
                        console.error('Registration error:', response.data);
                        
                        // Extract the error message from the response data object
                        let errorMessage = 'Registration failed. Please try again.';
                        if (response.data && typeof response.data === 'object' && response.data.message) {
                            errorMessage = response.data.message;
                        } else if (typeof response.data === 'string') {
                            errorMessage = response.data;
                        }
                        
                        showMessage(errorMessage, true);
                        reject(new Error(errorMessage));
                    }
                } catch (e) {
                    console.error('Error parsing server response:', e);
                    showMessage('Error processing server response. Please try again.', true);
                    reject(e);
                }
            } else {
                console.error('Server error response:', xhr.status, xhr.statusText);
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
        data.append('action', 'register_user');
        
        // Add all user data fields
        for (const [key, value] of Object.entries(userData)) {
            data.append(key, value);
        }
        
        data.append('_ajax_nonce', authData.nonce);
        
        xhr.send(data);
    });
}
