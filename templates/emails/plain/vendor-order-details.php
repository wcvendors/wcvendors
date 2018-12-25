<?php
/**
 * Vendor Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/vendor-order-details.php.
 *
 * @author         Jamie Madden, WC Vendors
 * @package        WCvendors/Templates/Emails/Plain
 * @version        2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

/* translators: %s: Order ID. */
echo wp_kses_post( wc_strtoupper( sprintf( __( 'Order number: %s', 'wc-vendors' ), $order->get_order_number() ) ) ) . "\n";
echo wc_format_datetime( $order->get_date_created() ) . "\n";  // WPCS: XSS ok.
do_action( 'wcv_email_vendor_notify_order_before_order_items' ) . "\n";
echo "\n" . wcv_get_vendor_order_items(
		$order, array( // WPCS: XSS ok.
		               'show_sku'       => $sent_to_vendor,
		               'vendor_id'      => $vendor_id,
		               'vendor_items'   => $vendor_items,
		               'totals_display' => $totals_display,
		               'show_image'     => false,
		               'image_size'     => array( 32, 32 ),
		               'plain_text'     => true,
		               'sent_to_admin'  => $sent_to_admin,
		               'sent_to_vendor' => $sent_to_vendor,
		)
	);
do_action( 'wcv_email_vendor_notify_order_after_order_items' ) . "\n";

echo "==========\n\n";

$totals = wcv_get_vendor_item_totals( $order, $vendor_items, $vendor_id, $email, $totals_display );
$totals = apply_filters( 'wcv_vendor_notify_order_item_totals', $totals, $order, $vendor_items, $vendor_id, $email, $totals_display );

if ( $totals ) {
	foreach ( $totals as $total ) {
		echo wp_kses_post( $total['label'] . "\t " . $total['value'] ) . "\n";
	}
}

if ( $order->get_customer_note() ) {
	do_action( 'wcv_vendor_notify_order_before_customer_note' ) . "\n";
	echo esc_html__( 'Note:', 'wc-vendors' ) . "\t " . wp_kses_post( wptexturize( $order->get_customer_note() ) ) . "\n";
	do_action( 'wcv_vendor_notify_order_after_customer_note' ) . "\n";
}


do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );
