<?php
/**
 *  Vendor notify denied (plain text)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/vendor-notify-denied.php
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

echo $content . "\n\n";

echo sprintf( __( 'Hi there. This is a notification about a vendor application on %s.', 'wcvendors' ), get_option( 'blogname' ) ) . "\n\n";
echo sprintf( __( 'Your application is currently: %s', 'wcvendors', $status ) ). "\n\n";
echo sprintf( __( 'Applicant username: %s', 'wcvendors' ), $user->user_login ). "\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
