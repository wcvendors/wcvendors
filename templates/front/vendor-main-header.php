<?php
/**
 * Vendor Main Header Template
 *
 * THIS FILE WILL LOAD ON VENDORS STORE URLs (such as yourdomain.com/vendors/bobs-store/)
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/front/vendor-main-header.php
 *
 * @author        Jamie Madden, WC Vendors
 * @package       WCVendors/Templates/Emails/HTML
 * @version       2.0.0
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

<h1><?php echo $shop_name; ?></h1>
<div class="wcv_shop_description">
	<?php echo $shop_description; ?>
</div>
