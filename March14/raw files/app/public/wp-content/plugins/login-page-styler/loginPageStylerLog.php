<?php

function lps_create_login_logs_table() {
	global $wpdb;

	$login_logs_table  = $wpdb->prefix . 'user_login_logs';  // Successful logins
	$failed_logs_table = $wpdb->prefix . 'user_failed_login_logs';  // Failed login attempts

	$charset_collate = $wpdb->get_charset_collate();

	// Table for successful login logs
	$login_logs_sql = "CREATE TABLE $login_logs_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        user_login varchar(100) NOT NULL,
        login_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        user_ip varchar(100) NOT NULL,
        user_role varchar(100) NOT NULL,
        profile_picture varchar(255) DEFAULT '' NOT NULL,
        session_duration int NOT NULL, 
        PRIMARY KEY (id)
    ) $charset_collate;";

	// Table for failed login logs
	$failed_logs_sql = "CREATE TABLE $failed_logs_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) DEFAULT 0,
        user_login varchar(100) NOT NULL,
        login_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        user_ip varchar(100) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $login_logs_sql );
	dbDelta( $failed_logs_sql );
}




function lps_log_user_login( $user_login ) {
	// Get user info
	$user = get_user_by( 'login', $user_login );
	if ( $user ) {
		global $wpdb;

		// Determine the user's IP address
		$user_ip = $_SERVER['REMOTE_ADDR'];
		if ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER ) ) {
			$user_ip = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0];
		} elseif ( array_key_exists( 'HTTP_CLIENT_IP', $_SERVER ) ) {
			$user_ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		$user_ip = sanitize_text_field( $user_ip );

		// Calculate session duration
		$session_duration = time() - strtotime( $user->user_registered );

		// Prepare data for insertion
		$data = array(
			'user_id'          => $user->ID,
			'user_login'       => $user_login,
			'login_time'       => current_time( 'mysql' ),
			'user_ip'          => $user_ip,
			'user_role'        => implode( ', ', $user->roles ),
			'profile_picture'  => get_avatar_url( $user->ID ), // Get the user's profile picture URL
			'session_duration' => $session_duration, // Log session duration
		);

		// Insert data into the database
		$wpdb->insert( $wpdb->prefix . 'user_login_logs', $data );
	}
}
add_action( 'wp_login', 'lps_log_user_login', 10, 2 );


function lps_schedule_log_cleanup() {
	if ( ! wp_next_scheduled( 'lps_cleanup_old_logs' ) ) {
		wp_schedule_event( time(), 'daily', 'lps_cleanup_old_logs' );
	}
}

// Hook into the scheduled event
add_action( 'lps_cleanup_old_logs', 'lps_cleanup_old_logs' );

function lps_cleanup_old_logs() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'user_login_logs';
	$wpdb->query( "DELETE FROM $table_name WHERE login_time < NOW() - INTERVAL 7 DAY" );
}



function lps_clear_log_cleanup_schedule() {
	$timestamp = wp_next_scheduled( 'lps_cleanup_old_logs' );
	wp_unschedule_event( $timestamp, 'lps_cleanup_old_logs' );
}

function lps_log_failed_login( $username ) {
	global $wpdb;

	// Ensure that we log the failed login attempt, even if the user doesn't exist
	// Determine the user's IP address
	$user_ip = $_SERVER['REMOTE_ADDR'];
	if ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER ) ) {
		$user_ip = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0];
	} elseif ( array_key_exists( 'HTTP_CLIENT_IP', $_SERVER ) ) {
		$user_ip = $_SERVER['HTTP_CLIENT_IP'];
	}
	$user_ip = sanitize_text_field( $user_ip );

	// If the username is empty, do not log it
	if ( empty( $username ) ) {
		return;
	}

	// Prepare the data for logging
	$data = array(
		'user_id'    => 0, // Set to 0 since this is a failed login
		'user_login' => $username,
		'login_time' => current_time( 'mysql' ),
		'user_ip'    => $user_ip,
	);

	// Insert the failed login into the logs table
	$wpdb->insert( $wpdb->prefix . 'user_failed_login_logs', $data );
}
add_action( 'wp_login_failed', 'lps_log_failed_login' );

function lps_clear_failed_login_on_success( $user_login ) {
	// Clear the transient for this IP address
	$user_ip = $_SERVER['REMOTE_ADDR'];
	if ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER ) ) {
		$user_ip = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0];
	} elseif ( array_key_exists( 'HTTP_CLIENT_IP', $_SERVER ) ) {
		$user_ip = $_SERVER['HTTP_CLIENT_IP'];
	}
	$user_ip = sanitize_text_field( $user_ip );

	// If there's a transient for this IP (indicating a previous failed attempt), clear it
	if ( get_transient( 'failed_login_' . $user_ip ) ) {
		delete_transient( 'failed_login_' . $user_ip );
	}
}
add_action( 'wp_login', 'lps_clear_failed_login_on_success', 10, 1 );
