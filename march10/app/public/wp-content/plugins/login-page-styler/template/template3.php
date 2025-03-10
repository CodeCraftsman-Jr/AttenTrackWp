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
		if ( get_option( 'lps_body_bg_img' ) == '' ) {
			echo esc_url( plugins_url( '../images/temp2.jpg', __FILE__ ) );
		} else {
			echo esc_attr( get_option( 'lps_body_bg_img' ) );
		}
		?>
)no-repeat center center fixed !important;
		background-size: 100% 100%;
	}

	#login form p {
		text-align: center;
	}

	.login h1 a {
		margin: 0 auto !important;
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
		border-top-left-radius: 0;
		border-top-right-radius: 0;
	}

	.login form .input:focus {
		box-shadow: none !important;
	}

	.language-switcher {
		margin: 100px;
	}

	.login form .input {
		background-color: transparent !important;
		border: none !important;
		border-bottom: 4px solid 
		<?php
		if ( get_option( 'lps_login_form_input_feild_border_color' ) !== '' ) {
										echo esc_attr( get_option( 'lps_login_form_input_feild_border_color' ) );
		} else {
			echo 'rgba(0, 0, 0, .1)';
		}
		?>
									!important;
		border-top: 4px solid
		<?php
		if ( get_option( 'lps_login_form_input_feild_border_color' ) !== '' ) {
										echo esc_attr( get_option( 'lps_login_form_input_feild_border_color' ) );
		} else {
			echo 'rgba(0, 0, 0, .1)';
		}
		?>
									!important;
		color: 
		<?php
		if ( get_option( 'lps_login_form_input_color' ) !== '' ) {
			echo esc_attr( get_option( 'lps_login_form_input_color' ) );
		} else {
			echo ' #948376 ';
		}
		?>
				;
		font-size: 20px !important;
		letter-spacing: -1px;
		outline: 0 !important;
		padding: 5px 20px !important;
		text-align: center !important;
		transition: all 0.3s !important;
		width: 190px !important;
		box-shadow: none !important;
	}

	.login .button-primary {}

	#login form p {
		text-align: center !important;
		float: none !important;
	}

	.login label {
		letter-spacing: 2px !important;
		display: inline !important;
		color: 
		<?php
		if ( get_option( 'lps_login_label_color' ) !== '' ) {
			echo esc_attr( get_option( 'lps_login_label_color' ) );
		} else {
			echo '#948376 ';
		}
		?>
				;
		cursor: text !important;
		font-size: 20px;
		line-height: 30px !important;
		text-transform: uppercase !important;
		-moz-transform: translateY(-34px) !important;
		-ms-transform: translateY(-34px) !important;
		-webkit-transform: translateY(-34px) !important;
		transform: translateY(-34px) !important;
		transition: all 0.3s !important;
	}

	.login form .input:focus {
		width: 280px !important;
	}

	.login form .input:focus+.login label {
		color: #F0F0F0;
		font-size: 13px !important;
		-moz-transform: translateY(-74px) !important;
		-ms-transform: translateY(-74px) !important;
		-webkit-transform: translateY(-74px) !important;
		transform: translateY(-74px) !important;
	}

	.login form .input:valid+.login label {
		font-size: 13px !important;
		-moz-transform: translateY(-74px) !important;
		-ms-transform: translateY(-74px) !important;
		-webkit-transform: translateY(-74px) !important;
		transform: translateY(-74px) !important;
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
			echo 'rgba(0, 0, 0, 0.2)';
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
			echo '#948376';
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
			echo '#908373';
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
				echo ' rgba(0, 0, 0, 0.2)';
			}
			?>
			;
		padding: 10px;
		border-radius: 5px;
		transition: all 0.3s ease 0s;
		background: ;
		color:
			<?php
			if ( get_option( 'lps_login_nav_color' ) !== '' ) {
				echo esc_attr( get_option( 'lps_login_nav_color' ) );
			} else {
				echo '#948376';
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
				echo '#908373';
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

	.log #backtoblog a {
		background:
			<?php
			if ( get_option( 'lps_login_nav_bgcolor' ) !== '' ) {
				echo esc_attr( get_option( 'lps_login_nav_bgcolor' ) );
			} else {
				echo 'rgba(0, 0, 0, 0.2)';
			}
			?>
			;
		transition: all 0.3s ease 0s;
		color: 
		<?php
		if ( get_option( 'lps_login_nav_color' ) !== '' ) {
			echo esc_attr( get_option( 'lps_login_nav_color' ) );
		} else {
			echo ' #948376';
		}
		?>
	}

	.login #backtoblog a:hover {
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

	.logi #backtoblog {
		width: 100%;
		padding: 0;
		text-align: center;
		padding-top: -85px;
		transition: all 0.3s ease 0s;
	}

	div#login {
		padding-top: 4%;
	}

	.login form .forgetmenot label {}

	.login form .forgetmenot label {}
</style>