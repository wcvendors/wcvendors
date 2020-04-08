<?php
/**
 * Vendor new order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/vendor-notify-order.php.
 *
 * @author  Jamie Madden, WC Vendors
 * @package WCVendors/Templates/Emails
 * @version 2.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

	<p><?php _e( 'You have received an order. The order is as follows:', 'wc-vendors' ); ?></p>

<?php

do_action( 'wcvendors_email_order_details', $order, $vendor_items, $totals_display, $vendor_id, $sent_to_vendor, $sent_to_admin, $plain_text, $email );

do_action( 'wcvendors_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_footer', $email );
