jQuery( document ).ready(
	function ($) {

		function lps_generate_temp_access_url() {
			
			var user_id                = $( '#current_user_id' ).val();
			var access_expiration_time = $( 'select[name="lps_access_expiration_time"]' ).val();

			if ( ! user_id) {
				$( '#lps_temp_access_message' ).text( 'User ID is missing.' );
				// console.log('Error: User ID is missing.');
				return;
			}

			// Validate expiration time
			if (access_expiration_time <= 0) {
				$( '#lps_temp_access_message' ).text( 'Invalid expiration time.' );
				// console.log('Error: Invalid expiration time.');
				return;
			}

			var data = {
				'action': 'lps_generate_temp_access_url',
				'user_id': user_id,
				'expiration_time': access_expiration_time,
				'nonce': lps_ajax_obj.nonce
			};

			$( '#generate_temp_url_button' ).attr( 'disabled', true ).val( 'Generating...' );

			// console.log('Sending data:', data);

			$.post(
				lps_ajax_obj.ajax_url,
				data,
				function (response) {
					// console.log('AJAX Response:', response);

					if (response.success) {
						// console.log('Generated Temporary Access URL:', response.data.temp_url);
						$( '#lps_temp_access_url' ).val( response.data.temp_url.replace( /&#038;/g, '&' ) );
						$( '#lps_temp_access_message' ).html( response.data.message );
					} else {
						$( '#lps_temp_access_message' ).text( response.data && response.data.message ? response.data.message : 'Failed to generate URL.' );
						// console.log('Error: ', response.data && response.data.message ? response.data.message : 'Failed to generate URL.');
					}
				}
			).fail(
				function (xhr, status, error) {
					$( '#lps_temp_access_message' ).text( 'AJAX request failed.' );
					// console.log('AJAX request failed:', xhr.responseText);
				}
			).always(
				function () {
					$( '#generate_temp_url_button' ).attr( 'disabled', false ).val( 'Generate URL' );
				}
			);
		}

		$( '#generate_temp_url_button' ).on(
			'click',
			function () {
				lps_generate_temp_access_url();
			}
		);

		function lps_revoke_temp_access() {
			var user_id = $( '#current_user_id' ).val();

			if ( ! user_id) {
				$( '#lps_temp_access_message' ).text( 'User ID is missing.' );
				// console.log('Error: User ID is missing.');
				return;
			}

			var data = {
				'action': 'lps_revoke_access',
				'user_id': user_id,
				'nonce': lps_ajax_obj.nonce // Ensure nonce is referenced correctly
			};

			$( '#revoke_temp_access_button' ).attr( 'disabled', true ).val( 'Revoking...' );

			$.post(
				lps_ajax_obj.ajax_url,
				data,
				function (response) {
					// console.log('Revoke AJAX Response:', response);

					if (response.success) {
						$( '#lps_temp_access_message' ).text( 'Temporary access revoked successfully.' );
						$( '#lps_temp_access_url' ).val( '' );
						// console.log('Temporary access revoked successfully.');
					} else {
						$( '#lps_temp_access_message' ).text( response.data && response.data.message ? response.data.message : 'Failed to revoke access.' );
						// console.log('Error revoking access: ', response.data && response.data.message ? response.data.message : 'Failed to revoke access.');
					}
				}
			).fail(
				function (xhr, status, error) {
					$( '#lps_temp_access_message' ).text( 'AJAX request failed: ' + xhr.responseText );
					// console.log('Revoke AJAX request failed:', xhr.responseText);
				}
			).always(
				function () {
					$( '#revoke_temp_access_button' ).attr( 'disabled', false ).val( 'Revoke Access' );
				}
			);
		}

		$( '#revoke_temp_access_button' ).on(
			'click',
			function () {
				lps_revoke_temp_access();
			}
		);
	}
);
