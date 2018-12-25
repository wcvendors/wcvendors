<?php
/**
 * Vendor List Template
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/front/vendors-list.php
 *
 * @author        Jamie Madden, WC Vendors
 * @package       WCVendors/Templates/Emails/HTML
 * @version       2.0.0
 *
 *    Template Variables available
 *  $shop_name : pv_shop_name
 *  $shop_description : pv_shop_description (completely sanitized)
 *  $shop_link : the vendor shop link
 *  $vendor_id  : current vendor id for customization
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="vendor_list" style="display:inline-block; margin-right:10%;">
	<center>
		<a href="<?php echo $shop_link; ?>"><?php echo get_avatar( $vendor_id, 200 ); ?></a><br/>
		<a href="<?php echo $shop_link; ?>" class="button"><?php echo $shop_name; ?></a>
		<br/><br/>
	</center>
</div>
