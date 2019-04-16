<?php
/**
 * Admin View: Quick Edit Products
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<fieldset class="inline-edit-col-left">
	<div id="wcvendors-fields-bulk" class="inline-edit-col">
			<h4><?php _e( 'WC Vendors', 'wcvendors' ); ?></h4>

			<?php do_action( 'wcvendors_product_quick_edit_start' ); ?> 

			<!-- Vendor Name -->
			<label class="inline-edit-author-vendor">
				<span class="title"><?php echo wcv_get_vendor_name(); ?></span>
				<span class="input-text-wrap">
					<?php
					wp_dropdown_users(
						array(
							'echo'     => true,
							'name'     => 'post_author-vendor',
							'role__in' => array(
								'vendor',
								'administrator',
							),
						)
					);
					?>
				</span>
			</label>

			<?php if ( apply_filters( 'wcvendors_quick_edit_commission', true ) ) : ?>
				<label class="inline-edit-commission-rate">
				<span class="title"><?php _e( 'Commission', 'wcvendors' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="_wcv_commission_rate" class="text wcv_commission_rate" placeholder="<?php esc_attr_e( 'Commission rate %', 'wcvendors' ); ?>" value="" />
				</span>
			</label>
			<?php endif; ?>

			<?php do_action( 'wcvendors_product_quick_edit_end' ); ?> 

		<input type="hidden" name="wcvendors_quick_edit" value="1" />
		<input type="hidden" name="wcvendors_quick_edit_nonce" value="<?php echo wp_create_nonce( 'wcvendors_quick_edit_nonce' ); ?>" />
	</div>
</fieldset>
			
