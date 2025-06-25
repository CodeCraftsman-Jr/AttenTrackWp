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
    $account_type = isset($_POST['account_type']) ? sanitize_text_field($_POST['account_type']) : 'user';

    // For development, we'll skip token verification since we don't have Firebase Admin SDK set up
    // In production, you would verify the token here
    
    // Validate email
    if (empty($email)) {
        wp_send_json_error('Email is required for authentication');
        return;
    }

    // Find or create WordPress user
    $user = get_user_by('email', $email);
    if (!$user) {
        // Generate a unique username based on email
        $base_username = sanitize_user(current(explode('@', $email)));
        $username = $base_username;
        $counter = 1;
        
        // Ensure username is unique
        while (username_exists($username)) {
            $username = $base_username . $counter;
            $counter++;
        }
        
        // Create new user
        $random_password = wp_generate_password(12, true, true);
        $user_id = wp_create_user($username, $random_password, $email);
        
        if (is_wp_error($user_id)) {
            error_log('Failed to create user: ' . $user_id->get_error_message());
            wp_send_json_error('Failed to create user account: ' . $user_id->get_error_message());
            return;
        }
        
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            error_log('Failed to retrieve newly created user');
            wp_send_json_error('Failed to retrieve user account after creation');
            return;
        }
        
        // Update user meta
        update_user_meta($user_id, 'first_name', $name);
        update_user_meta($user_id, $provider . '_id', $token);
        update_user_meta($user_id, 'account_type', $account_type);
    } else {
        // Update existing user's provider ID
        update_user_meta($user->ID, $provider . '_id', $token);
    }
    
    // Set user role based on account type
    $user_obj = new WP_User($user->ID);
    if ($account_type === 'institution') {
        $user_obj->set_role('institution');
    } else {
        // Only change role if they're not already an administrator
        if (!in_array('administrator', $user_obj->roles)) {
            $user_obj->set_role('subscriber');
        }
    }
    
    // Create or update user profile
    $ids = create_or_update_user_profile($user->ID);
    
    // Log the user in
    wp_clear_auth_cookie();
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);
    
    // Determine redirect URL based on role
    if (current_user_can('administrator')) {
        $redirect_url = admin_url();
    } else if ($account_type === 'institution' || in_array('institution', (array) $user_obj->roles)) {
        $redirect_url = home_url('/dashboard?type=institution');
    } else {
        $redirect_url = home_url('/dashboard?type=patient');
    }
    
    wp_send_json_success(array(
        'message' => 'Login successful',
        'redirect_url' => $redirect_url,
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
    $account_type = isset($_POST['account_type']) ? sanitize_text_field($_POST['account_type']) : 'user';
    
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
        
        // Set user role based on account type
        $user_obj = new WP_User($user->ID);
        if ($account_type === 'institution') {
            $user_obj->set_role('institution');
        } else {
            // Only change role if they're not already an administrator
            if (!in_array('administrator', $user_obj->roles)) {
                $user_obj->set_role('subscriber');
            }
        }
        
        // Log the user in
        wp_clear_auth_cookie();
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        
        // Update user meta
        update_user_meta($user->ID, 'profile_id', $ids['profile_id']);
        update_user_meta($user->ID, 'test_id', $ids['test_id']);
        update_user_meta($user->ID, 'account_type', $account_type);
        
        wp_send_json_success(array(
            'message' => 'Login successful',
            'redirect_url' => home_url('/dashboard-router'),
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
 * Custom login handler
 */
function attentrack_custom_login() {
    if (!isset($_POST['log']) || !isset($_POST['pwd'])) {
        return;
    }
    
    $username = sanitize_user($_POST['log']);
    $password = $_POST['pwd'];
    $remember = isset($_POST['rememberme']) ? true : false;
    
    // Try to log in the user
    $user = wp_signon(array(
        'user_login' => $username,
        'user_password' => $password,
        'remember' => $remember
    ));
    
    if (is_wp_error($user)) {
        // Login failed
        return;
    }
    
    // Login successful, redirect based on user role
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, $remember);
    
    // Check if user is administrator - send directly to admin panel
    if (current_user_can('administrator')) {
        wp_redirect(admin_url());
        exit;
    }
    // Check user role and redirect accordingly
    else if (in_array('institution', (array) $user->roles) || user_can($user->ID, 'institution')) {
        // Institution user - redirect to institution dashboard
        wp_redirect(home_url('/dashboard?type=institution'));
        exit;
    } else {
        // Regular user - redirect to patient dashboard
        wp_redirect(home_url('/dashboard?type=patient'));
        exit;
    }
}
add_action('login_form_login', 'attentrack_custom_login');

// Handle login form on our custom login page
function attentrack_process_login_form() {
    if (isset($_POST['attentrack_login_nonce']) && wp_verify_nonce($_POST['attentrack_login_nonce'], 'attentrack_login')) {
        $username = sanitize_user($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;
        
        $user = wp_signon(array(
            'user_login' => $username,
            'user_password' => $password,
            'remember' => $remember
        ));
        
        if (is_wp_error($user)) {
            // Store error message in session
            if (!session_id()) {
                session_start();
            }
            $_SESSION['login_error'] = $user->get_error_message();
            wp_redirect(home_url('/signin'));
            exit;
        }
        
        // Login successful
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember);
        
        // Check if user is administrator - send directly to admin panel
        if (current_user_can('administrator')) {
            wp_redirect(admin_url());
            exit;
        }
        // Check user role and redirect accordingly
        else if (in_array('institution', (array) $user->roles) || user_can($user->ID, 'institution')) {
            // Institution user - redirect to institution dashboard
            wp_redirect(home_url('/dashboard?type=institution'));
            exit;
        } else {
            // Regular user - redirect to patient dashboard
            wp_redirect(home_url('/dashboard?type=patient'));
            exit;
        }
    }
}
add_action('template_redirect', 'attentrack_process_login_form');

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

/**
 * AJAX handler for username/password login
 */
function attentrack_login() {
    // Verify nonce
    check_ajax_referer('auth-nonce', 'security');
    
    // Get login credentials
    $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) && $_POST['remember'] === '1';
    $account_type = isset($_POST['account_type']) ? sanitize_text_field($_POST['account_type']) : 'user';
    
    // Attempt to authenticate user
    $user = wp_authenticate($username, $password);
    
    if (is_wp_error($user)) {
        // Authentication failed
        wp_send_json_error(array(
            'message' => $user->get_error_message()
        ));
        return;
    }
    
    // Authentication successful
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, $remember);
    
    // Check user role and redirect accordingly
    $redirect_url = home_url('/dashboard');
    
    if (current_user_can('administrator')) {
        $redirect_url = admin_url();
    } else if (in_array('institution', (array) $user->roles) || user_can($user->ID, 'institution')) {
        $redirect_url = home_url('/dashboard?type=institution');
    } else {
        $redirect_url = home_url('/dashboard?type=patient');
    }
    
    wp_send_json_success(array(
        'message' => 'Login successful',
        'redirect_url' => $redirect_url
    ));
}
add_action('wp_ajax_nopriv_attentrack_login', 'attentrack_login');

/**
 * AJAX handler for username/password registration
 */
function attentrack_register() {
    // Verify nonce
    check_ajax_referer('auth-nonce', '_ajax_nonce');
    
    // Get registration data
    $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $account_type = isset($_POST['account_type']) ? sanitize_text_field($_POST['account_type']) : 'user';
    
    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        wp_send_json_error(array(
            'message' => 'Please fill in all required fields.'
        ));
        return;
    }
    
    // Check if username already exists
    if (username_exists($username)) {
        wp_send_json_error(array(
            'message' => 'This username is already taken. Please choose another one.'
        ));
        return;
    }
    
    // Check if email already exists
    if (email_exists($email)) {
        wp_send_json_error(array(
            'message' => 'This email is already registered. Please use another email or sign in.'
        ));
        return;
    }
    
    // Create user
    $user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($user_id)) {
        wp_send_json_error(array(
            'message' => $user_id->get_error_message()
        ));
        return;
    }
    
    // Set user role based on account type
    $user = new WP_User($user_id);
    
    if ($account_type === 'institution') {
        $user->set_role('institution');
    } else {
        $user->set_role('subscriber'); // Default role for patients/regular users
    }
    
    // Log the user in
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);
    
    // Determine redirect URL based on user role
    $redirect_url = home_url('/dashboard');
    
    if ($account_type === 'institution') {
        $redirect_url = home_url('/dashboard?type=institution');
    } else {
        $redirect_url = home_url('/dashboard?type=patient');
    }
    
    wp_send_json_success(array(
        'message' => 'Registration successful',
        'redirect_url' => $redirect_url
    ));
}
add_action('wp_ajax_nopriv_attentrack_register', 'attentrack_register');

?>
