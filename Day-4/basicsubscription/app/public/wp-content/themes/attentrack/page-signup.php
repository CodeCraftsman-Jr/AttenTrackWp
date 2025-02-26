<?php
/*
Template Name: Sign Up Page
*/

get_header();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Create Account</h2>
                    
                    <form id="signupForm">
                        <div class="mb-3">
                            <label for="fullName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullName" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" required>
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" required>
                        </div>
                        <button type="button" class="btn btn-primary w-100 mb-3" onclick="signUp()">Sign Up</button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-3">Or sign up with:</p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-danger" onclick="signUpWithGoogle()">
                                <i class="fab fa-google me-2"></i>Google
                            </button>
                            <button class="btn btn-outline-primary" onclick="signUpWithPhone()">
                                <i class="fas fa-phone me-2"></i>Phone Number
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <p class="mb-0">Already have an account? <a href="<?php echo esc_url(home_url('/sign-in')); ?>">Sign in here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Firebase SDK -->
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-auth-compat.js"></script>

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<script>
// Your web app's Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyDxwNXFliKJPC39UOweKnWNvpPipf7-PXc",
    authDomain: "innovproject-274c9.firebaseapp.com",
    projectId: "innovproject-274c9",
    storageBucket: "innovproject-274c9.firebasestorage.app",
    messagingSenderId: "111288496386",
    appId: "1:111288496386:web:38dd0ab7e126ebe93b521b"
};

// Initialize Firebase
let auth;
try {
    firebase.initializeApp(firebaseConfig);
    auth = firebase.auth();
    console.log('Firebase initialized successfully');
} catch (error) {
    console.error('Error initializing Firebase:', error);
}

function showMessage(message, isError = false) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${isError ? 'danger' : 'success'} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const cardBody = document.querySelector('.card-body');
    cardBody.insertBefore(alertDiv, cardBody.firstChild);
}

function signUp() {
    if (!auth) {
        showMessage('Firebase authentication not initialized', true);
        return;
    }

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const fullName = document.getElementById('fullName').value;

    if (password !== confirmPassword) {
        showMessage('Passwords do not match', true);
        return;
    }

    if (password.length < 8) {
        showMessage('Password must be at least 8 characters long', true);
        return;
    }

    auth.createUserWithEmailAndPassword(email, password)
        .then((userCredential) => {
            const user = userCredential.user;
            return user.updateProfile({
                displayName: fullName
            }).then(() => {
                showMessage('Account created successfully! Redirecting...');
                handleSuccessfulSignUp(user);
            });
        })
        .catch((error) => {
            showMessage(error.message, true);
        });
}

function signUpWithGoogle() {
    if (!auth) {
        showMessage('Firebase authentication not initialized', true);
        return;
    }

    console.log('Starting Google sign-up...');
    const provider = new firebase.auth.GoogleAuthProvider();
    
    auth.signInWithPopup(provider)
        .then((result) => {
            console.log('Google sign-up successful:', result.user);
            const user = result.user;
            showMessage('Sign up successful! Redirecting...');
            handleSuccessfulSignUp(user);
        })
        .catch((error) => {
            console.error('Google sign-up error:', error);
            showMessage('Error: ' + error.message, true);
        });
}

function signUpWithPhone() {
    // Redirect to sign-in page with phone authentication
    window.location.href = '<?php echo esc_url(home_url('/sign-in')); ?>?auth=phone';
}

function handleSuccessfulSignUp(user) {
    console.log('Handling successful sign-up for user:', user);
    
    const data = {
        action: 'firebase_auth_handler',
        firebase_id: user.uid,
        email: user.email,
        display_name: user.displayName,
        phone_number: user.phoneNumber
    };

    console.log('Sending data to WordPress:', data);

    fetch(ajax_object.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .then(data => {
        console.log('WordPress response:', data);
        if (data.success) {
            window.location.href = '<?php echo esc_url(home_url('/dashboard')); ?>';
        } else {
            showMessage('Error syncing with WordPress: ' + data.data, true);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error syncing with WordPress', true);
    });
}

// Listen for auth state changes
if (auth) {
    auth.onAuthStateChanged((user) => {
        if (user) {
            console.log('User is signed in:', user);
        } else {
            console.log('No user is signed in.');
        }
    });
}
</script>

<?php get_footer(); ?>
