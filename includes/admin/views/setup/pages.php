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
				<td class="table-desc"><?php _e( 'Dashboard', 'wcvendors' ); ?>

				</td>
				<td class="table-check">
					<?php wcv_single_select_page( 'wcvendors_dashboard_page_id', $dashboard_page_id, 'wc-enhanced-select' ); ?>
				</td>
			</tr>
			<tr>
				<td colspan="3" class="tool-tip">
					<?php _e( 'This page should contain the following shortcode. <code>[wcvendors_dashboard]</code>', 'wcvendors' ); ?>
				</td>
			</tr>
			<tr>
				<td class="table-desc"><?php printf( __( 'Vendors', 'wcvendors' ), ucfirst( wcv_get_vendor_name( false ) ) ); ?></td>
				<td class="table-check">
					<?php wcv_single_select_page( 'wcvendors_vendors_page_id', $vendors_page_id, 'wc-enhanced-select' ); ?>
				</td>
			</tr>
			<tr>
				<td colspan="3" class="tool-tip">
					<?php _e( 'This page should contain the following shortcode. <code>[wcvendors_stores]</code>', 'wcvendors' ); ?>
				</td>
			</tr>
			<tr>
				<td class="table-desc"><?php _e( 'Terms & Conditions', 'wcvendors' ); ?></td></td>
				<td class="table-check">
					<?php wcv_single_select_page( 'wcvendors_vendor_terms_page_id', $terms_page_id, 'wc-enhanced-select' ); ?>
				</td>
			</tr>
			<tr>
				<td colspan="3" class="tool-tip">
					<?php printf( __( 'This sets the page used to display the terms and conditions when a %s signs up.', 'wcvendors' ), lcfirst( wcv_get_vendor_name( false ) ) ); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="wcv-setup-actions step">
		<button type="submit" class="button button-next" value="<?php esc_attr_e( 'Next', 'wcvendors' ); ?>" name="save_step"><?php esc_html_e( 'Next', 'wcvendors' ); ?></button>
	</p>
</form>
