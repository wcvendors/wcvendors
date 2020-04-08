<?php
/**
 * Vendor Notify Application Approved
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/vendor-notify-approved.php
 *
 * @author  Jamie Madden, WC Vendors
 * @package WCVendors/Templates/Emails
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

	<p><?php echo $content; ?></p>

	<p><?php printf( __( 'Your username: %s', 'wc-vendors' ), $user->user_login ); ?></p>
<?php

do_action( 'woocommerce_email_footer', $email );
