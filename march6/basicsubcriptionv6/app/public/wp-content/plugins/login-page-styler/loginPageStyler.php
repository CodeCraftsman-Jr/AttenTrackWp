<?php

/**
 * Plugin Name: Custom Login Page Styler For WordPress
 * Plugin URI:https://pluginnestwp.website/custom-login-page-styler//
 * Description: Custom Login Page Styler to  Customize, Brand, Theme, Secure Your Login Page, Customize login logo and so much more.
 * Author: Zia Imtiaz
 * Requires at least: 4.0
 * Version: 7.1.2
 * Requires PHP:      5.3
 * Author URI:https://pluginnestwp.website/custom-login-page-styler//
 * License: GPLv2
 * Text Domain: login-page-styler
 * Domain Path: /languages
 */

/**
 * Summary of lps_login_template_design
 * Function to select templete design for login page
 */

// Security check to prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Summary of lps_login_template_design
 *
 * Adding login template
 */

/**
 * Summary of lps_login_background_color
 * function to change background color of login page
 */
function lps_login_background_color() {

	if ( ! empty( get_option( 'lps_login_background_color' ) ) ) {
		echo '<style> body {  background-color: ' . esc_attr( get_option( 'lps_login_background_color' ) ) . '; } </style>';
	}
}

/**
 * Summary of lps_login_label_color
 * function to change label color of login form
 */
function lps_login_label_color() {

	echo '<style> .login label{ color: ' . esc_attr( get_option( 'lps_login_label_color' ) ) . ';  } </style > ';
}


/**
 * Summary of lps_login_form_input_color_opacity
 * function to change  login form input fields opacity with color
 */

/*
Function lps_login_form_input_color_opacity() {

	echo ' < style > . login form . input{background: rgba( ' . esc_attr( get_option( 'lps_login_form_input_color_opacity' ) ) . ' ) ! important;} < / style > ';
}
*/
/**
 * Summary of lps_login_nav_size
 *
 * Nav links font size change
 */
function lps_login_nav_size() {

	echo '<style> .login #nav,
.login #backtoblog{font-size:' . esc_attr( get_option( 'lps_login_nav_size' ) ) . 'px ;}</style>';
}

/**
 * Summary of lps_login_nav_color
 * function to change color of login nav link
 */
function lps_login_nav_color() {

	echo '<style> .login #backtoblog a, 
	.login #nav a{ color : ' . esc_attr( get_option( 'lps_login_nav_color' ) ) . ';}</style>';
}

/**
 * Summary of lps_login_nav_hover_color
 * function to change color on hover over nav links
 */
function lps_login_nav_hover_color() {

	echo '<style> .login #backtoblog a:hover, .login #nav a:hover{ color : ' . esc_attr( get_option( 'lps_login_nav_hover_color' ) ) . ';}</style>';
}

function lps_login_nav_bgcolor() {

	echo '<style> .login #backtoblog a, 
	.login #nav a{ background : ' . esc_attr( get_option( 'lps_login_nav_bgcolor' ) ) . '; padding:5px;}</style>';
}

/**
 * Summary of lps_login_nav_bgcolor_hover
 * function to change color on hover over nav links
 */
function lps_login_nav_bgcolor_hover() {
	echo '<style> .login #backtoblog a:hover, .login #nav a:hover{ background : ' . esc_attr( get_option( 'lps_login_nav_bgcolor_hover' ) ) . ';}</style>';
}

/**
 * Summary of lps_login_form_border_radius
 * function to change border radius of login form
 */
function lps_login_form_border_radius() {

	echo '<style> .login form{ border-radius:' . esc_attr( get_option( 'lps_login_form_border_radius' ) ) . 'px ;}</style>';
}

/**
 * Summary of lps_login_label_size
 * function to change size of  input label
 */
function lps_login_label_size() {
	if ( get_option( 'lps_login_label_size' ) !== '' ) {
		echo '<style> .login label[for="user_login"], .login label[for="user_pass"], .login label[for="user_email"]  { font-size:' . esc_attr( get_option( 'lps_login_label_size' ) ) . 'px ;}</style>';
	}
}

/**
 * Summary of lps_login_remember_label_size
 * function to change the size of label for remember me checkbox
 */
function lps_login_remember_label_size() {

	echo '<style>  .login label[for="rememberme"] {font-size:' . esc_attr( get_option( 'lps_login_remember_label_size' ) ) . 'px  ;} </style>';
}


/**
 * Summary of lps_login_nav_link_hide
 * Function to hide nav link on login page
 */
function lps_login_nav_link_hide() {

	if ( get_option( 'lps_login_nav_link_hide' ) == 1 ) {

		echo '<style> .login #nav {display:none ;}</style>';
	} else {
		echo '<style> .login #nav {display:block ;}</style>';
	}
}


/**
 * Summary of lps_login_logo_hide
 * Function to hide login logo
 */
function lps_login_logo_hide() {

	if ( get_option( 'lps_login_logo_hide' ) === '1' ) {

		echo '<style> .login h1 a {display:none;}</style>';
	} else {
		echo '<style> .login h1 a {display:block;}</style>';
	}
}

/**
 * Summary of lps_login_form_position
 * Function to change position of login form
 */
function lps_login_form_position() {
	$position = get_option( 'lps_login_form_position' );
	$style    = '';

	switch ( $position ) {
		case 1:
			$style = 'top: 0; right: 0; bottom: 0; left: 0; padding: 10% 0 0 0 !important;';
			break;
		case 2:
			$style = 'top: 0; right: auto; bottom: 0; left: 0; padding: 10% 70% 0 0 !important;';
			break;
		case 3:
			$style = 'top: 0; right: 0; bottom: 0; left: auto; padding: 10% 0 0 70% !important;';
			break;
		case 4:
			$style = 'top: 0; right: auto; bottom: auto; left: auto; padding: 1% 0 0 0 !important;';
			break;
		case 5:
			$style = 'top: 0; right: auto; bottom: 0; left: 0; padding: 1% 70% 0 0 !important;';
			break;
		case 6:
			$style = 'top: 0; right: 0; bottom: 0; left: 0; padding: 1% 0 0 70% !important; overflow: hidden;';
			break;
		case 7:
			if ( get_option( 'lps_login_blog_link_hide' ) == 1 && get_option( ' lps_login_nav_link_hide' ) == 1 && get_option( 'lps_login_logo_hide' ) == 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 36% 0 0 0 !important;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) != 1 && get_option( 'lps_login_nav_link_hide' ) != 1 && get_option( 'lps_login_logo_hide' ) != 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 23.5% 0 0 0 !important;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) == 1 && get_option( 'lps_login_nav_link_hide' ) != 1 && get_option( 'lps_login_logo_hide' ) == 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 36% 0 0 0 !important;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) != 1 && get_option( 'lps_login_nav_link_hide' ) == 1 && get_option( 'lps_login_logo_hide' ) == 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 32% 0 0 0 !important;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) == 1 && get_option( 'lps_login_nav_link_hide' ) != 1 && get_option( 'lps_login_logo_hide' ) != 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 26% 0 0 0 !important;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) != 1 && get_option( 'lps_login_nav_link_hide' ) == 1 && get_option( 'lps_login_logo_hide' ) != 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 26% 0 0 0 !important;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) != 1 && get_option( 'lps_login_nav_link_hide' ) != 1 && get_option( 'lps_login_logo_hide' ) == 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 30% 0 0 0 !important;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) == 1 && get_option( 'lps_login_nav_link_hide' ) == 1 && get_option( 'lps_login_logo_hide' ) != 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 29% 0 0 0 !important;';
			}
			break;
		case 8:
			if ( get_option( 'lps_login_blog_link_hide' ) == 1 && get_option( 'lps_login_nav_link_hide' ) == 1 && get_option( 'lps_login_logo_hide' ) == 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 36% 70% 0 0;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) != 1 && get_option( 'lps_login_nav_link_hide' ) != 1 && get_option( 'lps_login_logo_hide' ) != 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 23.5% 70% 0 0;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) == 1 && get_option( 'lps_login_nav_link_hide' ) != 1 && get_option( 'lps_login_logo_hide' ) == 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 36% 70% 0 0;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) != 1 && get_option( 'lps_login_nav_link_hide' ) == 1 && get_option( 'lps_login_logo_hide' ) == 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 32% 70% 0 0;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) == 1 && get_option( 'lps_login_nav_link_hide' ) != 1 && get_option( 'lps_login_logo_hide' ) != 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 26% 70% 0 0;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) != 1 && get_option( 'lps_login_nav_link_hide' ) == 1 && get_option( 'lps_login_logo_hide' ) != 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 26% 70% 0 0;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) != 1 && get_option( 'lps_login_nav_link_hide' ) != 1 && get_option( 'lps_login_logo_hide' ) == 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 30% 70% 0 0;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) == 1 && get_option( 'lps_login_nav_link_hide' ) == 1 && get_option( 'lps_login_logo_hide' ) != 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 29% 70% 0 0;';
			}
			break;
		case 9:
			if ( get_option( 'lps_login_blog_link_hide' ) == 1 && get_option( 'lps_login_nav_link_hide' ) == 1 && get_option( 'lps_login_logo_hide' ) == 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 36% 0 0 70%;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) != 1 && get_option( 'lps_login_nav_link_hide' ) != 1 && get_option( 'lps_login_logo_hide' ) != 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 23.5% 0 0 70%;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) == 1 && get_option( 'lps_login_nav_link_hide' ) != 1 && get_option( 'lps_login_logo_hide' ) == 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 36% 0 0 70%;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) != 1 && get_option( 'lps_login_nav_link_hide' ) == 1 && get_option( 'lps_login_logo_hide' ) == 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 32% 0 0 70%;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) == 1 && get_option( 'lps_login_nav_link_hide' ) != 1 && get_option( 'lps_login_logo_hide' ) != 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 26% 0 0 70%;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) != 1 && get_option( 'lps_login_nav_link_hide' ) == 1 && get_option( 'lps_login_logo_hide' ) != 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 26% 0 0 70%;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) != 1 && get_option( 'lps_login_nav_link_hide' ) != 1 && get_option( 'lps_login_logo_hide' ) == 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 30% 0 0 70%;';
			} elseif ( get_option( 'lps_login_blog_link_hide' ) == 1 && get_option( 'lps_login_nav_link_hide' ) == 1 && get_option( 'lps_login_logo_hide' ) != 1 ) {
				$style = 'top: auto; right: auto; bottom: auto; left: auto; padding: 29% 0 0 70%;';
			}
			break;
		default:
			$style = 'padding: 8% 0 0 0 !important;';
			break;
	}

	echo '<style>div#login { ' . esc_attr( $style ) . '}</style>';
}

/**
 * Summary of lps_login_form_color
 * function to add login form color
 */
function lps_login_form_color() {
	// Get hex color value from options.
	$hex_color = get_option( 'lps_login_form_color' );

	// Get opacity value from options or use default of 1.
	// Get opacity value from options or use default of 1.
	//$opacity = get_option('lps_login_form_color_opacity');
	$opacity = 1;
	// Convert hex color to RGBA format with opacity.
	$rgba_color = hex_to_rgba( $hex_color, $opacity );

	echo '<style> .login form { background: ' . esc_attr( $rgba_color ) . ' ; margin:0 auto;}</style>';
}


/**
 * Summary of lps_login_form_input_color_opacity
 *
 * Adding input field opacity
 */
function lps_login_form_input_color_opacity() {
	// Get hex color value from options.
	$hex_color = get_option( 'lps_login_form_input_color' );

	// Get opacity value from options or use default of 1.
	// $opacity = get_option('lps_login_form_input_color_opacity');
	$opacity = 1;
	// Convert hex color to RGBA format with default opacity of 1.
	$rgba_color = hex_to_rgba( $hex_color, $opacity );

	echo '<style> .login form .input { background: ' . esc_attr( $rgba_color ) . '; }</style>';
}


/**
 * Summary of lps_login_logo_msg_hide
 * function to hide login error msg
 */
function lps_login_logo_msg_hide() {

	if ( get_option( 'lps_login_logo_msg_hide' ) === 1 ) {
		echo '<style> #login_error,.login .message{display:none ;}</style>';
	} else {

		echo '<style> #login_error,.login .message{display:block ;}</style>';
	}
}

/**
 * Summary of lps_login_blog_link_hide
 * function to hide login back to blog link
 */
function lps_login_blog_link_hide() {

	if ( get_option( 'lps_login_blog_link_hide' ) == 1 ) {
		echo '<style> .login #backtoblog{ display:none ;}</style>';
	} else {
		echo '<style> .login #backtoblog{ display:block ;}</style>';
	}
}

/**
 * Summary of lps_login_form_input_feild_border_radius
 * adding border radius for input field
 */
function lps_login_form_input_feild_border_radius() {

	echo '<style> .login form .input {border-radius:' . esc_attr( get_option( 'lps_login_form_input_feild_border_radius' ) ) . 'px !important;}</style>';
}


/**
 * Summary of lps_copyright_color
 * adding color for login copyright set by user
 */
function lps_copyright_color() {

	echo '<style> .login .copyright{color:' . esc_attr( get_option( 'lps_copyright_color' ) ) . ';}</style>';
}

/**
 * Summary of lps_login_custom_css
 * adding custom css  set by user
 */
function lps_login_custom_css() {

	echo '<style>' . esc_attr( get_option( 'lps_login_custom_css' ) ) . '</style>';
}


/**
 * Summary of lps_login_button_border_radius
 * login button border radius set by user
 */
function lps_login_button_border_radius() {

	echo '<style> .login .button-primary{ border-radius:' . esc_attr( get_option( 'lps_login_button_border_radius' ) ) . 'px; } </style>';
}


/**
 * Summary of lps_login_form_input_feild_border_color
 * changing border color of input field set by user
 */
function lps_login_form_input_feild_border_color() {

	echo '<style> .login form .input{border-color:' . esc_attr( get_option( 'lps_login_form_input_feild_border_color' ) ) . '!important;}</style>';
}

/**
 * Summary of lps_login_logo_link
 * changing link for login logo set by user
 */
function lps_login_logo_link() {

	return esc_url( get_option( 'lps_login_logo_link' ) );
}


/**
 * Summary of lps_login_logo_tittle
 * changing title for login logo
 */
function lps_login_logo_tittle() {

	return esc_attr( get_option( 'lps_login_logo_tittle' ) );
}


/**
 * Summary of lps_body_bg_img
 * adding bg img to login page body
 */
function lps_body_bg_img() {

	echo '<style> body{ background-image:url(' . esc_url( get_option( 'lps_body_bg_img' ) ) . ');background-position: center top !important;
	background-repeat: ' . esc_attr( get_option( 'lps_login_bg_repeat' ) ) . '!important; display:block;   background-attachment: fixed !important; background-size:100% 100% !important; }</style>';
}


/**
 * Summary of lps_login_logo
 * Adds a background image for the login logo with specified width and height.
 */
function lps_login_logo() {
	if ( get_option( 'lps_login_logo' ) !== '' ){
		$logo_url    = esc_url( get_option( 'lps_login_logo' ) );
		$logo_width  = esc_attr( get_option( 'lps_login_logo_width' ) );
		$logo_height = esc_attr( get_option( 'lps_login_logo_height' ) );

		echo '<style>
            .login .wp-login-logo a , .login h1 a {
                margin: 0 auto;
                background-image: url(' . $logo_url . ');
                background-size: ' . $logo_width . 'px ' . $logo_height . 'px;
                width: ' . $logo_width . 'px;
                height: ' . $logo_height . 'px;
            }
        </style>';
	}
}
// add_action('login_head', 'lps_login_logo');.

/**
 * Summary of lps_login_logo_width
 * changing logo width
 */
function lps_login_logo_width() {

	if ( get_option( 'lps_login_logo_width' ) !== '' ) {
		echo '<style> .login h1 a{ width:' . esc_attr( get_option( 'lps_login_logo_width' ) ) . 'px;}</style>';
	}
}

/**
 * Summary of lps_login_logo_height
 * changing login logo height
 */
function lps_login_logo_height() {
	if ( get_option( 'lps_login_logo_height' ) !== '' ) {
		echo '<style> .login h1 a{ height:' . esc_attr( get_option( 'lps_login_logo_height' ) ) . 'px;}</style>';
	}
}


/**
 * Summary of lps_login_button_color
 * Changing login button color
 */
function lps_login_button_color() {

	echo '<style> .login .button-primary{background-color:' . esc_attr( get_option( 'lps_login_button_color' ) ) . ';

    border-color:' . esc_attr( get_option( 'lps_login_button_border_color' ) ) . '; border:1px solid ' . esc_attr( get_option( 'lps_login_button_border_color' ) ) . ';

    color:' . esc_attr( get_option( 'lps_login_button_text_color' ) ) . ';
 

    }</style>';
}


/**
 * Summary of lps_login_form_input_text_color
 * Changing login form input text color
 */
function lps_login_form_input_text_color() {

	echo '<style> .login form .input{color:' . esc_attr( get_option( 'lps_login_form_input_text_color' ) ) . ';

    }</style>';
}




/**
 * Summary of lps_login_button_color_hover
 * Changing login button hover color
 */
function lps_login_button_color_hover() {

	echo '<style> .login .button-primary:hover {background-color:' . esc_attr( get_option( 'lps_login_button_color_hover' ) ) . ';

    border-color:' . esc_attr( get_option( 'lps_login_button_border_color_hover' ) ) . '; border:1px solid ' . esc_attr( get_option( 'lps_login_button_border_color_hover' ) ) . ';

    color:' . esc_attr( get_option( 'lps_login_button_text_color_hover' ) ) . ';

    }</style>';
}


/**
 * Summary of lps_login_form_border_style
 * Changing login form border color size and style
 */
function lps_login_form_border_style() {

	echo '<style> .login form{border-style:' . esc_attr( get_option( 'lps_login_form_border_style' ) ) . ';
     border-width:' . esc_attr( get_option( 'lps_login_form_border_size' ) ) . 'px ;
     border-color:' . esc_attr( get_option( 'lps_login_form_border_color' ) ) . ' ;}</style>';
}


/**
 * Summary of lps_login_form_input_border_style
 *
 * Changing border style of input field
 */
function lps_login_form_input_border_style() {

	echo '<style> .login form .input{border-style:' . esc_attr( get_option( 'lps_login_form_input_border_style' ) ) . ';
	 border-width:' . esc_attr( get_option( 'lps_login_form_input_border_size' ) ) . 'px ;}</style>';
}

/**
 * Summary of lps_login_form_bg
 *
 * Adding Login form background image
 */
function lps_login_form_bg() {

	echo '<style> .login form {background-image:url(' . esc_attr( get_option( 'lps_login_form_bg' ) ) . '); background-size:cover; display:block !important;}</style>';
}

/**
 * Summary of form width
 *
 * Adding form width
 */
function lps_login_form_width() {
	// Get the value from the option, or use default value.
	$login_form_width = get_option( 'lps_login_form_width' );

	// Output the custom styles if the value is greater than 0.
	if ( $login_form_width > 320 ) {
		echo '<style> #login { width: ' . esc_attr( $login_form_width ) . 'px!important; }

</style>';
	}
}





/**
 * Summary of lps_redirect_to_login
 *
 * Check if no user logged in and private site option is selected redirect to selected page
 */
function lps_redirect_to_login() {
	/*
	Commented code
	if ( ! is_user_logged_in() && get_option( 'lps_enable_private_site' ) == 1 ) {
	wp_redirect( home_url( '/wp-login.php/?redirect_to =' . $_SERVER['REQUEST_URI'] ) );
	exit;
	}
	*/

	if ( ! is_user_logged_in() && get_option( 'lps_enable_private_site' ) == 1 ) {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		if ( ! empty( $request_uri ) ) {
			$redirect_url = home_url( '/wp-login.php/?redirect_to=' . rawurlencode( $request_uri ) );
			wp_safe_redirect( esc_url( $redirect_url ) );
			exit;
		}
	}
}

// Array of option keys to check.
$option_keys = array(
	'lps_private_login_url',
	'lps_private_login_url2',
	'lps_private_login_url3',
	'lps_private_login_url4',
	'lps_private_login_url5',
);

// Check if any of the option values is not empty.
$should_redirect = false;
foreach ( $option_keys as $option_key ) {
	$option_value = get_option( $option_key );
	if ( ! empty( $option_value ) ) {
		$should_redirect = true;
		break; // Exit the loop if any option value is not empty.
	}
}


/**
 * Summary of lps_redirectpage_to_login
 *
 * Check if no user is logged in and any of the login URL options are set, then redirect to the selected page
 */
function lps_redirectpage_to_login() {
	if ( ! is_user_logged_in() ) {
		$login_urls = array(
			'lps_private_login_url',
			'lps_private_login_url2',
			'lps_private_login_url3',
			'lps_private_login_url4',
			'lps_private_login_url5',
		);

		foreach ( $login_urls as $option_key ) {
			$option_value = get_option( $option_key );

			if ( ! empty( $option_value ) && is_page( $option_value ) ) {
				// Get the current request URI
				$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

				if ( ! empty( $request_uri ) ) {
					$redirect_url = home_url( '/wp-login.php?redirect_to=' . rawurlencode( $request_uri ) );
					wp_safe_redirect( $redirect_url );
					exit;
				}
			}
		}
	}
}
//add_action( 'template_redirect', 'lps_redirectpage_to_login' );



/**
 * Custom function to process items with additional arguments for Login Menu.
 *
 * @param  array $items An array of items to process.
 * @param  array $args  Additional arguments for processing.
 * @return array       Processed items.
 */





/**
 * Summary of lps_action_links
 *
 * @param  array  $links An array of existing plugin action links.
 * @param  string $file  Path to the plugin file.
 * @return array Modified array of plugin action links.
 */
function lps_action_links( $links, $file ) {
	if ( plugin_basename( __DIR__ . '/loginPageStyler.php' ) == $file ) {
		$links[] = '<a href="' . get_bloginfo( 'url' ) . '/wp-admin/admin.php?page=lps_option">Settings</a>';
	}
	return $links;
}

/**
 * Summary of lps_loginLockdown_install
 * Function to create database tabeles  for limit login attempts
 */
function lps_login_lockdown_install() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lps_login_fails';

	// Check if the table exists in the cache.
	$table_exists = wp_cache_get( $table_name, 'lps_login_fails' );
	if ( false === $table_exists ) {
		// If not in cache, check in the database.
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
		// Store in cache for future checks.
		wp_cache_set( $table_name, $table_exists, 'lps_login_fails' );
	}

	if ( $table_exists !== $table_name ) {
		$sql = 'CREATE TABLE ' . $table_name . " (
			`lpslogin_attempt_ID` bigint(20) NOT NULL AUTO_INCREMENT,
			`lpsuser_id` bigint(20) NOT NULL,
			`lpslogin_attempt_date` datetime NOT NULL default '0000-00-00 00:00:00',
			`lpslogin_attempt_IP` varchar(100) NOT NULL default '',
			PRIMARY KEY  (`lpslogin_attempt_ID`)
			);";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Update cache after creating table.
		wp_cache_set( $table_name, $table_name, 'lps_login_fails' );
	}

	$table_name = $wpdb->prefix . 'lps_lockdowns';

	// Check if the table exists in the cache.
	$table_exists = wp_cache_get( $table_name, 'lps_lockdowns' );

	if ( false === $table_exists ) {
		// If not in cache, check in the database.
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		// Store in cache for future checks.
		wp_cache_set( $table_name, $table_exists, 'lps_lockdowns' );
	}

	if ( $table_exists !== $table_name ) {
		$sql = 'CREATE TABLE ' . $table_name . " (
            `lpslockdown_ID` bigint(20) NOT NULL AUTO_INCREMENT,
            `lpsuser_id` bigint(20) NOT NULL,
            `lpslockdown_date` datetime NOT NULL default '0000-00-00 00:00:00',
            `lpsrelease_date` datetime NOT NULL default '0000-00-00 00:00:00',
            `lpslockdown_IP` varchar(100) NOT NULL default '',
            PRIMARY KEY  (`lpslockdown_ID`)
            );";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Update cache after creating table.
		wp_cache_set( $table_name, $table_name, 'lps_lockdowns' );
	}
}

register_activation_hook( __FILE__, 'lps_login_lockdown_install' );
// register_activation_hook( __FILE__, 'lps_run_install' );


/**
 * Summary of lps_login_text_logo
 *
 * Adding text logo for login page
 */
function lps_login_text_logo() {
	$text_logo            = get_option( 'lps_login_text_logo' );
	$text_logocolor       = get_option( 'lps_textlogo_color' );
	$text_logocolor_hover = get_option( 'lps_textlogo_color_hover' );

	if ( ! empty( $text_logo ) ) {
		// Output custom CSS to display the text logo and hide the default WordPress logo.
		echo '<style type="text/css">
            .login h1 a {
                background-image: none !important;
                text-indent: 0 !important;
                display: block;
                height: auto !important;
				width:auto !important;
                font-size: 45px !important;
                font-weight: bold !important;
                color: ' . esc_attr( $text_logocolor ) . ';
                text-decoration: none;
                font-family: Arial, sans-serif;
            }
				.login h1 a:hover {
            color: ' . esc_attr( $text_logocolor_hover ) . '!important; /* Hover color */
        }
        </style>';
		echo '<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                var loginLogo = document.querySelector(".login h1 a");
                if (loginLogo) {
                    loginLogo.textContent = "' . esc_html( $text_logo ) . '";
                }
            });
        </script>';
	}
}


/**
 * Summary of lps_auto_remember_me
 *
 * Adding auto auto remember
 */
function lps_auto_remember_me() {
	if ( ! is_user_logged_in() && get_option( 'lps_auto_rememberme' ) == '1' ) {
		?>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				var rememberMe = document.getElementById('rememberme');
				if (rememberMe) {
					rememberMe.checked = true;
				}
			});
		</script>
		<?php
	}
}


/**
 * Summary of lps_copyright_notice
 *
 * Adding copyright notice  to loin page
 */
function lps_copyright_notice() {
	$lpscompanyname = esc_html( get_option( 'lps_login_copyright' ) );
	if ( get_option( 'lps_login_copyright' ) !== '' ) {
		echo '<div class="copyright">&copy; ' . esc_html( gmdate( 'Y' ) ) . ' ' . esc_html( $lpscompanyname ) . '. All rights reserved.</div>';
	}
}

/**
 * Summary of lps_copyright_textalign
 *
 * Adding copyright notice  text align
 */
function lps_copyright_textalign() {
	echo ' <style>   .login .copyright{ 
            text-align: center !important; 
            font-size: 14px; 
            position: absolute; 
            left: 50%; 
            transform: translateX(-50%); 
        }</style>';
}
add_action( 'login_head', 'lps_copyright_textalign' );


/**
 * Adjusts the user session timeout based on the settings.
 *
 * @param int $expirein The default session timeout value in seconds.
 *
 * @return int The adjusted session timeout value in seconds.
 */
function lps_user_session_timeout( $expirein ) {
	$session_timeout = get_option( 'lps_login_session_expire' ); // Get the lps session timeout from your settings.

	if ( ! empty( $session_timeout ) && is_numeric( $session_timeout ) && $session_timeout > 0 ) {
		return $session_timeout * 60; // Convert minutes to seconds.
	}

	return $expirein; // Use default if  session timeout is not set or invalid.
}

/**
 * Summary of lps_boxshadow_style
 *
 * Adding box shadow style to form
 */
function lps_boxshadow_styles() {
	// Get box shadow properties from options.
	$box_shadow_horizontal = get_option( 'lps_box_shadow_horizontal', '0' );
	$box_shadow_vertical   = get_option( 'lps_box_shadow_vertical', '0' );
	$box_shadow_blur       = get_option( 'lps_box_shadow_blur', '10' );  // Original blur value.
	$box_shadow_spread     = get_option( 'lps_box_shadow_spread', '0' );   // Corrected spread value.
	$box_shadow_color_hex  = get_option( 'lps_box_shadow_color', ' 000000' );
	$box_shadow_opacity    = get_option( 'lps_box_shadow_opacity', '1' );

	// Convert hex color to rgba.
	$box_shadow_color_rgba = hex_to_rgba( $box_shadow_color_hex, $box_shadow_opacity );

	echo '<style type="text/css">';

	// Set box shadow style.
	echo '.login form {';
	echo '  box-shadow: ' . esc_attr( $box_shadow_horizontal ) . 'px ' . esc_attr( $box_shadow_vertical ) . 'px ' . esc_attr( $box_shadow_blur ) . 'px ' . esc_attr( $box_shadow_spread ) . 'px ' . esc_attr( $box_shadow_color_rgba ) . ';';
	echo '}';

	echo '</style>';
}


/**
 * Convert a hex color code to RGBA format.
 *
 * @param string $color   The hex color code to convert.
 * @param float  $opacity The opacity value, a float between 0 and 1.
 *
 * @return string|false The RGBA color code on success, or false on failure.
 */
/**
 * Convert a hex color code to RGBA format.
 *
 * @param string $color   The hex color code to convert.
 * @param float  $opacity The opacity value, a float between 0 and 1.
 *
 * @return string|false The RGBA color code on success, or false on failure.
 */
function hex_to_rgba( $color, $opacity = 1 ) {
	// Remove the hash at the start if it's there.
	$color = ltrim( $color, '#' );

	// Check if the input is valid
	if ( ! preg_match( '/^[0-9a-fA-F]{3,6}$/', $color ) ) {
		return false;
	}

	// Expand shorthand hex to full form (e.g. #abc -> #aabbcc)
	if ( strlen( $color ) == 3 ) {
		$color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
	}

	// Split into RGB components
	list($r, $g, $b) = str_split( $color, 2 );
	$r               = hexdec( $r );
	$g               = hexdec( $g );
	$b               = hexdec( $b );

	// Ensure opacity is between 0 and 1
	$opacity = max( 0, min( 1, $opacity ) );

	return "rgba($r, $g, $b, $opacity)";
}



/**
 * Enqueue styles and add custom animation to the login form based on the selected animation option.
 */
function lps_login_page_animation() {
	// Get the selected animation option from the WordPress options.
	$selected_animation = get_option( 'lps_login_animation', 'fadeIn' );

	// Enqueue the necessary stylesheets based on the selected animation.
	wp_enqueue_style( 'animate-css', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css', array(), '3.7.2' );

	// Add a custom style to apply the selected animation to the login form.
	echo '<style>';
	echo '#login {';
	echo '    animation-name: ' . esc_attr( $selected_animation ) . ';';
	echo '    animation-duration: 1.2s;';
	echo '    animation-fill-mode: both;';
	echo '}';
	echo '</style>';
}

/**
 * Enqueue styles  for changing login button width.
 */
function lps_login_button_size() {
	$button_width = get_option( 'lps_login_button_size' );

	echo '<style> .login .button-primary{margin-top:2px; width:' . esc_attr( $button_width ) . 'px; } </style> ';
}

/**
 * Summary of lps_login_form_color
 * function to add login form color
 */
function lps_notice_color() {
	// Get hex color value from options.
	$hex_color        = get_option( 'lps_notice_bgcolor' );
	$text_noticecolor = get_option( 'lps_notice_textcolor' );

	// Get opacity value from options or use default of 1.
	// Get opacity value from options or use default of 1.
	// $opacity = get_option('lps_notice_bgcolor_opacity');.
	$opacity = 1;
	// Convert hex color to RGBA format with opacity.
	$rgba_color = hex_to_rgba( $hex_color, $opacity );

	echo '<style> .login .message, .login .notice, .login .success { background-color: ' . esc_attr( $rgba_color ) . ' ; }</style>';
	echo '<style> .login .message, .login .notice, .login .success { color: ' . esc_attr( $text_noticecolor ) . ' ; }</style>';
}

require 'loginPageStylerOption.php';
require 'loginPageStylerTempSetting.php';
require 'loginPageStylerLogSettings.php';
require 'lpsBlacklistSettings.php';

require 'loginPageStylerLim.php';

require 'loginPageStylerBgSlideShow.php';

require 'loginPageStylerAdminUrl.php';

require 'loginPageStylerLog.php';

require 'lpsBlacklist.php';


require 'loginPageStylerTemp.php';

// Register activation and deactivation hooks
register_activation_hook( __FILE__, 'lps_create_login_logs_table' );
register_activation_hook( __FILE__, 'lps_schedule_log_cleanup' );
register_deactivation_hook( __FILE__, 'lps_clear_log_cleanup_schedule' );
register_activation_hook( __FILE__, 'lps_create_ip_blocking_table' );


if ( get_option( 'lps_login_on_off' ) == 1 ) {
	include 'lpsReCaptcha.php';
}

if ( get_option( 'lps_login_on_off' ) == '1' ) {
	include 'lpsFiltersAndActions.php';
}

$login_widget        = get_option( 'lps_login_widgetButton', 0 );
$register_widget     = get_option( 'lps_register_widgetButton', 0 );
$lostpassword_widget = get_option( 'lps_lostpassword_widgetButton', 0 );

// Check if at least one of the options is 1.
if ( $login_widget == 1 || $register_widget == 1 || $lostpassword_widget == 1 ) {
	include 'loginPageStylerWidget.php';
}

function custom_login_notice( $message ) {
	// Check if it's the login page (and not any other action like lost password or register)
	if ( strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false && ! isset( $_REQUEST['action'] ) ) {
		// Append your custom notice before any existing messages
		$custom_notice = '<div class="notice notice-info is-dismissible"><p>' . esc_html__( 'This is your custom notice message with default styling.', 'login-page-styler' ) . '</p></div>';
		return $custom_notice . $message;
	}
	// Return any existing messages without modification
	return $message;
}
// add_filter('login_message', 'custom_login_notice');

if ( get_option( 'lps_show_math_captcha' ) == '1' ) {
	add_action( 'login_form', 'lps_add_math_captcha' );
	add_filter( 'authenticate', 'lps_verify_math_captcha', 30, 3 );
}
function lps_add_math_captcha() {
	// Generate random numbers for the CAPTCHA
	$num1 = rand( 1, 9 );
	$num2 = rand( 1, 9 );

	// Create a unique nonce for the CAPTCHA
	$captcha_nonce = wp_create_nonce( 'lps_captcha_nonce' );
	$transient_key = 'lps_captcha_' . $captcha_nonce;

	// Store the result as an integer in the transient
	set_transient( $transient_key, (int) ( $num1 + $num2 ), 30 * MINUTE_IN_SECONDS );

	// Output the math question and hidden nonce field in the form
	echo '<p>
            <label for="math_captcha">Please solve: ' . $num1 . ' + ' . $num2 . ' = ?</label>
            <input type="text" name="math_captcha" id="math_captcha" class="input" value="" size="25" />
          </p>';
	echo '<input type="hidden" name="lps_captcha_nonce" value="' . esc_attr( $captcha_nonce ) . '" />';
}

function lps_verify_math_captcha( $user, $username, $password ) {
	if ( isset( $_POST['math_captcha'] ) && isset( $_POST['lps_captcha_nonce'] ) ) {
		// Sanitize and cast input to an integer
		$captcha_nonce = sanitize_text_field( $_POST['lps_captcha_nonce'] );
		$transient_key = 'lps_captcha_' . $captcha_nonce;

		// Retrieve the expected CAPTCHA result from the transient
		$expected_result = (int) get_transient( $transient_key );
		$user_input      = (int) $_POST['math_captcha'];

		// Log the expected result and user input for debugging
		error_log( 'Expected CAPTCHA result: ' . $expected_result );
		error_log( 'User input for CAPTCHA: ' . $user_input );

		// Check if the expected result exists and validate it
		if ( $expected_result === false ) {
			error_log( 'CAPTCHA transient expired or missing.' );
			return new WP_Error( 'captcha_error', __( '<strong>ERROR</strong>: CAPTCHA has expired. Please try again.' ) );
		}

		// Strictly compare the integer values
		if ( $expected_result !== $user_input ) {
			error_log( "CAPTCHA mismatch: expected {$expected_result}, got {$user_input}" );
			return new WP_Error( 'captcha_error', __( '<strong>ERROR</strong>: Incorrect math CAPTCHA.' ) );
		}

		// If correct, delete the transient to avoid reuse
		delete_transient( $transient_key );
	} else {
		return new WP_Error( 'captcha_error', __( '<strong>ERROR</strong>: CAPTCHA is required.' ) );
	}

	return $user;
}
