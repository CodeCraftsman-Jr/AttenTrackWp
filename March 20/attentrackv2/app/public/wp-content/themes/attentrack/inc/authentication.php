<?php
/**
 * Authentication Functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/user-management.php';

/**
 * AJAX handler for verifying Firebase tokens
 */
function verify_firebase_token() {
    if (!isset($_POST['token']) || !isset($_POST['provider']) || !isset($_POST['email']) || !isset($_POST['name'])) {
        wp_send_json_error('Missing required parameters');
        return;
    }

    $token = sanitize_text_field($_POST['token']);
    $provider = sanitize_text_field($_POST['provider']);
    $email = sanitize_email($_POST['email']);
    $name = sanitize_text_field($_POST['name']);

    // For development, we'll skip token verification since we don't have Firebase Admin SDK set up
    // In production, you would verify the token here
    
    // Find or create WordPress user
    $user = get_user_by('email', $email);
    if (!$user) {
        // Create new user
        $username = sanitize_user(current(explode('@', $email)));
        $random_password = wp_generate_password();
        $user_id = wp_create_user($username, $random_password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error('Failed to create user account');
            return;
        }
        
        $user = get_user_by('ID', $user_id);
        
        // Update user meta
        update_user_meta($user_id, 'first_name', $name);
        update_user_meta($user_id, $provider . '_id', $token);
    }
    
    // Create or update user profile
    $ids = create_or_update_user_profile($user->ID);
    
    // Log the user in
    wp_clear_auth_cookie();
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);
    
    wp_send_json_success(array(
        'message' => 'Login successful',
        'redirect_url' => home_url(),
        'profile_id' => $ids['profile_id'],
        'test_id' => $ids['test_id']
    ));
}
add_action('wp_ajax_nopriv_verify_firebase_token', 'verify_firebase_token');
add_action('wp_ajax_verify_firebase_token', 'verify_firebase_token');

/**
 * AJAX handler for sending OTP
 */
function send_login_otp() {
    if (!isset($_POST['email_or_phone'])) {
        wp_send_json_error('Please provide email or phone number');
        return;
    }

    $email_or_phone = sanitize_text_field($_POST['email_or_phone']);
    
    // Find or create user
    $is_email = is_email($email_or_phone);
    if ($is_email) {
        $user = get_user_by('email', $email_or_phone);
        if (!$user) {
            // Create new user with email
            $username = sanitize_user(current(explode('@', $email_or_phone)));
            $random_password = wp_generate_password();
            $user_id = wp_create_user($username, $random_password, $email_or_phone);
            
            if (is_wp_error($user_id)) {
                wp_send_json_error('Failed to create user account');
                return;
            }
            
            $user = get_user_by('ID', $user_id);
        }
    } else {
        // Clean phone number
        $phone = preg_replace('/[^0-9]/', '', $email_or_phone);
        $user = get_user_by_phone($phone);
        
        if (!$user) {
            // Create new user with phone
            $username = 'user_' . $phone;
            $random_password = wp_generate_password();
            $user_id = wp_create_user($username, $random_password, $phone . '@attentrack.local');
            
            if (is_wp_error($user_id)) {
                wp_send_json_error('Failed to create user account');
                return;
            }
            
            create_or_update_user_profile($user_id, $phone);
            $user = get_user_by('ID', $user_id);
        }
    }
    
    // Generate and store OTP
    $otp = generate_and_store_otp($user->ID);
    
    // TODO: In production, send OTP via SMS/email
    // For development, we'll just return it
    wp_send_json_success(array(
        'message' => 'OTP sent successfully',
        'otp' => $otp // Remove this in production
    ));
}
add_action('wp_ajax_nopriv_send_login_otp', 'send_login_otp');
add_action('wp_ajax_send_login_otp', 'send_login_otp');

/**
 * AJAX handler for verifying OTP
 */
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
        $phone = preg_replace('/[^0-9]/', '', $email_or_phone);
        $user = get_user_by_phone($phone);
    }
    
    if (!$user) {
        wp_send_json_error('Invalid user');
        return;
    }
    
    // Verify OTP
    if (verify_user_otp($user->ID, $otp)) {
        // Get or create profile and test IDs
        $ids = create_or_update_user_profile($user->ID);
        
        // Log the user in
        wp_clear_auth_cookie();
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        
        // Update user meta
        update_user_meta($user->ID, 'profile_id', $ids['profile_id']);
        update_user_meta($user->ID, 'test_id', $ids['test_id']);
        
        wp_send_json_success(array(
            'message' => 'Login successful',
            'redirect_url' => home_url(),
            'profile_id' => $ids['profile_id'],
            'test_id' => $ids['test_id']
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
        wp_send_json_error('Invalid email address');
        return;
    }

    // Check if user exists
    if (email_exists($email)) {
        wp_send_json_error('Email already registered');
        return;
    }

    // Create user
    $user_id = wp_create_user($email, $password, $email);

    if (is_wp_error($user_id)) {
        wp_send_json_error($user_id->get_error_message());
        return;
    }

    // Generate profile and test IDs
    $profile_id = generate_profile_id();
    $test_id = generate_test_id();

    // Update user meta
    wp_update_user(array(
        'ID' => $user_id,
        'display_name' => $fullname,
        'first_name' => explode(' ', $fullname)[0],
        'last_name' => count(explode(' ', $fullname)) > 1 ? explode(' ', $fullname)[1] : '',
    ));
    update_user_meta($user_id, 'phone_number', $phone);
    update_user_meta($user_id, 'profile_id', $profile_id);
    update_user_meta($user_id, 'test_id', $test_id);

    // Log the user in
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    wp_send_json_success(array(
        'message' => 'Registration successful',
        'redirect_url' => home_url(),
        'profile_id' => $profile_id,
        'test_id' => $test_id
    ));
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

/**
 * AJAX handler for user logout
 */
function user_logout() {
    check_ajax_referer('auth-nonce');
    wp_logout();
    wp_send_json_success();
}
add_action('wp_ajax_user_logout', 'user_logout');

/**
 * AJAX handler for refreshing session
 */
function refresh_session() {
    check_ajax_referer('auth-nonce');
    if (is_user_logged_in()) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Not logged in');
    }
}
add_action('wp_ajax_refresh_session', 'refresh_session');

?>
