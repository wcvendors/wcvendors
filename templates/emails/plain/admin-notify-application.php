<?php
/**
 * Admin new notify vendor application (plain text)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/admin-notify-application.php
 *
 * @author         Jamie Madden, WC Vendors
 * @package        WCVendors/Templates/Emails/Plain
 * @version        2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '= ' . $email_heading . " =\n\n";

echo sprintf( __( 'Hi there. This is a notification about a %1$s application on %2$s.', 'wc-vendors' ), wcv_get_vendor_name( true, false ), get_option( 'blogname' ) ) . "\n\n";
echo sprintf( __( 'Applicant username: %s', 'wc-vendors' ), $user->user_login ) . "\n\n";

if ( 'pending' === $status ) {
	echo sprintf( __( 'You can approve or deny the application at the following link %s', 'wc-vendors' ), admin_url( 'users.php?role=pending_vendor' ) );
}

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
