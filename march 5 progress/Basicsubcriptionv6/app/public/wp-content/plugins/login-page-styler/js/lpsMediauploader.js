jQuery( document ).ready(
	function ($) {
		var mediaUploaders = {};

		$( '.lps-upload-button' ).on(
			'click',
			function (e) {
				e.preventDefault();

				var button     = $( this );
				var inputField = $( '#' + button.siblings( 'input[type="text"]' ).attr( 'id' ) );

				if (mediaUploaders[inputField.attr( 'id' )]) {
					mediaUploaders[inputField.attr( 'id' )].open();
					return;
				}

				mediaUploaders[inputField.attr( 'id' )] = wp.media(
					{
						title: 'Choose Image',
						button: {
							text: 'Choose Image'
						},
						multiple: false
					}
				);

				mediaUploaders[inputField.attr( 'id' )].on(
					'select',
					function () {
						var attachment = mediaUploaders[inputField.attr( 'id' )].state().get( 'selection' ).first().toJSON();
						inputField.val( attachment.url );
						// Trigger a custom event when an image is selected
						$( document ).trigger( 'imageSelected', [attachment.url] );
					}
				);

				mediaUploaders[inputField.attr( 'id' )].open();
			}
		);
	}
);
