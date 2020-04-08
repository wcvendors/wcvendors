<?php
/**
 * Admin New Vendor Product
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/admin-notify-product.php.
 *
 * @author        Jamie Madden, WC Vendors
 * @package       WCVendors/Templates/Emails/HTML
 * @version       2.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

	<p><?php printf( __( 'This is a notification about a new product on %s.', 'wc-vendors' ), get_option( 'blogname' ) ); ?></p>

	<p>
		<?php printf( __( 'Product title: %s', 'wc-vendors' ), $product->get_title() ); ?><br/>
		<?php printf( __( 'Submitted by: %s', 'wc-vendors' ), $vendor_name ); ?><br/>
		<?php printf( __( 'Edit product: <a href="%1$s">%1$s</a>', 'wc-vendors' ), admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ); ?>
	</p>

<?php

do_action( 'woocommerce_email_footer', $email );
