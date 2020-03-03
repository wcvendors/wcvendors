<?php
/**
 * Admin new notify new vendor product (plain text)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/admin-notify-product.php
 *
 * @author         Jamie Madden, WC Vendors
 * @package        WCVendors/Templates/Emails/Plain
 * @version        2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '= ' . $email_heading . " =\n\n";

do_action( 'woocommerce_email_header', $email_heading, $email );

echo printf( __( 'This is a notification about a new product on %s.', 'wc-vendors' ), get_option( 'blogname' ) ) . "\n\n";

echo sprintf( __( 'Product title: %s', 'wc-vendors' ), $product->get_title() ) . "\n\n";
echo sprintf( __( 'Submitted by: %s', 'wc-vendors' ), $vendor_name ) . "\n\n";
echo sprintf( __( 'Edit product: %s', 'wc-vendors' ), admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ) . "\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
