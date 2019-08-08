<?php
/**
 * Vendor Notify Application
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/vendor-notify-application.php
 *
 * @author  WC Vendors
 * @package WCVendors/Templates/Emails
 * @version 2.0.0
 * @since   1.0.13
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

	<p><?php printf( __( 'Hi there. This is a notification about your %1$s application on %2$s.', 'wc-vendors' ), wcv_get_vendor_name( true, false ), get_option( 'blogname' ) ); ?></p>
	<p><?php printf( __( 'Your application is currently: %s', 'wc-vendors' ), $status ); ?></p>
	<p><?php printf( __( 'Your username: %s', 'wc-vendors' ), $user->user_login ); ?></p>

<?php

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
