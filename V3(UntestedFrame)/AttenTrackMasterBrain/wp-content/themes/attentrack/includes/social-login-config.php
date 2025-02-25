<?php
if (!defined('ABSPATH')) exit;

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', home_url('/google-callback'));

// Facebook OAuth Configuration
define('FACEBOOK_APP_ID', 'YOUR_FACEBOOK_APP_ID');
define('FACEBOOK_APP_SECRET', 'YOUR_FACEBOOK_APP_SECRET');
define('FACEBOOK_REDIRECT_URI', home_url('/facebook-callback'));

// Firebase Configuration for Phone Auth
define('FIREBASE_API_KEY', 'YOUR_FIREBASE_API_KEY');
define('FIREBASE_AUTH_DOMAIN', 'your-app.firebaseapp.com');
define('FIREBASE_PROJECT_ID', 'your-project-id');
define('FIREBASE_STORAGE_BUCKET', 'your-app.appspot.com');
define('FIREBASE_MESSAGING_SENDER_ID', 'your-sender-id');
define('FIREBASE_APP_ID', 'your-app-id');

// Load required libraries
require_once get_template_directory() . '/vendor/autoload.php';

// Initialize Google Client
function get_google_client() {
    $client = new Google_Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);
    $client->addScope('email');
    $client->addScope('profile');
    return $client;
}

// Initialize Facebook SDK
function get_facebook_helper() {
    $fb = new Facebook\Facebook([
        'app_id' => FACEBOOK_APP_ID,
        'app_secret' => FACEBOOK_APP_SECRET,
        'default_graph_version' => 'v12.0',
    ]);
    return $fb->getRedirectLoginHelper();
}

// Handle social login user creation/login
function handle_social_login($email, $name, $provider_id, $provider) {
    $user = get_user_by('email', $email);
    
    if (!$user) {
        // Create new user
        $username = sanitize_user(strtolower(str_replace(' ', '', $name)));
        $count = 1;
        $original_username = $username;
        
        while (username_exists($username)) {
            $username = $original_username . $count;
            $count++;
        }
        
        $random_password = wp_generate_password(12, false);
        $user_id = wp_create_user($username, $random_password, $email);
        
        if (!is_wp_error($user_id)) {
            wp_update_user([
                'ID' => $user_id,
                'display_name' => $name,
                'first_name' => explode(' ', $name)[0],
                'last_name' => count(explode(' ', $name)) > 1 ? end(explode(' ', $name)) : ''
            ]);
            
            update_user_meta($user_id, $provider . '_id', $provider_id);
            $user = get_user_by('id', $user_id);
        }
    }
    
    if ($user) {
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        do_action('wp_login', $user->user_login, $user);
        return true;
    }
    
    return false;
}

// Handle phone number verification
function handle_phone_login($phone_number, $uid) {
    $user = get_users([
        'meta_key' => 'phone_number',
        'meta_value' => $phone_number,
        'number' => 1,
        'count_total' => false
    ]);
    
    if (empty($user)) {
        // Create new user
        $username = 'user_' . substr($phone_number, -4);
        $count = 1;
        $original_username = $username;
        
        while (username_exists($username)) {
            $username = $original_username . $count;
            $count++;
        }
        
        $random_password = wp_generate_password(12, false);
        $user_id = wp_create_user($username, $random_password, $phone_number . '@phone.user');
        
        if (!is_wp_error($user_id)) {
            update_user_meta($user_id, 'phone_number', $phone_number);
            update_user_meta($user_id, 'firebase_uid', $uid);
            $user = get_user_by('id', $user_id);
        }
    } else {
        $user = $user[0];
    }
    
    if ($user) {
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        do_action('wp_login', $user->user_login, $user);
        return true;
    }
    
    return false;
}
