<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Generate a temporary access URL for the specified user.
function lps_create_temporary_access_url( $user_id ) {
	// Generate a unique access key.
	$access_key = wp_generate_password( 20, false );

	// Get the user-configured access expiration time or default to 1 hour.
	$access_expiration_time = get_option( 'lps_access_expiration_time', 1 * HOUR_IN_SECONDS );

	// Set the expiration time from the current time.
	$expiration_time = time() + $access_expiration_time;

	// Save access key, expiration, and URL in user meta.
	update_user_meta( $user_id, 'lps_temp_access_key', sanitize_text_field( $access_key ) );
	update_user_meta( $user_id, 'lps_temp_access_expiration', sanitize_text_field( $expiration_time ) );

	// Debug log the stored values.
	// error_log('Stored Access Key: ' . sanitize_text_field($access_key));.
	// error_log('Stored Expiration Time: ' . $expiration_time);.

	// Create the temporary URL.
	$temp_url = add_query_arg(
		array(
			'temp_access' => sanitize_text_field( $access_key ),
			'user_id'     => intval( $user_id ),
		),
		site_url()
	);

	// Debug log the generated URL
	// error_log('Generated Temporary URL raw: ' . $temp_url); // Log the raw generated URL.

	// Save the generated URL in user meta without escaping.
	update_user_meta( $user_id, 'lps_temp_access_url', $temp_url );

	// Format the expiration time in hours or days.
	$expiration_message = '';
	if ( $access_expiration_time < DAY_IN_SECONDS ) {
		$expiration_message = ( $access_expiration_time / HOUR_IN_SECONDS ) . ' Hour(s)';
	} else {
		$expiration_message = ( $access_expiration_time / DAY_IN_SECONDS ) . ' Day(s)';
	}

	// Return the generated URL and expiration message.
	return array(
		'temp_url' => $temp_url, // Return the raw URL.
		'message'  => 'Temporary access URL created successfully for ' . esc_html( $expiration_message ) . '.',
	);
}


// Retrieve the stored URL (on page reload).
function lps_get_stored_temp_access_url( $user_id ) {
	return esc_url( get_user_meta( $user_id, 'lps_temp_access_url', true ) );
}

function lps_verify_temporary_access_url() {
	// error_log('Verifying temporary access URL...');  // Log the function entry.

	if ( isset( $_GET['temp_access'] ) && isset( $_GET['user_id'] ) ) {
		$user_id         = intval( $_GET['user_id'] );
		$access_key      = get_user_meta( $user_id, 'lps_temp_access_key', true );
		$expiration_time = get_user_meta( $user_id, 'lps_temp_access_expiration', true );

		// Log values for debugging.
		// error_log('Access Key: ' . $access_key);
		// error_log('Temp Access from URL: ' . sanitize_text_field($_GET['temp_access']));
		// error_log('Current Time: ' . current_time('timestamp'));
		// error_log('Expiration Time: ' . $expiration_time);.

		// Check if the access key matches and is still valid.
		if ( sanitize_text_field( $_GET['temp_access'] ) === $access_key && time() < $expiration_time ) {
			// Grant admin access.
			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id );
			do_action( 'wp_login', get_userdata( $user_id )->user_login, get_userdata( $user_id ) );

			// Redirect to the admin dashboard after successful login.
			wp_safe_redirect( admin_url() );
			exit;
		} else {
			// Redirect to home page if the URL is invalid or expired
			// error_log( 'Invalid access key or expired URL.' ); // Log the error.
			wp_safe_redirect( home_url() );
			exit;
		}
	}
}

add_action( 'init', 'lps_verify_temporary_access_url' );

// Revoke access
function lps_revoke_access( $user_id ) {
	// Remove the access key and expiration.
	delete_user_meta( $user_id, 'lps_temp_access_key' );
	delete_user_meta( $user_id, 'lps_temp_access_expiration' );

	// Optionally remove the stored URL as well.
	delete_user_meta( $user_id, 'lps_temp_access_url' );
}

// Handle the AJAX request to revoke access.
function lps_handle_revoke_access() {
	// Verify nonce.
	// Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'lps_ajax_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Invalid nonce.' ) );
        return;
    }

    // Check if the current user has the required capability (administrator)
    if ( ! current_user_can( 'administrator' ) ) {
        wp_send_json_error( array( 'message' => 'You do not have permission to perform this action.' ) );
        return;
    }

	// Check if user_id is passed.
	if ( isset( $_POST['user_id'] ) ) {
		$user_id = intval( $_POST['user_id'] );

		// Make sure the user exists.
		if ( get_userdata( $user_id ) ) {
			lps_revoke_access( $user_id );
			wp_send_json_success( array( 'message' => 'Access revoked successfully.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Invalid user ID.' ) );
		}
	} else {
		wp_send_json_error( array( 'message' => 'Missing user ID.' ) );
	}
}
add_action( 'wp_ajax_lps_revoke_access', 'lps_handle_revoke_access' );

// Auto-disable expired access.
function lps_auto_disable_expired_access() {
	$users = get_users();
	foreach ( $users as $user ) {
		$expiration_time = get_user_meta( $user->ID, 'lps_temp_access_expiration', true );
		if ( $expiration_time && time() > $expiration_time ) {
			// Revoke access if the expiration time has passed.
			lps_revoke_access( $user->ID );
		}
	}
}

add_action( 'init', 'lps_auto_disable_expired_access' );

// Handle the AJAX request to generate the temporary access URL
function lps_handle_generate_temp_access_url() {
	// Verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'lps_ajax_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid nonce.' ) );
		return;
	}
	
	// Check if the current user has the required capability (e.g., administrator)
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'You do not have permission to perform this action.' ) );
        return;
    }

	if ( isset( $_POST['user_id'] ) ) {
		$user_id = intval( $_POST['user_id'] );

		// Check if expiration time is set.
		$expiration_time = isset( $_POST['expiration_time'] ) ? intval( $_POST['expiration_time'] ) : 1 * HOUR_IN_SECONDS; // Default to 1 hour

		// Save the expiration time option.
		update_option( 'lps_access_expiration_time', $expiration_time );

		// Make sure the user exists
		if ( get_userdata( $user_id ) ) {
			// Generate the temporary URL
			$temp_access_data = lps_create_temporary_access_url( $user_id );

			if ( $temp_access_data ) {
				wp_send_json_success( $temp_access_data );  // Respond with success and temp access data.
			} else {
				wp_send_json_error( array( 'message' => 'Failed to generate URL.' ) );
			}
		} else {
			wp_send_json_error( array( 'message' => 'Invalid user ID.' ) );
		}
	} else {
		wp_send_json_error( array( 'message' => 'Missing user ID.' ) );
	}
}

add_action( 'wp_ajax_lps_generate_temp_access_url', 'lps_handle_generate_temp_access_url' );
