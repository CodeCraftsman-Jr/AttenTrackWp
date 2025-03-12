<?php

// Security check to prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Function to get IP address
function lps_getip() {
	// Define a list of trusted proxy headers (if applicable).
	$trusted_headers = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR' );

	foreach ( $trusted_headers as $header ) {
		if ( isset( $_SERVER[ $header ] ) ) {
			$ip_list = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) );
			$ip      = trim( end( $ip_list ) ); // Get the last IP in the list (most likely client IP).

			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return sanitize_text_field( $ip ); // Sanitize the IP address.
			}
		}
	}

	// If no trusted headers are found or if the IPs are invalid, use the remote address.
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : null;

	if ( $ip === false || $ip === '::1' || $ip === null ) {
		$ip = '127.0.0.1';
	}

	return sanitize_text_field( $ip ); // Sanitize the IP address.
}

// Function to count failed login attempts
function lps_counts( $username = '' ) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lps_login_fails';

	$ip = lps_getip();

	// Set a time limit (e.g., 30 minutes) for counting failed attempts.
	$time_limit = get_option( 'lps_attempts_within' ); // Adjust this as needed.

	$num_failsquery = "SELECT COUNT(lpslogin_attempt_ID) FROM $table_name " .
		"WHERE lpslogin_attempt_IP LIKE '%s' AND " .
		'lpslogin_attempt_date + INTERVAL ' . $time_limit . ' MINUTE > now()';
	$num_failsquery = $wpdb->prepare( $num_failsquery, $ip . '%' );

	$num_fails = $wpdb->get_var( $num_failsquery );

	return $num_fails;
}

// Function to increment failed login attempts
function lps_increment( $username = '' ) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lps_login_fails';

	$ip = lps_getip();

	$username = sanitize_user( $username );
	$user     = get_user_by( 'login', $username );

	if ( $user === false ) {
		$user_id = -1;
	} else {
		$user_id = $user->ID;
	}

	$insert  = 'INSERT INTO ' . $table_name . ' (lpsuser_id, lpslogin_attempt_date, lpslogin_attempt_IP) ' .
		"VALUES ('" . $user_id . "', now(), '%s')";
	$insert  = $wpdb->prepare( $insert, $ip );
	$results = $wpdb->query( $insert );
}

// Function to lock down the user
function lps_lockDown( $username = '' ) {
	global $wpdb;

	$block_time = get_option( 'lps_lock_time' );

	$table_name = $wpdb->prefix . 'lps_lockdowns';

	$ip = lps_getip();

	$username = sanitize_user( $username );

	$user = get_user_by( 'login', $username );

	if ( $user === false ) {
		$user_id = -1;
	} else {
		$user_id = $user->ID;
	}

	// Check if the IP is already locked down.
	$is_locked = lps_isLockedDown();
	if ( $is_locked ) {
		return; // IP is already locked down, no need to re-lock.
	}

	$insert  = 'INSERT INTO ' . $table_name . ' (lpsuser_id, lpslockdown_date, lpsrelease_date, lpslockdown_IP) ' .
		"VALUES ('" . $user_id . "', now(), date_add(now(), INTERVAL " .
		$block_time . " MINUTE), '%s')";
	$insert  = $wpdb->prepare( $insert, $ip );
	$results = $wpdb->query( $insert );
}

// Function to check if the user is still locked down
function lps_isLockedDown() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lps_lockdowns';

	$ip = lps_getip();

	$still_lockedquery = "SELECT lpsuser_id FROM $table_name " .
		'WHERE lpsrelease_date > now() AND ' .
		'lpslockdown_IP LIKE  %s';
	$still_lockedquery = $wpdb->prepare( $still_lockedquery, $ip . '%' );

	$still_locked = $wpdb->get_var( $still_lockedquery );

	return $still_locked;
}

// Function to list locked down users
function lps_listLockedDown() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lps_lockdowns';

	$list_locked = $wpdb->get_results(
		'SELECT lpslockdown_ID, floor((UNIX_TIMESTAMP(lpsrelease_date)-UNIX_TIMESTAMP(now()))/60) AS minutes_left, ' .
		"lpslockdown_IP FROM $table_name WHERE lpsrelease_date > now()",
		ARRAY_A
	);

	return $list_locked;
}

// Enable login attempts limiting if option is set.
if ( get_option( 'lps_enable_lim' ) == 1 ) {
	// remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);.
	add_filter( 'authenticate', 'lps_wp_authenticate_username_password', 20, 3 );
}

// Custom authentication function.
function lps_wp_authenticate_username_password( $user, $username, $password ) {
	global $wpdb;

	// Your custom authentication logic
	// ...
	if ( is_a( $user, 'WP_User' ) ) {
		return $user;
	}

	if ( empty( $username ) || empty( $password ) ) {
		$error = new WP_Error();

		if ( empty( $username ) ) {
			$error->add( 'empty_username', __( '<strong>ERROR</strong>: The username field is empty.' ) );
		}

		if ( empty( $password ) ) {
			$error->add( 'empty_password', __( '<strong>ERROR</strong>: The password field is empty.' ) );
		}

		return $error;
	}
	$userdata       = get_user_by( 'login', $username );
	$attemptstotal  = get_option( 'lps_login_attempts' ) - 1;
	$attemptsremain = $attemptstotal - lps_counts( $username );

	if ( ! $userdata ) {
		$error_message = sprintf(
			/* translators: 1: Error message, 2: Number of remaining attempts, 3: Lost password URL */
			__( 'ERROR: Invalid username. "%1$s" Attempts Remaining <a href="%2$s" title="Password Lost and Found">Lost your password</a>?' ),
			$attemptsremain,
			site_url( 'wp-login.php?action=lostpassword', 'login' )
		);

		return new WP_Error( 'invalid_username', $error_message );
	}

	// Apply the 'wp_authenticate_user' filter.
	$userdata = apply_filters( 'wp_authenticate_user', $userdata, $password );
	if ( is_wp_error( $userdata ) ) {
		return $userdata;
	}

	if ( ! wp_check_password( $password, $userdata->user_pass, $userdata->ID ) ) {
		/* translators: 1: Error message, 2: Number of remaining attempts, 3: Lost password URL */
		return new WP_Error(
			'incorrect_password',
			sprintf(
				__( '<strong>ERROR</strong>: Incorrect password. "%1$s" Attempts Remaining. <a href="%2$s" title="Password Lost and Found">Lost your password</a>?' ),
				$attemptsremain,
				// Replaced %1$s .
				site_url( 'wp-login.php?action=lostpassword', 'login' ) // Replaced %2$s.
			)
		);
	}
	// Call the lps_wp_authenticate function for additional authentication
	// return lps_wp_authenticate($username, $password);.
}

if ( get_option( 'lps_enable_lim' ) == 1 ) {
	add_filter( 'authenticate', 'lps_wp_authenticate', 30, 3 );
}
function lps_wp_authenticate( $user, $username, $password ) {
	// Simulate the logic from wp_authenticate().
	global $wpdb, $error;
	$username   = sanitize_user( $username );
	$password   = trim( $password );
	$retryafter = get_option( 'lps_lock_time' );

	if ( '' != lps_isLockedDown() ) {
		return new WP_Error(
			'incorrect_password',
			"<strong>ERROR</strong>: We're sorry, but this IP has been blocked due to too many recent " .
			"failed login attempts.<br /><br />Please try again later after <b>'" . $retryafter . "' minutes</b>."
		);
	}
	// $user = apply_filters('authenticate', null, $username, $password);.
	$user = apply_filters( 'wp_authenticate_user', $user, $username, $password );
	if ( $user === null ) {
		$user = new WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>: Invalid username or incorrect password.' ) );
	}

	// ...

	$ignore_codes = array( 'empty_username', 'empty_password' );

	if ( is_wp_error( $user ) && ! in_array( $user->get_error_code(), $ignore_codes ) ) {
		lps_increment( $username );
		if ( get_option( 'lps_login_attempts' ) <= lps_counts( $username ) ) {
			lps_lockDown( $username );
			return new WP_Error(
				'incorrect_password',
				sprintf(
					/* translators: 1: Error message, 2: Retry after duration */
					__( '%1$s: We\'re sorry, but this IP has been blocked due to too many recent failed login attempts. Please try again later after %2$s minutes.' ),
					__( 'ERROR' ),
					$retryafter
				)
			);
		}
	} else {
		do_action( 'wp_login_failed', $username );
	}

	return $user; // Return the WP_User object or an error using WP_Error.
}
// You can use apply_filters('wp_authenticate_user', $userdata, $password) to apply the 'wp_authenticate_user' filter and modify the $userdata before further processing. Similarly, apply_filters('authenticate', null, $username, $password) applies the 'authenticate' filter, allowing you to modify the authentication process or the user before returning it.
// Customize the logic inside these filters according to your specific requirements.
