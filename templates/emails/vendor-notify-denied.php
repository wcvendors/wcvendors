<?php
/**
 * Vendor Notify Application Denied
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/vendor-notify-denied.php
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

 <p><?php echo $content; ?></p>

 <p><?php echo $reason; ?></p>

 <p><?php printf( __( 'Applicant username: %s', 'wcvendors' ), $user->user_login ); ?></p>

 <?php

 /**
  * @hooked WC_Emails::email_footer() Output the email footer
  */
 do_action( 'woocommerce_email_footer', $email );
