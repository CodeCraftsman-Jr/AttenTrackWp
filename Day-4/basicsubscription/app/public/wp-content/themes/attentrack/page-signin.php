<?php
/*
Template Name: Sign In Page
*/

get_header();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Sign In</h2>
                    
                    <div id="loginStep1">
                        <div class="mb-3">
                            <label for="email_or_phone" class="form-label">Email or Phone Number</label>
                            <input type="text" class="form-control" id="email_or_phone" required>
                            <div class="form-text">Enter your email address or phone number</div>
                        </div>
                        <button type="button" class="btn btn-primary w-100" onclick="sendOTP()">Send OTP</button>
                    </div>

                    <div id="loginStep2" style="display: none;">
                        <div class="mb-3">
                            <label for="otp" class="form-label">Enter OTP</label>
                            <input type="text" class="form-control" id="otp" required>
                            <div class="form-text">Enter the OTP sent to your email/phone</div>
                        </div>
                        <button type="button" class="btn btn-primary w-100 mb-3" onclick="verifyOTP()">Verify OTP</button>
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="resendOTP()">Resend OTP</button>
                    </div>

                    <div class="mt-4 text-center">
                        <p class="mb-0">New user? <a href="<?php echo esc_url(home_url('/sign-up')); ?>">Click here to sign up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

function sendOTP() {
    const emailOrPhone = document.getElementById('email_or_phone').value;
    if (!emailOrPhone) {
        showMessage('Please enter email or phone number', true);
        return;
    }

    const data = {
        'action': 'send_login_otp',
        'email_or_phone': emailOrPhone
    };

    fetch(ajax_object.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('loginStep1').style.display = 'none';
            document.getElementById('loginStep2').style.display = 'block';
            showMessage('OTP sent successfully');
            
            // For demo purposes only - remove in production
            if (data.demo_otp) {
                showMessage('Demo OTP: ' + data.demo_otp);
            }
        } else {
            showMessage(data.data, true);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while sending OTP', true);
    });
}

function verifyOTP() {
    const emailOrPhone = document.getElementById('email_or_phone').value;
    const otp = document.getElementById('otp').value;

    if (!otp) {
        showMessage('Please enter OTP', true);
        return;
    }

    const data = {
        'action': 'verify_login_otp',
        'email_or_phone': emailOrPhone,
        'otp': otp
    };

    fetch(ajax_object.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Login successful! Redirecting...');
            setTimeout(() => {
                window.location.href = data.data.redirect_url;
            }, 1500);
        } else {
            showMessage(data.data, true);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while verifying OTP', true);
    });
}

function resendOTP() {
    sendOTP();
}

function signInWithGoogle() {
    console.log('Starting Google sign-in...');
    const provider = new firebase.auth.GoogleAuthProvider();
    
    firebase.auth()
        .signInWithPopup(provider)
        .then((result) => {
            console.log('Google sign-in successful:', result.user);
            const user = result.user;
            showMessage('Sign in successful! Redirecting...');
            handleSuccessfulSignIn(user);
        })
        .catch((error) => {
            console.error('Google sign-in error:', error);
            showMessage('Error: ' + error.message, true);
        });
}

function handleSuccessfulSignIn(user) {
    console.log('Handling successful sign-in for user:', user);
    
    // Send the Firebase user data to WordPress
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
            window.location.href = '<?php echo esc_url(home_url('/home2')); ?>';
        } else {
            showMessage('Error syncing with WordPress: ' + data.data, true);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error syncing with WordPress', true);
    });
}
</script>

<?php get_footer(); ?>
