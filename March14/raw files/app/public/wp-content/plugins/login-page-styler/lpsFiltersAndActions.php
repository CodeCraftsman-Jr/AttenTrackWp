<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'login_head', 'lps_login_background_color' );

add_action( 'login_head', 'lps_login_label_color' );

add_action( 'login_head', 'lps_login_nav_color' );

//add_action( 'login_head', 'lps_login_nav_hover_color' );

add_action( 'login_head', 'lps_login_nav_bgcolor' );

//add_action( 'login_head', 'lps_login_nav_bgcolor_hover' );

// add_action('login_head', 'lps_login_form_border_radius');

add_action( 'login_head', 'lps_login_nav_link_hide' );

add_action( 'login_head', 'lps_login_logo_hide' );

add_action( 'login_head', 'lps_login_form_position' );

add_action( 'login_head', 'lps_login_form_color' );

add_action( 'login_head', 'lps_login_logo_msg_hide' );

add_action( 'login_head', 'lps_login_blog_link_hide' );

add_action( 'login_head', 'lps_login_label_size' );

// add_action('login_head', 'lps_login_form_input_feild_border_radius');

// add_action( 'login_head', 'lps_login_form_color_opacity' );.

//add_action( 'login_head', 'lps_login_custom_css' );

add_action( 'login_head', 'lps_login_button_border_radius' );

add_action( 'login_head', 'lps_login_form_input_feild_border_color' );

add_action( 'login_head', 'lps_login_remember_label_size' );

add_action( 'login_head', 'lps_body_bg_img' );

add_action( 'login_head', 'lps_login_logo' );

// add_action('login_head', 'lps_notice_color');

/*
if (get_option('lps_login_logo_width') > '80') {
	add_action('login_head', 'lps_login_logo_width');
}

if (get_option('lps_login_logo_height') > '80') {
	add_action('login_head', 'lps_login_logo_height');
}
*/

add_action( 'login_head', 'lps_login_button_color' );

// add_action('login_head', 'lps_login_button_color_hover');

add_action( 'login_head', 'lps_login_form_input_color_opacity' );

add_action( 'login_head', 'lps_login_form_input_text_color' );

add_action( 'login_head', 'lps_login_form_border_style' );

add_action( 'login_head', 'lps_login_form_input_border_style' );

add_action( 'login_head', 'lps_login_form_bg' );

add_action( 'login_head', 'lps_login_nav_size' );

add_filter( 'login_headerurl', 'lps_login_logo_link' );

add_filter( 'login_headertext', 'lps_login_logo_tittle' );

add_filter( 'plugin_action_links', 'lps_action_links', 10, 2 );

//add_action( 'login_head', 'lps_login_template_design' );

// add_filter( 'login_headertitle', 'lps_login_logo_tittle' );.

// add_action('login_head', 'lps_font_label');

// add_action('login_head', 'lps_font_inputtext');

// add_action('login_head', 'lps_font_btn');

// add_action('login_head', 'lps_font_link');

// add_action('login_head', 'lps_font_msg');

// add_action('login_head', 'lps_font_textlogo');

// add_action('login_head', 'lps_font_copyright');

add_action( 'login_head', 'lps_copyright_color' );

if ( get_option( 'lps_enable_private_site' ) == 1 ) {
	add_action( 'template_redirect', 'lps_redirect_to_login' );
}

if ( $should_redirect ) {
	// Add an action to template_redirect.
	add_action( 'template_redirect', 'lps_redirectpage_to_login' );
}

// add_action( 'wp_logout', 'lps_logout_redirect_user' );.

// add_filter('login_redirect', 'lps_login_redirect_user', 10, 3);

// add_action('login_head', 'lps_login_text_logo');

if ( get_option( 'lps_auto_rememberme' ) == '1' ) {
	add_action( 'login_form', 'lps_auto_remember_me' );
}

if ( get_option( 'lps_login_copyright' ) !== '' ) {
	add_action( 'login_footer', 'lps_copyright_notice' );
}

// Hook into the login_head action to output the custom styles.
add_action( 'login_head', 'lps_boxshadow_styles' );

// Hook the custom_login_page_animation function to the login_enqueue_scripts action.
// add_action('login_enqueue_scripts', 'lps_login_page_animation');

/*
if (get_option('lps_login_button_size') > 68) {
add_action('login_head', 'lps_login_button_size');
}
*/
// lang switcher.
if ( get_option( 'lps_login_lang_hide' ) == 1 ) {
	add_filter( 'login_display_language_dropdown', '__return_false' );
}


// Hook into the 'auth_cookie_expiration' filter.
if ( get_option( 'lps_login_session_expire' ) > 0 ) {
	add_filter( 'auth_cookie_expiration', 'lps_user_session_timeout', 10, 3 );
}

/*
menu.
if (get_option('lps_loginout_menu') == 1) {
	add_filter('wp_nav_menu_items', 'lps_login_logout_link', 10, 2);
}*/

add_action( 'login_enqueue_scripts', 'lps_login_form_width' );
