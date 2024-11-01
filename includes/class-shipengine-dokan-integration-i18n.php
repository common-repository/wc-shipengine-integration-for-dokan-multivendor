<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://wpspins.com?utm-ref=shipengine-dokan-integration
 * @since      1.0.0
 *
 * @package    shipengine-dokan-integration
 * @subpackage shipengine-dokan-integration/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    shipengine-dokan-integration
 * @subpackage shipengine-dokan-integration/includes
 * @author     WPSPIN LLC <mike@wpxhouston.com>
 */
class Shipengine_Dokan_Integration_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'shipengine-dokan-integration',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
