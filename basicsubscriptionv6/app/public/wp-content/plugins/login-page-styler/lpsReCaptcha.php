<?php

// Security check to prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// For more information about the usage of Google ReCAPTCHA, refer to the readme file.
// See the "Usage of Google ReCAPTCHA" section in the readme for details.
// Google ReCAPTCHA script registration.
function login_recaptcha_script() {
	wp_register_script( 'recaptcha_login', 'https://www.google.com/recaptcha/api.js', array(), null, true );
	wp_enqueue_script( 'recaptcha_login' );
}
add_action( 'login_enqueue_scripts', 'login_recaptcha_script' );



if ( get_option( 'lps_login_captcha' ) == 1 ) {

	function lps_display_login_captcha() {
		$site_key        = get_option( 'rs_site_key' );
		$theme_recaptcha = get_option( 'lps_recaptcha_theme' );
		if ( $site_key ) {
			echo '<div style="margin-bottom: 10px; transform: scale(.94); transform-origin: 0 0" class="g-recaptcha" data-theme="' . esc_attr( $theme_recaptcha ) . '" data-sitekey="' . esc_attr( $site_key ) . '"></div>';
			wp_nonce_field( 'lps_login_captcha_nonce', 'lps_login_captcha_nonce' );
		}
	}
	add_action( 'login_form', 'lps_display_login_captcha' );

	function lps_verify_login_captcha( $user, $password ) {
		if ( isset( $_POST['g-recaptcha-response'] ) && isset( $_POST['lps_login_captcha_nonce'] ) && check_admin_referer( 'lps_login_captcha_nonce', 'lps_login_captcha_nonce' ) ) {
			$recaptcha_secret   = get_option( 'rs_private_key' );
			$recaptcha_response = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';

			if ( empty( $recaptcha_secret ) ) {
				return new WP_Error( 'Captcha Invalid', __( 'reCAPTCHA secret key is not configured.' ) );
			}

			if ( empty( $recaptcha_response ) ) {
				return new WP_Error( 'Captcha Invalid', __( 'Please complete the reCAPTCHA.' ) );
			}

			$response = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response );

			if ( is_wp_error( $response ) ) {
				return new WP_Error( 'Captcha Invalid', __( 'Error while verifying reCAPTCHA.' ) );
			}

			$response_body = wp_remote_retrieve_body( $response );
			$response_data = json_decode( $response_body, true );

			if ( isset( $response_data['success'] ) && $response_data['success'] ) {
				return $user;
			} else {
				return new WP_Error( 'Captcha Invalid', __( 'reCAPTCHA verification failed. You might be a bot.' ) );
			}
		} else {
			return new WP_Error( 'Captcha Invalid', __( 'Please complete the reCAPTCHA.' ) );
		}
	}

	add_filter( 'authenticate', 'lps_verify_login_captcha', 40, 2 );
}

if ( get_option( 'lps_reg_captcha' ) == 1 ) {
	function lps_display_register_captcha() {
		?>
		<div style="margin-bottom:5px; transform:scale(.93);transform-origin:0 0" class="g-recaptcha" data-theme="<?php echo esc_attr( get_option( 'lps_recaptcha_theme' ) ); ?>" data-sitekey="<?php echo esc_attr( get_option( 'rs_site_key' ) ); ?>"></div>
		<?php
		wp_nonce_field( 'lps_register_captcha_nonce', 'lps_register_captcha_nonce' );
	}
	add_action( 'register_form', 'lps_display_register_captcha' );

	function lps_verify_registration_captcha( $errors, $sanitized_user_login, $user_email ) {
		if ( isset( $_POST['g-recaptcha-response'] ) && isset( $_POST['lps_register_captcha_nonce'] ) && check_admin_referer( 'lps_register_captcha_nonce', 'lps_register_captcha_nonce' ) ) {
			$recaptcha_secret = get_option( 'rs_private_key' );
			$response         = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_secret . '&response=' . $_POST['g-recaptcha-response'] );
			$response_body    = wp_remote_retrieve_body( $response );
			$response_data    = json_decode( $response_body, true );

			if ( ! is_wp_error( $response ) && isset( $response_data['success'] ) && $response_data['success'] ) {
				return $errors;
			} else {
				$errors->add( 'captcha_invalid', __( '<strong>ERROR</strong>: reCAPTCHA verification failed. You might be a bot.' ) );
			}
		} else {
			$errors->add( 'captcha_invalid', __( '<strong>ERROR</strong>: Please complete the reCAPTCHA.' ) );
		}
		return $errors;
	}
	add_filter( 'registration_errors', 'lps_verify_registration_captcha', 10, 3 );
}

if ( get_option( 'lps_lost_captcha' ) == 1 ) {
	function lps_display_lost_captcha() {
		?>
		<div style="margin-bottom:5px; transform:scale(.95);transform-origin:0 0" class="g-recaptcha" data-theme="<?php echo esc_attr( get_option( 'lps_recaptcha_theme' ) ); ?>" data-sitekey="<?php echo esc_attr( get_option( 'rs_site_key' ) ); ?>"></div>
		<?php
		wp_nonce_field( 'lps_lost_captcha_nonce', 'lps_lost_captcha_nonce' );
	}
	add_action( 'lostpassword_form', 'lps_display_lost_captcha' );

	function lps_verify_lostpassword_captcha() {
		if ( isset( $_POST['g-recaptcha-response'] ) && isset( $_POST['lps_lost_captcha_nonce'] ) && check_admin_referer( 'lps_lost_captcha_nonce', 'lps_lost_captcha_nonce' ) ) {
			$recaptcha_secret = get_option( 'rs_private_key' );
			$response         = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_secret . '&response=' . $_POST['g-recaptcha-response'] );
			$response_body    = wp_remote_retrieve_body( $response );
			$response_data    = json_decode( $response_body, true );

			if ( ! is_wp_error( $response ) && isset( $response_data['success'] ) && $response_data['success'] ) {
				return; // Successfully verified
			} else {
				wp_die( __( '<strong>ERROR</strong>: reCAPTCHA verification failed. You might be a bot.' ) );
			}
		} else {
			wp_die( __( '<strong>ERROR</strong>: Please complete the reCAPTCHA.' ) );
		}
	}
	add_action( 'lostpassword_post', 'lps_verify_lostpassword_captcha' );
}
