<?php
/**
 * Provide a template for ShipEngine shipping label info email.
 *
 * This file is used to markup the shipping label info template.
 *
 * @link       https://wpspins.com?utm-ref=shipengine-dokan-integration
 * @since      1.0.0
 *
 * @package    shipengine-dokan-integration
 * @subpackage shipengine-dokan-integration/admin/partials
 */

?>
<h2>
<?php esc_html_e( 'Shipping Label Info', 'wc-shipengine-integration-for-dokan-multivendor' ); ?>
</h2>
<table>
	<thead>
		<tr>
			<th><?php esc_html_e( 'Shipping company', 'wc-shipengine-integration-for-dokan-multivendor' ); ?></th>
			<th><?php esc_html_e( 'Tracking number', 'wc-shipengine-integration-for-dokan-multivendor' ); ?></th>
			<th><?php esc_html_e( 'Label download link', 'wc-shipengine-integration-for-dokan-multivendor' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th>
				<?php echo esc_html( $service_title ); ?>
			</th>
			<th>
				<?php echo esc_html( $tracking_number ); ?>
			</th>
			<th>
				<a href="<?php echo ! empty( $label_download ) && is_array( $label_download ) ? esc_url( $label_download['href'] ) : ''; ?>" target="_blank"><?php esc_html_e( 'Download shipping label', 'wc-shipengine-integration-for-dokan-multivendor' ); ?></a>
			</th>
		</tr>
	</tbody>
</table>
