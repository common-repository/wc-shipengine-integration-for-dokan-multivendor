<?php
/**
 * This class resposibes for ShipEngine API calling.
 *
 * @link       https://wpspins.com?utm-ref=shipengine-dokan-integration
 * @since      1.0.0
 *
 * @package    shipengine-dokan-integration
 * @subpackage shipengine-dokan-integration/includes
 */

use WeDevs\DokanPro\Modules\DeliveryTime\StorePickup\Helper;

/**
 * This class resposibes for ShipEngine API calling.
 *
 * @package    shipengine-dokan-integration
 * @subpackage shipengine-dokan-integration/includes
 * @author     WPSPIN LLC <mike@wpxhouston.com>
 */
class Shipengine_Dokan_Integration_Api {
	/**
	 * API key of Shipenigne.
	 *
	 * @var mixed
	 */
	protected $api_key;
	/**
	 * API base URL;
	 *
	 * @var mixed
	 */
	protected $base_url;
	/**
	 * List of endpoints.
	 *
	 * @var array
	 */
	protected $end_points;
	/**
	 * API dimension units.
	 *
	 * @var array
	 */
	protected $api_dimension_units;
	/**
	 * API weight units.
	 *
	 * @var array
	 */
	protected $api_weight_units;
	/**
	 * Define the auto functionality of the class.
	 *
	 * @return void
	 */
	public function __construct() {
		$options = get_option( 'sdi_helper_options' );
		if ( ! empty( $options ) && isset( $options['api_key'] ) ) {
			$this->api_key = $options['api_key'];
		}
		if ( ! empty( $options ) && isset( $options['time_out'] ) ) {
			$this->time_out = intval( $options['time_out'] );
		} else {
			$this->time_out = 120;
		}
		$this->base_url   = 'https://api.shipengine.com';
		$this->end_points = array(
			'get_carriers'       => '/v1/carriers',
			'get_shipping_rates' => '/v1/rates',
			'estimate_rate'      => '/v1/rates/estimate',
			'purchase_label'     => '/v1/labels',
			'track_package'      => '/v1/tracking',
		);
		// Set units mapping.
		$this->api_dimension_units = array(
			'in' => 'inch',
			'cm' => 'centimeter',
		);
		$this->api_weight_units    = array(
			'oz'  => 'ounce',
			'lbs' => 'pound',
			'g'   => 'gram',
			'kg'  => 'kilogram',
		);
	}
	/**
	 * Get full path of API using key.
	 *
	 * @param  mixed $path_key key of API to get.
	 * @param  array $args get request args.
	 * @return string
	 */
	public function shipengine_get_api_path( $path_key, $args = null ) {
		if ( ! $args ) {
			return $this->base_url . $this->end_points[ $path_key ];
		}
		return add_query_arg( $args, $this->base_url . $this->end_points[ $path_key ] );
	}
	/**
	 * Return list of carriers.
	 *
	 * @return mixed
	 */
	public function shipeigne_get_carriers() {
		$response = wp_remote_get(
			$this->shipengine_get_api_path( 'get_carriers' ),
			array(
				'timeout' => $this->time_out,
				'headers' => array(
					'api-key' => $this->api_key,
				),
			),
		);

		if ( is_array( $response ) && ! is_wp_error( $response ) && ! isset( $response['errors'] ) ) {
			$response_body = wp_remote_retrieve_body( $response );
			return json_decode( $response_body, true );
		}
		return false;
	}
	/**
	 * ShipEngine estimate rates.
	 *
	 * @param  array $package package array of shipment.
	 * @return mixed false or lowest rate array.
	 */
	public function shipengine_estimate_rates( $package ) {
		$carriers     = $this->shipeigne_get_carriers();
		$carrier_list = array();
		if ( ! $carriers ) {
			return false;
		}
		foreach ( $carriers['carriers'] as $carrier ) {
			$carrier_list [ $carrier['carrier_id'] ] = $carrier['friendly_name'];
		}
		$carriers_ids = array_keys( $carrier_list );
		if ( empty( $package['contents'] ) ) {
			return false;
		}
		$_product               = array_values( $package['contents'] )[0]['data'];
		$vendor                 = dokan_get_vendor_by_product( $_product->get_id() );
		$vendor_store_locations = Helper::get_vendor_store_pickup_locations( $vendor->get_id(), false, true );
		$vendor_store_location  = isset( $vendor_store_locations[0] ) ? $vendor_store_locations[0] : array();
		$package_address        = $package['destination'];
		$package_user           = $package['user'];
		$from_country_code      = isset( $vendor_store_location['country'] ) ? $vendor_store_location['country'] : '';
		$from_postal_code       = isset( $vendor_store_location['zip'] ) ? $vendor_store_location['zip'] : '';
		$to_country_code        = isset( $package_address['country'] ) ? $package_address['country'] : '';
		$to_postal_code         = isset( $package_address['postcode'] ) ? $package_address['postcode'] : '';
		$to_city_locality       = isset( $package_address['city'] ) ? $package_address['city'] : '';
		$to_state_province      = isset( $package_address['state'] ) ? $package_address['state'] : '';
		// Get default WooCommerce measurements units.
		$wc_weight_unit = get_option( 'woocommerce_weight_unit' );
		// Prepare API measurements units.
		$api_weight_unit = $this->api_weight_units[ $wc_weight_unit ];
		$total_weight    = 0;
		foreach ( $package['contents'] as $item_id => $values ) {
			$_product       = $values['data'];
			$product_weight = $_product->get_weight();
			$total_weight  += floatval( $product_weight );
		}
		$weight = array(
			'value' => $total_weight,
			'unit'  => $api_weight_unit,
		);
		// Build Request body.
		$request_body                                  = array();
		$request_body['carrier_ids']                   = $carriers_ids;
		$request_body['from_country_code']             = $from_country_code;
		$request_body['from_postal_code']              = $from_postal_code;
		$request_body['to_country_code']               = $to_country_code;
		$request_body['to_postal_code']                = $to_postal_code;
		$request_body['to_city_locality']              = $to_city_locality;
		$request_body['to_state_province']             = $to_state_province;
		$request_body['weight']                        = $weight;
		$request_body['confirmation']                  = 'none';
		$request_body['address_residential_indicator'] = 'no';
		// Parse body to json.
		$request_body = wp_json_encode( $request_body );
		// Buid request.
		$response = wp_remote_post(
			$this->shipengine_get_api_path( 'estimate_rate' ),
			array(
				'body'    => $request_body,
				'timeout' => $this->time_out,
				'headers' => array(
					'api-key'      => $this->api_key,
					'Content-Type' => 'application/json',
				),
			),
		);
		if ( is_array( $response ) && ! is_wp_error( $response ) && ! isset( $response['errors'] ) ) {
			$response_body = wp_remote_retrieve_body( $response );
			$response_body = json_decode( $response_body, true );
			$rates         = $response_body;
			if ( count( $rates ) > 0 ) {
				$lowest_rate_index = -1;
				$lowest_rate       = PHP_INT_MAX;
				foreach ( $rates as $index => $rate ) {
					if ( $rate['shipping_amount']['amount'] < $lowest_rate ) {
						$lowest_rate       = $rate['shipping_amount']['amount'];
						$lowest_rate_index = $index;
					}
				}
				return $rates[ $lowest_rate_index ];
			}
			return false;
		}
		return false;
	}
	/**
	 * Get lowest shipping rates API.
	 *
	 * @param  array $package package array of shipment.
	 * @return mixed false or lowest rate array.
	 */
	public function shipengine_get_lowest_shipping_rate( $package ) {
		if ( ! isset( $package['destination']['address'] ) || empty( $package['destination']['address'] ) ) {
			return $this->shipengine_estimate_rates( $package );
		}
		$carriers     = $this->shipeigne_get_carriers();
		$carrier_list = array();
		if ( ! $carriers ) {
			return false;
		}
		foreach ( $carriers['carriers'] as $carrier ) {
			$carrier_list [ $carrier['carrier_id'] ] = $carrier['friendly_name'];
		}
		$carriers_ids = array_keys( $carrier_list );
		if ( empty( $package['contents'] ) ) {
			return false;
		}
		// Get first product so we can get vendor data from it, all products have same vendor since single vendor mode is enabled.
		$_product               = array_values( $package['contents'] )[0]['data'];
		$vendor                 = dokan_get_vendor_by_product( $_product->get_id() );
		$vendor_store_locations = Helper::get_vendor_store_pickup_locations( $vendor->get_id(), false, true );
		$vendor_store_location  = isset( $vendor_store_locations[0] ) ? $vendor_store_locations[0] : array();
		$package_address        = $package['destination'];
		$package_user           = $package['user'];
		$ship_from_address      = array(
			'company_name'   => $vendor->get_shop_name(),
			'name'           => $vendor->get_name(),
			'phone'          => $vendor->get_phone(),
			'address_line1'  => isset( $vendor_store_location['street_1'] ) ? $vendor_store_location['street_1'] : '',
			'address_line2'  => isset( $vendor_store_location['street_2'] ) ? $vendor_store_location['street_2'] : '',
			'city_locality'  => isset( $vendor_store_location['city'] ) ? $vendor_store_location['city'] : '',
			'state_province' => isset( $vendor_store_location['state'] ) ? $vendor_store_location['state'] : '',
			'postal_code'    => isset( $vendor_store_location['zip'] ) ? $vendor_store_location['zip'] : '',
			'country_code'   => isset( $vendor_store_location['country'] ) ? $vendor_store_location['country'] : '',
		);
		$ship_to_address        = array(
			'name'           => wp_get_current_user()->display_name,
			'address_line1'  => isset( $package_address['address'] ) ? $package_address['address'] : '',
			'address_line2'  => isset( $package_address['address_2'] ) ? $package_address['address_2'] : '',
			'city_locality'  => isset( $package_address['city'] ) ? $package_address['city'] : '',
			'state_province' => isset( $package_address['state'] ) ? $package_address['state'] : '',
			'postal_code'    => isset( $package_address['postcode'] ) ? $package_address['postcode'] : '',
			'country_code'   => isset( $package_address['country'] ) ? $package_address['country'] : '',
		);
		// Get default WooCommerce measurements units.
		$wc_weight_unit    = get_option( 'woocommerce_weight_unit' );
		$wc_dimension_unit = get_option( 'woocommerce_dimension_unit' );
		// Prepare API measurements units.
		$api_weight_unit    = $this->api_weight_units[ $wc_weight_unit ];
		$api_dimension_unit = $this->api_dimension_units[ $wc_dimension_unit ];
		// All packages wrapper.
		$packages = array();
		foreach ( $package['contents'] as $item_id => $values ) {
			$_product = $values['data'];
			// Get product dimensions info.
			$product_weight = $_product->get_weight();
			$product_height = $_product->get_height();
			$product_length = $_product->get_length();
			$product_width  = $_product->get_width();
			// Build package array.
			$package     = array(
				'weight'     => array(
					'value' => $product_weight,
					'unit'  => $api_weight_unit,
				),
				'dimensions' => array(
					'unit'   => $api_dimension_unit,
					'length' => $product_length,
					'width'  => $product_width,
					'height' => $product_height,
				),
			);
			$packages [] = $package;
		}
		// Build Request body.
		$request_body                 = array();
		$request_body['rate_options'] = array( 'carrier_ids' => $carriers_ids );
		$request_body['shipment']     = array(
			'validate_address' => 'no_validation',
			'ship_from'        => $ship_from_address,
			'ship_to'          => $ship_to_address,
			'packages'         => $packages,
		);
		// Parse the request body to JSON.
		$request_body = wp_json_encode( $request_body );
		$response     = wp_remote_post(
			$this->shipengine_get_api_path( 'get_shipping_rates' ),
			array(
				'body'    => $request_body,
				'timeout' => $this->time_out,
				'headers' => array(
					'api-key'      => $this->api_key,
					'Content-Type' => 'application/json',
				),
			),
		);
		if ( is_array( $response ) && ! is_wp_error( $response ) && ! isset( $response['errors'] ) ) {
			$response_body = wp_remote_retrieve_body( $response );
			$response_body = json_decode( $response_body, true );
			if ( isset( $response_body['rate_response'] ) ) {
				$rate_response = $response_body['rate_response'];
				$rates         = $rate_response['rates'];
				if ( count( $rates ) > 0 ) {
					$lowest_rate_index = -1;
					$lowest_rate       = PHP_INT_MAX;
					foreach ( $rates as $index => $rate ) {
						if ( $rate['shipping_amount']['amount'] < $lowest_rate ) {
							$lowest_rate       = $rate['shipping_amount']['amount'];
							$lowest_rate_index = $index;
						}
					}
					return $rates[ $lowest_rate_index ];
				}
			}
			return false;
		}
		return false;
	}
	/**
	 * Create label API.
	 *
	 * @param  int $order_id WC order ID to creata label for.
	 * @return array
	 */
	public function shipengine_purchase_label( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( 0 === count( $order->get_items( 'shipping' ) ) ) {
			return;
		}
		$shipping_item = array_values( $order->get_items( 'shipping' ) )[0];
		$service_code  = $shipping_item->get_meta( 'service_code' );
		$items         = $order->get_items();
		if ( count( $items ) > 0 ) {
			$_product = array_values( $items )[0]->get_product();
		}
		// Get first product so we can get vendor data from it, all products have same vendor since single vendor mode is enabled.
		$vendor                 = dokan_get_vendor_by_product( $_product->get_id() );
		$vendor_store_locations = Helper::get_vendor_store_pickup_locations( $vendor->get_id(), false, true );
		$vendor_store_location  = isset( $vendor_store_locations[0] ) ? $vendor_store_locations[0] : array();
		$ship_from_address      = array(
			'name'           => $vendor->get_name(),
			'phone'          => $vendor->get_phone(),
			'address_line1'  => isset( $vendor_store_location['street_1'] ) ? $vendor_store_location['street_1'] : '',
			'address_line2'  => isset( $vendor_store_location['street_2'] ) ? $vendor_store_location['street_2'] : '',
			'city_locality'  => isset( $vendor_store_location['city'] ) ? $vendor_store_location['city'] : '',
			'state_province' => isset( $vendor_store_location['state'] ) ? $vendor_store_location['state'] : '',
			'postal_code'    => isset( $vendor_store_location['zip'] ) ? $vendor_store_location['zip'] : '',
			'country_code'   => isset( $vendor_store_location['country'] ) ? $vendor_store_location['country'] : '',
		);
		$ship_to_address        = array(
			'name'           => $order->get_formatted_shipping_full_name(),
			'address_line1'  => $order->get_shipping_address_1(),
			'address_line2'  => $order->get_shipping_address_2(),
			'city_locality'  => $order->get_shipping_city(),
			'state_province' => $order->get_shipping_state(),
			'postal_code'    => $order->get_shipping_postcode(),
			'country_code'   => $order->get_shipping_country(),
		);
		// Get default WooCommerce measurements units.
		$wc_weight_unit    = get_option( 'woocommerce_weight_unit' );
		$wc_dimension_unit = get_option( 'woocommerce_dimension_unit' );
		// Prepare API measurements units.
		$api_weight_unit    = $this->api_weight_units[ $wc_weight_unit ];
		$api_dimension_unit = $this->api_dimension_units[ $wc_dimension_unit ];
		// All packages wrapper.
		$packages = array();
		foreach ( $order->get_items() as $item_id => $item ) {
			$_product = $item->get_product();
			// Get product dimensions and weight info.
			$product_weight = $_product->get_weight();
			$product_height = $_product->get_height();
			$product_length = $_product->get_length();
			$product_width  = $_product->get_width();
			// Build package array.
			$package     = array(
				'weight'     => array(
					'value' => $product_weight,
					'unit'  => $api_weight_unit,
				),
				'dimensions' => array(
					'unit'   => $api_dimension_unit,
					'length' => $product_length,
					'width'  => $product_width,
					'height' => $product_height,
				),
			);
			$packages [] = $package;
		}
		$request_body             = array();
		$request_body['shipment'] = array(
			'service_code'     => $service_code,
			'validate_address' => 'no_validation',
			'ship_from'        => $ship_from_address,
			'ship_to'          => $ship_to_address,
			'packages'         => $packages,
		);
		// Parse the request body to JSON.
		$request_body = wp_json_encode( $request_body );
		$response     = wp_remote_post(
			$this->shipengine_get_api_path( 'purchase_label' ),
			array(
				'body'    => $request_body,
				'timeout' => $this->time_out,
				'headers' => array(
					'api-key'      => $this->api_key,
					'Content-Type' => 'application/json',
				),
			),
		);
		if ( is_array( $response ) && ! is_wp_error( $response ) && ! isset( $response['errors'] ) ) {
			$response_body = wp_remote_retrieve_body( $response );
			$response_body = json_decode( $response_body, true );
			if ( isset( $response_body['label_download'] ) && is_array( $response_body['label_download'] ) ) {
				$label_download = esc_url( $response_body['label_download']['href'] );
			} else {
				$label_download = '';
			}
			/* translators: %1$s tracking number %2$s label download link */
			$note = sprintf( __( 'Shipping label was created successfully tracking number %1$s label download link %2$s', 'wc-shipengine-integration-for-dokan-multivendor' ), $response_body['tracking_number'], $label_download );
			$order->add_order_note( $note );
			return $response_body;
		} elseif ( ! is_array( $response ) || is_wp_error( $response ) ) {
			$note = __( 'Couldn\t create shipping label due to internal error.', 'wc-shipengine-integration-for-dokan-multivendor' );
			$order->add_order_note( $note );
		} elseif ( isset( $response['errors'] ) ) {
			$errors_list = array();
			foreach ( $response['errors'] as $error ) {
				$errors_list[] = $error['message'];
			}
			$note = __( 'Couldn\t create shipping label due to API errors:', 'wc-shipengine-integration-for-dokan-multivendor' );
			$order->add_order_note( $note );
		}
		return false;
	}
	/**
	 * Get realtime info of order package tracking.
	 *
	 * @param  mixed $order_id order ID to get its track info.
	 * @return mixed
	 */
	public function shipengine_track_package( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( 0 === count( $order->get_items( 'shipping' ) ) ) {
			return;
		}
		$shipping_item   = array_values( $order->get_items( 'shipping' ) )[0];
		$carrier_code    = get_post_meta( $order_id, '_shipping_carrier_code', true );
		$tracking_number = get_post_meta( $order_id, '_shipping_tracking_number', true );
		if ( ! empty( $carrier_code ) && ! empty( $tracking_number ) ) {
			$get_args = array(
				'carrier_code'    => $carrier_code,
				'tracking_number' => $tracking_number,
			);
			$response = wp_remote_get(
				$this->shipengine_get_api_path( 'track_package', $get_args ),
				array(
					'timeout' => $this->time_out,
					'headers' => array(
						'api-key'      => $this->api_key,
						'Content-Type' => 'application/json',
					),
				),
			);
			if ( is_array( $response ) && ! is_wp_error( $response ) && ! isset( $response['errors'] ) ) {
				$response_body = wp_remote_retrieve_body( $response );
				return json_decode( $response_body, true );
			}
			return false;
		}
		return '';
	}
}
