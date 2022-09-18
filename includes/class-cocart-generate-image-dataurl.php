<?php
/**
 * CoCart Generate Image Data Url core setup.
 *
 * @author   Sébastien Dumont
 * @category Package
 * @license  GPL-2.0+
 */

namespace CoCart\Extension;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main CoCart Generate Image Data Url class.
 *
 * @class CoCart\Extension\GenerateImageDataUrl
 */
final class GenerateImageDataUrl {

	/**
	 * Plugin Version
	 *
	 * @access public
	 * @static
	 */
	public static $version = '1.0.0';

	/**
	 * Initiate CoCart Generate Image Data Url.
	 *
	 * @access public
	 * @static
	 */
	public static function init() {
		// Update CoCart add-on counter upon activation.
		register_activation_hook( COCART_GENERATE_IMAGE_DATAURL_FILE, array( __CLASS__, 'activate_addon' ) );

		// Update CoCart add-on counter upon deactivation.
		register_deactivation_hook( COCART_GENERATE_IMAGE_DATAURL_FILE, array( __CLASS__, 'deactivate_addon' ) );

		// Adds the featured image for the item as a base64 dataUrl.
		add_filter( 'cocart_cart_items', array( __CLASS__, 'add_featured_image_dataurl' ), 10, 4 );
		add_filter( 'cocart_cart_items_schema', array( __CLASS__, 'featured_image_dataurl_schema' ) );

		// Adds data image for each image in the product gallery as a base64 dataUrl.
		// add_filter( '', array( __CLASS__, '' ) );

		// Load translation files.
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ), 0 );
	} // END init()

	/**
	 * Return the name of the package.
	 *
	 * @access public
	 * @static
	 * @return string
	 */
	public static function get_name() {
		return 'CoCart Generate Image Data Url';
	}

	/**
	 * Return the version of the package.
	 *
	 * @access public
	 * @static
	 * @return string
	 */
	public static function get_version() {
		return self::$version;
	}

	/**
	 * Return the path to the package.
	 *
	 * @access public
	 * @static
	 * @return string
	 */
	public static function get_path() {
		return dirname( __DIR__ );
	}

	/**
	 * Runs when the plugin is activated.
	 *
	 * Adds plugin to list of installed CoCart add-ons.
	 *
	 * @access public
	 */
	public static function activate_addon() {
		$addons_installed = get_option( 'cocart_addons_installed', array() );

		$plugin = plugin_basename( COCART_GENERATE_IMAGE_DATAURL_FILE );

		// Check if plugin is already added to list of installed add-ons.
		if ( ! in_array( $plugin, $addons_installed, true ) ) {
			array_push( $addons_installed, $plugin );
			update_option( 'cocart_addons_installed', $addons_installed );
		}
	} // END activate_addon()

	/**
	 * Runs when the plugin is deactivated.
	 *
	 * Removes plugin from list of installed CoCart add-ons.
	 *
	 * @access public
	 */
	public static function deactivate_addon() {
		$addons_installed = get_option( 'cocart_addons_installed', array() );

		$plugin = plugin_basename( COCART_GENERATE_IMAGE_DATAURL_FILE );

		// Remove plugin from list of installed add-ons.
		if ( in_array( $plugin, $addons_installed, true ) ) {
			$addons_installed = array_diff( $addons_installed, array( $plugin ) );
			update_option( 'cocart_addons_installed', $addons_installed );
		}
	} // END deactivate_addon()

	/**
	 * Adds the featured image for the item as a base64 dataUrl.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param array      $items     All items in the cart currently.
	 * @param string     $item_key  The item key generated based on the details of the item.
	 * @param array      $cart_item The item in the cart containing the default cart item data.
	 * @param WC_Product $_product  The product data of the item in the cart.
	 *
	 * @return array $items Returns all items in the cart with the additional data.
	 */
	public static function add_featured_image_dataurl( $items, $item_key, $cart_item, $_product ) {
		$thumbnail_id = ! empty( $_product->get_image_id() ) ? $_product->get_image_id() : get_option( 'woocommerce_placeholder_image', 0 );

		$data = self::generate_image_dataurl( $thumbnail_id );

		if ( $data ) {
			$items[ $item_key ]['featured_image_dataurl'] = $data;
		}

		return $items;
	} // END add_featured_image_dataurl()

	/**
	 * Adds the featured image dataUrl to the cart item schema.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param array $schema Passes any other additional schema already added.
	 *
	 * @return array $schema Returns all schema for the cart item.
	 */
	public static function featured_image_dataurl_schema( $schema ) {
		$schema['featured_image_dataurl'] = array(
			'description' => __( 'DataUrl or base64 url of the featured image of the item in cart.', 'cocart-generate-image-dataurl' ),
			'type'        => 'string',
			'context'     => array( 'view' ),
			'readonly'    => true,
		);

		return $schema;
	} // END featured_image_dataurl_schema()

	/**
	 * Generates image dataUrl for the image ID passed.
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @param int $image_id The image ID we are generating dataUrl for.
	 *
	 * @return string|null
	 */
	protected static function generate_image_dataurl( $image_id ) {
		$image   = wp_get_attachment_image_src( $image_id, 'woocommerce_thumbnail' )[0];
		$type    = pathinfo( $image, PATHINFO_EXTENSION );
		$data    = file_get_contents( $image );
		$dataUri = 'data:image/' . $type . ';base64,' . base64_encode( $data );

		if ( $data !== false ) {
			return $dataUri;
		}

		return null;
	} // END generate_image_dataurl()

	/**
	 * Load the plugin translations if any ready.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/cocart-generate-image-dataurl/cocart-generate-image-dataurl-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/cocart-generate-image-dataurl-LOCALE.mo
	 *
	 * @access public
	 * @static
	 */
	public static function load_plugin_textdomain() {
		if ( function_exists( 'determine_locale' ) ) {
			$locale = determine_locale();
		} else {
			$locale = is_admin() ? get_user_locale() : get_locale();
		}

		$locale = apply_filters( 'plugin_locale', $locale, 'cocart-generate-image-dataurl' );

		unload_textdomain( 'cocart-generate-image-dataurl' );
		load_textdomain( 'cocart-generate-image-dataurl', WP_LANG_DIR . '/cocart-generate-image-dataurl/cocart-generate-image-dataurl-' . $locale . '.mo' );
		load_plugin_textdomain( 'cocart-generate-image-dataurl', false, plugin_basename( dirname( COCART_GENERATE_IMAGE_DATAURL_FILE ) ) . '/languages' );
	} // END load_plugin_textdomain()

} // END class
