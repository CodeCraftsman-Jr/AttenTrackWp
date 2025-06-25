/**
 * Firebase Email Authentication
 * This file handles email verification using Firebase Authentication
 */

// Initialize Firebase Email Auth
function initFirebaseEmailAuth() {
    // Check if Firebase is initialized
    if (typeof firebase === 'undefined') {
        console.error('Firebase is not initialized. Please refresh the page and try again.');
        return;
    }
    
    console.log('Firebase Email Auth initialized');
    
    // Check if the user is opening the app from an email link
    if (window.location.href.includes('mode=signIn')) {
        console.log('Email sign-in link detected, processing authentication...');
        checkEmailVerification();
    }
}

// Send verification email
function sendEmailVerification(email) {
    return new Promise((resolve, reject) => {
        console.log('Sending verification email to:', email);
        
        // Configure the email action code settings
        const actionCodeSettings = {
            // URL you want to redirect back to after email verification
            url: window.location.origin + window.location.pathname,
            handleCodeInApp: true
        };
        
        console.log('Action code settings:', actionCodeSettings);
        
        // Send sign-in link to the user's email
        firebase.auth().sendSignInLinkToEmail(email, actionCodeSettings)
            .then(() => {
                console.log('Verification email sent successfully');
                
                // Save the email locally to remember the user when they open the link
                window.localStorage.setItem('emailForSignIn', email);
                
                // Save the account type if available
                const accountType = getAccountType();
                if (accountType) {
                    window.localStorage.setItem('accountTypeForSignIn', accountType);
                }
                
                resolve({ 
                    success: true, 
                    message: 'Verification email sent successfully',
                    data: { email: email }
                });
            })
            .catch((error) => {
                console.error('Error sending verification email:', error);
                reject(error);
            });
    });
}

// Check if the user is opening the app from an email link
function checkEmailVerification() {
    console.log('Checking for email verification link...');
    
    // Check if the URL contains the sign-in link
    if (firebase.auth().isSignInWithEmailLink(window.location.href)) {
        console.log('Email sign-in link detected');
        
        // Get the email from localStorage (saved when sending the link)
        let email = window.localStorage.getItem('emailForSignIn');
        
        if (!email) {
            // If the email isn't in localStorage, prompt the user for it
            email = window.prompt('Please provide your email for confirmation');
        }
        
        if (!email) {
            console.error('No email provided for authentication');
            alert('Authentication failed: Email is required to complete sign-in');
            return;
        }
        
        console.log('Attempting to sign in with email link for:', email);
        
        // Show loading message
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'alert alert-info';
        loadingDiv.innerHTML = '<div class="d-flex align-items-center"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Authenticating, please wait...</div>';
        document.body.insertBefore(loadingDiv, document.body.firstChild);
        
        // Sign in the user with the email link
        firebase.auth().signInWithEmailLink(email, window.location.href)
            .then((result) => {
                console.log('Successfully signed in with email link');
                
                // Clear email from storage
                window.localStorage.removeItem('emailForSignIn');
                
                // Get the user
                const user = result.user;
                
                // Get ID token with a true refresh
                return user.getIdToken(true).then(idToken => {
                    return { user, idToken };
                });
            })
            .then(({ user, idToken }) => {
                console.log('Got ID token, sending to server');
                
                // Send the user data to the server
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    // Use WordPress AJAX URL if available, or construct it
                    const ajaxUrl = (typeof ajaxurl !== 'undefined') ? ajaxurl : 
                                  (typeof authData !== 'undefined' && authData.ajaxUrl) ? authData.ajaxUrl : 
                                  window.location.origin + '/wp-admin/admin-ajax.php';
                    
                    console.log('Using AJAX URL:', ajaxUrl);
                    xhr.open('POST', ajaxUrl, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    
                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                resolve(response);
                            } catch (e) {
                                console.error('Error parsing response:', e);
                                reject(new Error('Unable to parse server response'));
                            }
                        } else {
                            console.error('Server error:', xhr.status, xhr.responseText);
                            reject(new Error('Server error: ' + xhr.status));
                        }
                    };
                    
                    xhr.onerror = function() {
                        console.error('Network error');
                        reject(new Error('Network error'));
                    };
                    
                    // Prepare the data to send
                    const data = new URLSearchParams();
                    data.append('action', 'verify_firebase_token');
                    data.append('token', idToken); // Use ID token for auth
                    data.append('email', user.email);
                    data.append('account_type', getAccountType() || 'user');
                    
                    // Send the request
                    xhr.send(data.toString());
                    
                    console.log('Sending authentication data to server:', {
                        action: 'verify_firebase_token',
                        email: user.email,
                        account_type: getAccountType() || 'user'
                    });
                });
            })
            .then((response) => {
                console.log('Server response:', response);
                
                if (response.success) {
                    // Show success message
                    loadingDiv.className = 'alert alert-success';
                    loadingDiv.innerHTML = '<div class="d-flex align-items-center"><i class="fas fa-check-circle me-2"></i>Authentication successful! Redirecting...</div>';
                    
                    // Redirect to the dashboard
                    setTimeout(() => {
                        window.location.href = response.data.redirect || '/dashboard';
                    }, 1000);
                } else {
                    // Show error message
                    loadingDiv.className = 'alert alert-danger';
                    loadingDiv.innerHTML = '<div class="d-flex align-items-center"><i class="fas fa-exclamation-circle me-2"></i>Authentication failed: ' + (response.data || 'Unknown error') + '</div>';
                }
            })
            .catch((error) => {
                console.error('Error in authentication process:', error);
                
                // Show error message
                if (loadingDiv) {
                    loadingDiv.className = 'alert alert-danger';
                    loadingDiv.innerHTML = '<div class="d-flex align-items-center"><i class="fas fa-exclamation-circle me-2"></i>Authentication error: ' + error.message + '</div>';
                } else {
                    alert('Authentication error: ' + error.message);
                }
            });
    } else {
        console.log('No email sign-in link detected in URL');
    }
}

// Helper function to get the account type
function getAccountType() {
    // Try to get account type from the form
    const accountTypeElement = document.getElementById('account-type');
    if (accountTypeElement) {
        return accountTypeElement.value || 'user';
    }
    
    // If not found in form, check localStorage (for email link auth)
    const storedAccountType = window.localStorage.getItem('accountTypeForSignIn');
    if (storedAccountType) {
        return storedAccountType;
    }
    
    // If not found in localStorage, check URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('account_type')) {
        return urlParams.get('account_type');
    }
    
    // Default to 'user'
    return 'user';
}

// Check for email verification when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Wait for Firebase to be initialized
    function waitForFirebase() {
        if (typeof firebase !== 'undefined' && window.firebaseInitialized) {
            // Check if the user is opening the app from an email link
            checkEmailVerification();
        } else {
            // Wait a bit more for Firebase to initialize
            setTimeout(waitForFirebase, 100);
        }
    }

    waitForFirebase();
});
