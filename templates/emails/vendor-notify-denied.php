<?php
/**
 * Vendor Notify Application Denied
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/vendor-notify-denied.php
 *
 * @author  Jamie Madden, WC Vendors
 * @package WCVendors/Templates/Emails
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

	<p><?php echo $content; ?></p>

	<p><?php echo $reason; ?></p>

	<p><?php printf( __( 'Applicant username: %s', 'wc-vendors' ), $user->user_login ); ?></p>

<?php

do_action( 'woocommerce_email_footer', $email );
