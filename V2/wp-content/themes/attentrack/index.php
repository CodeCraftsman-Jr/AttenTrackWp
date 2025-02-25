<?php get_header(); ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Welcome to AttenTrack</h2>
                    
                    <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php
                        $error = $_GET['error'];
                        switch ($error) {
                            case 'google':
                                echo 'Google login failed. Please try again.';
                                break;
                            case 'facebook':
                                echo 'Facebook login failed. Please try again.';
                                break;
                            case 'phone':
                                echo 'Phone verification failed. Please try again.';
                                break;
                            default:
                                echo 'Login failed. Please try again.';
                        }
                        ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Social Login Buttons -->
                    <div class="d-grid gap-3 mb-4">
                        <button class="btn btn-danger btn-lg d-flex align-items-center justify-content-center gap-2" onclick="loginWithGoogle()">
                            <i class="fab fa-google"></i>
                            Continue with Google
                        </button>
                        
                        <button class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2" onclick="loginWithFacebook()">
                            <i class="fab fa-facebook-f"></i>
                            Continue with Facebook
                        </button>
                        
                        <button class="btn btn-success btn-lg d-flex align-items-center justify-content-center gap-2" onclick="showPhoneLogin()">
                            <i class="fas fa-phone"></i>
                            Continue with Phone
                        </button>
                    </div>
                    
                    <!-- Phone Login Modal -->
                    <div class="modal fade" id="phoneLoginModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Phone Login</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="phone-login-step-1">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control form-control-lg" id="phone" placeholder="+91 1234567890">
                                        </div>
                                        <button class="btn btn-primary w-100" onclick="sendOTP()">Send OTP</button>
                                    </div>
                                    <div id="phone-login-step-2" style="display: none;">
                                        <div class="mb-3">
                                            <label for="otp" class="form-label">Enter OTP</label>
                                            <input type="text" class="form-control form-control-lg" id="otp" placeholder="Enter OTP">
                                        </div>
                                        <button class="btn btn-primary w-100" onclick="verifyOTP()">Verify OTP</button>
                                    </div>
                                    <div id="recaptcha-container" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Firebase -->
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-auth-compat.js"></script>

<script>
// Initialize Firebase
const firebaseConfig = {
    apiKey: '<?php echo FIREBASE_API_KEY; ?>',
    authDomain: '<?php echo FIREBASE_AUTH_DOMAIN; ?>',
    projectId: '<?php echo FIREBASE_PROJECT_ID; ?>',
    storageBucket: '<?php echo FIREBASE_STORAGE_BUCKET; ?>',
    messagingSenderId: '<?php echo FIREBASE_MESSAGING_SENDER_ID; ?>',
    appId: '<?php echo FIREBASE_APP_ID; ?>'
};

firebase.initializeApp(firebaseConfig);

// Google Login
function loginWithGoogle() {
    window.location.href = '<?php echo get_google_client()->createAuthUrl(); ?>';
}

// Facebook Login
function loginWithFacebook() {
    window.location.href = '<?php echo get_facebook_helper()->getLoginUrl(FACEBOOK_REDIRECT_URI, ['email']); ?>';
}

// Phone Login
let phoneNumber, confirmationResult;

function showPhoneLogin() {
    const modal = new bootstrap.Modal(document.getElementById('phoneLoginModal'));
    modal.show();
    
    // Initialize reCAPTCHA
    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
        'size': 'normal',
        'callback': (response) => {
            // Enable the Send OTP button
            document.querySelector('#phone-login-step-1 button').disabled = false;
        }
    });
    window.recaptchaVerifier.render();
}

function sendOTP() {
    phoneNumber = document.getElementById('phone').value;
    firebase.auth().signInWithPhoneNumber(phoneNumber, window.recaptchaVerifier)
        .then((result) => {
            confirmationResult = result;
            document.getElementById('phone-login-step-1').style.display = 'none';
            document.getElementById('phone-login-step-2').style.display = 'block';
        })
        .catch((error) => {
            alert('Error sending OTP: ' + error.message);
        });
}

function verifyOTP() {
    const code = document.getElementById('otp').value;
    confirmationResult.confirm(code)
        .then((result) => {
            const user = result.user;
            // Send verification to WordPress
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'verify_phone',
                    phone: phoneNumber,
                    uid: user.uid
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.redirect;
                    } else {
                        alert('Login failed: ' + response.data);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        })
        .catch((error) => {
            alert('Invalid OTP. Please try again.');
        });
}
</script>

<?php get_footer(); ?>
