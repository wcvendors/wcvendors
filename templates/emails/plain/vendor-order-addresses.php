<?php
/**
 * Vendor Order Customer Details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/vendor-order-addresses.php.
 *
 * @author  Jamie Madden, WC Vendors
 * @package WCVendors/Templates/Emails/Plain
 * @version 2.1.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $show_customer_billing_name ) {
	echo esc_html( $customer_billing_name ) . "\n";
}

if ( $show_shipping_address ) {
	echo "\n" . esc_html( wc_strtoupper( __( 'Billing address', 'wc-vendors' ) ) ) . "\n\n";
	echo preg_replace( '#<br\s*/?>#i', "\n", $order->get_formatted_billing_address() ) . "\n"; // WPCS: XSS ok.
}
if ( $show_customer_phone ) {
	if ( $order->get_billing_phone() ) {
		echo $order->get_billing_phone() . "\n"; // WPCS: XSS ok.
	}
}

if ( $show_customer_email ) {
	if ( $order->get_billing_email() ) {
		echo $order->get_billing_email() . "\n"; // WPCS: XSS ok.
	}
}

if ( $show_shipping_address ) {
	if ( $show_customer_shipping_name ) {
		echo esc_html( $customer_shipping_name ) . "\n";
	}

	if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() ) {
		$shipping = $order->get_formatted_shipping_address();

		if ( $shipping ) {
			echo "\n" . esc_html( wc_strtoupper( __( 'Shipping address', 'wc-vendors' ) ) ) . "\n\n";
			echo preg_replace( '#<br\s*/?>#i', "\n", $shipping ) . "\n"; // WPCS: XSS ok.
		}
	}
}
