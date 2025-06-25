<?php
/**
 * Script to update existing users to have institution role
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Update users to have institution role
 * This function will be called on theme activation
 */
function update_existing_users_to_institution() {
    // Get users with institution_type meta set to 'institution'
    $institution_users = get_users(array(
        'meta_key' => 'account_type',
        'meta_value' => 'institution',
        'fields' => 'ID'
    ));

    // Update each user's role
    foreach ($institution_users as $user_id) {
        $user = new WP_User($user_id);
        
        // Skip administrators
        if (in_array('administrator', $user->roles)) {
            continue;
        }
        
        // Set role to institution
        $user->set_role('institution');
    }
}

/**
 * Add institution role if it doesn't exist
 */
function ensure_institution_role_exists() {
    // Check if the role already exists
    if (!get_role('institution')) {
        // Add the institution role with capabilities
        add_role(
            'institution',
            'Institution',
            array(
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'publish_posts' => false,
                'upload_files' => true,
                'manage_institution_users' => true,
            )
        );
    }
}

/**
 * Add a button in admin to manually update users
 */
function add_update_institution_users_button() {
    if (!current_user_can('administrator')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>Update Institution Users</h1>
        <p>This will update all users with account_type = 'institution' to have the institution role.</p>
        <form method="post" action="">
            <?php wp_nonce_field('update_institution_users_nonce', 'update_institution_users_nonce'); ?>
            <input type="hidden" name="update_institution_users" value="1">
            <p>
                <input type="submit" class="button button-primary" value="Update Institution Users">
            </p>
        </form>
    </div>
    <?php
}

/**
 * Handle the form submission
 */
function handle_update_institution_users() {
    if (
        isset($_POST['update_institution_users']) &&
        isset($_POST['update_institution_users_nonce']) &&
        wp_verify_nonce($_POST['update_institution_users_nonce'], 'update_institution_users_nonce')
    ) {
        // Ensure role exists
        ensure_institution_role_exists();
        
        // Update users
        update_existing_users_to_institution();
        
        // Add admin notice
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>Institution users have been updated successfully.</p>
            </div>
            <?php
        });
    }
}

// Add menu page for updating institution users - REMOVED
// function add_update_institution_users_page() {
//     add_management_page(
//         'Update Institution Users',
//         'Update Institution Users',
//         'manage_options',
//         'update-institution-users',
//         'add_update_institution_users_button'
//     );
// }

// Run on theme activation
add_action('after_switch_theme', 'ensure_institution_role_exists');
add_action('after_switch_theme', 'update_existing_users_to_institution');

// Add admin page - REMOVED
// add_action('admin_menu', 'add_update_institution_users_page');
add_action('admin_init', 'handle_update_institution_users');
