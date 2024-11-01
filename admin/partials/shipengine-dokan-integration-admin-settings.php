<?php
/**
 * Settings page for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wpspins.com?utm-ref=shipengine-dokan-integration
 * @since      1.0.0
 *
 * @package    shipengine-dokan-integration
 * @subpackage shipengine-dokan-integration/admin/partials
 */

?>
<h2>
	<?php esc_attr_e( 'Shipengine Dokan integration settings page', 'wc-shipengine-integration-for-dokan-multivendor' ); ?>
</h2>
<form action="options.php" method="post">
<?php
	settings_fields( 'sdi_helper_settings' );
	do_settings_sections( 'sdi_helper_settings' );
?>
	<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
</form>
