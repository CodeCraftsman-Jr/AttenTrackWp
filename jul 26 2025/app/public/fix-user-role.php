<?php
/**
 * Fix User Role Script
 * 
 * This standalone script updates the role and account type for a specific user
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load WordPress
require_once('wp-load.php');

// Only allow administrators to access this page
if (!current_user_can('administrator')) {
    wp_die('You do not have permission to access this page.');
}

// Process form submission
if (isset($_POST['update_user_role']) && isset($_POST['user_id']) && isset($_POST['new_role']) && isset($_POST['new_account_type'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = sanitize_text_field($_POST['new_role']);
    $new_account_type = sanitize_text_field($_POST['new_account_type']);
    
    // Include the role access check functions
    require_once( dirname( __FILE__ ) . '/wp-content/themes/attentrack/inc/role-access-check.php' );

    // Update user role using the comprehensive function
    $result = update_user_role_and_account_type($user_id, $new_role, $new_account_type);

    if ($result) {
        $success_message = "Successfully updated user ID $user_id to $new_role role and $new_account_type account type.";
    } else {
        $error_message = "Failed to update user ID $user_id. Please check the error logs.";
    }
}

// Get all users
$users = get_users();

// Get specific user info for blackloverz333
$blackloverz = get_user_by('login', 'blackloverz333');
if ($blackloverz) {
    $blackloverz_id = $blackloverz->ID;
    $blackloverz_roles = $blackloverz->roles;
    $blackloverz_account_type = get_user_meta($blackloverz_id, 'account_type', true);
    
    global $wpdb;
    $blackloverz_consolidated = $wpdb->get_row($wpdb->prepare(
        "SELECT account_type FROM {$wpdb->prefix}attentrack_user_data WHERE user_id = %d",
        $blackloverz_id
    ));
    
    if ($blackloverz_consolidated) {
        $blackloverz_consolidated_type = $blackloverz_consolidated->account_type;
    } else {
        $blackloverz_consolidated_type = 'Not found in consolidated table';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fix User Role</title>
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
        h1, h2 {
            color: #333;
        }
        .notice {
            padding: 10px 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .notice-success {
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            color: #3c763d;
        }
        .notice-error {
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            color: #a94442;
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
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .form-table {
            width: 100%;
            border: none;
        }
        .form-table th, .form-table td {
            border: none;
            padding: 15px 10px;
        }
        select, input[type="submit"] {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        input[type="submit"] {
            background-color: #0073aa;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #005177;
        }
        .highlight {
            background-color: #ffffd0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Fix User Role and Account Type</h1>
        
        <?php if (isset($success_message)) : ?>
            <div class="notice notice-success">
                <p><?php echo $success_message; ?></p>
            </div>
        <?php endif; ?>
        
        <h2>blackloverz333 Current Status</h2>
        <?php if (isset($blackloverz_id)) : ?>
            <table>
                <tr>
                    <th>User ID</th>
                    <td><?php echo $blackloverz_id; ?></td>
                </tr>
                <tr>
                    <th>Roles</th>
                    <td><?php echo implode(', ', $blackloverz_roles); ?></td>
                </tr>
                <tr>
                    <th>Account Type (Meta)</th>
                    <td><?php echo $blackloverz_account_type; ?></td>
                </tr>
                <tr>
                    <th>Account Type (Consolidated)</th>
                    <td><?php echo $blackloverz_consolidated_type; ?></td>
                </tr>
            </table>
            
            <form method="post" action="">
                <input type="hidden" name="user_id" value="<?php echo $blackloverz_id; ?>">
                <input type="hidden" name="new_role" value="subscriber">
                <input type="hidden" name="new_account_type" value="user">
                <p>
                    <input type="submit" name="update_user_role" value="Fix blackloverz333 (Change to Regular User)">
                </p>
            </form>
        <?php else : ?>
            <p>User blackloverz333 not found.</p>
        <?php endif; ?>
        
        <h2>Update Any User</h2>
        
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="user_id">Select User</label></th>
                    <td>
                        <select name="user_id" id="user_id">
                            <?php foreach ($users as $user) : ?>
                                <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->user_login); ?> (ID: <?php echo esc_html($user->ID); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="new_role">New Role</label></th>
                    <td>
                        <select name="new_role" id="new_role">
                            <option value="client">Client (Test Taker)</option>
                            <option value="staff">Staff (Institution Employee)</option>
                        <option value="institution_admin">Institution Admin (Institution Owner)</option>
                        <option value="subscriber">Subscriber (Regular User)</option>
                            <option value="administrator">Administrator</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="new_account_type">New Account Type</label></th>
                    <td>
                        <select name="new_account_type" id="new_account_type">
                            <option value="user">User</option>
                            <option value="institution">Institution</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p>
                <input type="submit" name="update_user_role" value="Update User Role and Account Type">
            </p>
        </form>
        
        <h2>All Users</h2>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Account Type (Meta)</th>
                    <th>Account Type (Consolidated)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                global $wpdb;
                foreach ($users as $user) : 
                    $account_type_meta = get_user_meta($user->ID, 'account_type', true);
                    
                    $consolidated_account_type = $wpdb->get_var($wpdb->prepare(
                        "SELECT account_type FROM {$wpdb->prefix}attentrack_user_data WHERE user_id = %d",
                        $user->ID
                    ));
                    
                    $highlight = ($user->user_login === 'blackloverz333') ? 'class="highlight"' : '';
                ?>
                    <tr <?php echo $highlight; ?>>
                        <td><?php echo esc_html($user->ID); ?></td>
                        <td><?php echo esc_html($user->user_login); ?></td>
                        <td><?php echo esc_html($user->user_email); ?></td>
                        <td><?php echo esc_html(implode(', ', $user->roles)); ?></td>
                        <td><?php echo esc_html($account_type_meta); ?></td>
                        <td><?php echo esc_html($consolidated_account_type); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
