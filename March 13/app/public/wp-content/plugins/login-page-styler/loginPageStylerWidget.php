<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Summary of register_login_widget_area
 *
 * Registering widget area
 */
function register_login_widget_area() {
	register_sidebar(
		array(
			'name'          => 'Login Widget Area',
			'id'            => 'login_widget_area',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}

add_action( 'widgets_init', 'register_login_widget_area' );

/**
 * Summary of lps_login_widget
 *
 * Creating login widget
 */
function lps_login_widget() {

	echo '<div class="login-widget">';
	echo '<h2 class="widget-title"></h2>';
	echo '<style>
            .login-widget {
            
            }
            .login-widget button {
                display: block;
                float:left;
                margin: 10px 0;
                padding: 10px;
                font-size: 16px;
                margin-right:5px;
            }
        </style>';

	if ( is_user_logged_in() ) {
		echo '<button onclick="location.href=\'' . esc_url( wp_logout_url() ) . '\';">Logout</button>';
		echo '</div>';
	} else {

		// User is not logged in, show login, register, and lost password buttons.
		$show_login         = get_option( 'lps_login_widgetButton' ) == 1;
		$show_register      = get_option( 'lps_register_widgetButton' ) == 1;
		$show_lost_password = get_option( 'lps_lostpassword_widgetButton' ) == 1;

		if ( $show_login ) {
			echo '<button onclick="location.href=\'' . esc_url( wp_login_url() ) . '\';">Login</button>';
		}

		if ( $show_register ) {
			echo '<button onclick="location.href=\'' . esc_url( wp_registration_url() ) . '\';">Register</button>';
		}

		if ( $show_lost_password ) {
			echo '<button onclick="location.href=\'' . esc_url( wp_lostpassword_url() ) . '\';">Lost Password</button>';
		}

		echo '</div>';
	}
}

/**
 * Summary of lps_login_widget_shortcode
 *
 * Creating short code  for  user
 */
function lps_login_widget_shortcode() {
	ob_start(); // Start output buffering.
	lps_login_widget(); // Output the login buttons.
	return ob_get_clean(); // Return the buffered content.
}

// Register the shortcode.
add_shortcode( 'lps_login_widget', 'lps_login_widget_shortcode' );
