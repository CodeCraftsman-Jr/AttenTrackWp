// Firebase Configuration
const firebaseConfig = {
    apiKey: "AIzaSyDxwNXFliKJPC39UOweKnWNvpPipf7-PXc",
    authDomain: "innovproject-274c9.firebaseapp.com",
    projectId: "innovproject-274c9",
    storageBucket: "innovproject-274c9.firebasestorage.app",
    messagingSenderId: "111288496386",
    appId: "1:111288496386:web:38dd0ab7e126ebe93b521b"
};

// Initialize Firebase and Auth Providers
let auth;
let googleProvider;
let facebookProvider;

try {
    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);
    auth = firebase.auth();
    
    // Initialize Providers
    googleProvider = new firebase.auth.GoogleAuthProvider();
    googleProvider.addScope('email');
    googleProvider.addScope('profile');
    
    facebookProvider = new firebase.auth.FacebookAuthProvider();
    facebookProvider.addScope('email');
    facebookProvider.addScope('public_profile');
    
    console.log('Firebase and providers initialized successfully');
} catch (error) {
    console.error('Error initializing Firebase:', error);
}

// Export for use in other files
window.auth = auth;
window.googleProvider = googleProvider;
window.facebookProvider = facebookProvider;

// Configure auth persistence to LOCAL
// This ensures the user stays logged in across browser sessions
auth.setPersistence(firebase.auth.Auth.Persistence.LOCAL)
    .catch((error) => {
        console.error('Error setting persistence:', error);
    });
