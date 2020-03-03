<?php
/**
 * Admin new notify vendor approved (plain text)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/admin-notify-approved.php
 *
 * @author         Lindeni Mahlalela, WC Vendors
 * @package        WCVendors/Templates/Emails/Plain
 * @version        2.0.13
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '= ' . $email_heading . " =\n\n";

echo sprintf( __( 'Hi there. You or another admin has approved a user to be a %1$s on %2$s.', 'wc-vendors' ), wcv_get_vendor_name( true, false ), get_option( 'blogname' ) ) . "\n\n";
echo sprintf( __( 'Application status: %s', 'wc-vendors' ), esc_attr(ucfirst( $status ) ) );
echo sprintf( __( 'Approved username: %s', 'wc-vendors' ), esc_attr( $user->user_login ) ) . "\n\n";


echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
