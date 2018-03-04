<?php
/**
 * Admin View: Step One
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<form method="post">
	<?php wp_nonce_field( 'wcv-setup' ); ?>
	<p class="store-setup"><?php printf( __( 'Select the pages for relevant frontend features for %s', 'wcvendors' ), lcfirst( wcv_get_vendor_name( false ) ) ); ?></p>

	<table class="wcv-setup-table-pages">
		<thead>
			<tr>
				<td class="table-desc"><strong><?php _e( 'Pages', 'wcvendors' ); ?></strong></td>
				<td class="table-check"></td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="table-desc"><?php _e( 'Vendor Dashboard', 'wcvendors' ); ?>

				</td>
				<td class="table-check">
					<?php wcv_single_select_page( 'vendor_dashboard_page', $dashboard_page, 'wc-enhanced-select' ); ?>
				</td>
			</tr>
			<tr>
				<td colspan="3" class="tool-tip">
					<?php _e( 'This page should contain the following shortcode. <code>[wcv_vendor_dashboard]</code>', 'wcvendors' ); ?>
				</td>
			</tr>
			<tr>
				<td class="table-desc"><?php printf( __( 'Shop Settings', 'wcvendors' ), ucfirst( wcv_get_vendor_name( false ) ) ); ?></td>
				<td class="table-check">
					<?php wcv_single_select_page( 'shop_setttings_page', $shop_settings_page, 'wc-enhanced-select' ); ?>
				</td>
			</tr>
			<tr>
				<td colspan="3" class="tool-tip">
					<?php _e( 'This page should contain the following shortcode. <code>[wcv_shop_settings]</code>', 'wcvendors' ); ?>
				</td>
			</tr>
			<tr>
				<td class="table-desc"><?php _e( 'Orders Page', 'wcvendors' ); ?></td></td>
				<td class="table-check">
					<?php wcv_single_select_page( 'product_orders_page', $orders_page, 'wc-enhanced-select' ); ?>
				</td>
			</tr>
			<tr>
				<td colspan="3" class="tool-tip">
					<?php _e( 'This page should contain the following shortcode. <code>[wcv_orders]</code>', 'wcvendors' ); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="wcv-setup-actions step">
		<button type="submit" class="button button-next" value="<?php esc_attr_e( "Next", 'wcvendors' ); ?>" name="save_step"><?php esc_html_e( "Next", 'wcvendors' ); ?></button>
	</p>
</form>
