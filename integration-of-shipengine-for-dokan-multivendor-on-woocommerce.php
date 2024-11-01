<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wpspins.com?utm-ref=shipengine-dokan-integration
 * @since             1.0.0
 * @package           shipengine-dokan-integration
 *
 * @wordpress-plugin
 * Plugin Name:       Integration of Shipengine for Dokan Multivendor on WooCommerce
 * Plugin URI:        https://wpspins.com?utm-ref=shipengine-dokan-integration
 * Description:       The plugin does integrate the Shipengine account with WooCommerce websites running on the Dokan Multivendor Plugin. The plugin uses quotes, label creation, and shipment tracking features of Shipengine. As the ShipEngine website says: ShipEngine is the platform of choice for world-class shipping and logistics. Their shipping APIs help brands, eCommerce platforms, 3PLs, and others save time and money on shipping. Read more about it on their website or reach out to us for any additional features https://wpspins.com
 * Version:           1.0.2
 * Author:            WPSPIN LLC
 * Author URI:        https://wpspins.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-shipengine-integration-for-dokan-multivendor
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SDI_HELPER_VERSION', '1.0.0' );
define( 'SDI_FILE_PATH', plugin_dir_path( __FILE__ ) );
define( 'SDI_FILE_BASE', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-shipengine-dokan-integration-activator.php
 */
function activate_sdi_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-shipengine-dokan-integration-activator.php';
	Shipengine_Dokan_Integration_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-shipengine-dokan-integration-deactivator.php
 */
function deactivate_sdi_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-shipengine-dokan-integration-deactivator.php';
	Shipengine_Dokan_Integration_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_sdi_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_sdi_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-shipengine-dokan-integration.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sdi_plugin() {

	$plugin = new Shipengine_Dokan_Integration();
	$plugin->run();

}
run_sdi_plugin();
