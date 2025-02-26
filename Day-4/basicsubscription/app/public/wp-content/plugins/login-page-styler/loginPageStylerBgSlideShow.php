<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function enqueue_login_page_scripts_and_styles() {
	// Enqueue jQuery.
	wp_enqueue_script( 'jquery' );

	// Enqueue the latest version of Swiper Slider script.
	wp_enqueue_script( 'swiper-slider', 'https://unpkg.com/swiper/swiper-bundle.min.js', array( 'jquery' ), '6.8.4', true );

	// Enqueue the latest version of Swiper Slider stylesheet.
	wp_enqueue_style( 'swiper-slider-css', 'https://unpkg.com/swiper/swiper-bundle.min.css', array(), '6.8.4' );
}
add_action( 'login_enqueue_scripts', 'enqueue_login_page_scripts_and_styles' );

// Initialize the slideshow.
function initialize_login_slideshow_with_swiper() {
	// $background_images = get_option( 'lps_body_bg_slideshow', array() );
	$background_images = array_filter( get_option( 'lps_body_bg_slideshow', array() ) );
	$background_images = array_values( $background_images ); // Reset array keys.

	// $animation_style = get_option('lps_slideshow_animation_style', 'fade');
	// $delay_inseconds = get_option('lps_slideshow_time', 2);

	$animation_style = 'fade';
	$delay_inseconds = 1;

	if ( ! empty( $background_images ) ) {
		echo '<style>
			.login-background-slideshow {
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				z-index: -1;
				overflow: hidden;
			}

			.swiper-slide {
				width: 100vw;
				height: 100vh;
				background-size: cover;
				background-position: center;
			}

	}
		</style>';

		echo '<script>
	jQuery(document).ready(function ($) {
		// Create a container for the slideshow
		$(\'body.login\').append(\'<div class="swiper-container login-background-slideshow"><div class="swiper-wrapper"></div></div>\');

		// Populate the slideshow container with background images
		var backgroundImages = ' . wp_json_encode( $background_images ) . ';

		console.log(\'Number of images:\', backgroundImages.length);

		for (var i = 0; i < backgroundImages.length; i++) {
			// Check if the current image is valid (not undefined)
			if (backgroundImages[i] !== undefined) {
				$(\'.swiper-wrapper\').append(\'<div class="swiper-slide" style="background-image: url(\' + backgroundImages[i] + \')"></div>\');
			}
		}

		// Initialize the slideshow with selected animation style
		var swiper = new Swiper(\'.login-background-slideshow\', {
			autoplay: {
				delay: \'' . esc_js( $delay_inseconds ) . '\'* 1000,
				disableOnInteraction: false,
			},
			loop: true, // Set loop to false to prevent duplication of slides
			effect: \'' . esc_js( $animation_style ) . '\',
			grabCursor: true,
			keyboard: {
				enabled: true,
				onlyInViewport: false,
			},
			
		});

		console.log(\'Number of slides in Swiper:\', swiper.slides.length);
	});
</script>';
	}
}
add_action( 'login_footer', 'initialize_login_slideshow_with_swiper' );
