<?php
/**
 * Shipengine Shipment method.
 *
 * @link       https://wpspins.com?utm-ref=shipengine-dokan-integration
 * @since      1.0.0
 *
 * @package    shipengine-dokan-integration
 * @subpackage shipengine-dokan-integration/public
 */

if ( ! class_exists( 'Shipengine_Dokan_Integration_Shipment' ) ) {
	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-shipengine-dokan-integration-api.php';
	/**
	 * Sdi_WC_Shipengine_Method
	 */
	class Shipengine_Dokan_Integration_Shipment extends WC_Shipping_Method {
		/**
		 * Constructor for your shipping class
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->id                 = 'Shipengine_Dokan_Integration_Shipment';
			$this->method_title       = __( 'Shipengine Shipping', 'wc-shipengine-integration-for-dokan-multivendor' );
			$this->method_description = __( 'Shipengine service integration', 'wc-shipengine-integration-for-dokan-multivendor' );
			$this->title              = 'Shipegine Shipment';
			$this->enabled            = $this->get_option( 'enabled' );
			$this->tax_status         = $this->get_option( 'tax_status' );
			$this->shipengine_api     = new Shipengine_Dokan_Integration_Api();
			$this->init();
		}
		/**
		 * Checking is gateway enabled or not
		 *
		 * @return boolean [description]
		 */
		public function is_method_enabled() {
			return 'yes' === $this->enabled;
		}

		/**
		 * Init your settings
		 *
		 * @access public
		 * @return void
		 */
		public function init() {
			$this->init_form_fields();
			$this->init_settings();
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}
		/**
		 * Define settings field for this shipping
		 *
		 * @return void
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'    => array(
					'title'       => __( 'Enable', 'wc-shipengine-integration-for-dokan-multivendor' ),
					'type'        => 'checkbox',
					'description' => __( 'Enable this shipping.', 'wc-shipengine-integration-for-dokan-multivendor' ),
					'default'     => 'yes',
				),
				'tax_status' => array(
					'title'   => __( 'Tax Status', 'wc-shipengine-integration-for-dokan-multivendor' ),
					'type'    => 'select',
					'default' => 'taxable',
					'options' => array(
						'taxable' => __( 'Taxable', 'wc-shipengine-integration-for-dokan-multivendor' ),
						'none'    => _x( 'None', 'Tax status', 'wc-shipengine-integration-for-dokan-multivendor' ),
					),
				),
			);
		}
		/**
		 * Calculate_shipping function.
		 *
		 * @access public
		 * @param mixed $package package.
		 * @return void
		 */
		public function calculate_shipping( $package = array() ) {
			if ( ! $this->is_method_enabled() ) {
				return;
			}
			$lowest_rate = $this->shipengine_api->shipengine_get_lowest_shipping_rate( $package );
			if ( $lowest_rate ) {
				$tax_rate = 'none' === $this->tax_status ? false : '';
				$rate     = array(
					'id'        => $lowest_rate['service_code'],
					'label'     => $lowest_rate['carrier_friendly_name'],
					'cost'      => $lowest_rate['shipping_amount']['amount'],
					'meta_data' => array(
						'service_code' => $lowest_rate['service_code'],
					),
					'taxes'     => $tax_rate,
					'package'   => $package,
				);
				$this->add_rate( $rate );
			}
		}
	}
}
