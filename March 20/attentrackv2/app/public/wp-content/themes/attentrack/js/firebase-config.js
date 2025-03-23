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

        // Add auth state change listener
        window.auth.onAuthStateChanged((user) => {
            const currentPath = window.location.pathname;
            
            if (user) {
                // User is signed in
                if (currentPath.includes('/signin') || currentPath.includes('/signup')) {
                    // Redirect to selection page as per auth flow memory
                    const selectionPage = document.querySelector('a[href*="selection-page"]')?.href || '/selection-page';
                    window.location.href = selectionPage;
                }
            } else {
                // User is signed out - protect all pages except public ones
                if (!currentPath.includes('/signin') && 
                    !currentPath.includes('/signup') && 
                    !currentPath.includes('/index.php') && 
                    !currentPath.includes('/wp-admin') &&
                    !currentPath.includes('/wp-login.php')) {
                    // Redirect to signin as per auth flow memory
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
