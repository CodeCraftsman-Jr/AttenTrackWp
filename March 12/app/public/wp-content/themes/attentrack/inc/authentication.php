<?php
// Authentication related functions

// Generate OTP
function generate_otp() {
    return wp_rand(100000, 999999);
}

// Store OTP in user meta
function store_user_otp($user_id, $otp) {
    $expiry = time() + (10 * 60); // OTP valid for 10 minutes
    update_user_meta($user_id, 'login_otp', $otp);
    update_user_meta($user_id, 'login_otp_expiry', $expiry);
}

// Verify OTP
function verify_user_otp($user_id, $otp) {
    $stored_otp = get_user_meta($user_id, 'login_otp', true);
    $expiry = get_user_meta($user_id, 'login_otp_expiry', true);
    
    if ($stored_otp == $otp && time() < $expiry) {
        delete_user_meta($user_id, 'login_otp');
        delete_user_meta($user_id, 'login_otp_expiry');
        return true;
    }
    return false;
}

// AJAX handler for sending OTP
function send_login_otp() {
    if (!isset($_POST['email_or_phone'])) {
        wp_send_json_error('Please provide email or phone number');
        return;
    }

    $email_or_phone = sanitize_text_field($_POST['email_or_phone']);
    
    // Check if input is email or phone
    $is_email = is_email($email_or_phone);
    $is_phone = preg_match('/^[0-9]{10}$/', $email_or_phone);
    
    if (!$is_email && !$is_phone) {
        wp_send_json_error('Please provide a valid email or phone number');
        return;
    }
    
    // Find user by email or phone
    if ($is_email) {
        $user = get_user_by('email', $email_or_phone);
    } else {
        $user = get_users(array(
            'meta_key' => 'phone_number',
            'meta_value' => $email_or_phone,
            'number' => 1
        ));
        $user = !empty($user) ? $user[0] : null;
    }
    
    if (!$user) {
        wp_send_json_error('No account found with this ' . ($is_email ? 'email' : 'phone number'));
        return;
    }
    
    // Generate and store OTP
    $otp = generate_otp();
    store_user_otp($user->ID, $otp);
    
    // Send OTP
    if ($is_email) {
        $subject = 'Your Login OTP';
        $message = 'Your OTP for login is: ' . $otp . '. Valid for 10 minutes.';
        wp_mail($email_or_phone, $subject, $message);
    } else {
        // For demo purposes, we'll just show the OTP
        // In production, you'd want to integrate with an SMS service
        wp_send_json_success(array(
            'message' => 'OTP sent successfully',
            'demo_otp' => $otp // Remove this in production
        ));
    }
    
    wp_send_json_success('OTP sent successfully');
}
add_action('wp_ajax_nopriv_send_login_otp', 'send_login_otp');
add_action('wp_ajax_send_login_otp', 'send_login_otp');

// AJAX handler for verifying OTP
function verify_login_otp() {
    if (!isset($_POST['email_or_phone']) || !isset($_POST['otp'])) {
        wp_send_json_error('Please provide all required information');
        return;
    }

    $email_or_phone = sanitize_text_field($_POST['email_or_phone']);
    $otp = sanitize_text_field($_POST['otp']);
    
    // Find user
    $is_email = is_email($email_or_phone);
    if ($is_email) {
        $user = get_user_by('email', $email_or_phone);
    } else {
        $user = get_users(array(
            'meta_key' => 'phone_number',
            'meta_value' => $email_or_phone,
            'number' => 1
        ));
        $user = !empty($user) ? $user[0] : null;
    }
    
    if (!$user) {
        wp_send_json_error('Invalid user');
        return;
    }
    
    // Verify OTP
    if (verify_user_otp($user->ID, $otp)) {
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        do_action('wp_login', $user->user_login, $user);
        
        wp_send_json_success(array(
            'message' => 'Login successful',
            'redirect_url' => home_url('/dashboard')
        ));
    } else {
        wp_send_json_error('Invalid or expired OTP');
    }
}
add_action('wp_ajax_nopriv_verify_login_otp', 'verify_login_otp');
add_action('wp_ajax_verify_login_otp', 'verify_login_otp');

// AJAX handler for user registration
function register_user() {
    if (!isset($_POST['fullname']) || !isset($_POST['email']) || !isset($_POST['phone']) || !isset($_POST['password'])) {
        wp_send_json_error('Please provide all required information');
        return;
    }

    $fullname = sanitize_text_field($_POST['fullname']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $password = $_POST['password'];

    // Validate email
    if (!is_email($email)) {
        wp_send_json_error('Please provide a valid email address');
        return;
    }

    // Validate phone number
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        wp_send_json_error('Please provide a valid 10-digit phone number');
        return;
    }

    // Check if email already exists
    if (email_exists($email)) {
        wp_send_json_error('Email address already registered');
        return;
    }

    // Check if phone number already exists
    $existing_users = get_users(array(
        'meta_key' => 'phone_number',
        'meta_value' => $phone,
        'number' => 1
    ));

    if (!empty($existing_users)) {
        wp_send_json_error('Phone number already registered');
        return;
    }

    // Create username from email
    $username = sanitize_user(current(explode('@', $email)), true);
    $counter = 1;
    $new_username = $username;
    
    while (username_exists($new_username)) {
        $new_username = $username . $counter;
        $counter++;
    }

    // Create user
    $user_id = wp_create_user($new_username, $password, $email);

    if (is_wp_error($user_id)) {
        wp_send_json_error($user_id->get_error_message());
        return;
    }

    // Update user meta
    wp_update_user(array(
        'ID' => $user_id,
        'display_name' => $fullname
    ));
    
    update_user_meta($user_id, 'phone_number', $phone);

    wp_send_json_success('Registration successful');
}
add_action('wp_ajax_nopriv_register_user', 'register_user');
add_action('wp_ajax_register_user', 'register_user');

// Add menu page for authentication settings
function attentrack_auth_settings_menu() {
    add_menu_page(
        'Authentication Settings',
        'Auth Settings',
        'manage_options',
        'attentrack-auth-settings',
        'attentrack_auth_settings_page',
        'dashicons-lock',
        100
    );
}
add_action('admin_menu', 'attentrack_auth_settings_menu');

// Create the settings page
function attentrack_auth_settings_page() {
    ?>
    <div class="wrap">
        <h2>Authentication Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('attentrack_auth_settings');
            do_settings_sections('attentrack-auth-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
function attentrack_register_auth_settings() {
    register_setting('attentrack_auth_settings', 'attentrack_google_client_id');
    register_setting('attentrack_auth_settings', 'attentrack_google_client_secret');
    
    add_settings_section(
        'attentrack_auth_settings_section',
        'Google Authentication Settings',
        'attentrack_auth_settings_section_callback',
        'attentrack-auth-settings'
    );
    
    add_settings_field(
        'google_client_id',
        'Google Client ID',
        'attentrack_google_client_id_callback',
        'attentrack-auth-settings',
        'attentrack_auth_settings_section'
    );
    
    add_settings_field(
        'google_client_secret',
        'Google Client Secret',
        'attentrack_google_client_secret_callback',
        'attentrack-auth-settings',
        'attentrack_auth_settings_section'
    );
}
add_action('admin_init', 'attentrack_register_auth_settings');

function attentrack_auth_settings_section_callback() {
    echo '<p>Enter your Google OAuth credentials here. You can obtain these from the Google Cloud Console.</p>';
}

function attentrack_google_client_id_callback() {
    $client_id = get_option('attentrack_google_client_id');
    echo '<input type="text" name="attentrack_google_client_id" value="' . esc_attr($client_id) . '" class="regular-text">';
}

function attentrack_google_client_secret_callback() {
    $client_secret = get_option('attentrack_google_client_secret');
    echo '<input type="text" name="attentrack_google_client_secret" value="' . esc_attr($client_secret) . '" class="regular-text">';
}
?>
