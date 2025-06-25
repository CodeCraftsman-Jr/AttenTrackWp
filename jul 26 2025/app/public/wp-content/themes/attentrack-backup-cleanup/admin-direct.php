<?php
/**
 * Admin Direct Access
 * 
 * This page allows direct access to the WordPress admin dashboard
 * bypassing any custom login redirection.
 */

// Ensure WordPress is loaded
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

// Check if user is logged in
if (!is_user_logged_in()) {
    // Display login form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Access - AttenTrack</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                background: #f0f0f1;
                color: #3c434a;
                margin: 0;
                padding: 0;
            }
            .login-container {
                max-width: 320px;
                margin: 60px auto;
                padding: 26px 24px 46px;
                background: #fff;
                box-shadow: 0 1px 3px rgba(0,0,0,.13);
            }
            h1 {
                text-align: center;
                margin-bottom: 20px;
            }
            .form-group {
                margin-bottom: 16px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: 500;
            }
            input[type="text"],
            input[type="password"] {
                width: 100%;
                padding: 8px;
                font-size: 14px;
                border: 1px solid #ddd;
                box-sizing: border-box;
            }
            .button {
                background: #2271b1;
                border: none;
                color: #fff;
                padding: 8px 12px;
                font-size: 14px;
                cursor: pointer;
                width: 100%;
            }
            .button:hover {
                background: #135e96;
            }
            .error {
                background: #f8d7da;
                color: #842029;
                padding: 10px;
                margin-bottom: 16px;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>Admin Access</h1>
            
            <?php if (isset($_POST['username']) && isset($_POST['password'])): ?>
                <?php
                // Process login
                $creds = array(
                    'user_login'    => sanitize_user($_POST['username']),
                    'user_password' => $_POST['password'],
                    'remember'      => true
                );
                
                $user = wp_signon($creds, false);
                
                if (is_wp_error($user)) {
                    echo '<div class="error">' . $user->get_error_message() . '</div>';
                } else {
                    // Force redirect to admin regardless of role
                    wp_set_current_user($user->ID);
                    wp_set_auth_cookie($user->ID);
                    
                    echo '<script>window.location.href = "' . admin_url() . '";</script>';
                    echo '<div style="text-align: center; margin-top: 20px;">Login successful! Redirecting to admin dashboard...</div>';
                    echo '<div style="text-align: center; margin-top: 10px;"><a href="' . admin_url() . '">Click here if not redirected automatically</a></div>';
                    exit;
                }
                ?>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="button">Access Admin</button>
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
} else {
    // User is already logged in, redirect directly to admin
    echo '<script>window.location.href = "' . admin_url() . '";</script>';
    echo '<div style="text-align: center; margin-top: 100px;">Redirecting to admin dashboard...</div>';
    echo '<div style="text-align: center; margin-top: 10px;"><a href="' . admin_url() . '">Click here if not redirected automatically</a></div>';
    exit;
}
?>
