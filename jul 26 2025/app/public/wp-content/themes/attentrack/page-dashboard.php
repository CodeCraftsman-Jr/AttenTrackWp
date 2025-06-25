<?php
/**
 * Template Name: Dashboard
 * Multi-Tier Dashboard Router
 */

get_header();

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_safe_redirect(home_url('/signin'));
    exit;
}

// Get current user and determine their dashboard type
$current_user = wp_get_current_user();
$user_roles = $current_user->roles;

// Determine user's primary role and appropriate dashboard
$dashboard_type = '';
if (in_array('administrator', $user_roles)) {
    $dashboard_type = 'admin';
} elseif (in_array('institution_admin', $user_roles)) {
    $dashboard_type = 'institution';
} elseif (in_array('staff', $user_roles)) {
    $dashboard_type = 'staff';
} elseif (in_array('client', $user_roles)) {
    $dashboard_type = 'client';
} elseif (in_array('institution', $user_roles)) {
    // Legacy support for old institution role
    $dashboard_type = 'institution';
} elseif (in_array('patient', $user_roles)) {
    // Legacy support for old patient role
    $dashboard_type = 'client';
} else {
    // Default to client for unknown roles
    $dashboard_type = 'client';
}

// Allow override from URL parameter if user has permission
$requested_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
if ($requested_type) {
    // Validate user can access requested dashboard type
    $can_access = false;
    switch ($requested_type) {
        case 'admin':
            $can_access = in_array('administrator', $user_roles);
            break;
        case 'institution':
            $can_access = in_array('administrator', $user_roles) ||
                         in_array('institution_admin', $user_roles) ||
                         in_array('institution', $user_roles); // Legacy support
            break;
        case 'staff':
            $can_access = in_array('administrator', $user_roles) ||
                         in_array('institution_admin', $user_roles) ||
                         in_array('staff', $user_roles);
            break;
        case 'client':
            $can_access = in_array('administrator', $user_roles) ||
                         in_array('institution_admin', $user_roles) ||
                         in_array('client', $user_roles) ||
                         in_array('patient', $user_roles); // Legacy support
            break;
    }

    if ($can_access) {
        $dashboard_type = $requested_type;
    } else {
        // Redirect to their appropriate dashboard with error
        wp_safe_redirect(add_query_arg(array(
            'type' => $dashboard_type,
            'error' => 'access_denied'
        ), home_url('/dashboard')));
        exit;
    }
}

// Show error message if present
if (isset($_GET['error'])) {
    $error_message = '';
    switch ($_GET['error']) {
        case 'access_denied':
            $error_message = 'You do not have permission to access that dashboard.';
            break;
        case 'subscription_expired':
            $error_message = 'Your subscription has expired. Please renew to continue.';
            break;
        default:
            $error_message = 'An error occurred. Please try again.';
    }

    echo '<div class="container mt-4"><div class="alert alert-warning">' . esc_html($error_message) . '</div></div>';
}

// Log dashboard access
if (function_exists('attentrack_log_audit_action')) {
    attentrack_log_audit_action($current_user->ID, 'dashboard_access', 'dashboard', null,
        function_exists('attentrack_get_user_institution_id') ? attentrack_get_user_institution_id($current_user->ID) : null,
        array('dashboard_type' => $dashboard_type));
}

// Load appropriate dashboard template
switch ($dashboard_type) {
    case 'admin':
        // Redirect to WordPress admin for administrators
        wp_safe_redirect(admin_url());
        exit;

    case 'institution':
        // Check if user has institution
        $institution_id = function_exists('attentrack_get_user_institution_id') ?
                         attentrack_get_user_institution_id($current_user->ID) : null;

        if (!$institution_id) {
            echo '<div class="container mt-4">';
            echo '<div class="alert alert-danger">';
            echo '<h4>Institution Not Found</h4>';
            echo '<p>You are not associated with any institution. Please contact support.</p>';
            echo '</div>';
            echo '</div>';
            get_footer();
            exit;
        }

        // Check subscription status if function exists
        if (class_exists('AttenTrack_Subscription_Manager')) {
            $subscription_manager = AttenTrack_Subscription_Manager::getInstance();
            $subscription = $subscription_manager->get_institution_subscription($institution_id);

            if (!$subscription || $subscription->status !== 'active') {
                // Show subscription expired template if available
                $template = locate_template('subscription-expired-template.php');
                if ($template) {
                    include $template;
                    get_footer();
                    exit;
                }
            }
        }

        $template = locate_template('institution-dashboard-template.php');
        if ($template) {
            include $template;
        } else {
            echo '<div class="container mt-4">';
            echo '<div class="alert alert-danger">Institution dashboard template not found</div>';
            echo '</div>';
        }
        break;

    case 'staff':
        // Check if staff member has any assigned clients
        if (function_exists('attentrack_get_staff_assigned_clients')) {
            $assigned_clients = attentrack_get_staff_assigned_clients($current_user->ID);

            // Set global variable for template
            global $attentrack_assigned_clients;
            $attentrack_assigned_clients = $assigned_clients;
        }

        $template = locate_template('staff-dashboard-template.php');
        if ($template) {
            include $template;
        } else {
            echo '<div class="container mt-4">';
            echo '<div class="alert alert-danger">Staff dashboard template not found</div>';
            echo '</div>';
        }
        break;

    case 'client':
        $template = locate_template('client-dashboard-template.php');
        if (!$template) {
            // Try legacy template name
            $template = locate_template('patient-dashboard-template.php');
        }

        if ($template) {
            include $template;
        } else {
            echo '<div class="container mt-4">';
            echo '<div class="alert alert-danger">Client dashboard template not found</div>';
            echo '</div>';
        }
        break;

    default:
        echo '<div class="container mt-4">';
        echo '<div class="alert alert-danger">Invalid dashboard type: ' . esc_html($dashboard_type) . '</div>';
        echo '</div>';
}

get_footer();
?>
