<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function lps_register_hooks() {
	$custom_login_url = get_option( 'lps_new_login_url', '' );

	if ( $custom_login_url ) {
		add_action( 'plugins_loaded', 'lps_plugin_on_page_loaded' );
		add_action( 'init', 'lps_handle_logout', 1 );
		add_action( 'wp_loaded', 'lps_redirect_page', 1 );
		add_filter( 'site_url', 'lps_filter_login_url', 10, 4 );
		add_filter( 'login_url', 'lps_login_url', 10, 3 );
	}
}

// Register hooks
add_action( 'init', 'lps_register_hooks' );

/**
 * Deactivate wp-login and activate the new URL
 */
function lps_plugin_on_page_loaded() {
	global $pagenow, $lps_is_login;
	$custom_login_url = get_option( 'lps_new_login_url', '' );

	if ( $custom_login_url ) {
		// Check if REQUEST_URI is set before using it.
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			// Unsplash the REQUEST_URI before sanitizing.
			$request_uri = wp_unslash( $_SERVER['REQUEST_URI']);

			// Check for wp-login.php.
			if ( strpos( rawurldecode( $request_uri ), 'wp-login.php' ) !== false && ! is_user_logged_in() ) {
				// Redirect to 404 since the user is not logged in and custom login URL is set.
				nocache_headers();
				wp_safe_redirect( home_url( '/404' ) );
				exit;
			}
		}
	}
}

function lps_handle_logout() {
	if ( isset( $_GET['action'] ) && $_GET['action'] === 'logout' && isset( $_GET['_wpnonce'] ) ) {
		// Unsplash the nonce and sanitize it.
		$nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );

		if ( wp_verify_nonce( $nonce, 'log-out' ) ) {
			wp_logout(); // Process logout.

			// Check if the user was in the admin panel.
			if ( is_admin() ) {
				wp_safe_redirect( admin_url() );
			} else {
				wp_safe_redirect( home_url() ); // Redirect to home or another page for non-admin users.
			}
			exit;
		}
	}
}



/**
 * Handle redirections and special cases like logout.
 */
function lps_redirect_page() {
	global $pagenow, $lps_is_login;

	$custom_login_url    = get_option( 'lps_new_login_url' );
	$custom_redirect_url = get_option( 'lps_new_redirection_url', '' );

	if ( $custom_login_url ) {
		// Get the request URI and sanitize it.
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';

		// Parse the URI to get the path.
		$request_path = trim( wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );

		// If we're at the custom login URL and logged out, show the default login page.
		if ( $request_path === $custom_login_url ) {
			$user_login = '';
			$error      = '';
			require_once ABSPATH . 'wp-login.php';
			exit;
		} else {
			// If the request path is not the custom login URL, check if it's the site URL.
			if ( $request_path === trim( wp_parse_url( site_url( $custom_login_url ), PHP_URL_PATH ), '/' ) ) {
				$user_login = '';
				$error      = '';
				require_once ABSPATH . 'wp-login.php';
				exit;
			}
		}
	}

	// Redirect based on the custom redirect URL setting.
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';

	if ( ( strpos( $request_uri, 'wp-login.php' ) !== false || is_admin() ) && ! is_user_logged_in() ) {
				nocache_headers();

		// Check if custom_redirect_url is set and redirect accordingly.
		if ( ! empty( $custom_redirect_url ) ) {
			// Redirect to 404 if '404' is set in the redirect option.
			if ( $custom_redirect_url === '404' ) {
				wp_safe_redirect( home_url( '/404' ) );
			} else {
				// Redirect to the custom URL or slug set in the options.
				wp_safe_redirect( home_url( $custom_redirect_url ) );
			}
		} else {
			// Default to 404 if no custom redirect is set.
			wp_safe_redirect( home_url( '/404' ) );
		}
		exit;
	}
}

function lps_filter_login_url( $url, $path, $scheme, $blog_id ) {
	$custom_login_url = get_option( 'lps_new_login_url', '' );

	if ( strpos( $url, 'wp-login.php' ) !== false ) {
		$args = explode( '?', $url );
		if ( isset( $args[1] ) ) {
			parse_str( $args[1], $args );
			$url = add_query_arg( $args, get_site_url() . '/' . $custom_login_url );
		} else {
			$url = get_site_url() . '/' . $custom_login_url;
		}
	}

	return $url;
}

function lps_login_url( $login_url, $redirect, $force_reauth ) {
	if ( get_option( 'lps_new_login_url' ) ) {
		if ( mb_strpos( $_SERVER['REQUEST_URI'], 'wp-admin/install.php' ) ) {
			return admin_url();
		}

		if ( is_404() ) {
			nocache_headers();
			return '#';
		}

		if ( $force_reauth === false ) {
			return $login_url;
		}

		if ( empty( $redirect ) ) {
			return $login_url;
		}

		$redirect = explode( '?', $redirect );

		if ( $redirect[0] === admin_url( 'options.php' ) ) {
			$login_url = admin_url();
		}
	}

	return $login_url;
}
