<?php
/**
 * New Woocommerce shipement methods.
 *
 * @link       https://wpspins.com?utm-ref=shipengine-dokan-integration
 * @since      1.0.0
 *
 * @package    shipengine-dokan-integration
 * @subpackage shipengine-dokan-integration/public
 */

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {

	/**
	 * Init shipengine method.
	 *
	 * @return void
	 */
	function sdi_init_shipengine_shipement() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-shipengine-dokan-integration-shipment.php';
	}
	add_action( 'woocommerce_shipping_init', 'sdi_init_shipengine_shipement' );

	/**
	 * Add shipengine method.
	 *
	 * @param  mixed $methods current WC methods.
	 * @return array
	 */
	function sdi_add_new_shipping_method( $methods ) {
		$methods['Shipengine_Dokan_Integration_Shipment'] = 'Shipengine_Dokan_Integration_Shipment';
		return $methods;
	}
	add_action( 'woocommerce_shipping_methods', 'sdi_add_new_shipping_method' );
}
