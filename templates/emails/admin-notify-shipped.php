<?php
/**
 * Admin Vendor Shipped
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/admin-notify-shipped.php.
 *
 * @author        Jamie Madden, WC Vendors
 * @package       WCVendors/Templates/Emails/HTML
 * @version       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email );

?>
	<p><?php printf( __( '%1$s has marked  order #%2$s as shipped.', 'wc-vendors' ), WCV_Vendors::get_vendor_shop_name( $vendor_id ), $order->get_id() ); ?></p>
<?php


do_action( 'woocommerce_email_footer', $email );
