<?php
// Security check to prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function lps_menu() {
	add_menu_page(
		'Login Page Styler',             // Page title.
		'Login Page Styler',             // Menu title.
		'manage_options',                 // Capability.
		'lps_option',                     // Menu slug.
		'lps_settings_page',              // Callback function.
		'',                               // Icon (can leave empty).
		20                                 // Position.
	);

	// Add submenu for temporary access
	add_submenu_page(
		'lps_option',                     // Parent slug.
		'Temporary Access',               // Page title.
		'Temporary Access',               // Menu title.
		'manage_options',                 // Capability.
		'lps_temp_access',                // Menu slug.
		'lps_temp_access_page_callback'   // Callback function.
	);

	// Add submenu for blocking IP and regions
	add_submenu_page(
		'lps_option',                     // Parent slug
		'Block IP & Regions',             // Page title
		'Block IP & Regions',             // Menu title
		'manage_options',                 // Capability
		'lps_block_ip_regions',           // Menu slug
		'lps_display_ip_blocking'       // Callback function to handle blocking IPs and regions
	);

	// Add submenu for user login logs
	add_submenu_page(
		'lps_option',                     // Parent slug
		'User Login Logs',                // Page title
		'User Login Logs',                // Menu title
		'manage_options',                 // Capability
		'lps_user_login_logs',            // Menu slug
		'lps_display_login_logs'          // Callback function to display the logs
	);

	add_action( 'admin_init', 'lps_register_settings' );
}
add_action( 'admin_menu', 'lps_menu' );


// require_once 'lpsCustomizerSettingsPage.php';
add_action( 'init', 'lps_load_textdomain' );

/**
 * Summary of lps_load_textdomain
 * Function to load textdomain
 */
function lps_load_textdomain() {
	load_plugin_textdomain( 'login-page-styler', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}


/**
 * Summary of lps_register_settings
 * Funtion to register settings set by user
 */
function lps_register_settings() {

	register_setting( 'lps-settings-group', 'lps_login_logo_hide', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_logo_msg_hide', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_on_off', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_blog_link_hide', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_lang_hide', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_nav_link_hide', 'absint' );
	register_setting( 'lps-settings-group', 'lps_auto_rememberme', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_copyright ', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_login_session_expire', 'absint' );
	register_setting( 'lps-settings-group', 'lps_copyright_color', 'sanitize_hex_color' );

	register_setting( 'lps-settings-group', 'lps_login_logo_link', 'esc_url' );
	register_setting( 'lps-settings-group', 'lps_login_logo_tittle', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_login_logo', 'esc_url' );
	register_setting( 'lps-settings-group', 'lps_login_logo_width', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_logo_height', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_text_logo', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_textlogo_color', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_textlogo_color_hover', 'sanitize_text_field' );

	register_setting( 'lps-settings-group', 'lps_login_bg_repeat', 'sanitize_key' );
	register_setting( 'lps-settings-group', 'lps_recaptcha_theme', 'sanitize_key' );
	register_setting( 'lps-settings-group', 'lps_body_bg_img', 'esc_url' );
	register_setting( 'lps-settings-group', 'lps_login_background_color', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_login_background_image', 'esc_url' );
	register_setting( 'lps-settings-group', 'lps_slideshow_animation_style', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_slideshow_time', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_body_bg_slideshow', 'sanitize_lps_body_bg_slideshow' );

	register_setting( 'lps-settings-group', 'lps_login_form_input_color', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_login_form_input_color_opacity', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_login_form_border_style', 'sanitize_key' );
	register_setting( 'lps-settings-group', 'lps_login_form_input_border_style', 'sanitize_key' );
	register_setting( 'lps-settings-group', 'lps_login_form_input_border_size', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_form_width', 'absint' );
	// register_setting( 'lps-settings-group', 'lps_login_form_height', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_form_border_size', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_form_bg', 'esc_url' );
	register_setting( 'lps-settings-group', 'lps_login_form_color_opacity', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_login_form_border_radius', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_label_size', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_label_color', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_login_form_border_color', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_login_form_input_feild_border_color', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_login_remember_label_size', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_form_position', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_form_color', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_login_form_input_feild_border_radius', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_form_input_text_color', 'sanitize_hex_color' );

	// register_setting('lps-settings-group', 'lps_login_form_label_font');.

	register_setting( 'lps-settings-group', 'lps_box_shadow_vertical', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_box_shadow_horizontal', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_box_shadow_blur', 'absint' );
	register_setting( 'lps-settings-group', 'lps_box_shadow_color', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_box_shadow_opacity', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_box_shadow_spread', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_animation', 'sanitize_text_field' );

	register_setting( 'lps-settings-group', 'lps_login_nav_size', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_nav_color', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_login_nav_hover_color', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_login_nav_bgcolor', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_login_nav_bgcolor_hover', 'sanitize_hex_color' );

	register_setting( 'lps-settings-group', 'lps_login_button_color', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_login_button_border_color', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_login_button_color_hover', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_login_button_text_color', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_login_button_text_color_hover', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_login_button_border_color_hover', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_login_button_border_radius', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_button_size', 'absint' );
	register_setting( 'lps-settings-group', 'lps_notice_textcolor', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_notice_bgcolor', 'sanitize_hex_color' );
	register_setting( 'lps-settings-group', 'lps_notice_bgcolor_opacity', 'sanitize_text_field' );

	register_setting( 'lps-settings-group', 'lps_gfontlab', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_gfont_inputtext', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_gfontlink', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_gfontmsg', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_gfontbtn', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_gfonttext_logo', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_gfont_copyright', 'sanitize_text_field' );

	register_setting( 'lps-settings-group', 'lps_login_custom_css', 'sanitize_textarea_field' );

	register_setting( 'lps-settings-group', 'lps_layout', 'sanitize_text_field' );

	register_setting( 'lps-settings-group', 'lps_login_captcha', 'absint' );
	register_setting( 'lps-settings-group', 'lps_reg_captcha', 'absint' );
	register_setting( 'lps-settings-group', 'lps_lost_captcha', 'absint' );
	register_setting( 'lps-settings-group', 'lps_show_math_captcha', 'absint' );
	register_setting( 'lps-settings-group', 'rs_site_key', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'rs_private_key', 'sanitize_text_field' );

	register_setting( 'lps-settings-group', 'lps_enable_private_site', 'absint' );
	register_setting( 'lps-settings-group', 'lps_private_login_url', 'sanitize_key' );
	register_setting( 'lps-settings-group', 'lps_private_login_url2', 'sanitize_key' );
	register_setting( 'lps-settings-group', 'lps_private_login_url3', 'sanitize_key' );
	register_setting( 'lps-settings-group', 'lps_private_login_url4', 'sanitize_key' );
	register_setting( 'lps-settings-group', 'lps_private_login_url5', 'sanitize_key' );

	register_setting( 'lps-settings-group', 'lps_enable_lim', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_attempts', 'absint' );
	register_setting( 'lps-settings-group', 'lps_attempts_within', 'absint' );
	register_setting( 'lps-settings-group', 'lps_lock_time', 'absint' );

	register_setting( 'lps-settings-group', 'lps_loginout_menu', 'absint' );
	register_setting( 'lps-settings-group', 'lps_login_widgetButton', 'absint' );
	register_setting( 'lps-settings-group', 'lps_register_widgetButton', 'absint' );
	register_setting( 'lps-settings-group', 'lps_lostpassword_widgetButton', 'absint' );

	register_setting( 'lps-settings-group', 'lps_redirect_users', 'sanitize_key' );
	register_setting( 'lps-settings-group', 'lps_redirectafter_users', 'sanitize_key' );

	register_setting( 'lps-settings-group', 'lps_new_login_url', 'sanitize_text_field' );
	register_setting( 'lps-settings-group', 'lps_new_redirection_url', 'sanitize_text_field' );

	// Register settings for the temporary access submenu
	register_setting( 'lps-temp-access-settings-group', 'lps_temp_access_enabled' );
	register_setting( 'lps-temp-access-settings-group', 'lps_access_expiration_time' );
	register_setting( 'lps-temp-access-settings-group', 'lps_expiration_notifications' );
}

/**
 * Reset all plugin settings to default values.
 */
function lps_reset_settings() {
	// List of all options to reset
	
	// Check for capability: Only administrators can reset the settings
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
    }

   // Verify nonce for reset action
   if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'lps_reset_nonce' ) ) {
	wp_send_json_error( array( 'message' => 'Invalid reset nonce' ) );
}
	
	$options = array(
		'lps_login_logo_hide',
		'lps_login_logo_msg_hide',
		'lps_login_on_off',
		'lps_login_blog_link_hide',
		'lps_login_lang_hide',
		'lps_login_nav_link_hide',
		'lps_auto_rememberme',
		'lps_login_copyright',
		'lps_login_session_expire',
		'lps_copyright_color',
		'lps_login_logo_link',
		'lps_login_logo_tittle',
		'lps_login_logo',
		'lps_login_logo_width',
		'lps_login_logo_height',
		'lps_login_text_logo',
		'lps_textlogo_color',
		'lps_textlogo_color_hover',
		'lps_login_bg_repeat',
		'lps_body_bg_img',
		'lps_login_background_color',
		'lps_login_background_image',
		'lps_slideshow_animation_style',
		'lps_slideshow_time',
		'lps_body_bg_slideshow',
		'lps_login_form_input_color',
		'lps_login_form_input_text_color',
		'lps_login_form_input_color_opacity',
		'lps_login_form_border_style',
		'lps_login_form_input_border_style',
		'lps_login_form_input_border_size',
		'lps_login_form_width',
		'lps_login_form_bg',
		'lps_login_form_color_opacity',
		'lps_login_form_border_radius',
		'lps_login_label_size',
		'lps_login_label_color',
		'lps_login_form_border_color',
		'lps_login_form_input_feild_border_color',
		'lps_login_remember_label_size',
		'lps_login_form_position',
		'lps_login_form_color',
		'lps_login_form_input_feild_border_radius',
		'lps_box_shadow_vertical',
		'lps_box_shadow_horizontal',
		'lps_box_shadow_blur',
		'lps_box_shadow_color',
		'lps_box_shadow_opacity',
		'lps_box_shadow_spread',
		'lps_login_animation',
		'lps_login_nav_size',
		'lps_login_nav_color',
		'lps_login_nav_hover_color',
		'lps_login_button_color',
		'lps_login_button_border_color',
		'lps_login_button_color_hover',
		'lps_login_button_text_color',
		'lps_login_button_text_color_hover',
		'lps_login_button_border_color_hover',
		'lps_login_button_border_radius',
		'lps_login_button_size',
		'lps_gfontlab',
		'lps_gfont_inputtext',
		'lps_gfontlink',
		'lps_gfontmsg',
		'lps_gfontbtn',
		'lps_gfonttext_logo',
		'lps_gfont_copyright',
		'lps_login_custom_css',
		'lps_layout',
		'lps_login_captcha',
		'lps_reg_captcha',
		'lps_lost_captcha',
		'rs_site_key',
		'rs_private_key',
		'lps_enable_private_site',
		'lps_private_login_url',
		'lps_private_login_url2',
		'lps_private_login_url3',
		'lps_private_login_url4',
		'lps_private_login_url5',
		'lps_enable_lim',
		'lps_login_attempts',
		'lps_attempts_within',
		'lps_lock_time',
		'lps_loginout_menu',
		'lps_login_widgetButton',
		'lps_register_widgetButton',
		'lps_lostpassword_widgetButton',
		'lps_redirect_users',
		'lps_redirectafter_users',
		'lps_notice_textcolor',
		'lps_notice_bgcolor',
		'lps_notice_bgcolor_opacity',
		'lps_new_login_url',
		'lps_new_redirection_url',
		'lps_show_math_captcha',
		'lps_login_nav_bgcolor',
		'lps_login_nav_bgcolor_hover',
	);

	// Reset each option to its default value
	foreach ( $options as $option ) {
		delete_option( $option );
	}

	// Optionally, send a success response
	wp_send_json_success( 'Settings reset to default values.' );
}

// Register AJAX action
add_action( 'wp_ajax_lps_reset_settings', 'lps_reset_settings' );


function lps_handle_reset() {
	if ( isset( $_POST['lps_reset_settings'] ) && $_POST['lps_reset_settings'] == '1' ) {
		lps_reset_settings();
	}
}

/**
 * Sanitize the array of background image URLs set by the user.
 *
 * @param array $input The array of background image URLs.
 * @return array Sanitized array of background image URLs.
 */
function sanitize_lps_body_bg_slideshow( $input ) {
	$sanitized_input = array();

	// Sanitize each input URL.
	foreach ( $input as $url ) {
		$sanitized_input[] = esc_url_raw( $url );
	}

	return $sanitized_input;
}

/**
 * Summary of lps_delete_settings
 * Funtion to delet registered options
 */
function lps_delete_settings() {
	delete_option( 'lps_login_on_off' );
	delete_option( 'lps_login_logo_hide' );
	delete_option( 'lps_login_logo_msg_hide' );
	delete_option( 'lps_login_nav_link_hide' );
	delete_option( 'lps_login_blog_link_hide' );
}

register_deactivation_hook( __FILE__, 'lps_delete_settings' );

add_action( 'admin_enqueue_scripts', 'lps_enqueue_color_picker' );

/**
 * Function to enqueue color picker, media upload, fonts, and CSS
 */
function lps_enqueue_color_picker() {
	if ( isset( $_GET['page'] ) && ( 'lps_option' === $_GET['page'] ) ) {

		// Check if jQuery is enqueued
		if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery' );
		}
		// Enqueue necessary styles and scripts
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'custom_wp_admin_css', plugins_url( 'css/style.css', __FILE__ ), array(), '2.1.12' );
		wp_enqueue_style( 'font_select', plugins_url( 'css/fontselect.css', __FILE__ ), array(), '1.001' );
		wp_enqueue_script( 'g-fonts-script', plugins_url( 'js/jquery.fontselect.js', __FILE__ ), array( 'jquery' ), '1.021', true );

		wp_enqueue_script( 'login-page-styler', plugins_url( 'js/loginPageStyler.js', __FILE__ ), array( 'wp-color-picker', 'jquery' ), '1.13', true );
		wp_enqueue_script( 'login-page-styler-mapping', plugins_url( 'js/loginPageStylerMappingJs.js', __FILE__ ), array( 'wp-color-picker', 'jquery' ), '1.1812', true );
		// Pass the option value to the script
		$lps_layout = esc_js( trim( get_option( 'lps_layout' ) ) );
		wp_localize_script( 'login-page-styler-mapping', 'lpsData', array( 'layout' => $lps_layout ) );
	}
}


function enqueue_media_uploader_scripts() {
	if ( is_admin() ) {
		wp_enqueue_media();
		wp_enqueue_script( 'lps-media-uploader', plugin_dir_url( __FILE__ ) . 'js/lpsMediauploader.js', array( 'jquery' ), '1.01', true );
	}
}

add_action( 'admin_enqueue_scripts', 'enqueue_media_uploader_scripts' );


/// Enqueue JavaScript for admin page
function lps_enqueue_admin_scripts() {
	// Ensure we're loading the script on the specific admin page
	if (isset($_GET['page']) && $_GET['page'] === 'lps_temp_access') {

	wp_enqueue_script( 'lps-admin-script', plugins_url( 'js/lpsTempAjax.js', __FILE__ ), array( 'jquery' ), '0.1', true );

	// Pass the AJAX URL and nonce to the script
	wp_localize_script(
		'lps-admin-script',
		'lps_ajax_obj',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'lps_ajax_nonce' ), // Create nonce for security
		)
	);
}
}
add_action( 'admin_enqueue_scripts', 'lps_enqueue_admin_scripts' );



function lps_enqueue_reset_nonce_script() {
    if (isset($_GET['page']) && $_GET['page'] === 'lps_option') {
        wp_enqueue_script('lps-reset-settings-script', plugin_dir_url(__FILE__) . 'js/lpsResetAjax.js', array('jquery'), '1.0', true);
        wp_localize_script('lps-reset-settings-script', 'lps_reset_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('lps_reset_nonce')
        ));
    }
}
add_action('admin_enqueue_scripts', 'lps_enqueue_reset_nonce_script');


/**
 * Summary of lps_settings_page
 * Funtion to make admin settings page
 */
function lps_settings_page() {
	?>
	<?php settings_errors(); ?>
	<div class="main">

		<input class="tabin" id="tab1" type="radio" name="tabs" checked>
		<label class="tabla" for="tab1">Styling</label>

		<input class="tabin" id="tab2" type="radio" name="tabs">
		<label class="tabla" for="tab2">Template</label>

		<input class="tabin" id="tab3" type="radio" name="tabs">
		<label class="tabla" for="tab3">Google Or Math Captcha</label>

		<input class="tabin" id="tab4" type="radio" name="tabs">
		<label class="tabla" for="tab4">Login/Logout Menu Item</label>

		<input class="tabin" id="tab5" type="radio" name="tabs">
		<label class="tabla" for="tab5">Login URL/ Login Redirect</label>

		<input class="tabin" id="tab6" type="radio" name="tabs">
		<label class="tabla" for="tab6">Login Protected</label>


		<input class="tabin" id="tab7" type="radio" name="tabs">
		<label class="tabla" for="tab7">Limit Login</label>

		<input class="tabin" id="tab8" type="radio" name="tabs">
		<label class="tabla" for="tab8">Blocked IP </label>

		<div class="content">
			<div id="content1">
				<div class='wrap'>


					<form method="post" action="options.php">

						<?php settings_fields( 'lps-settings-group' ); ?>

						<?php do_settings_sections( 'lps_option' ); ?>

						<h1><?php esc_html_e( 'Login Page Styler' ); ?></h1>

						<h3 style="color: #FFBA00;">
							<?php esc_html_e( 'Enable plugin and save settings for styling to take effect on Live Preview and Frontend  Login page ', 'login-page-styler' ); ?>
						</h3>


						<table class="form-table">
							<tr valign='top'>
								<th scope='row'><?php esc_html_e( 'Enable Plugin :', 'login-page-styler' ); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_login_on_off" class="onoffswitch-checkbox"
											id="myonoffswitch" value='1' <?php checked( 1, absint( get_option( 'lps_login_on_off' ) ) ); ?> />
										<label class="onoffswitch-label" for="myonoffswitch">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
										<p></p>
									</div>
								</td>
							</tr>

							<tr valign='top'>
								<th scope='row'><?php esc_html_e( 'Hide Login Logo', 'login-page-styler' ); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_login_logo_hide" class="onoffswitch-checkbox"
											id="myonoffswitch2" value='1' <?php checked( 1, absint( get_option( 'lps_login_logo_hide' ) ) ); ?> />
										<label class="onoffswitch-label" for="myonoffswitch2">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>


							<tr valign='top'>
								<th scope='row'><?php esc_html_e( 'Hide Login Error Msg', 'login-page-styler' ); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_login_logo_msg_hide" class="onoffswitch-checkbox"
											id="myonoffswitch3" value='1' <?php checked( 1, absint( get_option( 'lps_login_logo_msg_hide' ) ) ); ?> />
										<label class="onoffswitch-label" for="myonoffswitch3">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>



							<tr valign='top'>
								<th scope='row'><?php esc_html_e( 'Hide Lost Password Link', 'login-page-styler' ); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_login_nav_link_hide" class="onoffswitch-checkbox"
											id="myonoffswitch4" value='1' <?php checked( 1, absint( get_option( 'lps_login_nav_link_hide' ) ) ); ?> />
										<label class="onoffswitch-label" for="myonoffswitch4">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>


							<tr valign='top'>
								<th scope='row'><?php esc_html_e( 'Hide Back to Blog Link', 'login-page-styler' ); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_login_blog_link_hide" class="onoffswitch-checkbox"
											id="myonoffswitch5" value='1' <?php checked( 1, absint( get_option( 'lps_login_blog_link_hide' ) ) ); ?> />
										<label class="onoffswitch-label" for="myonoffswitch5">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>

							<tr valign='top'>
								<th scope='row'>
									<?php esc_html_e( 'Hide Language switcher on Login ', 'login-page-styler' ); ?>
								</th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_login_lang_hide" class="onoffswitch-checkbox"
											id="myonoffswitch6" value='1' <?php checked( 1, absint( get_option( 'lps_login_lang_hide' ) ) ); ?> />
										<label class="onoffswitch-label" for="myonoffswitch6">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>

							<tr valign='top'>
								<th scope='row'><?php esc_html_e( 'Auto Remember me  ', 'login-page-styler' ); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_auto_rememberme" class="onoffswitch-checkbox"
											id="myonoffswitch7" value='1' <?php checked( 1, absint( get_option( 'lps_auto_rememberme' ) ) ); ?> />
										<label class="onoffswitch-label" for="myonoffswitch7">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Login Session Expire', 'login-page-styler' ); ?></th>
								<td><label for="lps_login_session_expire">
										<input type="number" id="lps_login_session_expire" name="lps_login_session_expire"
											value="<?php echo esc_attr( get_option( 'lps_login_session_expire' ) ); ?>" />
										<p class="description">
											<?php esc_html_e( 'Set the session expiration time in minutes. e.g: 10 ,Leave empty or 0 for default wp seesion expiration', 'login-page-styler' ); ?>
										</p>
									</label></td>
							</tr>

						</table>


						<style>
							.wrapper th {
								color: #666;
								font-size: 1em;
								padding-top: 7px;

							}


							.wrapper td {
								padding-left: 1px;
							}

							.wrapper h3 a,
							p a {
								text-decoration: none;
							}

							.wrapper td p {
								color: #666;
								font-size: 1em;
							}

							.wrapper {
								display: flex;
							}

							.accordion-container {
								flex: 1;
								width: 23%;
								margin-left: -11px;
							}

							.accordion input[type="checkbox"] {
								display: none;
							}

							.accordion-label {
								display: block;
								background-color: #1D2327;
								color: #fff;
								padding: 15px;
								cursor: pointer;
								font-weight: bold;
								border: 1px solid #FFBA00;
								border-bottom: none;
								position: relative;
								transition: background-color 0.3s;
								text-align: center;
							}

							.accordion-label:hover {
								background-color: #FFBA00;
							}

							.accordion-content {
								display: none;
								padding: 0px;
								border: 0px solid #2980b9;
								border-top: none;
								background-color: #ffffff;
								color: #333;
								overflow-y: auto;
								max-height: 400px;
								/* Adjust the maximum height as needed */
							}

							.accordion input[type="checkbox"]:checked+.accordion-label+.accordion-content {
								display: block;
							}

							.accordion input[type="checkbox"]:not(:checked)+.accordion-label+.accordion-content {
								display: none;
							}

							.accordion input[type="checkbox"]+.accordion-label::before {
								content: "+";
								float: right;
								font-size: 18px;
								line-height: 18px;
								transition: transform 0.3s;
							}

							.accordion input[type="checkbox"]:checked+.accordion-label::before {
								content: "+";
								transform: rotate(45deg);
							}

							.preview-container {
								padding: 0px;
								border: 1px solid #ddd;
								background-color: #f9f9f9;
								color: #333;
								width: 82%;
							}

							/*  .independent-preview {
														Style the independent preview here 
													}
													*/

							.accordion-content table {
								max-width: 100%;
								width: auto;
							}

							.accordion-content label {
								color: #FFBA00;
								font-weight: 600;
							}
						</style>

						<!--<label for="inputField">Enter Text:</label>
						<input type="text" class="color_picker" id="inputFieldlps">

						<div id="preview">Live Preview: <span id="previewText"></span></div> -->

						<h3 style="color: #FFBA00;" class="description">
							<?php
							echo esc_html__( 'Premium Feature will also work on Live Preview. To apply Premium Feature on Login, you have to ', 'login-page-styler' ) .
								'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Buy Premium', 'login-page-styler' ) . '</a>' .
								esc_html__( '.', 'login-page-styler' );
							?>
						</h3>
						<div class="wrapper">
							<div class="accordion-container">
								<div class="accordion">
									<input type="checkbox" id="section1" name="accordion">
									<label class="accordion-label" for="section1">Logo</label>
									<div class="accordion-content">

										<table class="form-table">

											<!-- Logo Link -->
											<tr valign="top">
												<td>
													<label
														for="lps_login_logo_link"><?php esc_html_e( 'Logo Link', 'login-page-styler' ); ?></label><br />
													<input type="text" placeholder="your-site.com" id="lps_login_logo_link"
														name="lps_login_logo_link" size="25"
														value="<?php echo esc_url( get_option( 'lps_login_logo_link' ) ); ?>" />
													<p class="description">
														<?php esc_html_e( 'It will redirect users when the logo is clicked.', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>

											<!-- Logo Title -->
											<tr valign="top">
												<td>
													<label
														for="lps_login_logo_tittle"><?php esc_html_e( 'Logo Title', 'login-page-styler' ); ?></label><br />
													<input type="text" size="25" id="lps_login_logo_tittle"
														name="lps_login_logo_tittle"
														value="<?php echo esc_attr( get_option( 'lps_login_logo_tittle' ) ); ?>" />
													<p class="description">
														<?php esc_html_e( 'Enter the title for the logo, e.g., Powered by Your Site.', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>

											<!-- Logo Image -->
											<tr valign="top">
												<td>
													<label
														for="lps_login_logo"><?php esc_html_e( 'Logo Image', 'login-page-styler' ); ?></label><br />
													<input id="lps_login_logo" type="text" name="lps_login_logo"
														value="<?php echo esc_url( get_option( 'lps_login_logo' ) ); ?>"
														size="25" />
													<input class="lps-upload-button button" type="button"
														value="Upload Image" />
													<p class='description'>
														<?php esc_html_e( 'Use Logo', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>

											<!-- Logo Width -->
											<tr valign="top">
												<td>
													<label
														for="lps_login_logo_width"><?php esc_html_e( 'Logo Width', 'login-page-styler' ); ?></label><br />
													<input type='range' id='lps_login_logo_width'
														name='lps_login_logo_width' min='0' max='350'
														value='<?php echo absint( get_option( 'lps_login_logo_width' ) ); ?>'
														oninput="this.form.amountInputW.value=this.value" />
													<input type="number" name="amountInputW" min="0" max="350"
														value='<?php echo absint( get_option( 'lps_login_logo_width' ) ); ?>'
														size='2'
														oninput="this.form.lps_login_logo_width.value=this.value" />px
													<p class="description">
														<?php
														esc_html_e( 'Change logo width up to 350 px.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

											<!-- Logo Height -->
											<tr valign="top">
												<td>
													<label
														for="lps_login_logo_height"><?php esc_html_e( 'Logo Height', 'login-page-styler' ); ?></label><br />
													<input type='range' id='lps_login_logo_height'
														name='lps_login_logo_height' min='0' max='200'
														value='<?php echo absint( get_option( 'lps_login_logo_height' ) ); ?>'
														oninput="this.form.amountInputH.value=this.value" />
													<input type="number" name="amountInputH" min="0" max="200"
														value='<?php echo absint( get_option( 'lps_login_logo_height' ) ); ?>'
														size='4'
														oninput="this.form.lps_login_logo_height.value=this.value" />px
													<p class="description">
														<?php
														echo esc_html__( 'Change logo height up to 200 px.', 'login-page-styler' );
														?>
													</p>

												</td>
											</tr>

											<!-- Text Logo -->
											<tr valign="top">
												<td>
													<label
														for="lps_login_text_logo"><?php esc_html_e( 'Text Logo', 'login-page-styler' ); ?></label><br />
													<input type="text" id="lps_login_text_logo" name="lps_login_text_logo"
														value="<?php echo esc_attr( get_option( 'lps_login_text_logo' ) ); ?>" />
													<p class="description">
														<?php esc_html_e( 'Enter Text Logo  .', 'login-page-styler' ); ?>
													</p>
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>


											<!-- Text Logo Color -->
											<tr>
												<td>
													<label
														for='lps_textlogo_color'><?php esc_html_e( 'Text Logo Color', 'login-page-styler' ); ?></label><br />
													<input type='text' class='color_picker' id='lps_textlogo_color'
														name='lps_textlogo_color'
														value='<?php echo esc_attr( get_option( 'lps_textlogo_color' ) ); ?>' />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

											<!-- Text Logo Color Hover -->
											<tr>
												<td>
													<label
														for='lps_textlogo_color_hover'><?php esc_html_e( 'Text Logo Color Hover', 'login-page-styler' ); ?></label><br />
													<input type='text' class='color_picker' id='lps_textlogo_color_hover'
														name='lps_textlogo_color_hover'
														value='<?php echo esc_attr( get_option( 'lps_textlogo_color_hover' ) ); ?>' />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

											<!-- Google font for Text Logo -->
											<tr valign="top">
												<td>
													<label
														for="lps_gfonttext_logo"><?php esc_html_e( 'Google font for Text Logo', 'login-page-styler' ); ?></label><br />
													<input name="lps_gfonttext_logo" id="lps_gfonttext_logo"
														class="lps_textlogofont" type="text"
														value="<?php echo esc_attr( get_option( 'lps_gfonttext_logo' ) ); ?>" />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section2" name="accordion">
									<label class="accordion-label" for="section2">Background</label>
									<div class="accordion-content">

										<table class="form-table">
											<tr valign="top">
												<td>
													<label
														for="lps_login_background_color"><?php esc_html_e( 'Background Color', 'login-page-styler' ); ?></label><br />
													<input type="text" class="color_picker" id="lps_login_background_color"
														name="lps_login_background_color"
														value="<?php echo esc_attr( get_option( 'lps_login_background_color' ) ); ?>" />
												</td>
											</tr>

											<tr valign="top">
												<td>
													<label
														for="lps_body_bg_img"><?php esc_html_e( 'Background Image', 'login-page-styler' ); ?></label><br />
													<input id="lps_body_bg_img" type="text" name="lps_body_bg_img"
														value="<?php echo esc_url( get_option( 'lps_body_bg_img' ) ); ?>"
														size="25" /></br>
													<input class="lps-upload-button button" type="button"
														value="Upload Image" />
													<p class='description'>
														<?php esc_html_e( 'Upload or Select  Background Image', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>

											<tr valign="top">
												<td>
													<label for="lps_login_bg_repeat">
														<?php esc_html_e( 'Login Body Background Image Repeat', 'login-page-styler' ); ?>
													</label><br />
													<select name='lps_login_bg_repeat' id='lps_login_bg_repeat'>
														<option value='no-repeat' <?php selected( sanitize_key( get_option( 'lps_login_bg_repeat' ) ), 'no-repeat' ); ?>>No Repeat</option>
														<option value='repeat-x' <?php selected( sanitize_key( get_option( 'lps_login_bg_repeat' ) ), 'repeat-x' ); ?>>Repeat X</option>
														<option value='repeat-y' <?php selected( sanitize_key( get_option( 'lps_login_bg_repeat' ) ), 'repeat-y' ); ?>>Repeat Y</option>
													</select>
												</td>
											</tr>


										</table>

										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section3" name="accordion">
									<label class="accordion-label" for="section3">Form</label>
									<div class="accordion-content">

										<table class="form-table">


											<tr valign="top">
												<td><label
														for="lps_login_form_bg"><?php esc_html_e( 'Form Background Image', 'login-page-styler' ); ?></label><br />
													<input id="lps_login_form_bg" type="text" name="lps_login_form_bg"
														value="<?php echo esc_url( get_option( 'lps_login_form_bg' ) ); ?>"
														size="25" />
													<input class="lps-upload-button" type="button" value="Upload Image" />
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_form_position'><?php esc_html_e( 'Form Position', 'login-page-styler' ); ?></label><br />
													<select id="lps_login_form_position" name="lps_login_form_position">
														<option value='1' <?php selected( absint( get_option( 'lps_login_form_position' ) ), '1' ); ?>>Middle-Center</option>
														<option value='2' <?php selected( absint( get_option( 'lps_login_form_position' ) ), '2' ); ?>>Middle-Left</option>
														<option value='3' <?php selected( absint( get_option( 'lps_login_form_position' ) ), '3' ); ?>>Middle-Right</option>
														<option value='4' <?php selected( absint( get_option( 'lps_login_form_position' ) ), '4' ); ?>>Top-Center</option>
														<option value='5' <?php selected( absint( get_option( 'lps_login_form_position' ) ), '5' ); ?>>Top-Left</option>
														<option value='6' <?php selected( absint( get_option( 'lps_login_form_position' ) ), '6' ); ?>>Top-Right</option>
														<option value='7' <?php selected( absint( get_option( 'lps_login_form_position' ) ), '7' ); ?>>Bottom-Center</option>
														<option value='8' <?php selected( absint( get_option( 'lps_login_form_position' ) ), '8' ); ?>>Bottom-Left</option>
														<option value='9' <?php selected( absint( get_option( 'lps_login_form_position' ) ), '9' ); ?>>Bottom-Right</option>
													</select>
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_animation'><?php esc_html_e( 'Form Animation', 'login-page-styler' ); ?></label><br />
													<select id="lps_login_animation" name="lps_login_animation">
														<option value="fadeIn" <?php selected( get_option( 'lps_login_animation' ), 'fadeIn' ); ?>>Fade
															In</option>
														<option value="slideInLeft" <?php selected( get_option( 'lps_login_animation' ), 'slideInLeft' ); ?>>
															Slide In from Left</option>
														<option value="bounceIn" <?php selected( get_option( 'lps_login_animation' ), 'bounceIn' ); ?>>
															Bounce In</option>
														<option value="rotateIn" <?php selected( get_option( 'lps_login_animation' ), 'rotateIn' ); ?>>
															Rotate In</option>
														<option value="zoomIn" <?php selected( get_option( 'lps_login_animation' ), 'zoomIn' ); ?>>Zoom
															In</option>
														<option value="flash" <?php selected( get_option( 'lps_login_animation' ), 'flash' ); ?>>Flash
														</option>
														<option value="pulse" <?php selected( get_option( 'lps_login_animation' ), 'pulse' ); ?>>Pulse
														</option>
														<option value="shake" <?php selected( get_option( 'lps_login_animation' ), 'shake' ); ?>>Shake
														</option>
														<option value="rollIn" <?php selected( get_option( 'lps_login_animation' ), 'rollIn' ); ?>>Roll
															In</option>
														<option value="swing" <?php selected( get_option( 'lps_login_animation' ), 'swing' ); ?>>Swing
														</option>
														<option value="rubberBand" <?php selected( get_option( 'lps_login_animation' ), 'rubberBand' ); ?>>
															Rubber Band</option>
														<option value="tada" <?php selected( get_option( 'lps_login_animation' ), 'tada' ); ?>>Tada
														</option>
														<option value="jello" <?php selected( get_option( 'lps_login_animation' ), 'jello' ); ?>>Jello
														</option>
														<!-- Add more animation options as needed -->
													</select>
													<p class="description">
														<?php esc_html_e( 'Select login Form animation.', 'login-page-styler' ); ?>
													</p>
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

											<tr>
												<td><label
														for='lps_login_form_color'><?php esc_html_e( 'Form Color', 'login-page-styler' ); ?></label><br />
													<input type='text' class='color_picker' id='lps_login_form_color'
														name='lps_login_form_color'
														value='<?php echo esc_attr( get_option( 'lps_login_form_color' ) ); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_form_color_opacity'><?php esc_html_e( 'Form Opacity', 'login-page-styler' ); ?></label><br />
													<input type='range' step='0.01' min='0' max='1'
														id='lps_login_form_color_opacity'
														name='lps_login_form_color_opacity'
														value='<?php echo esc_attr( get_option( 'lps_login_form_color_opacity', '1' ) ); ?>'
														oninput="this.form.amountInputFormOpacity.value=this.value" />
													<input type='number' step='0.01' min='0' max='1'
														name='amountInputFormOpacity'
														value='<?php echo esc_attr( get_option( 'lps_login_form_color_opacity', '1' ) ); ?>'
														size='4'
														oninput="this.form.lps_login_form_color_opacity.value=this.value" />
													<p class='description'>
														<?php esc_html_e( 'Login form Opacity/transparency, Select color then change opacity', 'login-page-styler' ); ?>
													</p>
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_form_border_radius'><?php esc_html_e( 'Form Border Radius', 'login-page-styler' ); ?></label><br />
													<input type='range' id='lps_login_form_border_radius'
														name='lps_login_form_border_radius' min='0' max='50'
														value='<?php echo absint( get_option( 'lps_login_form_border_radius' ) ); ?>'
														oninput="this.form.amountInput4.value=this.value" />
													<input type="number" name="amountInput4" min="0" max="50"
														value='<?php echo absint( get_option( 'lps_login_form_border_radius' ) ); ?>'
														size='4'
														oninput="this.form.lps_login_form_border_size.value=this.value" />px
													<p class="description">
														<?php esc_html_e( ' Form border radius', 'login-page-styler' ); ?>
													</p>
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>


											<tr valign="top">
												<td><label
														for="lps_login_form_border_size"><?php esc_html_e( 'Form Border Style', 'login-page-styler' ); ?></label><br />
													<input type='range' id='lps_login_form_border_size'
														name='lps_login_form_border_size' min='0' max='10'
														value='<?php echo absint( get_option( 'lps_login_form_border_size' ) ); ?>'
														oninput="this.form.amountInput3.value=this.value" />
													<input type="number" name="amountInput3" min="0" max="10"
														value='<?php echo absint( get_option( 'lps_login_form_border_size' ) ); ?>'
														size='4'
														oninput="this.form.lps_login_form_border_size.value=this.value" />px
													<p class="description">
														<?php esc_html_e( ' Form border size', 'login-page-styler' ); ?>
													</p></br>

													</br>
													<label for="lps_login_form_border_style"></label>
													<select name='lps_login_form_border_style'
														id='lps_login_form_border_style'>
														<option value='none' <?php selected( sanitize_key( get_option( 'lps_login_form_border_style' ) ), 'none' ); ?>>None</option>
														<option value='solid' <?php selected( sanitize_key( get_option( 'lps_login_form_border_style' ) ), 'solid' ); ?>>Solid</option>
														<option value='dashed' <?php selected( sanitize_key( get_option( 'lps_login_form_border_style' ) ), 'dashed' ); ?>>Dashed</option>
														<option value='dotted' <?php selected( sanitize_key( get_option( 'lps_login_form_border_style' ) ), 'dotted' ); ?>>Dotted</option>
														<option value='double' <?php selected( sanitize_key( get_option( 'lps_login_form_border_style' ) ), 'double' ); ?>>Double</option>
													</select>
													<p class="description">
														<?php esc_html_e( ' Form border style,', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_form_border_color'><?php esc_html_e( 'Form Border Color', 'login-page-styler' ); ?></label><br />
													<input type='text' class='color_picker' id='lps_login_form_border_color'
														name='lps_login_form_border_color'
														value='<?php echo esc_attr( get_option( 'lps_login_form_border_color' ) ); ?>' />
												</td>
											</tr>


											<tr valign='top'>
												<td><label
														for='lps_login_form_width'><?php esc_html_e( 'Form Width', 'login-page-styler' ); ?></label><br />
													<input type='range' id='lps_login_form_width'
														name='lps_login_form_width' min='320' max='1000'
														value='<?php echo max( 320, absint( get_option( 'lps_login_form_width' ) ) ); ?>'
														oninput="this.form.amountInputFw.value=this.value" />
													<input type="number" name="amountInputFw" min="320" max="1000"
														value='<?php echo max( 320, absint( get_option( 'lps_login_form_width' ) ) ); ?>'
														size='4'
														oninput="this.form.lps_login_form_width.value=this.value" />px
													<p class='description'>
														<?php esc_html_e( 'Default width for form is 320', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_label_color'><?php esc_html_e( 'Label Color', 'login-page-styler' ); ?></label><br />
													<input type='text' class='color_picker' id='lps_login_label_color'
														name='lps_login_label_color'
														value='<?php echo esc_attr( get_option( 'lps_login_label_color' ) ); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_label_size'><?php esc_html_e( 'Label Size', 'login-page-styler' ); ?></label><br />
													<input type='range' id='lps_login_label_size'
														name='lps_login_label_size' min='14' max='30'
														value='<?php echo max( 14, absint( get_option( 'lps_login_label_size' ) ) ); ?>'
														oninput="this.form.amountInput.value=this.value" />
													<input type="number" name="amountInput" min="14" max="30"
														value='<?php echo max( 14, absint( get_option( 'lps_login_label_size' ) ) ); ?>'
														size='4'
														oninput="this.form.lps_login_label_size.value=this.value" />px
												</td>
											</tr>

											<tr>
												<td><label
														for='lps_login_form_input_color'><?php esc_html_e( 'Input Field Color', 'login-page-styler' ); ?></label><br />
													<input type='text' class='color_picker' id='lps_login_form_input_color'
														name='lps_login_form_input_color'
														value='<?php echo esc_attr( get_option( 'lps_login_form_input_color' ) ); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_form_input_color_opacity'><?php esc_html_e( 'Input Field Opacity', 'login-page-styler' ); ?></label><br />
													<input type='range' step='0.01' min='0' max='1'
														id='lps_login_form_input_color_opacity'
														name='lps_login_form_input_color_opacity'
														value='<?php echo esc_attr( get_option( 'lps_login_form_input_color_opacity', '1' ) ); ?>'
														oninput="this.form.amountInputInputOpacity.value=this.value" />
													<input type='number' step='0.01' min='0' max='1'
														name='amountInputInputOpacity'
														value='<?php echo esc_attr( get_option( 'lps_login_form_input_color_opacity', '1' ) ); ?>'
														size='4'
														oninput="this.form.lps_login_form_input_color_opacity.value=this.value" />
													<p class='description'>
														<?php esc_html_e( ' Input-Field Opacity. This option make input field transparent', 'login-page-styler' ); ?>
													</p>
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

											<tr>
												<td><label
														for='lps_login_form_input_text_color'><?php esc_html_e( 'Input Field Text Color', 'login-page-styler' ); ?></label><br />
													<input type='text' class='color_picker'
														id='lps_login_form_input_text_color'
														name='lps_login_form_input_text_color'
														value='<?php echo esc_attr( get_option( 'lps_login_form_input_text_color' ) ); ?>' />
												</td>
											</tr>


											<tr valign='top'>
												<td><label
														for='lps_login_form_input_feild_border_color'><?php esc_html_e( 'Input Field Border Color', 'login-page-styler' ); ?></label><br />
													<input type='text' class='color_picker'
														id='lps_login_form_input_feild_border_color'
														name='lps_login_form_input_feild_border_color'
														value='<?php echo esc_attr( get_option( 'lps_login_form_input_feild_border_color' ) ); ?>' />
												</td>
											</tr>

											<tr valign="top">
												<td><label
														for="lps_login_form_input_border_size"><?php esc_html_e( 'Input Field Border Style', 'login-page-styler' ); ?></label><br />
													<input type='range' id='lps_login_form_input_border_size'
														name='lps_login_form_input_border_size' min='0' max='10'
														value='<?php echo absint( get_option( 'lps_login_form_input_border_size' ) ); ?>'
														oninput="this.form.amountInput5.value=this.value" />
													<input type="number" name="amountInput5" min="0" max="10"
														value='<?php echo absint( get_option( 'lps_login_form_input_border_size' ) ); ?>'
														size='4'
														oninput="this.form.lps_login_form_border_size.value=this.value" />px
													<p class="description">
														<?php esc_html_e( 'Input Field border size.', 'login-page-styler' ); ?>
													</p>

													</br>
													<label for="lps_login_form_input_border_style"></label><br />
													<select name='lps_login_form_input_border_style'
														id='lps_login_form_input_border_style'>
														<option value='none' <?php selected( sanitize_key( get_option( 'lps_login_form_input_border_style' ) ), 'none' ); ?>>None</option>
														<option value='solid' <?php selected( sanitize_key( get_option( 'lps_login_form_input_border_style' ) ), 'solid' ); ?>>Solid</option>
														<option value='dashed' <?php selected( sanitize_key( get_option( 'lps_login_form_input_border_style' ) ), 'dashed' ); ?>>Dashed</option>
														<option value='dotted' <?php selected( sanitize_key( get_option( 'lps_login_form_input_border_style' ) ), 'dotted' ); ?>>Dotted</option>
														<option value='double' <?php selected( sanitize_key( get_option( 'lps_login_form_input_border_style' ) ), 'double' ); ?>>Double</option>
													</select>
													<p class="description">
														<?php esc_html_e( 'Input Field border style.', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_form_input_feild_border_radius'><?php esc_html_e( 'Input Field Border Radius', 'login-page-styler' ); ?></label><br />
													<input type='range' id='lps_login_form_input_feild_border_radius'
														name='lps_login_form_input_feild_border_radius' min='0' max='20'
														value='<?php echo absint( get_option( 'lps_login_form_input_feild_border_radius' ) ); ?>'
														oninput="this.form.amountInput7.value=this.value" />
													<input type="number" name="amountInput7" min="0" max="20"
														value='<?php echo absint( get_option( 'lps_login_form_input_feild_border_radius' ) ); ?>'
														size='4'
														oninput="this.form.lps_login_form_input_feild_border_radius.value=this.value" />px
													<p class="description">
														<?php esc_html_e( 'Change Login form input-field border radius.', 'login-page-styler' ); ?>
													</p>
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>



											<tr valign='top'>
												<td><label
														for='lps_login_remember_label_size'><?php esc_html_e( 'Remember Me Label Size', 'login-page-styler' ); ?></label><br />
													<input type='range' id='lps_login_remember_label_size'
														name='lps_login_remember_label_size' min='12' max='25'
														value='<?php echo max( 12, absint( get_option( 'lps_login_remember_label_size' ) ) ); ?>'
														oninput="this.form.amountInput2.value=this.value" />
													<input type="number" name="amountInput2" min="12" max="25"
														value='<?php echo max( 12, absint( get_option( 'lps_login_remember_label_size' ) ) ); ?>'
														size='4'
														oninput="this.form.lps_login_remember_label_size.value=this.value" />px
												</td>
											</tr>


											<tr>
												<td><label
														for='lps_box_shadow_color'><?php esc_html_e( 'Form Box Shadow Color', 'login-page-styler' ); ?></label><br />
													<input type='text' class='color_picker' id='lps_box_shadow_color'
														name='lps_box_shadow_color'
														value='<?php echo esc_attr( get_option( 'lps_box_shadow_color', '#000000' ) ); ?>' />
													<p class='description'>
														<?php esc_html_e( 'Set the color for the login box shadow.', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_box_shadow_opacity'><?php esc_html_e( 'Form Box Shadow Opacity', 'login-page-styler' ); ?></label><br />
													<input type='range' step='0.1' min='0' max='1'
														id='lps_box_shadow_opacity' name='lps_box_shadow_opacity'
														value='<?php echo esc_attr( get_option( 'lps_box_shadow_opacity', '0.3' ) ); ?>'
														oninput="this.form.amountInputBSOpacity.value=this.value" />
													<input type='number' step='0.1' min='0' max='1'
														name='amountInputBSOpacity'
														value='<?php echo esc_attr( get_option( 'lps_box_shadow_opacity', '0.3' ) ); ?>'
														size='4'
														oninput="this.form.lps_box_shadow_opacity.value=this.value" />
													<p class='description'>
														<?php esc_html_e( 'Set the opacity for the login box shadow (between 0 and 1).', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_box_shadow_horizontal'><?php esc_html_e( 'Form Box Shadow Horizontal Offset', 'login-page-styler' ); ?></label><br />
													<input type='range' id='lps_box_shadow_horizontal'
														name='lps_box_shadow_horizontal' min='-20' max='20'
														value='<?php echo esc_attr( get_option( 'lps_box_shadow_horizontal' ) ); ?>'
														oninput="this.form.amountInputBSH.value=this.value" />
													<input type="number" name="amountInputBSH" min="-20" max="20"
														value='<?php echo esc_attr( get_option( 'lps_box_shadow_horizontal' ) ); ?>'
														size='4'
														oninput="this.form.lps_box_shadow_horizontal.value=this.value" />
													<p class="description">
														<?php esc_html_e( 'Set the horizontal offset for the login box shadow.', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_box_shadow_vertical'><?php esc_html_e( 'Form Box Shadow Vertical Offset', 'login-page-styler' ); ?></label><br />
													<input type='range' id='lps_box_shadow_vertical'
														name='lps_box_shadow_vertical' min='-20' max='20'
														value='<?php echo absint( get_option( 'lps_box_shadow_vertical' ) ); ?>'
														oninput="this.form.amountInputBSV.value=this.value" />
													<input type="number" name="amountInputBSV" min="-20" max="20"
														value='<?php echo absint( get_option( 'lps_box_shadow_vertical' ) ); ?>'
														size='4'
														oninput="this.form.lps_box_shadow_vertical.value=this.value" />
													<p class="description">
														<?php esc_html_e( 'Set the vertical offset for the login box shadow.', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>


											<tr valign='top'>
												<td>
													<label
														for='lps_box_shadow_blur'><?php esc_html_e( 'Form Box Shadow Blur Radius', 'login-page-styler' ); ?></label><br />
													<input type='range' id='lps_box_shadow_blur' name='lps_box_shadow_blur'
														min='0' max='100'
														value='<?php echo absint( get_option( 'lps_box_shadow_blur', '10' ) ); ?>'
														oninput="this.form.amountInputBSBlur.value=this.value" />
													<input type='number' name='amountInputBSBlur' min='0' max='100'
														value='<?php echo absint( get_option( 'lps_box_shadow_blur', '10' ) ); ?>'
														size='4' oninput="this.form.lps_box_shadow_blur.value=this.value" />
													<p class='description'>
														<?php esc_html_e( 'Set the blur radius for the login box shadow.', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>

											<tr valign='top'>

												<td>
													<label
														for='lps_box_shadow_spread'><?php esc_html_e( 'Form Box Shadow Spread Radius', 'login-page-styler' ); ?></label><br />
													<input type='range' id='lps_box_shadow_spread'
														name='lps_box_shadow_spread' min='0' max='100'
														value='<?php echo absint( get_option( 'lps_box_shadow_spread', '0' ) ); ?>'
														oninput="this.form.amountInputBSSpread.value=this.value" />
													<input type='number' name='amountInputBSSpread' min='0' max='100'
														value='<?php echo absint( get_option( 'lps_box_shadow_spread', '0' ) ); ?>'
														size='4'
														oninput="this.form.lps_box_shadow_spread.value=this.value" />
													<p class='description'>
														<?php esc_html_e( 'Set the spread radius for the login box shadow.', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>



										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section4" name="accordion">
									<label class="accordion-label" for="section4">Google Fonts</label>
									<div class="accordion-content">

										<table class="form-table">
											<tr valign="top">
												<td><label
														for="lps_gfontlab"><?php esc_html_e( 'Google font Label ', 'login-page-styler' ); ?></label><br />
													<input name="lps_gfontlab" id="lps_gfontlab" class="lps_labfont"
														type="text"
														value="<?php echo esc_attr( get_option( 'lps_gfontlab' ) ); ?>" />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

											<tr valign="top">
												<td><label
														for="lps_gfont_inputtext"><?php esc_html_e( 'Google font Input Field Text ', 'login-page-styler' ); ?></label><br />
													<input name="lps_gfont_inputtext" id="lps_gfont_inputtext"
														class="lps_inputfont" type="text"
														value="<?php echo esc_attr( get_option( 'lps_gfont_inputtext' ) ); ?>" />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

											<tr valign="top">
												<td><label
														for="lps_gfontlink"><?php esc_html_e( 'Google font Navigation Links ', 'login-page-styler' ); ?></label><br />
													<input name="lps_gfontlink" id="lps_gfontlink" class="lps_linkfont"
														type="text"
														value="<?php echo esc_attr( get_option( 'lps_gfontlink' ) ); ?>" />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

											<tr valign="top">
												<td><label
														for="lps_gfontmsg"><?php esc_html_e( 'Google font Error Messages ', 'login-page-styler' ); ?></label><br />
													<input name="lps_gfontmsg" id="lps_gfontmsg" class="lps_msgfont"
														type="text"
														value="<?php echo esc_attr( get_option( 'lps_gfontmsg' ) ); ?>" />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

											<tr valign="top">
												<td><label
														for="lps_gfontbtn"><?php esc_html_e( 'Google font Button ', 'login-page-styler' ); ?></label><br />
													<input name="lps_gfontbtn" id="lps_gfontbtn" class="lps_btnfont"
														type="text"
														value="<?php echo esc_attr( get_option( 'lps_gfontbtn' ) ); ?>" />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>
										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section5" name="accordion">
									<label class="accordion-label" for="section5">Button</label>
									<div class="accordion-content">

										<table class="form-table">

											<tr valign='top'>
												<td><label
														for='lps_login_button_color'><?php esc_html_e( 'Login Button Color', 'login-page-styler' ); ?></label></br>
													<input type='text' class='color_picker' id='lps_login_button_color'
														name='lps_login_button_color'
														value='<?php echo esc_attr( get_option( 'lps_login_button_color' ) ); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_button_color_hover'><?php esc_html_e( 'Login Button Color Hover', 'login-page-styler' ); ?></label>
													<input type='text' class='color_picker'
														id='lps_login_button_color_hover'
														name='lps_login_button_color_hover'
														value='<?php echo esc_attr( get_option( 'lps_login_button_color_hover' ) ); ?>' />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_button_text_color'><?php esc_html_e( 'Login Button Text Color', 'login-page-styler' ); ?></label>
													<input type='text' class='color_picker' id='lps_login_button_text_color'
														name='lps_login_button_text_color'
														value='<?php echo esc_attr( get_option( 'lps_login_button_text_color' ) ); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_button_text_color_hover'><?php esc_html_e( 'Login Button Text Color Hover', 'login-page-styler' ); ?></label>
													<input type='text' class='color_picker'
														id='lps_login_button_text_color_hover'
														name='lps_login_button_text_color_hover'
														value='<?php echo esc_attr( get_option( 'lps_login_button_text_color_hover' ) ); ?>' />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_button_border_color'><?php esc_html_e( 'Login Button Border Color', 'login-page-styler' ); ?></label>
													<input type='text' class='color_picker'
														id='lps_login_button_border_color'
														name='lps_login_button_border_color'
														value='<?php echo esc_attr( get_option( 'lps_login_button_border_color' ) ); ?>' />
												</td>
											</tr>


											<tr valign='top'>
												<td><label
														for='lps_login_button_border_color_hover'><?php esc_html_e( 'Login Button Border Color Hover', 'login-page-styler' ); ?></label></br>
													<input type='text' class='color_picker'
														id='lps_login_button_border_color_hover'
														name='lps_login_button_border_color_hover'
														value='<?php echo esc_attr( get_option( 'lps_login_button_border_color_hover' ) ); ?>' />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_button_border_radius'><?php esc_html_e( 'Login Button Border Radius', 'login-page-styler' ); ?></label>
													<input type='range' id='lps_login_button_border_radius'
														name='lps_login_button_border_radius' min='0' max='30'
														value='<?php echo absint( get_option( 'lps_login_button_border_radius' ) ); ?>'
														oninput="this.form.amountInput6.value=this.value" />
													<input type="number" name="amountInput6" min="0" max="30"
														value='<?php echo absint( get_option( 'lps_login_button_border_radius' ) ); ?>'
														size='4'
														oninput="this.form.lps_login_button_border_radius.value=this.value" />
													<p class="description">
														<?php esc_html_e( 'Slide to change login button border radius .', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_button_size'><?php esc_html_e( 'Login Button Size', 'login-page-styler' ); ?></label>
													<input type='range' step='' min='68' max='272'
														id='lps_login_button_size' name='lps_login_button_size'
														value='<?php echo max( 68, absint( get_option( 'lps_login_button_size' ) ) ); ?>'
														oninput="this.form.amountInputButtonsize.value=this.value" />
													<input type='number' step='' min='68' max='400'
														name='amountInputButtonsize'
														value='<?php echo max( 68, absint( get_option( 'lps_login_button_size' ) ) ); ?>'
														size='4'
														oninput="this.form.lps_login_button_size.value=this.value" />px
													<p class='description'>
														<?php esc_html_e( 'Change login button size .68px is default button size , 272px matches the size of inputfield .', 'login-page-styler' ); ?>
													</p>
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section6" name="accordion">
									<label class="accordion-label" for="section6">Navigation Links</label>
									<div class="accordion-content">

										<table class="form-table">

											<tr vlaign='top'>
												<td><label
														for='lps_login_nav_color'><?php esc_html_e( 'Navigation Links Text Color', 'login-page-styler' ); ?></label>
													<input type='text' class='color_picker' id='lps_login_nav_color'
														name='lps_login_nav_color'
														value='<?php echo esc_attr( get_option( 'lps_login_nav_color' ) ); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_login_nav_hover_color'><?php esc_html_e( 'Navigation Links Text Color Hover', 'login-page-styler' ); ?></label>
													<input type='text' class='color_picker' id='lps_login_nav_hover_color'
														name='lps_login_nav_hover_color'
														value='<?php echo esc_attr( get_option( 'lps_login_nav_hover_color' ) ); ?>' />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

											<tr vlaign='top'>
												<td><label
														for='lps_login_nav_bgcolor'><?php esc_html_e( 'Navigation Links Background Color', 'login-page-styler' ); ?></label>
													<input type='text' class='color_picker' id='lps_login_nav_bgcolor'
														name='lps_login_nav_bgcolor'
														value='<?php echo esc_attr( get_option( 'lps_login_nav_bgcolor' ) ); ?>' />
												</td>
											</tr>

											<tr vlaign='top'>
												<td><label
														for='lps_login_nav_bgcolor_hover'><?php esc_html_e( 'Navigation Links Background Color Hover', 'login-page-styler' ); ?></label>
													<input type='text' class='color_picker' id='lps_login_nav_bgcolor_hover'
														name='lps_login_nav_bgcolor_hover'
														value='<?php echo esc_attr( get_option( 'lps_login_nav_bgcolor_hover' ) ); ?>' />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>


											<tr valign='top'>
												<td><label
														for='lps_login_nav_size'><?php esc_html_e( 'Navigation Link Size', 'login-page-styler' ); ?></label>
													<input type='range' id='lps_login_nav_size' name='lps_login_nav_size'
														min='13' max='20'
														value='<?php echo max( 13, absint( get_option( 'lps_login_nav_size' ) ) ); ?>'
														oninput="this.form.amountInput8.value=this.value" />
													<input type="number" name="amountInput8" min="13" max="20"
														value='<?php echo max( 13, absint( get_option( 'lps_login_nav_size' ) ) ); ?>'
														size='4' oninput="this.form.lps_login_nav_size.value=this.value" />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>

												</td>
											</tr>

										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section7" name="accordion">
									<label class="accordion-label" for="section7">Login Footer</label>
									<div class="accordion-content">

										<table class="form-table">
											<tr valign="top">
												<td><label
														for="lps_login_copyright"><?php esc_html_e( ' Copyright / Add your company', 'login-page-styler' ); ?></label><br />
													&copy <?php echo esc_attr( gmdate( 'Y' ) ); ?> <input type="text"
														id="lps_login_copyright" name="lps_login_copyright" size="25"
														placeholder="Your website or Company name "
														value="<?php echo esc_attr( get_option( 'lps_login_copyright' ) ); ?>" />All
													rights reserved.
													<p class="description">
														<?php esc_html_e( ' Shows Copyright in the footer of login page just add your company name  ', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>

											<tr>
												<td><label
														for='lps_copyright_color'><?php esc_html_e( 'Copyright Text Color', 'login-page-styler' ); ?></label><br />
													<input type='text' class='color_picker' id='lps_copyright_color'
														name='lps_copyright_color'
														value='<?php echo esc_attr( get_option( 'lps_copyright_color' ) ); ?>' />
													<p class='description'>
														<?php esc_html_e( 'Set the color for the Copyright Footor text.', 'login-page-styler' ); ?>
													</p>

												</td>
											</tr>

											<tr valign="top">
												<td><label
														for="lps_gfont_copyright"><?php esc_html_e( 'Google font for Copyright ', 'login-page-styler' ); ?></label>
													<input name="lps_gfont_copyright" id="lps_gfont_copyright"
														class="lps_copyrightfont" type="text"
														value="<?php echo esc_attr( get_option( 'lps_gfont_copyright' ) ); ?>" />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section8" name="accordion">
									<label class="accordion-label" for="section8">Error Notice Success Mesages</label>
									<div class="accordion-content">

										<table class="form-table">

											<tr>
												<td>
													<label
														for='lps_notice_textcolor'><?php esc_html_e( 'Error, Notice Message Text Color', 'login-page-styler' ); ?></label><br />
													<input type='text' class='color_picker' id='lps_notice_textcolor'
														name='lps_notice_textcolor'
														value='<?php echo esc_attr( get_option( 'lps_notice_textcolor' ) ); ?>' />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>

											</tr>

											<tr>
												<td>
													<label
														for='lps_notice_bgcolor'><?php esc_html_e( 'Error, Notice Message Background Color', 'login-page-styler' ); ?></label><br />
													<input type='text' class='color_picker' id='lps_notice_bgcolor'
														name='lps_notice_bgcolor'
														value='<?php echo esc_attr( get_option( 'lps_notice_bgcolor' ) ); ?>' />
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label
														for='lps_notice_bgcolor_opacity'><?php esc_html_e( 'Message Background Opacity', 'login-page-styler' ); ?></label><br />
													<input type='range' step='0.1' min='0' max='1'
														id='lps_notice_bgcolor_opacity' name='lps_notice_bgcolor_opacity'
														value='<?php echo esc_attr( get_option( 'lps_notice_bgcolor_opacity', '1' ) ); ?>'
														oninput="this.form.amountInputMessageOpacity.value=this.value" />
													<input type='number' step='0.1' min='0' max='1'
														name='amountInputMessageOpacity'
														value='<?php echo esc_attr( get_option( 'lps_notice_bgcolor_opacity', '1' ) ); ?>'
														size='4'
														oninput="this.form.lps_notice_bgcolor_opacity.value=this.value" />
													<p class='description'>
														<?php esc_html_e( 'Change opacity', 'login-page-styler' ); ?>
													</p>
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>


										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section9" name="accordion">
									<label class="accordion-label" for="section9">Slider Background</label>
									<div class="accordion-content">

										<h3 style="color: #FFBA00; font-weight:bold" class='description'>
											<?php esc_html_e( 'Set Images  then save settings for Background slider to work , If u want to stop just remove all pictures', 'login-page-styler' ); ?>
										</h3>

										<table class="form-table">
											<tr valign="top">
												<td>

													<label for="lps_body_bg_slideshow">
														<?php esc_html_e( 'Login Background Slider', 'login-page-styler' ); ?>
													</label></br>
													<?php
													// Get the array of background images.
													$background_images = get_option( 'lps_body_bg_slideshow', array() );

													for ( $i = 0; $i < 4; $i++ ) {
														$image_url = isset( $background_images[ $i ] ) ? esc_url( $background_images[ $i ] ) : '';
														?>
														<label for="lps_body_bg_slideshow<?php echo esc_attr( $i + 1 ); ?>">
															Slider Image <?php echo esc_attr( $i + 1 ); ?>:
															<input id="lps_body_bg_slideshow<?php echo esc_attr( $i + 1 ); ?>"
																type="text" name="lps_body_bg_slideshow[]"
																value="<?php echo $image_url; ?>" size="25" />
															<input class="lps-upload-button button" type="button"
																value="Upload Image" />
														</label>
														<br>
														<?php
													}
													?>
													<p class='description'>
														<?php esc_html_e( 'Enter background image URLs for each image (one per line)', 'login-page-styler' ); ?>
													</p>
												</td>
											</tr>


											<tr valign="top">
												<td>
													<label for="lps_slideshow_animation_style">
														<?php esc_html_e( 'Slideshow Animation Style', 'login-page-styler' ); ?>
													</label><br />
													<select name='lps_slideshow_animation_style'
														id='lps_slideshow_animation_style'>
														<option disabled value='fade' <?php selected( sanitize_text_field( get_option( 'lps_slideshow_animation_style' ) ), 'fade' ); ?>>Fade</option>
														<option disabled value='slide' <?php selected( sanitize_text_field( get_option( 'lps_slideshow_animation_style' ) ), 'slide' ); ?>>Slide</option>
														<option disabled value='flip' <?php selected( sanitize_text_field( get_option( 'lps_slideshow_animation_style' ) ), 'flip' ); ?>>Flip</option>
														<option disabled value='cube' <?php selected( sanitize_text_field( get_option( 'lps_slideshow_animation_style' ) ), 'cube' ); ?>>Cube</option>
													</select>
													<p class="description">
														<?php esc_html_e( 'Select animation for the slides, Preset is fade', 'login-page-styler' ); ?>
													</p>
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
													<p class="description">
													</p>
												</td>
											</tr>

											<tr valign='top'>
												<td>
													<label for='lps_slideshow_time'>
														<?php esc_html_e( 'Slideshow Time in Seconds', 'login-page-styler' ); ?>
													</label><br />
													<input disabled type='range' id='lps_slideshow_time'
														name='lps_slideshow_time' min='1' max='10'
														value='<?php echo absint( get_option( 'lps_slideshow_time' ) ); ?>'
														oninput="this.form.amountInputSst.value=this.value" />
													<input disabled type="number" name="amountInputSst" min="1" max="10"
														value='<?php echo max( 1, absint( get_option( 'lps_slideshow_time' ) ) ); ?>'
														size='4' oninput="this.form.lps_slideshow_time.value=this.value" />
													<p class='description'>
														<?php esc_html_e( 'Change the time of Slideshow preset time is 1 sec ', 'login-page-styler' ); ?>
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
													</p>
													<p class="description">
													</p>
												</td>
											</tr>
											<!-- Add more content if needed -->
										</table>
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section10" name="accordion">
									<label class="accordion-label" for="section10">Custom Css</label>
									<div class="accordion-content">

										<table class="form-table">
											<tr valign="top">
												<td><label
														for="lps_login_custom_css"><?php esc_html_e( 'Custom CSS', 'login-page-styler' ); ?></label>
													<textarea disabled cols="26" rows="7" id="lps_login_custom_css"
														name="lps_login_custom_css"><?php echo esc_textarea( get_option( 'lps_login_custom_css' ) ); ?> </textarea>
													<p class='description'>
														<?php esc_html_e( 'Add styling inside this text area.', 'login-page-styler' ); ?>
													</p>
													<p class="description">
														<?php
														echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
															'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
															esc_html__( '.', 'login-page-styler' );
														?>
													</p>
												</td>
											</tr>
										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								<p class='lpsresetbutton'><button type="button" id="lps-reset-button">Reset
										Settings</button></p>

								
								<p class="submit">
									<input type="submit" id="lpsbutton-primary-main" id="lpsbutton-primary-main"
										class="button-primary"
										value="<?php esc_html_e( 'Save Changes', 'login-page-styler' ); ?>" />
								</p>
								<!-- Repeat the structure for other accordion sections -->

							</div>

							<div class="preview-container">
								<div class="independent-preview">
									<!-- Content for the independent preview goes here -->
									<!-- This content will not be affected by the accordion scroll -->

									<iframe id="lps-iframe"
										src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'lpsnewLogin.php' ); ?>"
										frameborder="0" width="100%" height="800px"></iframe>

								</div>
							</div>

						</div>



						<h3 style="text-align: center;">
							<?php esc_html_e( 'Get Premium Support 24/7 Through E-mail : ziaimtiaz21@gmail.com ', 'login-page-styler' ); ?>
						</h3>
						<h3 style="text-align: center;">
							<?php esc_html_e( 'If you want us to Style your login page or want any new Feature Email us : ziaimtiaz21@gmail.com', 'login-page-styler' ); ?>
						</h3>
						<p style="text-align: center;"><b><a
									href="https://wordpress.org/support/plugin/login-page-styler/reviews/?filter=5"
									target="_blank">Give it a 5 star rating</a></b> on WordPress.org.</p>

				</div>
			</div>

			<div id="content2">
				<div class='wrap'>

					<h1><?php esc_html_e( 'Custom Templates', 'login-page-styler' ); ?></h1>
					<table class="form-table">
						<tr valign='top'>

							<h3 style="text-align: center;">
								<?php esc_html_e( ' Just few option can be changed for template like Logo , Background image , Label Color Label Size ', 'login-page-styler' ); ?>
							</h3>
							<th scope='row'><?php esc_html_e( 'Select Layout', 'login-page-styler' ); ?></th>
							<td><label for='layout'>
									<p class="pp" style='padding:0px 0px 0px 0px ;'>None <input type="radio"
											name="lps_layout" id="layout" value="lay0" <?php checked( 'lay0', esc_attr( get_option( 'lps_layout' ) ) ); ?> /></p>
								</label>
								</br>
								</br>

								<label for='layout1'>
									<p class="pp" style='padding:0px 0px 0px 0px ;'>Layout 1 <input disabled type="radio"
											name="lps_layout" id="layout1" value="lay1" <?php checked( 'lay1', esc_attr( get_option( 'lps_layout' ) ) ); ?> /></p>
									<p class="description">
										<?php
										echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
											'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
											esc_html__( '.', 'login-page-styler' );
										?>
									</p>
									<img width='600px'
										src='<?php echo esc_url( plugins_url( 'images/scrnsht.png', __FILE__ ) ); ?>' />
								</label>
								</br>

								<label for='layout2'>
									<p class="pp" style='padding:0px 0px 0px 0px ;'>Layout 2<input disabled type="radio"
											name="lps_layout" id="layout2" value="lay2" <?php checked( 'lay2', esc_attr( get_option( 'lps_layout' ) ) ); ?> /></p>
									<p class="description">
										<?php
										echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
											'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
											esc_html__( '.', 'login-page-styler' );
										?>
									</p>
									<img width='600px'
										src='<?php echo esc_url( plugins_url( 'images/scrnsht1.png', __FILE__ ) ); ?>' />
								</label>
								</br>

								<label for='layout3'>
									<p class="pp" style='padding:0px 0px 0px 0px ;'>Layout 3 <input disabled type="radio"
											name="lps_layout" id="layout3" value="lay3" <?php checked( 'lay3', esc_attr( get_option( 'lps_layout' ) ) ); ?> /></p>
									<p class="description">
										<?php
										echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
											'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
											esc_html__( '.', 'login-page-styler' );
										?>
									</p>
									<img width='600px'
										src='<?php echo esc_url( plugins_url( 'images/scrnsht2.png', __FILE__ ) ); ?>' />
								</label>
								</br>
							</td>
						</tr>

					</table>

					<p class="submit">
						<input type="submit" id="lpsbutton-primary" class="button-primary"
							value="<?php esc_html_e( 'Save Changes', 'login-page-styler' ); ?>" />
					</p>

				</div>

			</div>


			<div id="content3">
				<div class='wrap'>
					<h1><?php esc_html_e( 'Google ReCaptcha V2' ); ?></h1>
					<table class="form-table">
						<p>You need to <a href="https://www.google.com/recaptcha/admin" rel="external">Register you domain
								for free on google recaptcha</a> and get Site and Secret keys For V2 of Google recaptcha to
							make ReCaptcha work.</p>

						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Site Key', 'login-page-styler' ); ?></th>
							<td><label for="rs_site_key">
									<input type="text" id="rs_site_key" size="50" name="rs_site_key"
										value="<?php echo esc_attr( get_option( 'rs_site_key' ) ); ?>" />
									<p class="description"><?php esc_html_e( 'Enter Site Key ', 'login-page-styler' ); ?></p>
								</label></td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Secret Key', 'login-page-styler' ); ?></th>
							<td><label for="rs_private_key">
									<input type="text" id="rs_private_key" size="50" name="rs_private_key"
										value="<?php echo esc_attr( get_option( 'rs_private_key' ) ); ?>" />
									<p class="description"><?php esc_html_e( 'Enter Secret Key ', 'login-page-styler' ); ?>
									</p>
								</label></td>
						</tr>

						<tr valign='top'>
							<th scope='row'><?php esc_html_e( 'Enable Google ReCaptcha On Login', 'login-page-styler' ); ?>
							</th>
							<td>
								<div class="onoffswitch">
									<input type="checkbox" name="lps_login_captcha" class="onoffswitch-checkbox"
										id="myonoffswitchcap" value='1' <?php checked( 1, absint( get_option( 'lps_login_captcha' ) ) ); ?> />
									<label class="onoffswitch-label" for="myonoffswitchcap">
										<span class="onoffswitch-inner"></span>
										<span class="onoffswitch-switch"></span>
									</label>
								</div>
							</td>
						</tr>


						<tr valign='top'>
							<th scope='row'>
								<?php esc_html_e( 'Enable Google ReCaptcha On Registration', 'login-page-styler' ); ?>
							</th>
							<td>
								<div class="onoffswitch">
									<input disabled type="checkbox" name="lps_reg_captcha" class="onoffswitch-checkbox"
										id="myonoffswitchcap1" value='1' <?php checked( 1, absint( get_option( 'lps_reg_captcha' ) ) ); ?> />
									<label class="onoffswitch-label" for="myonoffswitchcap1">
										<span class="onoffswitch-inner"></span>
										<span class="onoffswitch-switch"></span>
									</label>
								</div>
								<p class="description">
									<?php
									echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
										'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
										esc_html__( '.', 'login-page-styler' );
									?>
								</p>
							</td>
						</tr>

						<tr valign='top'>
							<th scope='row'>
								<?php esc_html_e( 'Enable Google ReCaptcha On Lost Password', 'login-page-styler' ); ?>
							</th>
							<td>
								<div class="onoffswitch">
									<input disabled type="checkbox" name="lps_lost_captcha" class="onoffswitch-checkbox"
										id="myonoffswitchcap2" value='1' <?php checked( 1, absint( get_option( 'lps_lost_captcha' ) ) ); ?> />
									<label class="onoffswitch-label" for="myonoffswitchcap2">
										<span class="onoffswitch-inner"></span>
										<span class="onoffswitch-switch"></span>
									</label>
								</div>
								<p class="description">
									<?php
									echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
										'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
										esc_html__( '.', 'login-page-styler' );
									?>
								</p>
							</td>
						</tr>


						<tr valign="top">
							<th scope='row'><?php esc_html_e( 'Theme for recaptcha', 'login-page-styler' ); ?></th>
							<td><label for="lps_recaptcha_theme">
									<select name='lps_recaptcha_theme' id='lps_recaptcha_theme'>
										<option disabled value='light' <?php selected( sanitize_text_field( get_option( 'lps_recaptcha_theme' ) ), 'light' ); ?>>
											Light</option>
										<option disabled value='dark' <?php selected( sanitize_text_field( get_option( 'lps_recaptcha_theme' ) ), 'dark' ); ?>>
											Dark</option>
									</select>
									<p class="description">
										<?php esc_html_e( 'Select Theme for Google recaptcha', 'login-page-styler' ); ?>
									</p>
									<p class="description">
										<?php
										echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
											'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
											esc_html__( '.', 'login-page-styler' );
										?>
									</p>

								</label>
							</td>
						</tr>
					</table>

					<div id="headings-data">
						<div id="hed3">
							<h3><?php esc_html_e( ' Math Captcha  ', 'login-page-styler' ); ?></h3>
						</div>
						<table class="form-table">

							<tr valign='top'>
								<th scope='row'><?php esc_html_e( 'Show Math Captcha On Login', 'login-page-styler' ); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_show_math_captcha" class="onoffswitch-checkbox"
											id="myonoffswitchMathCaptcha" value='1' <?php checked( 1, absint( get_option( 'lps_show_math_captcha' ) ) ); ?> />
										<label class="onoffswitch-label" for="myonoffswitchMathCaptcha">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>
						</table>
					</div>


					<p class="submit">
						<input type="submit" id="lpsbutton-primary" class="button-primary"
							value="<?php esc_html_e( 'Save Changes', 'login-page-styler' ); ?>" />
					</p>

				</div>

			</div>


			<div id="content4">
				<div class="wrap">

					<h1><?php esc_html_e( 'Login Logout Menu Item', 'login-page-styler' ); ?></h1>
					<table class="form-table">
						<tr valign='top'>
							<th scope='row'><?php esc_html_e( 'Show Login/Logout In Menu', 'login-page-styler' ); ?></th>
							<td>
								<div class="onoffswitch">
									<input disabled type="checkbox" name="lps_loginout_menu" class="onoffswitch-checkbox"
										id="myonoffswitchmenu" value='1' <?php checked( 1, absint( get_option( 'lps_loginout_menu' ) ) ); ?> />
									<label class="onoffswitch-label" for="myonoffswitchmenu">
										<span class="onoffswitch-inner"></span>
										<span class="onoffswitch-switch"></span>
									</label>
								</div>
								<p class="description">
									<?php esc_html_e( 'This feature will show a login/logout menu item in your sites menu.', 'login-page-styler' ); ?>
								</p>
								<p class="description">
									<?php
									echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
										'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
										esc_html__( '.', 'login-page-styler' );
									?>
								</p>
							</td>
						</tr>
					</table>

					<div id="headings-data">
						<div id="hed3">
							<h3><?php esc_html_e( ' Login Short Code  ', 'login-page-styler' ); ?></h3>
						</div>
						<table class="form-table">
							<tr valign='top'>
								<th scope='row'><?php esc_html_e( 'Show Login Button', 'login-page-styler' ); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_login_widgetButton" class="onoffswitch-checkbox"
											id="myonoffswitchWB" value='1' <?php checked( 1, absint( get_option( 'lps_login_widgetButton' ) ) ); ?> />
										<label class="onoffswitch-label" for="myonoffswitchWB">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>


							<tr valign='top'>
								<th scope='row'><?php esc_html_e( 'Show Register Button', 'login-page-styler' ); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_register_widgetButton" class="onoffswitch-checkbox"
											id="myonoffswitchWB2" value='1' <?php checked( 1, absint( get_option( 'lps_register_widgetButton' ) ) ); ?> />
										<label class="onoffswitch-label" for="myonoffswitchWB2">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>



							<tr valign='top'>
								<th scope='row'><?php esc_html_e( 'Show Lost Password Button', 'login-page-styler' ); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_lostpassword_widgetButton"
											class="onoffswitch-checkbox" id="myonoffswitchWB3" value='1' <?php checked( 1, absint( get_option( 'lps_lostpassword_widgetButton' ) ) ); ?> />
										<label class="onoffswitch-label" for="myonoffswitchWB3">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>

							<tr valign='top'>
								<th scope='row'><?php esc_html_e( 'Login ShortCode', 'login-page-styler' ); ?></th>
								<td><label for='lps_login_shortcode'>
										<p class='description'><?php esc_html_e( '[lps_login_widget]' ); ?></p>
										<p class="description">
											<?php esc_html_e( 'Select button which you want to show then save settings and use the  short code ' ); ?>
										</p>
									</label></td>
							</tr>

						</table>
					</div>

					<p class="submit">
						<input type="submit" id="lpsbutton-primary" class="button-primary"
							value="<?php esc_html_e( 'Save Changes', 'login-page-styler' ); ?>" />
					</p>

				</div>
			</div>


			<div id="content5">
				<div class="wrap">

					<h1><?php esc_html_e( 'New Login URL / Admin Login URL', 'login-page-styler' ); ?></h1>
					<table class="form-table">

						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'New Login URL', 'login-page-styler' ); ?></th>
							<td>
								<label for="lps_new_login_url">
									<?php
									// Get the option and ensure a default value if it doesn't exist yet
									$lps_new_login_url = get_option( 'lps_new_login_url', '' ); // Default to empty string if option is not set
									?>
									<p style="background-color: #FFBA00; width:auto; float:left; margin-top:10px;">
										<?php echo esc_url( get_site_url() . '/' ); ?>
									</p> <input type="text" id="lps_new_login_url" placeholder="" name="lps_new_login_url"
										value="<?php echo esc_attr( $lps_new_login_url ); ?>" />
									<p class="description">
										<?php esc_html_e( 'Add your slug for example: my-login or mylogin', 'login-page-styler' ); ?>
									</p>
								</label>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Redirect to , When  old wp-login or wp-admin is visited', 'login-page-styler' ); ?>
							</th>
							<td>
								<label for="lps_new_redirection_url">
									<?php
									// Get the option and ensure a default value if it doesn't exist yet
									$lps_new_redirection_url = get_option( 'lps_new_redirection_url', '' ); // Default to empty string if option is not set
									?>
									<p style="background-color: #FFBA00; width:auto; float:left; margin-top:10px;">
										<?php echo esc_url( get_site_url() . '/' ); ?>
									</p><input type="text" id="lps_new_redirection_url" placeholder=""
										name="lps_new_redirection_url"
										value="<?php echo esc_attr( $lps_new_redirection_url ); ?>" />
									<p class="description">
										<?php esc_html_e( 'Leave empty or use 404 to land on 404, You can also use page slugs if you want people who visit wp-login.php or wp-admin to a specific page ', 'login-page-styler' ); ?>
									</p>
								</label>
							</td>
						</tr>


					</table>

					<div id="headings-data">
						<div id="hed3">
							<h3><?php esc_html_e( ' Login Redirect / Logout Redirect  ', 'login-page-styler' ); ?></h3>
						</div>
						<table class="form-table">
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Redirect user after login', 'login-page-styler' ); ?></th>
								<td>
									<label for="lps_redirect_users">
										<select name="lps_redirect_users" id="lps_redirect_users">
											<option disabled selected="selected" value="">
												<?php echo esc_attr( __( 'None' ) ); ?>
											</option>
											<?php
											$selected_page = get_option( 'lps_redirect_users' );
											$pages         = get_pages();
											foreach ( $pages as $page ) {
												$option_value = $page->post_name;
												$option_label = esc_html( $page->post_title );
												$selected     = selected( $selected_page, $page->post_name, false );
												?>
												<option disabled value='<?php echo esc_attr( $option_value ); ?>' <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $option_label ); ?></option>
											<?php } ?>
										</select>
										<p class="description">
											<?php esc_html_e( 'Select page which you want user to land on after login.', 'login-page-styler' ); ?>
										</p>
										<p class="description">
											<?php
											echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
												'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
												esc_html__( '.', 'login-page-styler' );
											?>
										</p>
									</label>
								</td>
							</tr>


							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Redirect user after logout', 'login-page-styler' ); ?></th>
								<td>
									<label for="lps_redirectafter_users">
										<select name="lps_redirectafter_users" id="lps_redirectafter_users">
											<option disabled selected="selected" value="">
												<?php echo esc_attr( __( 'None' ) ); ?>
											</option>
											<?php
											$selected_page = get_option( 'lps_redirectafter_users' );
											$pages         = get_pages();
											foreach ( $pages as $page ) {
												$option_value = esc_attr( $page->post_name );
												$option_label = esc_html( $page->post_title );
												$selected     = selected( $selected_page, $page->post_name, false );
												?>
												<option disabled value='<?php echo esc_attr( $option_value ); ?>' <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $option_label ); ?></option>
											<?php } ?>
										</select>
										<p class="description">
											<?php esc_html_e( 'Select page which you want user to land on after logout', 'login-page-styler' ); ?>
										</p>
										<p class="description">
											<?php
											echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
												'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
												esc_html__( '.', 'login-page-styler' );
											?>
										</p>
									</label>
								</td>
							</tr>
						</table>
					</div>

					<p class="submit">
						<input type="submit" id="lpsbutton-primary" class="button-primary"
							value="<?php esc_html_e( 'Save Changes', 'login-page-styler' ); ?>" />
					</p>

				</div>
			</div>

			<div id="content6">
				<div class="wrap">
					<h1><?php esc_html_e( 'Login Protected Site', 'login-page-styler' ); ?></h1>
					<table class="form-table">

						<tr valign='top'>
							<th scope='row'><?php esc_html_e( 'Enable Private Site', 'login-page-styler' ); ?></th>
							<td>
								<div class="onoffswitch">
									<input disabled type="checkbox" name="lps_enable_private_site"
										class="onoffswitch-checkbox" id="myonoffswitchprivatesite" value='1' <?php checked( 1, absint( get_option( 'lps_enable_private_site' ) ) ); ?> />
									<label class="onoffswitch-label" for="myonoffswitchprivatesite">
										<span class="onoffswitch-inner"></span>
										<span class="onoffswitch-switch"></span>
									</label>
								</div>
								<p class="description">
									<?php esc_html_e( 'This feature will make your whole site login protected site', 'login-page-styler' ); ?>
								</p>
								<p class="description">
									<?php
									echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
										'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
										esc_html__( '.', 'login-page-styler' );
									?>
								</p>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Block Page Access 1', 'login-page-styler' ); ?></th>
							<td>
								<label for="lps_private_login_url">
									<select name="lps_private_login_url" id="lps_private_login_url">
										<option selected="selected" value="<?php echo esc_attr( '' ); ?>">
											<?php echo esc_html( __( 'None' ) ); ?>
										</option>
										<?php
										$selected_page = get_option( 'lps_private_login_url' );
										$pages         = get_pages();
										foreach ( $pages as $page ) {
											$option_value = esc_attr( $page->post_name );
											$option_label = esc_html( $page->post_title );
											$selected     = selected( $selected_page, $page->post_name, false );
											?>
											<option value='<?php echo esc_attr( $option_value ); ?>' <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $option_label ); ?></option>
										<?php } ?>
									</select>
									<p class="description">
										<?php esc_html_e( 'Select page which you want to be login protected ', 'login-page-styler' ); ?>
									</p>
								</label>
							</td>
						</tr>





						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Block Page Access 2 ', 'login-page-styler' ); ?></th>
							<td>
								<label for="lps_private_login_url2">
									<select name="lps_private_login_url2" id="lps_private_login_url2">
										<option disabled selected="selected" value="<?php echo esc_attr( '' ); ?>">
											<?php echo esc_html( __( 'None' ) ); ?>
										</option>
										<?php
										$selected_page = get_option( 'lps_private_login_url2' );
										$pages         = get_pages();
										foreach ( $pages as $page ) {
											$option_value = esc_attr( $page->post_name );
											$option_label = esc_html( $page->post_title );
											$selected     = selected( $selected_page, $page->post_name, false );
											?>
											<option disabled value='<?php echo esc_attr( $option_value ); ?>' <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $option_label ); ?></option>
										<?php } ?>
									</select>
									<p class="description">
										<?php esc_html_e( 'Select page which you want to be login protected ', 'login-page-styler' ); ?>
									</p>
									<p class="description">
										<?php
										echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
											'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
											esc_html__( '.', 'login-page-styler' );
										?>
									</p>
								</label>
							</td>
						</tr>




						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Block Page Access 3', 'login-page-styler' ); ?></th>
							<td>
								<label for="lps_private_login_url3">
									<select name="lps_private_login_url3" id="lps_private_login_url3">
										<option disabled selected="selected" value="<?php echo esc_attr( '' ); ?>">
											<?php echo esc_html( __( 'None' ) ); ?>
										</option>
										<?php
										$selected_page = get_option( 'lps_private_login_url3' );
										$pages         = get_pages();
										foreach ( $pages as $page ) {
											$option_value = esc_attr( $page->post_name );
											$option_label = esc_html( $page->post_title );
											$selected     = selected( $selected_page, $page->post_name, false );
											?>
											<option disabled value='<?php echo esc_attr( $option_value ); ?>' <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $option_label ); ?></option>
										<?php } ?>
									</select>
									<p class="description">
										<?php esc_html_e( 'Select page which you want to be login protected.', 'login-page-styler' ); ?>
									</p>
									<p class="description">
										<?php
										echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
											'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
											esc_html__( '.', 'login-page-styler' );
										?>
									</p>
								</label>
							</td>
						</tr>



						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Block Page Access 4', 'login-page-styler' ); ?></th>
							<td>
								<label for="lps_private_login_url4">
									<select name="lps_private_login_url4" id="lps_private_login_url4">
										<option disabled selected="selected" value="<?php echo esc_attr( '' ); ?>">
											<?php echo esc_html( __( 'None' ) ); ?>
										</option>
										<?php
										$selected_page = get_option( 'lps_private_login_url4' );
										$pages         = get_pages();
										foreach ( $pages as $page ) {
											$option_value = esc_attr( $page->post_name );
											$option_label = esc_html( $page->post_title );
											$selected     = selected( $selected_page, $page->post_name, false );
											?>
											<option disabled value='<?php echo esc_attr( $option_value ); ?>' <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $option_label ); ?></option>
										<?php } ?>
									</select>
									<p class="description">
										<?php esc_html_e( 'Select page which you want to be login protected.', 'login-page-styler' ); ?>
									</p>
									<p class="description">
										<?php
										echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
											'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
											esc_html__( '.', 'login-page-styler' );
										?>
									</p>
								</label>
							</td>
						</tr>




						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Block Page Access 5', 'login-page-styler' ); ?></th>
							<td>
								<label for="lps_private_login_url5">
									<select name="lps_private_login_url5" id="lps_private_login_url5">
										<option disabled selected="selected" value="<?php echo esc_attr( '' ); ?>">
											<?php echo esc_html( __( 'None' ) ); ?>
										</option>
										<?php
										$selected_page = get_option( 'lps_private_login_url5' );
										$pages         = get_pages();
										foreach ( $pages as $page ) {
											$option_value = esc_attr( $page->post_name );
											$option_label = esc_html( $page->post_title );
											$selected     = selected( $selected_page, $page->post_name, false );
											?>
											<option disabled value='<?php echo esc_attr( $option_value ); ?>' <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $option_label ); ?></option>
										<?php } ?>
									</select>
									<p class="description">
										<?php esc_html_e( 'Select page which you want to be login protected.', 'login-page-styler' ); ?>
									</p>
									<p class="description">
										<?php
										echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
											'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
											esc_html__( '.', 'login-page-styler' );
										?>
									</p>
								</label>
							</td>
						</tr>

					</table>

					<p class="submit">
						<input type="submit" id="lpsbutton-primary" class="button-primary"
							value="<?php esc_html_e( 'Save Changes', 'login-page-styler' ); ?>" />
					</p>

				</div>
			</div>


			<div id="content7">
				<div class="wrap">
					<h1><?php esc_html_e( 'Limit Login Security' ); ?></h1>

					<table class="form-table">
						<tr valign='top'>
							<th scope='row'><?php esc_html_e( 'Enable Limit Login Security', 'login-page-styler' ); ?></th>
							<td>
								<div class="onoffswitch">
									<input disabled type="checkbox" name="lps_enable_lim" class="onoffswitch-checkbox"
										id="myonoffswitchl" value='1' <?php checked( 1, absint( get_option( 'lps_enable_lim' ) ) ); ?> />
									<label class="onoffswitch-label" for="myonoffswitchl">
										<span class="onoffswitch-inner"></span>
										<span class="onoffswitch-switch"></span>
									</label>
								</div>
								<p class="description">
									<?php esc_html_e( 'Select Yes to Enable limit login on your login page ', 'login-page-styler' ); ?>
								</p>
								<p class="description">
									<?php
									echo esc_html__( 'This Feature is Premium.', 'login-page-styler' ) .
										'<a href="https://pluginnestwp.website/custom-login-page-styler/" target="_blank">' . esc_html__( 'Unlock Here', 'login-page-styler' ) . '</a>' .
										esc_html__( '.', 'login-page-styler' );
									?>
								</p>
							</td>
						</tr>


						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Login Attempts', 'login-page-styler' ); ?></th>
							<td><label for="lps_login_attempts">
									<input disabled type="number" id="lps_login_attempts" placeholder="2"
										name="lps_login_attempts" size="40"
										value="<?php echo esc_attr( get_option( 'lps_login_attempts' ) ); ?>" /> Attempts.
									<p class="description">
										<?php esc_html_e( 'Number of Attempts before login lockdown.', 'login-page-styler' ); ?>
									</p>
								</label></td>
						</tr>


						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Attempts With In ', 'login-page-styler' ); ?></th>
							<td><label for="lps_attempts_within">
									<input disabled type="number" id="lps_attempts_within" placeholder="1"
										name="lps_attempts_within" size="40"
										value="<?php echo esc_attr( get_option( 'lps_attempts_within' ) ); ?>" /> Minutes
									<p class="description">
										<?php esc_html_e( ' Failed Attempts within this time period will be blocked.', 'login-page-styler' ); ?>
									</p>
								</label></td>
						</tr>


						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Lockdown Time', 'login-page-styler' ); ?></th>
							<td><label for="lps_lock_time">
									<input disabled type="number" id="lps_lock_time" placeholder="2" name="lps_lock_time"
										size="40" value="<?php echo esc_attr( get_option( 'lps_lock_time' ) ); ?>" /> Minutes
									<p class="description">
										<?php esc_html_e( ' Time period to block an IP to rety the Login Attempts  ' ); ?>
									</p>
								</label></td>
						</tr>

					</table>

					<p class="submit">
						<input type="submit" id="lpsbutton-primary" class="button-primary"
							value="<?php esc_html_e( 'Save Changes', 'login-page-styler' ); ?>" />
					</p>

					</form>

				</div>
			</div>




			<div id="content8">

				<div class="wrap">
					<h1><?php esc_html_e( 'Limit Login Blocked Ip', 'login-page-styler' ); ?></h1>

					<?php
					global $wpdb;
					$table_name = $wpdb->prefix . 'lps_lockdowns';

					if ( isset( $_POST['release_lockdowns'] ) ) {
						$nonce = isset( $_POST['release_lockdowns_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['release_lockdowns_nonce'] ) ) : '';

						if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'release_lockdowns_nonce' ) ) {
							echo 'Nonce verification failed.';
						} elseif ( isset( $_POST['releaseme'] ) ) {
							$releaseme = isset( $_POST['releaseme'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['releaseme'] ) ) : array();
							$released  = array_map( 'intval', $releaseme );

							if ( ! empty( $released ) ) {
								foreach ( $released as $release_id ) {
									$releasequery = "UPDATE $table_name SET lpsrelease_date = now() WHERE lpslockdown_ID = %d";
									$releasequery = $wpdb->prepare( $releasequery, $release_id );
									$results      = $wpdb->query( $releasequery );
								}

								echo 'IPs released successfully.';
							} else {
								echo 'No IPs selected for release.';
							}
						}
					}
					?>

					<?php
					$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
					?>

					<form method="post" action="<?php echo esc_url( $request_uri ); ?>">

						<h3>
							<?php
							$dalist        = lps_listLockedDown();
							$num_lockedout = count( $dalist );

							if ( $num_lockedout == 1 ) {
								/* translators: %d is the number of locked out IP addresses */
								printf( esc_html__( 'There is currently %d locked out IP address.', 'login-page-styler' ), esc_html( $num_lockedout ) );
							} else {
								/* translators: %d is the number of locked out IP addresses */
								printf( esc_html__( 'There are currently %d locked out IP addresses.', 'login-page-styler' ), esc_html( $num_lockedout ) );
							}
							?>
						</h3>

						<?php
						if ( $num_lockedout == 0 ) {
							echo '<p>No IP currently locked out.</p>';
						} else {
							foreach ( $dalist as $key => $option ) {
								?>
								<li><input type="checkbox" name="releaseme[]"
										value="<?php echo esc_attr( $option['lpslockdown_ID'] ); ?>">
									<?php echo esc_attr( $option['lpslockdown_IP'] ); ?> Country: (<?php echo esc_attr( $tags ); ?> )
									(<?php echo esc_attr( $option['minutes_left'] ); ?> minutes left)</li>
								<?php
							}
						}
						?>

						<p class="submit">
							<?php wp_nonce_field( 'release_lockdowns_nonce', 'release_lockdowns_nonce' ); ?>
							<input type="submit" class="button button-primary" name="release_lockdowns"
								value="<?php esc_html_e( 'Release Selected', 'login-page-styler' ); ?>" />
						</p>
					</form>
				</div>

			</div>

		</div>

	</div>



<?php } ?>