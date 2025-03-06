<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function lps_temp_access_page_callback() {
	$access_expiration_time = get_option( 'lps_access_expiration_time', DAY_IN_SECONDS );
	$current_user_id        = get_current_user_id();
	$stored_url             = lps_get_stored_temp_access_url( $current_user_id );

	?>
	<div class="wrap lps-temp-access">
		<h1><?php esc_html_e( 'Temporary Admin Access Settings', 'login-page-styler' ); ?></h1>
		<h2><?php esc_html_e( 'Allow temporary admin-level access for troubleshooting or collaboration, with security through automatic expiration.', 'login-page-styler' ); ?></h2>

		<!-- Enhanced CSS for styling -->
		<style>
			.lps-temp-access {
				max-width: 750px;
				padding: 20px;
				background: #f9f9f9;
				border: 1px solid #e1e1e1;
				border-radius: 8px;
				box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.05);
			}

			.lps-temp-access h1 {
				font-size: 26px;
				color: #333;
				margin-bottom: 8px;
			}

			.lps-temp-access h2 {
				font-size: 16px;
				color: #666;
				font-weight: 400;
				line-height: 1.6;
				margin-bottom: 20px;
			}

			.lps-temp-access .form-table {
				width: 100%;
				margin-bottom: 20px;
			}

			.lps-temp-access .form-table th {
				text-align: left;
				padding: 10px 15px 10px 0;
				font-weight: 600;
				color: #333;
				vertical-align: top;
				width: 35%;
			}

			.lps-temp-access .form-table td {
				padding: 10px 0;
				width: 65%;
			}

			.lps-temp-access .form-table select,
			.lps-temp-access .form-table input[type="text"] {
				width: 100%;
				padding: 8px;
				font-size: 14px;
				border: 1px solid #ccc;
				border-radius: 4px;
				box-sizing: border-box;
			}

			.lps-temp-access .button {
				background-color: #0073aa;
				color: #fff;
				border: none;
				padding: 8px 12px;
				border-radius: 4px;
				font-size: 14px;
				cursor: pointer;
				transition: background-color 0.3s ease;
				margin-right: 5px;
			}

			.lps-temp-access .button:hover {
				background-color: #ffba00;
			}

			.lps-temp-access .description {
				color: #666;
				font-size: 13px;
				margin-top: 5px;
			}

			.lps-temp-access #lps_temp_access_url {
				margin-top: 10px;
				padding: 6px;
				background: #f4f4f4;
				border: 1px solid #ccc;
				color: #333;
				font-size: 13px;
			}
		</style>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'lps-temp-access-settings-group' );
			wp_nonce_field( 'lps_temp_access_nonce_action', 'lps_temp_access_nonce_field' );
			?>
			<table class="form-table">
				<input type="hidden" id="current_user_id" value="<?php echo esc_attr( $current_user_id ); ?>" />

				<tr>
					<th scope="row"><?php esc_html_e( 'Access Expiration Time', 'login-page-styler' ); ?></th>
					<td>
						<select name="lps_access_expiration_time">
							<optgroup label="Hours">
								<?php
								for ( $i = 1; $i <= 24; $i++ ) {
									$selected = ( $access_expiration_time == $i * HOUR_IN_SECONDS ) ? 'selected' : '';
									printf(
										"<option value='%s' %s>%s Hour%s</option>",
										esc_attr( $i * HOUR_IN_SECONDS ),
										esc_attr( $selected ),
										esc_html( $i ),
										esc_html( $i > 1 ? 's' : '' )
									);
								}
								?>
							</optgroup>
							<optgroup label="Days">
								<?php
								for ( $i = 1; $i <= 30; $i++ ) {
									$selected = ( $access_expiration_time == $i * DAY_IN_SECONDS ) ? 'selected' : '';
									printf(
										"<option value='%s' %s>%s Day%s</option>",
										esc_attr( $i * DAY_IN_SECONDS ),
										esc_attr( $selected ),
										esc_html( $i ),
										esc_html( $i > 1 ? 's' : '' )
									);
								}
								?>
							</optgroup>
						</select>
						<p class="description"><?php esc_html_e( 'Set the expiration time for the temporary access URL (choose between hours or days).', 'login-page-styler' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Generate Temporary Access URL', 'login-page-styler' ); ?></th>
					<td>
						<input type="button" id="generate_temp_url_button" class="button" value="Generate URL" />
						<input type="text" id="lps_temp_access_url" value="<?php echo esc_url( $stored_url ); ?>" readonly />
						<p id="lps_temp_access_message" class="description"><?php esc_html_e( 'Create a temporary URL for admin access.', 'login-page-styler' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Revoke Temporary Access', 'login-page-styler' ); ?></th>
					<td>
						<input type="button" id="revoke_temp_access_button" class="button" value="Revoke Access" />
						<p class="description"><?php esc_html_e( 'Revoke the temporary admin access URL immediately.', 'login-page-styler' ); ?></p>
					</td>
				</tr>

			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
