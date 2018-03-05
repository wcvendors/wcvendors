<?php
/**
 * Vendor Notify Application
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/vendor-notify-application.php
 *
 *
 * @author  WC Vendors
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

<p><?php printf( __( 'Hi there. This is a notification about a vendor application on %s.', 'wcvendors' ), get_option( 'blogname' ) ); ?></p>
<p><?php printf( __( 'Your application is currently: %s', 'wcvendors' ), $status ); ?></p>
<p><?php printf( __( 'Applicant username: %s', 'wcvendors' ), $user->user_login ); ?></p>

 <?php

 /**
  * @hooked WC_Emails::email_footer() Output the email footer
  */
 do_action( 'woocommerce_email_footer', $email );
