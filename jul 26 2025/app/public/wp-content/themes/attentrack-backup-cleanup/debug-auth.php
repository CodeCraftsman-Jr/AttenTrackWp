<?php
/**
 * Debug Authentication
 * 
 * This file helps debug the authentication process
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load WordPress
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

// Only allow administrators to access this page
if (!current_user_can('administrator')) {
    wp_die('You do not have permission to access this page.');
}

// Get the consolidated user data table name
global $wpdb;
$user_data_table = $wpdb->prefix . 'attentrack_user_data';

// Check if the table exists
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$user_data_table'");

// Get the verify_firebase_token function code
$verify_firebase_token_file = get_template_directory() . '/inc/consolidated-authentication.php';
$verify_firebase_token_code = file_exists($verify_firebase_token_file) ? file_get_contents($verify_firebase_token_file) : 'File not found';

// Get the role access check code
$role_access_check_file = get_template_directory() . '/inc/role-access-check.php';
$role_access_check_code = file_exists($role_access_check_file) ? file_get_contents($role_access_check_file) : 'File not found';

// Get auth.js code
$auth_js_file = get_template_directory() . '/js/auth.js';
$auth_js_code = file_exists($auth_js_file) ? file_get_contents($auth_js_file) : 'File not found';

// Get firebase-config.js code
$firebase_config_js_file = get_template_directory() . '/js/firebase-config.js';
$firebase_config_js_code = file_exists($firebase_config_js_file) ? file_get_contents($firebase_config_js_file) : 'File not found';

// Get the AJAX URL
$ajax_url = admin_url('admin-ajax.php');

// Get the nonce for verify_firebase_token
$nonce = wp_create_nonce('verify_firebase_token_nonce');

// Create a test script to debug the authentication flow
$test_script = "
// Debug authentication flow
console.log('Starting authentication debug');

// Test AJAX request to verify_firebase_token
function testAjaxRequest() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '$ajax_url', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        console.log('Server response received:', xhr.status, xhr.responseText);
        document.getElementById('ajax-response').textContent = xhr.responseText;
    };
    xhr.onerror = function() {
        console.error('AJAX error:', xhr.status, xhr.statusText);
        document.getElementById('ajax-response').textContent = 'Error: ' + xhr.statusText;
    };
    
    const data = new URLSearchParams();
    data.append('action', 'verify_firebase_token');
    data.append('token', document.getElementById('test-token').value);
    data.append('provider', document.getElementById('test-provider').value);
    data.append('email', document.getElementById('test-email').value);
    data.append('name', document.getElementById('test-name').value);
    data.append('account_type', document.getElementById('test-account-type').value);
    data.append('_ajax_nonce', '$nonce');
    
    console.log('Sending AJAX request with data:', Object.fromEntries(data));
    xhr.send(data);
}
";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Debug Authentication</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1, h2, h3 {
            color: #333;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        pre {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow: auto;
            max-height: 300px;
        }
        .code-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .code-header button {
            padding: 5px 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 15px;
            background-color: #0073aa;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #005177;
        }
        .response {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Debug Authentication</h1>
        
        <div class="section">
            <h2>Database Status</h2>
            <p>User Data Table: <?php echo $table_exists ? '<span style="color: green;">Exists</span>' : '<span style="color: red;">Does Not Exist</span>'; ?></p>
            
            <?php if ($table_exists) : ?>
                <h3>Sample User Data</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>Email</th>
                            <th>Account Type</th>
                            <th>Google ID</th>
                            <th>Facebook ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $users = $wpdb->get_results("SELECT id, user_id, email, account_type, google_id, facebook_id FROM $user_data_table LIMIT 5");
                        if ($users) {
                            foreach ($users as $user) {
                                echo "<tr>";
                                echo "<td>" . esc_html($user->id) . "</td>";
                                echo "<td>" . esc_html($user->user_id) . "</td>";
                                echo "<td>" . esc_html($user->email) . "</td>";
                                echo "<td>" . esc_html($user->account_type) . "</td>";
                                echo "<td>" . esc_html($user->google_id) . "</td>";
                                echo "<td>" . esc_html($user->facebook_id) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No users found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Test Authentication AJAX Request</h2>
            <p>This form allows you to test the verify_firebase_token AJAX endpoint directly.</p>
            
            <div class="form-group">
                <label for="test-token">Token (UID):</label>
                <input type="text" id="test-token" value="test_uid_123456">
            </div>
            
            <div class="form-group">
                <label for="test-provider">Provider:</label>
                <select id="test-provider">
                    <option value="google">Google</option>
                    <option value="facebook">Facebook</option>
                    <option value="phone">Phone</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="test-email">Email:</label>
                <input type="email" id="test-email" value="test@example.com">
            </div>
            
            <div class="form-group">
                <label for="test-name">Name:</label>
                <input type="text" id="test-name" value="Test User">
            </div>
            
            <div class="form-group">
                <label for="test-account-type">Account Type:</label>
                <select id="test-account-type">
                    <option value="user">User</option>
                    <option value="institution">Institution</option>
                </select>
            </div>
            
            <button onclick="testAjaxRequest()">Test AJAX Request</button>
            
            <div class="response">
                <h3>Response:</h3>
                <pre id="ajax-response">No response yet</pre>
            </div>
        </div>
        
        <div class="section">
            <div class="code-header">
                <h2>verify_firebase_token Function</h2>
                <button onclick="document.getElementById('verify-firebase-token-code').style.display = document.getElementById('verify-firebase-token-code').style.display === 'none' ? 'block' : 'none'">Toggle Code</button>
            </div>
            <pre id="verify-firebase-token-code" style="display: none;"><?php echo htmlspecialchars($verify_firebase_token_code); ?></pre>
        </div>
        
        <div class="section">
            <div class="code-header">
                <h2>Role Access Check Functions</h2>
                <button onclick="document.getElementById('role-access-check-code').style.display = document.getElementById('role-access-check-code').style.display === 'none' ? 'block' : 'none'">Toggle Code</button>
            </div>
            <pre id="role-access-check-code" style="display: none;"><?php echo htmlspecialchars($role_access_check_code); ?></pre>
        </div>
        
        <div class="section">
            <div class="code-header">
                <h2>auth.js</h2>
                <button onclick="document.getElementById('auth-js-code').style.display = document.getElementById('auth-js-code').style.display === 'none' ? 'block' : 'none'">Toggle Code</button>
            </div>
            <pre id="auth-js-code" style="display: none;"><?php echo htmlspecialchars($auth_js_code); ?></pre>
        </div>
        
        <div class="section">
            <div class="code-header">
                <h2>firebase-config.js</h2>
                <button onclick="document.getElementById('firebase-config-js-code').style.display = document.getElementById('firebase-config-js-code').style.display === 'none' ? 'block' : 'none'">Toggle Code</button>
            </div>
            <pre id="firebase-config-js-code" style="display: none;"><?php echo htmlspecialchars($firebase_config_js_code); ?></pre>
        </div>
    </div>
    
    <script>
        <?php echo $test_script; ?>
    </script>
</body>
</html>
