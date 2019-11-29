<?php
/**
 * Vendor sold by Template
 *
 * The template for displaying the vendor sold by on the shop loop
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/front/vendor-sold-by.php
 *
 * @author        Jamie Madden, WC Vendors
 * @package       WCVendors/Templates/Emails/HTML
 * @version       2.1.17
 *
 *
 * Template Variables available
 * $vendor :            For pulling additional user details from vendor account.  This is an array.
 * $vendor_id  :        current vendor user id number
 * $shop_name :        Store/Shop Name (From Vendor Dashboard Shop Settings)
 * $shop_description : Shop Description (completely sanitized) (From Vendor Dashboard Shop Settings)
 * $seller_info :        Seller Info(From Vendor Dashboard Shop Settings)
 * $vendor_email :        Vendors email address
 * $vendor_login :    Vendors user_login name
 * $vendor_shop_link : URL to the vendors store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<small class="wcvendors_sold_by_in_loop"><?php echo wcv_get_vendor_sold_by( $vendor_id ); ?></small><br/>
