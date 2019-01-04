<?php
/**
 * Shop Name Template
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/dashboard/settings/shop-name.php
 *
 * @author        Jamie Madden, WC Vendors
 * @package       WCVendors/Templates/Emails/HTML
 * @version       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="pv_shop_name_container">
	<p><b><?php _e( 'Shop Name', 'wc-vendors' ); ?></b><br/>
		<?php _e( 'Your shop name is public and must be unique.', 'wc-vendors' ); ?><br/>

		<input type="text" name="pv_shop_name" id="pv_shop_name" placeholder="Your shop name"
		       value="<?php echo get_user_meta( $user_id, 'pv_shop_name', true ); ?>"/>
	</p>
</div>
