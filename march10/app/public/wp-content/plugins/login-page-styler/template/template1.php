<?php

	// Security check to prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	/**  This is a PHP file that contains CSS rules
	 * No file-level documentation comment needed in this context
	 *
	 * @package Login Page Styler
	 */

?>

<style>
	html {

		background: none;
	}

	body {

		background-color: 
		<?php
		if ( get_option( 'lps_login_background_color' ) !== '' ) {
							echo esc_attr( get_option( 'lps_login_background_color' ) );
		} else {
			echo '#f5683d';
		}
		?>
			;
		text-align: center !important;

	}

	#login form p {
		text-align: center !important;
		float: none;
	}

	.login h1 a {

		margin: 0 auto;
	}

	.login h1 {
		<?php
			$hex_color = get_option( 'lps_login_form_color' );

			// Get opacity value from options or use default of 1.
			$opacity = get_option( 'lps_login_form_color_opacity', 1 );

			// Convert hex color to RGBA format with opacity.
			$rgba_color = hex_to_rgba( $hex_color, $opacity );

		if ( ! empty( $hex_color ) ) {
			echo 'background: ' . esc_attr( $rgba_color ) . ';';
		} else {
			echo 'background:white;'; // Default background color
		}
		?>
		padding-top: 20px;
		border-top-left-radius: 5px;
		border-top-right-radius: 5px;
	}

	.login .message, .login .notice, .login .success{
		box-shadow: none;
		margin: 0;
	}
	 
	.login form {

		margin: 0;
		box-shadow: none !important;
		<?php
			$hex_color = get_option( 'lps_login_form_color' );

			// Get opacity value from options or use default of 1.
			$opacity = get_option( 'lps_login_form_color_opacity', 1 );

			// Convert hex color to RGBA format with opacity.
			$rgba_color = hex_to_rgba( $hex_color, $opacity );

		if ( ! empty( $hex_color ) ) {
			echo 'background: ' . esc_attr( $rgba_color ) . ';';
		} else {
			echo 'background:white;'; // Default background color
		}
		?>
		border-top-left-radius: 0px !important;
		border-top-right-radius: 0px !important;
		border-bottom-left-radius: 5px !important;
		border-bottom-right-radius: 5px !important;
	}

	.login form .input {
		background: 
		<?php
						// Get hex color value from options.
						$hex_color = get_option( 'lps_login_form_input_color' );

						// Get opacity value from options or use default of 1.
						$opacity = get_option( 'lps_login_form_input_color_opacity' );

						// Convert hex color to RGBA format with default opacity of 1.
						$rgba_color = hex_to_rgba( $hex_color, $opacity );
		if ( get_option( 'lps_login_form_input_color' ) !== '' ) {
			echo esc_attr( $rgba_color );
		} else {
			echo ' #EFEFEF';
		}
		?>
			;
		box-shadow: none;

		text-align: center !important;
		padding: 10px;
	}

	.login input.password-input {
		padding-right: 0px !important;
	}

	.login .button-primary {}

	.login form label {
		color: 
		<?php
		if ( get_option( 'lps_login_label_color' ) !== '' ) {
			echo esc_attr( get_option( 'lps_login_label_color' ) );
		} else {
			echo ' #616161';
		}
		?>
		;
	}

	.wp-core-ui .button-group.button-large .button,
	.wp-core-ui .button.button-large {
		width: 100%;
		border: none;
		box-shadow: none;
		text-shadow: none;
		height: 44px;
		font-size: 17px;
		margin-top: 14px;
		transition: all 0.3s ease 0s;
		color: 
		<?php
		if ( get_option( 'lps_login_button_text_color' ) !== '' ) {
			echo esc_attr( get_option( 'lps_login_button_text_color' ) );
		} else {
			echo 'white';
		}
		?>
		;

	}

	.login .button-primary:hover {
		background: 
		<?php
		if ( get_option( 'lps_login_button_color_hover' ) !== '' ) {
			echo esc_attr( get_option( 'lps_login_button_color_hover' ) );
		} else {
			echo '#478ffb';
		}
		?>
		;
		color: 
		<?php
		if ( get_option( 'lps_login_button_text_color_hover' ) !== '' ) {
			echo esc_attr( get_option( 'lps_login_button_text_color_hover' ) );
		} else {
			echo 'white';
		}
		?>
		;
	}

	.login #nav {
		width: 100%;
		padding: 0;
		text-align: center;
		transition: all 0.3s ease 0s;
	}

	.login #nav a {
		width: 100%;
		background:
			<?php
			if ( get_option( 'lps_login_nav_bgcolor' ) !== '' ) {
				echo esc_attr( get_option( 'lps_login_nav_bgcolor' ) );
			} else {
				echo '#2271b1';
			}
			?>
			;
		padding: 10px;
		border-radius: 5px;
		transition: all 0.3s ease 0s;
		color:
			<?php
			if ( get_option( 'lps_login_nav_color' ) !== '' ) {
				echo esc_attr( get_option( 'lps_login_nav_color' ) );
			} else {
				echo ' white';
			}
			?>
			; 
	}

	.login #nav a:hover {
		background: 
		<?php
		if ( get_option( 'lps_login_nav_bgcolor_hover' ) !== '' ) {
			echo esc_attr( get_option( 'lps_login_nav_bgcolor_hover' ) );
		} else {
			echo '#0085BA';
		}
		?>
		;
		color: 
		<?php
		if ( get_option( 'lps_login_nav_hover_color' ) !== '' ) {
			echo esc_attr( get_option( 'lps_login_nav_hover_color' ) );
		} else {
			echo ' white';
		}
		?>
		; 
	}

	p #reg_passmail {}

	.login #backtoblog a {
		transition: all 0.3s ease 0s;
		color: 
		<?php
		if ( get_option( 'lps_login_nav_color' ) !== '' ) {
			echo esc_attr( get_option( 'lps_login_nav_color' ) );
		} else {
			echo ' #948376';
		}
		?>
		;
	}

	.login #backtoblog a:hover {
		color:
			<?php
			if ( get_option( 'llps_login_nav_hover_color' ) !== '' ) {
				echo esc_attr( get_option( 'lps_login_nav_hover_color' ) );
			} else {
				echo ' #6F7273';
			}
			?>
			;
	}

	.login #backtoblog {
		width: 100%;
		padding: 0;
		text-align: center;
		transition: all 0.3s ease 0s;
	}

	div#login {
		padding-top: 4%;
	}

	.login form .forgetmenot label {}

	.login form .forgetmenot label {}
</style>