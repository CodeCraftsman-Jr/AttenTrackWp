// Initialize Firebase and Auth Providers
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Check if Firebase is already initialized using our flag
        if (window.firebaseInitialized) {
            console.log('Firebase already initialized, reusing existing instance');
            return;
        }

        // Initialize Firebase
        if (!window.firebaseConfig) {
            throw new Error('Firebase configuration not found');
        }
        
        // Initialize Firebase if not already initialized
        if (!firebase.apps.length) {
            firebase.initializeApp(window.firebaseConfig);
        }
        
        window.auth = firebase.auth();
        
        // Initialize Providers
        window.googleProvider = new firebase.auth.GoogleAuthProvider();
        window.googleProvider.addScope('email');
        window.googleProvider.addScope('profile');
        
        window.facebookProvider = new firebase.auth.FacebookAuthProvider();
        window.facebookProvider.addScope('email');
        window.facebookProvider.addScope('public_profile');

        // Set persistence to LOCAL
        window.auth.setPersistence(firebase.auth.Auth.Persistence.LOCAL)
            .catch((error) => {
                console.error("Persistence error:", error);
            });
            
        // Handle redirect result for social login
        if (window.location.pathname.includes('/signin') || window.location.pathname.includes('/signup')) {
            // Check for redirect result
            firebase.auth().getRedirectResult()
                .then((result) => {
                    if (result.user) {
                        console.log('Successfully signed in after redirect');
                        
                        // Get account type from session storage
                        const accountType = sessionStorage.getItem('attentrack_account_type') || 'user';
                        
                        // Show loading message
                        const alertContainer = document.getElementById('alert-container');
                        if (alertContainer) {
                            alertContainer.innerHTML = '<div class="alert alert-info">Successfully signed in! Verifying your account...</div>';
                        }
                        
                        // Send token to server
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', window.authData?.ajaxUrl || '<?php echo admin_url("admin-ajax.php"); ?>', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        
                        xhr.onload = function() {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.success) {
                                        // Show success message
                                        if (alertContainer) {
                                            alertContainer.innerHTML = '<div class="alert alert-success">Login successful! Redirecting...</div>';
                                        }
                                        // Redirect to dashboard
                                        window.location.href = response.data.redirect_url || '/dashboard';
                                    } else {
                                        // Show error message
                                        if (alertContainer) {
                                            alertContainer.innerHTML = '<div class="alert alert-danger">' + (response.data || 'Error processing your request') + '</div>';
                                        }
                                    }
                                } catch (e) {
                                    console.error('Error parsing response:', e);
                                    if (alertContainer) {
                                        alertContainer.innerHTML = '<div class="alert alert-danger">Error processing server response</div>';
                                    }
                                }
                            } else {
                                console.error('Server error:', xhr.status, xhr.statusText);
                                if (alertContainer) {
                                    alertContainer.innerHTML = '<div class="alert alert-danger">Server error. Please try again.</div>';
                                }
                            }
                        };
                        
                        xhr.onerror = function() {
                            console.error('Network error');
                            if (alertContainer) {
                                alertContainer.innerHTML = '<div class="alert alert-danger">Network error. Please try again.</div>';
                            }
                        };
                        
                        // Debug the user data
                        console.log('User data:', {
                            uid: result.user.uid,
                            email: result.user.email,
                            displayName: result.user.displayName,
                            providerId: result.credential?.providerId || 'unknown'
                        });
                        
                        const data = new URLSearchParams();
                        data.append('action', 'verify_firebase_token');
                        data.append('token', result.user.uid);
                        
                        // Handle provider ID safely
                        let provider = 'unknown';
                        if (result.credential && result.credential.providerId) {
                            provider = result.credential.providerId
                                .replace('facebook.com', 'facebook')
                                .replace('google.com', 'google');
                        } else if (result.user.providerData && result.user.providerData.length > 0) {
                            // Fallback to user provider data
                            provider = result.user.providerData[0].providerId
                                .replace('facebook.com', 'facebook')
                                .replace('google.com', 'google');
                        }
                        data.append('provider', provider);
                        
                        // Handle email safely
                        data.append('email', result.user.email || '');
                        
                        // Handle display name safely
                        data.append('name', result.user.displayName || result.user.email?.split('@')[0] || 'User');
                        data.append('account_type', accountType);
                        data.append('_ajax_nonce', window.authData?.nonce || '');
                        
                        xhr.send(data);
                    }
                })
                .catch((error) => {
                    // Don't show error for normal page loads (when there's no redirect)
                    if (error.code !== 'auth/null-credential') {
                        console.error('Error handling redirect result:', error);
                        const alertContainer = document.getElementById('alert-container');
                        if (alertContainer) {
                            alertContainer.innerHTML = '<div class="alert alert-danger">Error signing in: ' + error.message + '</div>';
                        }
                    }
                });
        }

        // Add auth state change listener - with protection for signin and signup pages
        window.auth.onAuthStateChanged((user) => {
            const currentPath = window.location.pathname;
            
            // Don't redirect on signin or signup pages to prevent infinite loops
            if (currentPath.includes('/signin') || currentPath.includes('/signup')) {
                return;
            }
            
            if (user) {
                // User is signed in
                console.log('User is signed in');
                // No redirects needed here - we'll let the normal flow handle this
            } else {
                // User is signed out - protect all pages except public ones
                if (!currentPath.includes('/index.php') && 
                    !currentPath.includes('/wp-admin') &&
                    !currentPath.includes('/wp-login.php') &&
                    !currentPath.includes('/admin-direct.php')) {
                    
                    console.log('User is not signed in, redirecting to signin page');
                    // Redirect to signin
                    const signinLink = document.querySelector('a[href*="signin"]')?.href || '/signin';
                    window.location.href = signinLink;
                }
            }
        });

        // Mark Firebase as initialized
        window.firebaseInitialized = true;
        console.log('Firebase initialized successfully');
    } catch (error) {
        console.error('Firebase initialization error:', error);
        if (document.getElementById('errorMessage')) {
            document.getElementById('errorMessage').textContent = "Error initializing authentication. Please try again later.";
            document.getElementById('errorMessage').style.display = 'block';
        }
    }
});
