<?php
/**
 * Seller Info Template
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/dashboard/settings/seller-info.php
 *
 * @author        Jamie Madden, WC Vendors
 * @package       WCVendors/Templates/Emails/HTML
 * @version       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>


<div id="pv_seller_info_container">
	<p>
		<b><?php echo apply_filters( 'wcvendors_seller_info_label', __( 'Seller info', 'wc-vendors' ) ); ?></b><br/>
		<?php _e( 'This is displayed on each of your products.', 'wc-vendors' ); ?></p>

	<p>
		<?php

		if ( $global_html || $has_html ) {
			$old_post        = $GLOBALS['post'];
			$GLOBALS['post'] = 0;
			wp_editor( $seller_info, 'pv_seller_info' );
			$GLOBALS['post'] = $old_post;
		} else {
			?>
			<textarea class="large-text" rows="10" id="pv_seller_info_unhtml" style="width:95%"
			          name="pv_seller_info"><?php echo $seller_info; ?></textarea>
			<?php
		}

		?>
	</p>
</div>
