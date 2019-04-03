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
	<p class="store-setup"><?php esc_html_e( 'The following wizard will help you configure your marketplace and get your vendors onboard quickly.', 'wc-vendors' ); ?></p>

	<table class="wcv-setup-table">
		<thead>
		<tr>
			<td class="table-desc"><strong><?php _e( 'General', 'wc-vendors' ); ?></strong></td>
			<td class="table-check"></td>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td class="table-desc"><?php printf( __( 'Allow users to apply to become a %s', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ); ?></td>
			<td class="table-check">
				<input
						type="checkbox"
						class="option_checkbox"
						id="wcv_vendor_allow_registration"
						name="wcv_vendor_allow_registration"
						value="yes"
					<?php checked( $allow_registration, 'yes' ); ?>
				/>
			</td>
		</tr>
		<tr>
			<td class="table-desc"><?php printf( __( 'Manually approve %s applications', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ); ?></td>
			<td class="table-check">
				<input
						type="checkbox"
						class="option_checkbox"
						id="wcv_vendor_approve_registration"
						name="wcv_vendor_approve_registration"
						value="yes"
					<?php checked( $manual_approval, 'yes' ); ?>
				/>
			</td>
		</tr>
		<tr>
			<td class="table-desc"><?php printf( __( 'Give any taxes to %s', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ); ?></td>
			</td>
			<td class="table-check">
				<input
						type="checkbox"
						class="option_checkbox"
						id="wcv_vendor_give_taxes"
						name="wcv_vendor_give_taxes"
						value="yes"
					<?php checked( $vendor_taxes, 'yes' ); ?>
				/>
			</td>
		</tr>
		<tr>
			<td class="table-desc"><?php printf( __( 'Give any shipping to %s', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ); ?></td>
			</td>
			<td class="table-check">
				<input
						type="checkbox"
						class="option_checkbox"
						id="wcv_vendor_give_shipping"
						name="wcv_vendor_give_shipping"
						value="yes"
					<?php checked( $vendor_shipping, 'yes' ); ?>
				/>
			</td>
		</tr>
		</tbody>
	</table>

	<strong><?php _e( 'Commission', 'wc-vendors' ); ?></strong>

	<p class="store-setup"><?php _e( 'Commissions are calculated per product. The commission rate can be set globally, at a vendor level or at a product level.', 'wc-vendors' ); ?></p>

	<!-- Vendor commission rate -->
	<p class="store-setup wcv-setup-input">
		<label class="" for="">
			<?php esc_html_e( 'Global Commission Rate %', 'wc-vendors' ); ?>
		</label>
		<input
				type="text"
				id="wcv_vendor_commission_rate"
				name="wcv_vendor_commission_rate"
				placeholder="%"
				value="<?php echo $commission_rate; ?>"
		/>
	</p>
	<p class="wcv-setup-actions step">
		<button type="submit" class="button button-next" value="<?php esc_attr_e( 'Next', 'wc-vendors' ); ?>"
		        name="save_step"><?php esc_html_e( 'Next', 'wc-vendors' ); ?></button>
	</p>
</form>
