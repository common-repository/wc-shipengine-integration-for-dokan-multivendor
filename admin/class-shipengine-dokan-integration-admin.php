<?php
/**
 *
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wpspins.com?utm-ref=shipengine-dokan-integration
 * @since      1.0.0
 *
 * @package    shipengine-dokan-integration
 * @subpackage shipengine-dokan-integration/admin
 */

?>

<?php
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-shipengine-dokan-integration-api.php';
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    shipengine-dokan-integration
 * @subpackage shipengine-dokan-integration/admin
 * @author     WPSPIN LLC <mike@wpxhouston.com>
 */
class Shipengine_Dokan_Integration_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	/**
	 * Shipengine API instance.
	 *
	 * @var Shipengine_Dokan_Integration_Api
	 */
	private $shipengine_api;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name       The name of this plugin.
	 * @param string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name    = $plugin_name;
		$this->version        = $version;
		$this->shipengine_api = new Shipengine_Dokan_Integration_Api();
		// Additional WooCommerce hooks.
		add_action( 'plugins_loaded', array( $this, 'wc_shipengine_plugins_loaded' ) );
		add_action( 'woocommerce_order_status_processing', array( $this, 'wc_purchase_label' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'wc_shipping_track_label_email' ), 5, 4 );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'wc_shipping_track_url_email' ), 5, 4 );
		add_action( 'woocommerce_view_order', array( $this, 'wc_shipping_tracking_page' ) );
		add_filter( 'woocommerce_admin_order_actions', array( $this, 'wc_shipengine_track_url' ), 10, 2 );
		add_filter( 'woocommerce_admin_order_actions', array( $this, 'wc_shipengine_label_url' ), 10, 2 );
		add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'wc_shipengine_info_url' ), 10, 2 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'wc_dokan_validate_cart_for_single_seller_mode' ), 10, 2 );
		add_filter( 'dokan_get_dashboard_nav', array( $this, 'sdi_dokan_rename_dashboard_nav' ), 40 );
		add_action( 'admin_notices', array( $this, 'sdi_donate_notice' ) );
	}
	/**
	 * Donate admin notice.
	 *
	 * @return void
	 */
	public function sdi_donate_notice() {
		$wc_page = array( 'wc-admin', 'wc-reports', 'wc-settings', 'wc-status', 'wc-addons' );
		// @codingStandardsIgnoreStart
		if ( isset( $_GET['page'] ) &&
		in_array( $_GET['page'], $wc_page, true ) ||
		isset( $_GET['post_type'] ) &&
		in_array( $_GET['post_type'], array( 'shop_order' ), true ) ) {
		// @codingStandardsIgnoreEnd
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php esc_html_e( 'Thanks for installing "Integration of Shipengine for Dokan Multivendor on WooCommerce". You can support our work by donating.', 'wc-shipengine-integration-for-dokan-multivendor' ); ?>
					<a target="__blank" href="https://wpspins.com/support-our-work/"><?php esc_html_e( 'Click this banner to see donation form', 'wc-shipengine-integration-for-dokan-multivendor' ); ?></a>
				</p>
			</div>
			<?php
		}
	}
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Shipengine_Dokan_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Shipengine_Dokan_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/shipengine-dokan-integration-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Shipengine_Dokan_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Shipengine_Dokan_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/shipengine-dokan-integration-admin.js', array( 'jquery' ), $this->version, false );

	}
	/**
	 * Add settings link to the plugin.
	 *
	 * @param  mixed $links Plugin action links.
	 * @param  mixed $file current plugin.
	 * @return array
	 */
	public function sdi_helper_setting_link( $links, $file ) {
		if ( SDI_FILE_BASE === $file ) {
			$url           = esc_url(
				add_query_arg(
					'page',
					'sdi_helper_settings',
					get_admin_url() . 'admin.php',
				)
			);
			$settings_link = "<a href='$url'>" . __( 'Settings', 'wc-shipengine-integration-for-dokan-multivendor' ) . '</a>';
			array_push(
				$links,
				$settings_link,
			);
		}
		return $links;
	}
	/**
	 * After plugins loaded action callback.
	 *
	 * @return void
	 */
	public function wc_shipengine_plugins_loaded() {
		remove_filter( 'woocommerce_add_to_cart_validation', 'dokan_validate_cart_for_single_seller_mode', 10, 2 );
	}
	/**
	 * Register Settings page for the plugin.
	 *
	 * @return void
	 */
	public function sdi_helper_settings() {
		add_options_page( __( 'Shipengine Dokan integration Settings', 'wc-shipengine-integration-for-dokan-multivendor' ), __( 'Shipengine Dokan integration Settings', 'wc-shipengine-integration-for-dokan-multivendor' ), 'manage_options', 'sdi_helper_settings', array( $this, 'sdi_helper_settings_render' ) );
	}
	/**
	 * Include template of settings page.
	 *
	 * @return void
	 */
	public function sdi_helper_settings_render() {
		include SDI_FILE_PATH . 'admin/partials/shipengine-dokan-integration-admin-settings.php';
	}
	/**
	 * Update labels of dokan dashboard.
	 *
	 * @param  mixed $urls Dokan dashboard navbar items.
	 * @return array
	 */
	public function sdi_dokan_rename_dashboard_nav( $urls ) {
		$urls['orders']['title'] = esc_html__( 'Orders', 'wc-shipengine-integration-for-dokan-multivendor' );
		return $urls;
	}
	/**
	 * Add custom message when single vendor mode is enabled.
	 *
	 * @param  mixed $valid Validation flag.
	 * @param  mixed $product_id Product ID.
	 * @return bool
	 */
	public function wc_dokan_validate_cart_for_single_seller_mode( $valid, $product_id ) {
		if ( ! dokan_validate_boolean( dokan_is_single_seller_mode_enable() ) ) {
			return $valid;
		}
		$products                = WC()->cart->get_cart();
		$products[ $product_id ] = array( 'product_id' => $product_id );
		if ( ! $products ) {
			return $valid;
		}
		$vendors = array();
		foreach ( $products as $key => $data ) {
			$product_id = isset( $data['product_id'] ) ? $data['product_id'] : 0;
			$vendor     = dokan_get_vendor_by_product( $product_id );
			$vendor_id  = $vendor && $vendor->get_id() ? $vendor->get_id() : 0;
			if ( ! $vendor_id ) {
				continue;
			}
			if ( ! in_array( $vendor_id, $vendors, true ) ) {
				array_push( $vendors, $vendor_id );
			}
		}
		if ( count( $vendors ) > 1 ) {
			$options             = get_option( 'sdi_helper_options' );
			$single_mode_message = isset( $options['single_mode_message'] ) ? $options['single_mode_message'] : '';
			if ( ! empty( $single_mode_message ) ) {
				wc_add_notice( $single_mode_message, 'error' );
			} else {
				wc_add_notice( __( 'Sorry, you can\'t add more than one vendor\'s product in the cart.', 'wc-shipengine-integration-for-dokan-multivendor' ), 'error' );
			}
			$valid = false;
		}
		return $valid;
	}
	/**
	 * Register sections and fields of settings page.
	 *
	 * @return void
	 */
	public function sdi_helper_register_settings() {
		register_setting( 'sdi_helper_settings', 'sdi_helper_options' );
		add_settings_section( 'sdi_helper_api_settings_section', __( 'API Settings Sections', 'wc-shipengine-integration-for-dokan-multivendor' ), array( $this, 'sdi_helper_plugin_section_text' ), 'sdi_helper_settings' );
		add_settings_field( 'sdi_helper_shipengine_api_key', __( 'Shipengine API Key', 'wc-shipengine-integration-for-dokan-multivendor' ), array( $this, 'sdi_helper_shipengine_api_key' ), 'sdi_helper_settings', 'sdi_helper_api_settings_section' );
		add_settings_field( 'sdi_helper_shipengine_api_timout', __( 'Shipengine API Timeout', 'wc-shipengine-integration-for-dokan-multivendor' ), array( $this, 'sdi_helper_shipengine_api_timout' ), 'sdi_helper_settings', 'sdi_helper_api_settings_section' );
		add_settings_field( 'sdi_helper_shipengine_single_mode_message', __( 'Single vendor mode message', 'wc-shipengine-integration-for-dokan-multivendor' ), array( $this, 'sdi_helper_shipengine_single_mode_message' ), 'sdi_helper_settings', 'sdi_helper_api_settings_section' );
	}
	/**
	 * Section header content.
	 *
	 * @return void
	 */
	public function sdi_helper_plugin_section_text() {
		echo '<p>' . esc_html__( 'API settings', 'wc-shipengine-integration-for-dokan-multivendor' ) . '</p>';
	}
	/**
	 * Render field of API key.
	 *
	 * @return void
	 */
	public function sdi_helper_shipengine_api_key() {
		$options = get_option( 'sdi_helper_options' );
		$api_key = isset( $options['api_key'] ) ? $options['api_key'] : '';
		echo "<input id='sdi_helper_shipengine_api_key' name='sdi_helper_options[api_key]' type='text' class='regular-text' value='" . esc_attr( $api_key ) . "' />";
	}
	/**
	 * Render field of API Timeout.
	 *
	 * @return void
	 */
	public function sdi_helper_shipengine_api_timout() {
		$options = get_option( 'sdi_helper_options' );
		$timeout = isset( $options['api_timeout'] ) ? $options['api_timeout'] : 120;
		echo "<input id='sdi_helper_shipengine_api_timeout' name='sdi_helper_options[api_timeout]' type='text' class='regular-text' value='" . esc_attr( $timeout ) . "' />";
	}
	/**
	 * Render field of single vendor mode message.
	 *
	 * @return void
	 */
	public function sdi_helper_shipengine_single_mode_message() {
		$options             = get_option( 'sdi_helper_options' );
		$single_mode_message = isset( $options['single_mode_message'] ) ? $options['single_mode_message'] : '';
		echo "<input id='sdi_helper_shipengine_single_mode_message' name='sdi_helper_options[single_mode_message]' type='text' class='regular-text' value='" . esc_attr( $single_mode_message ) . "' />";
	}
	/**
	 * Plugin admin notices.
	 *
	 * @return void
	 */
	public function sdi_admin_notices() {
		// Check the reuqired plugins.
		$plugin_messages = array();
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		$required_plugins = array(
			array(
				'name'     => 'WooCommerce',
				'download' => 'https://wordpress.org/plugins/woocommerce/',
				'path'     => 'woocommerce/woocommerce.php',
			),
			array(
				'name'     => 'Dokan Pro',
				'download' => 'https://wedevs.com/dokan',
				'path'     => 'dokan-pro/dokan-pro.php',
			),
		);
		foreach ( $required_plugins as $plugin ) {
			// Check if plugin exists.
			if ( ! is_plugin_active( $plugin['path'] ) ) {
				/* translators: %1$s plugin name %2$s plugin download */
				$plugin_messages[] = sprintf( __( 'Shipengine Dokan integration helper plugin requires you to install the %1$s plugin, you can download it from <a href="%2$s">Here</a>.', 'wc-shipengine-integration-for-dokan-multivendor' ), $plugin['name'], $plugin['download'] );
			}
		}
		if ( count( $plugin_messages ) > 0 ) {
			echo '<div class="error notice">';
			foreach ( $plugin_messages as $message ) {
				echo '<div>' . wp_kses_data( $message ) . '</div>';
			}
			echo '</div>';
		} else {
			$is_single_seller = dokan_is_single_seller_mode_enable() === 'off' ? false : true;
			if ( ! $is_single_seller ) {
				echo '<div class="error notice">';
				esc_html_e( 'You need to enable single seller mode in order to make ShipEngine API work correctly as follow: Dokan >> Settings >> General >> Enable Single Seller Mode.', 'wc-shipengine-integration-for-dokan-multivendor' );
				echo '</div>';
			}
		}
	}
	/**
	 * Create label API when order is status is processing (Payment received (paid) and stock has been reduced).
	 *
	 * @param  int $order_id WC order ID to creata label for.
	 * @return void
	 */
	public function wc_purchase_label( $order_id ) {
		$label_info = $this->shipengine_api->shipengine_purchase_label( $order_id );
		if ( $label_info ) {
			update_post_meta( $order_id, '_shipping_id', isset( $label_info['shipment_id'] ) ? $label_info['shipment_id'] : '' );
			update_post_meta( $order_id, '_shipping_label_id', isset( $label_info['label_id'] ) ? $label_info['label_id'] : '' );
			update_post_meta( $order_id, '_shipping_label_status', isset( $label_info['status'] ) ? $label_info['status'] : '' );
			update_post_meta( $order_id, '_shipping_tracking_number', isset( $label_info['tracking_number'] ) ? $label_info['tracking_number'] : '' );
			update_post_meta( $order_id, '_shipping_label_download', isset( $label_info['label_download'] ) ? $label_info['label_download'] : '' );
			update_post_meta( $order_id, '_shipping_carrier_code', isset( $label_info['carrier_code'] ) ? $label_info['carrier_code'] : '' );
		}
	}
	/**
	 * Helper function to get vendor for order.
	 *
	 * @param  mixed $order Order object.
	 * @return object vendor object.
	 */
	private function wc_shipping_get_order_vendor( $order ) {
		$items = $order->get_items();
		if ( count( $items ) > 0 ) {
			$_product = array_values( $items )[0]->get_product();
		}
		// Get first product so we can get vendor data from it, all products have same vendor since single vendor mode is enabled.
		$vendor = dokan_get_vendor_by_product( $_product->get_id() );
		return $vendor;
	}
	/**
	 * Add track info to the customer order completed email.
	 *
	 * @param  mixed $order Current order ID.
	 * @param  mixed $sent_to_admin send to admin flag.
	 * @param  mixed $plain_text plain text email.
	 * @param  mixed $email email to be sent.
	 * @return void
	 */
	public function wc_shipping_track_url_email( $order, $sent_to_admin, $plain_text, $email ) {
		if ( 'customer_completed_order' !== $email->id ) {
			return;
		}
		$order_id      = $order->get_id();
		$tracking_info = $this->shipengine_api->shipengine_track_package( $order_id );
		$tracking_link = '';
		if ( $tracking_info && is_array( $tracking_info ) ) {
			if ( isset( $tracking_info['tracking_url'] ) ) {
				$tracking_link = $tracking_info['tracking_url'];
			}
		}
		if ( empty( $tracking_link ) ) {
			return;
		}
		$shipping_item   = array_values( $order->get_items( 'shipping' ) )[0];
		$service_title   = $shipping_item->get_name();
		$shipment_id     = get_post_meta( $order_id, '_shipping_id', true );
		$tracking_number = get_post_meta( $order_id, '_shipping_tracking_number', true );
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/emails/shipengine-dokan-integration-shipping-url-info.php';
	}
	/**
	 * Add label info to the vendor new order email.
	 *
	 * @param  mixed $order Current order ID.
	 * @param  mixed $sent_to_admin send to admin flag.
	 * @param  mixed $plain_text plain text email.
	 * @param  mixed $email object email to be sent.
	 * @return void
	 */
	public function wc_shipping_track_label_email( $order, $sent_to_admin, $plain_text, $email ) {
		if ( 'dokan_vendor_new_order' !== $email->id || 0 === count( $order->get_items( 'shipping' ) ) ) {
			// If current email isn't sent to vendor.
			return;
		}
		$shipping_item   = array_values( $order->get_items( 'shipping' ) )[0];
		$service_code    = $shipping_item->get_meta( 'service_code' );
		$service_title   = $shipping_item->get_name();
		$order_id        = $order->get_id();
		$shipment_id     = get_post_meta( $order_id, '_shipping_id', true );
		$label_id        = get_post_meta( $order_id, '_shipping_label_id', true );
		$status          = get_post_meta( $order_id, '_shipping_label_status', true );
		$tracking_number = get_post_meta( $order_id, '_shipping_tracking_number', true );
		$label_download  = get_post_meta( $order_id, '_shipping_label_download', true );
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/emails/shipengine-dokan-integration-shipping-label-info.php';
	}
	/**
	 * Shipping tracking page.
	 *
	 * @param  mixed $order_id Order ID.
	 * @return void
	 */
	public function wc_shipping_tracking_page( $order_id ) {
		$tracking_info = $this->shipengine_api->shipengine_track_package( $order_id );
		$tracking_link = '';
		if ( $tracking_info && is_array( $tracking_info ) ) {
			if ( isset( $tracking_info['tracking_url'] ) ) {
				$tracking_link = $tracking_info['tracking_url'];
			}
		}
		if ( empty( $tracking_link ) ) {
			return;
		}
		$order           = wc_get_order( $order_id );
		$shipping_item   = array_values( $order->get_items( 'shipping' ) )[0];
		$service_title   = $shipping_item->get_name();
		$shipment_id     = get_post_meta( $order_id, '_shipping_id', true );
		$tracking_number = get_post_meta( $order_id, '_shipping_tracking_number', true );
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/shipengine-dokan-integration-shipping-url-info.php';
	}
	/**
	 * Output label download link in vendor orders page.
	 *
	 * @param  array  $actions Array of actions.
	 * @param  object $order Order object.
	 * @return array
	 */
	public function wc_shipengine_label_url( $actions, $order ) {
		$label_download = get_post_meta( $order->get_id(), '_shipping_label_download', true );
		$vendor         = $this->wc_shipping_get_order_vendor( $order );
		if ( $vendor->get_id() === get_current_user_id() &&
		! empty( $label_download ) &&
		is_array( $label_download ) ) {
			$actions['label'] = array(
				'url'    => esc_url( $label_download['href'] ),
				'name'   => __( 'Label download', 'wc-shipengine-integration-for-dokan-multivendor' ),
				'action' => 'Label Download',
				'icon'   => '<i class="fa fa-file">&nbsp;</i>',
			);
		}
		return $actions;
	}
	/**
	 * Output tracking info link in customer orders page.
	 *
	 * @param  array  $actions Array of actions.
	 * @param  object $order Order object.
	 * @return array
	 */
	public function wc_shipengine_info_url( $actions, $order ) {
		$order_id      = $order->get_id();
		$tracking_info = $this->shipengine_api->shipengine_track_package( $order_id );
		$tracking_link = '';
		if ( $tracking_info && is_array( $tracking_info ) ) {
			if ( isset( $tracking_info['tracking_url'] ) ) {
				$tracking_link = $tracking_info['tracking_url'];
			}
		}
		if ( empty( $tracking_link ) ) {
			return $actions;
		}
		$actions['tracking'] = array(
			'url'    => esc_url( $tracking_link ),
			'name'   => __( 'Tracking Link', 'wc-shipengine-integration-for-dokan-multivendor' ),
			'action' => 'Tracking Link',
			'icon'   => '<i class="fa fa-map-marker">&nbsp;</i>',
		);
		return $actions;
	}
	/**
	 * Output URL of package tracking URL in vendor orders page.
	 *
	 * @param  array  $actions Array of actions.
	 * @param  object $order Order object.
	 * @return array
	 */
	public function wc_shipengine_track_url( $actions, $order ) {
		$tracking_info = $this->shipengine_api->shipengine_track_package( $order->get_id() );
		if ( $tracking_info && is_array( $tracking_info ) ) {
			if ( isset( $tracking_info['tracking_url'] ) ) {
				$actions['tracking'] = array(
					'url'    => esc_url( $tracking_info['tracking_url'] ),
					'name'   => __( 'Tracking Link', 'wc-shipengine-integration-for-dokan-multivendor' ),
					'action' => 'Tracking Link',
					'icon'   => '<i class="fa fa-map-marker">&nbsp;</i>',
				);
			}
		}
		return $actions;
	}
}

