<?php
/**
 *  Vendor notify approved (plain text)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/vendor-notify-approved.php
 *
 * @author         Jamie Madden, WC Vendors
 * @package        WCVendors/Templates/Emails/Plain
 * @version        2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '= ' . $email_heading . " =\n\n";

echo $content . "\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
