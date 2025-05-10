<?php
/**
 * Template Name: Dashboard Router
 * 
 * This template redirects users to the appropriate dashboard based on their role.
 */

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_safe_redirect(home_url('/signin'));
    exit;
}

// Get current user and role
$current_user = wp_get_current_user();
$user_roles = $current_user->roles;
$user_role = !empty($user_roles) ? $user_roles[0] : 'subscriber';

// Debug information
error_log('User ID: ' . $current_user->ID);
error_log('User roles: ' . print_r($user_roles, true));
error_log('Account type: ' . get_user_meta($current_user->ID, 'account_type', true));

// Check user role and redirect accordingly
if (current_user_can('institution')) {
    // Institution user - redirect to institution dashboard
    wp_redirect(add_query_arg('type', 'institution', home_url('/dashboard')));
    exit;
} else {
    // Regular user - redirect to patient dashboard
    wp_redirect(add_query_arg('type', 'patient', home_url('/dashboard')));
    exit;
}
