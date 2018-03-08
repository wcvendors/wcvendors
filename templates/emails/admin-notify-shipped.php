<?php
/**
 * Admin new order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/admin-new-order.php.
 *
 * @author WCVendors
 * @package WCVendors/Templates/Emails/HTML
 * @version 2.0.0
 */

 if ( ! defined( 'ABSPATH' ) ) {
 	exit;
 }

 /**
  * @hooked WC_Emails::email_header() Output the email header
  */
 do_action( 'woocommerce_email_header', $email_heading, $email );

?><p><?php printf( __('%s has marked  order #%s as shipped.' ), WCV_Vendors::get_vendor_shop_name( $vendor_id ), $order->get_id() ) ?></p><?php

 /**
  * @hooked WC_Emails::email_footer() Output the email footer
  */
 do_action( 'woocommerce_email_footer', $email );
