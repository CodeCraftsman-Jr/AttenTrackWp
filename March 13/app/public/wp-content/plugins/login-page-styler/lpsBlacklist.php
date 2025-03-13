<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Create table for IP blocking
function lps_create_ip_blocking_table() {
	global $wpdb;
	$table_name      = $wpdb->prefix . 'lps_ip_blocking';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        ip_address varchar(100) DEFAULT NULL,
        region varchar(100) DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

// Function to get region info using ip-api
function get_user_region_by_ip( $ip_address ) {
	$url = "http://ip-api.com/json/{$ip_address}?fields=countryCode";

	$response = wp_remote_get( $url );
	if ( is_wp_error( $response ) ) {
		return null; // Handle errors
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body );

	if ( isset( $data->countryCode ) ) {
		return $data->countryCode; // Returns the country code (e.g., 'US' for the USA).
	}
	return null;
}

// Check if the current user's IP or region is blocked.
function lps_block_login_page() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'lps_ip_blocking';

	// Determine the user's IP address.
	// Check if REMOTE_ADDR exists.
	if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$user_ip = wp_unslash( $_SERVER['REMOTE_ADDR'] );
	}

	// Check if HTTP_X_FORWARDED_FOR exists.
	if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		// Unsplash before sanitizing.
		$forwarded_ips = wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		$user_ip       = explode( ',', $forwarded_ips )[0]; // Take the first IP.
	}

	// Check if HTTP_CLIENT_IP exists.
	if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		// Unsplash before sanitizing.
		$client_ip = wp_unslash( $_SERVER['HTTP_CLIENT_IP'] );
		$user_ip   = sanitize_text_field( $client_ip ); // Sanitize the IP.
	}

	// Sanitize the final IP address.
	$user_ip = sanitize_text_field( $user_ip );

	// Check if IP is blocked.
	$blocked_ip = $wpdb->get_var( $wpdb->prepare( "SELECT ip_address FROM $table_name WHERE ip_address = %s", $user_ip ) );

	// Get the user's region using the IP address.
	$user_region = get_user_region_by_ip( $user_ip );

	// Check if the user's region is blocked.
	$blocked_region = $wpdb->get_var( $wpdb->prepare( "SELECT region FROM $table_name WHERE region = %s", $user_region ) );

	if ( $blocked_ip ) {
		wp_die( esc_html__( 'Your IP access to the login page has been restricted.', 'login-page-styler' ) );
	}
	if ( $blocked_region ) {
		wp_die( esc_html__( 'Your Country access to the login page has been restricted.', 'login-page-styler' ) );
	}
}
add_action( 'login_init', 'lps_block_login_page' );
