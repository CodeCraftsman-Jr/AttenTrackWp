<?php
/**
 * Template Name: Sign In
 */

get_header();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Sign In</h2>
                    
                    <!-- Success and Error Messages -->
                    <div id="successMessage" class="alert alert-success" style="display: none;"></div>
                    <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>

                    <!-- Social Login Buttons -->
                    <div class="mb-4">
                        <button type="button" id="googleSignIn" onclick="signInWithGoogle()" 
                                class="btn w-100 mb-3 d-flex align-items-center justify-content-center" 
                                style="background-color: #fff; border: 1px solid #ddd; color: #444; height: 44px;">
                            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" class="me-2" style="width: 18px;">
                            Continue with Google
                        </button>
                        
                        <button type="button" id="facebookSignIn" onclick="signInWithFacebook()" 
                                class="btn w-100 d-flex align-items-center justify-content-center" 
                                style="background-color: #1877f2; border: none; color: #fff; height: 44px;">
                            <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="#fff">
                                <path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm3 8h-1.35c-.538 0-.65.221-.65.778v1.222h2l-.209 2h-1.791v7h-3v-7h-2v-2h2v-2.308c0-1.769.931-2.692 3.029-2.692h1.971v3z"/>
                            </svg>
                            Continue with Facebook
                        </button>
                    </div>

                    <!-- Divider -->
                    <div class="position-relative my-4">
                        <hr>
                        <span class="position-absolute top-50 start-50 translate-middle px-3 bg-white text-muted" style="font-size: 14px;">
                            or continue with phone
                        </span>
                    </div>

                    <!-- Phone Number Input -->
                    <div id="phoneStep">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">+91</span>
                                <input type="tel" class="form-control" id="phone" 
                                    placeholder="98765 43210" 
                                    pattern="\d{10}" 
                                    minlength="10" 
                                    maxlength="10"
                                    oninput="this.value = this.value.replace(/\D/g, '')"
                                    required>
                            </div>
                            <div class="form-text">Enter your 10-digit phone number without country code</div>
                        </div>
                        
                        <!-- reCAPTCHA container -->
                        <div id="recaptcha-container" class="mb-3 d-flex justify-content-center"></div>
                        
                        <button type="button" id="sendCode" class="btn btn-primary w-100" 
                                onclick="sendVerificationCode()" 
                                style="background-color: #4285f4; border: none;"
                                data-original-text="Send Code"
                                disabled>
                            Send Code
                        </button>
                    </div>

                    <!-- Verification Code Input -->
                    <div id="verification-code-container" style="display: none;">
                        <div class="mb-3">
                            <label for="verification-code" class="form-label">Verification Code</label>
                            <input type="text" class="form-control" id="verification-code" 
                                placeholder="Enter 6-digit code" 
                                pattern="\d{6}" 
                                maxlength="6"
                                oninput="this.value = this.value.replace(/\D/g, '')"
                                required>
                            <div class="form-text">Enter the 6-digit verification code sent to your phone</div>
                        </div>
                        
                        <div class="text-center mb-3">
                            <span id="timer" class="text-muted"></span>
                        </div>
                        
                        <button type="button" id="verifyCode" class="btn btn-primary w-100 mb-3" 
                                onclick="verifyCode()"
                                style="background-color: #4285f4; border: none;"
                                data-original-text="Verify Code">
                            Verify Code
                        </button>
                        
                        <button type="button" id="resendCode" class="btn btn-outline-secondary w-100" 
                                onclick="sendVerificationCode()"
                                data-original-text="Resend Code"
                                disabled>
                            Resend Code
                        </button>
                    </div>

                    <div class="mt-4 text-center">
                        <p class="mb-0">New user? <a href="<?php echo esc_url(home_url('/sign-up')); ?>" style="color: #4285f4; text-decoration: none;">Click here to sign up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.btn-primary {
    text-transform: uppercase;
    font-weight: 500;
    padding: 12px;
}

.input-group-text {
    background-color: #fff;
    border-right: none;
}

.form-control {
    border-left: none;
}

.form-control:focus {
    border-color: #ced4da;
    box-shadow: none;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

button {
    font-family: 'Google Sans', Roboto, Arial, sans-serif;
}

#googleSignIn:hover {
    background-color: #f8f9fa;
}

#facebookSignIn:hover {
    background-color: #166fe5;
}
</style>

<!-- Firebase SDK -->
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-auth-compat.js"></script>

<!-- Custom Scripts -->
<script src="<?php echo get_template_directory_uri(); ?>/js/firebase-config.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/auth.js"></script>

<!-- Initialize phone input validation -->
<script>
document.getElementById('phone').addEventListener('input', function() {
    const phone = this.value;
    const sendCodeBtn = document.getElementById('sendCode');
    
    // Enable button only if phone number is exactly 10 digits
    sendCodeBtn.disabled = !/^\d{10}$/.test(phone);
});
</script>

<?php get_footer(); ?>