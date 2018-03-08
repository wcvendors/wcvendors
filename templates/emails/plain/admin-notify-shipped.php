<?php
/**
 * Admin new notify vendor shipped (plain text)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/admin-notify-shipped.php
 *
 *
 * @author		WC Vendors
 * @package 	WCVendors/Templates/Emails/Plain
 * @version		2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo "= " . $email_heading . " =\n\n";

echo sprintf( __('%s has marked  order #%s as shipped.' ), WCV_Vendors::get_vendor_shop_name( $vendor_id ), $order->get_id() ) . "\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
