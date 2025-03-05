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

		text-align: center;
		background: url(
		<?php
		if ( get_option( 'lps_body_bg_img' ) === '' ) {

			echo esc_url( plugins_url( '../images/temp.jpg', __FILE__ ) );
		} else {

			echo esc_attr( get_option( 'lps_body_bg_img' ) );
		}
		?>
) no-repeat center center fixed !important;
		background-size: cover !important;
	}

	.login h1 a {
		margin: 0 auto;
	}

	.login form label {}

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
			echo 'background: rgba(0, 0, 0, 0.2);'; // Default background color
		}
		?>
		padding-top: 20px;
		border-top-left-radius: 5px !important;
		border-top-right-radius: 5px !important;
	}

	.login .message, .login .notice, .login .success{
		box-shadow: none !important;
		margin: 0;
	}
	 
	.login form {
		box-shadow: none !important;
		margin: 0;
		<?php
		$hex_color = get_option( 'lps_login_form_color' );

		// Get opacity value from options or use default of 1.
		$opacity = get_option( 'lps_login_form_color_opacity', 1 );

		// Convert hex color to RGBA format with opacity.
		$rgba_color = hex_to_rgba( $hex_color, $opacity );

		if ( ! empty( $hex_color ) ) {
			echo 'background: ' . esc_attr( $rgba_color ) . ';';
		} else {
			echo 'background: rgba(0, 0, 0, 0.2);'; // Default background color
		}
		?>
		border-bottom-left-radius: 5px;
		border-bottom-right-radius: 5px;

	}

	.login form .input:focus {
		box-shadow: none;
	}

	.login form .input {

		background-color: transparent;
		border: none;
		border-bottom: 4px solid 
		<?php
		if ( get_option( 'lps_login_form_input_feild_border_color' ) !== '' ) {
										echo esc_attr( get_option( 'lps_login_form_input_feild_border_color' ) );
		} else {
			echo '#ffffff';
		}
		?>
									!important;
		color: 
		<?php
		if ( get_option( 'lps_login_form_input_text_color' ) !== '' ) {
					echo esc_attr( get_option( 'lps_login_form_input_text_color' ) );
		} else {
			echo '#ffffff';
		}
		?>
				;
		font-size: 20px;
		letter-spacing: -1px;
		outline: 0;
		padding: 5px 20px;
		text-align: center;
		transition: all 0.3s;
		width: 190px;
		box-shadow: none;
	}

	.login .button-primary {}

	#login form p {
		text-align: center;
		display: inline-block;
	}

	.login label {
		display: inline !important;
		letter-spacing: 2px;
		color: 
		<?php
		if ( get_option( 'lps_login_label_color' ) !== '' ) {
			echo esc_attr( get_option( 'lps_login_label_color' ) );
		} else {
			echo 'white';
		}
		?>
				;
		cursor: text;
		font-size: 20px;
		line-height: 30px;
		text-transform: uppercase;
		-moz-transform: translateY(-34px);
		-ms-transform: translateY(-34px);
		-webkit-transform: translateY(-34px);
		transform: translateY(-34px);
		transition: all 0.3s;
	}

	.login form .input:focus {
		width: 280px;
	}

	.login form .input:focus+.login label {
		color: #F0F0F0;
		font-size: 13px;
		-moz-transform: translateY(-74px);
		-ms-transform: translateY(-74px);
		-webkit-transform: translateY(-74px);
		transform: translateY(-74px);
	}

	.login form .input:valid+.login label {
		font-size: 13px;
		-moz-transform: translateY(-74px);
		-ms-transform: translateY(-74px);
		-webkit-transform: translateY(-74px);
		transform: translateY(-74px);
	}


	.wp-core-ui .button-group.button-large .button,
	.wp-core-ui .button.button-large {
		width: 100%;
		border: none;
		box-shadow: none;
		text-shadow: none;
		background: 
		<?php
		if ( get_option( 'lps_login_button_color' ) !== '' ) {
			echo esc_attr( get_option( 'lps_login_button_color' ) );
		} else {
			echo '#75888E';
		}
		?>
					;
		height: 44px;
		font-size: 17px;
		margin-top: 14px;
		transition: all 0.3s ease 0s;
		color: 
		<?php
		if ( get_option( 'lps_login_button_text_color' ) !== '' ) {
			echo esc_attr( get_option( 'lps_login_button_text_color' ) );
		} else {
			echo ' #E4E4E4';
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
			echo '#82B1A1';
		}
		?>
					;
		color: 
		<?php
		if ( get_option( 'lps_login_button_text_color_hover' ) !== '' ) {
			echo esc_attr( get_option( 'lps_login_button_text_color_hover' ) );
		} else {
			echo '#82B1A1';
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
		border:
			<?php
			if ( get_option( 'lps_login_button_border_color' ) !== '' ) {
				echo '1px solid';
			}
			?>
			;
		border-color: <?php echo esc_attr( get_option( 'lps_login_button_border_color' ) ); ?>;
		background:
			<?php
			if ( get_option( 'lps_login_nav_bgcolor' ) !== '' ) {
				echo esc_attr( get_option( 'lps_login_nav_bgcolor' ) );
			} else {
				echo '#75888E';
			}
			?>
			;
		padding: 10px;
		border-radius: 5px;
		transition: all 0.3s ease 0s;
		color:
			<?php
			if ( get_option( 'lps_login_nav_bgcolor' ) !== '' ) {
				echo esc_attr( get_option( 'lps_login_nav_color' ) );
			} else {
				echo '#E4E4E4';
			}
			?>
			;
	}

	.login #nav a:hover {

		border:
			<?php
			if ( get_option( 'lps_login_button_border_color_hover' ) !== '' ) {
				echo '1px solid';
			}
			?>
			;
		border-color: <?php echo esc_attr( get_option( 'lps_login_button_border_color_hover' ) ); ?>;
		background:
			<?php
			if ( get_option( 'lps_login_nav_bgcolor_hover' ) !== '' ) {
				echo esc_attr( get_option( 'lps_login_nav_bgcolor_hover' ) );
			} else {
				echo '#82B1A1';
			}
			?>
			;
		color:
			<?php
			if ( get_option( 'lps_login_nav_hover_color' ) !== '' ) {
				echo esc_attr( get_option( 'lps_login_nav_hover_color' ) );
			} else {
				echo 'white';
			}
			?>
			;
	}

	p #reg_passmail {}

	.login #backtoblog a {
		width: 100%;
		border:
			<?php
			if ( get_option( 'lps_login_button_border_color' ) !== '' ) {
				echo '1px solid';
			}
			?>
			;
		border-color: <?php echo esc_attr( get_option( 'lps_login_button_border_color' ) ); ?>;
		background:
			<?php
			if ( get_option( 'lps_login_nav_bgcolor' ) !== '' ) {
				echo esc_attr( get_option( 'lps_login_nav_bgcolor' ) );
			} else {
				echo '#75888E';
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
				echo '#E4E4E4';
			}
			?>
			;
	}

	.login #backtoblog a:hover {

		border:
			<?php
			if ( get_option( 'lps_login_button_border_color_hover' ) !== '' ) {
				echo '1px solid';
			}
			?>
			;
		border-color: <?php echo esc_attr( get_option( 'lps_login_button_border_color_hover' ) ); ?>;
		background:
			<?php
			if ( get_option( 'lps_login_nav_bgcolor_hover' ) !== '' ) {
				echo esc_attr( get_option( 'lps_login_nav_bgcolor_hover' ) );
			} else {
				echo '#82B1A1';
			}
			?>
			;
		color:
			<?php
			if ( get_option( 'lps_login_nav_hover_color' ) !== '' ) {
				echo esc_attr( get_option( 'lps_login_nav_hover_color' ) );
			} else {
				echo 'white';
			}
			?>
			;
	}

	.login #backtoblog {
		width: 100%;
		padding: 0;
		text-align: center;
		margin-top: 30px;
		transition: all 0.3s ease 0s;
	}

	div#login {
		padding-top: 4%;
	}

	.login form .forgetmenot label {}
</style>