<?php
/*
 * Plugin Name: CoCart Generate Image Data Url
 * Plugin URI:  https://cocart.xyz
 * Description: Extends CoCart by generating base64 dataUrl images.
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Version:     1.0.0
 * Text Domain: cocart-generate-image-dataurl
 * Domain Path: /languages/
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * WC requires at least: 6.4
 * WC tested up to: 6.9
 *
 * @package CoCart Generate Image Data Url
 */

defined( 'ABSPATH' ) || exit;

if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
	return;
}

if ( ! defined( 'COCART_GENERATE_IMAGE_DATAURL_FILE' ) ) {
	define( 'COCART_GENERATE_IMAGE_DATAURL_FILE', __FILE__ );
}

// Include the main CoCart Generate Image Data Url class.
if ( ! class_exists( 'CoCart\Extension\GenerateImageDataUrl', false ) ) {
	include_once untrailingslashit( plugin_dir_path( COCART_GENERATE_IMAGE_DATAURL_FILE ) ) . '/includes/class-cocart-generate-image-dataurl.php';
}

/**
 * Returns the main instance of cocart_generate_image_dataurl and only runs if it does not already exists.
 *
 * @return cocart_generate_image_dataurl
 */
if ( ! function_exists( 'cocart_generate_image_dataurl' ) ) {
	function cocart_generate_image_dataurl() {
		return \CoCart\Extension\GenerateImageDataUrl::init();
	}

	cocart_generate_image_dataurl();
}
