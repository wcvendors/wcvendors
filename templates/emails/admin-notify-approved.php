<?php
/**
 * Notify admin about an approved vendor
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/admin-notify-approved.php.
 *
 * @author        Lindeni Mahlalela, WC Vendors
 * @package       WCVendors/Templates/Emails/HTML
 * @version       2.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

	<p><?php printf( __( 'Hi there. You or another admin has approved a user to be a %1$s on %2$s.', 'wc-vendors' ), wcv_get_vendor_name( true, false ), get_option( 'blogname' ) ); ?></p>
	<p><?php printf( __( 'Application status: %s', 'wc-vendors' ), esc_attr( ucfirst( $status ) ) ); ?></p>
	<p><?php printf( __( 'Applicant username: %s', 'wc-vendors' ), esc_attr( $user->user_login ) ); ?></p>

<?php

do_action( 'woocommerce_email_footer', $email );
