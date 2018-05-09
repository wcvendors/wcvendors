<?php

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 *
 *
 * @author Matt Gates <http://mgates.me>
 * @package
 */


class WCV_Emails
{


	/**
	 *
	 */
	public function __construct() {

		add_filter( 'woocommerce_email_classes', array( $this, 'email_classes' ) );
		add_filter( 'woocommerce_order_actions', array( $this, 'order_actions' ) );
		add_action( 'woocommerce_order_action_send_vvendor_new_order', array( $this, 'order_actions_save') );
		// Deprecaited

		add_action( 'set_user_role', array( $this, 'application_status_email' ), 10, 2 );
		add_action( 'transition_post_status', array( $this, 'trigger_new_product' ), 10, 3 );

		// Low stock
		// These fatal error in WC3.3.3 @todo fix !
		add_filter( 'woocommerce_email_recipient_low_stock', 	array( $this, 'vendor_stock_email'), 10, 2 );
		add_filter( 'woocommerce_email_recipient_no_stock', 	array( $this, 'vendor_stock_email'), 10, 2 );
		add_filter( 'woocommerce_email_recipient_backorder', 	array( $this, 'vendor_stock_email'), 10, 2 );

		// New emails
		// Triggers
		add_action( 'wcvendors_vendor_ship', 		array( $this, 'vendor_shipped' ), 10, 3 );
		add_action( 'wcvendors_email_order_details',array( $this, 'vendor_order_details'), 10, 8 );
		add_action( 'transition_post_status', 		array( $this, 'new_vendor_product' ), 10, 3 );
		add_action( 'set_user_role', 				array( $this, 'vendor_application' ), 10, 2 );

	}


	// Depreciated
	public function trigger_new_product( $from, $to, $post )
	{
		global $woocommerce;

		if ( $from != $to && $post->post_status == 'pending' && WCV_Vendors::is_vendor( $post->post_author ) && $post->post_type == 'product' ) {
			$mails = $woocommerce->mailer()->get_emails();
			if ( !empty( $mails ) ) {
				$mails[ 'WC_Email_Notify_Admin' ]->trigger( $post->post_id, $post );
			}
		}
	}


	/**
	 * @depreciated
	 *
	 * @param unknown $user_id
	 * @param unknown $role
	 */
	public function application_status_email( $user_id, $role ) {

		global $woocommerce;

		if ( !empty( $_POST[ 'apply_for_vendor' ] ) || ( !empty( $_GET[ 'action' ] ) && ( $_GET[ 'action' ] == 'approve_vendor' || $_GET[ 'action' ] == 'deny_vendor' ) ) ) {

			if ( $role == 'pending_vendor' ) {
				$status = __( 'pending', 'wc-vendors' );
			} else if ( $role == 'vendor' ) {
				$status = __( 'approved', 'wc-vendors' );
			} else if ( !empty( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'deny_vendor' ) {
				$status = __( 'denied', 'wc-vendors' );
			}

			$mails = $woocommerce->mailer()->get_emails();

			if ( isset( $status ) && !empty( $mails ) ) {
				$mails[ 'WC_Email_Approve_Vendor' ]->trigger( $user_id, $status );
			}
		}
	}

	/**
	 *
	 *
	 * @param unknown $emails
	 *
	 * @return unknown
	 */
	public function email_classes( $emails ){

		require_once wcv_plugin_dir . 'classes/admin/emails/class-wc-notify-admin.php';
		require_once wcv_plugin_dir . 'classes/admin/emails/class-wc-notify-vendor.php';
		require_once wcv_plugin_dir . 'classes/admin/emails/class-wc-approve-vendor.php';
		require_once wcv_plugin_dir . 'classes/admin/emails/class-wc-notify-shipped.php';

		// Emails to depreciate
		$emails[ 'WC_Email_Notify_Vendor' ]  = new WC_Email_Notify_Vendor();
		$emails[ 'WC_Email_Approve_Vendor' ] = new WC_Email_Approve_Vendor();
		$emails[ 'WC_Email_Notify_Admin' ]   = new WC_Email_Notify_Admin();
		$emails[ 'WC_Email_Notify_Shipped' ] = new WC_Email_Notify_Shipped();

		// New emails introduced in @since 2.0.0
		$emails[ 'WCVendors_Customer_Notify_Shipped'] 		= include( wcv_plugin_dir . 'classes/admin/emails/class-wcv-customer-notify-shipped.php' );
		$emails[ 'WCVendors_Admin_Notify_Shipped'] 			= include( wcv_plugin_dir . 'classes/admin/emails/class-wcv-admin-notify-shipped.php' );
		$emails[ 'WCVendors_Admin_Notify_Product'] 			= include( wcv_plugin_dir . 'classes/admin/emails/class-wcv-admin-notify-product.php' );
		$emails[ 'WCVendors_Admin_Notify_Application'] 		= include( wcv_plugin_dir . 'classes/admin/emails/class-wcv-admin-notify-application.php' );
		$emails[ 'WCVendors_Vendor_Notify_Application'] 	= include( wcv_plugin_dir . 'classes/admin/emails/class-wcv-vendor-notify-application.php' );
		$emails[ 'WCVendors_Vendor_Notify_Approved'] 		= include( wcv_plugin_dir . 'classes/admin/emails/class-wcv-vendor-notify-approved.php' );
		$emails[ 'WCVendors_Vendor_Notify_Denied'] 			= include( wcv_plugin_dir . 'classes/admin/emails/class-wcv-vendor-notify-denied.php' );
		$emails[ 'WCVendors_Vendor_Notify_Order'] 			= include( wcv_plugin_dir . 'classes/admin/emails/class-wcv-vendor-notify-order.php' );

		return $emails;

	} // email_classes()

	/**
	 *	 Add the vendor email to the low stock emails.
	 *
	 */
	public function vendor_stock_email( $emails, $product ) {

		if ( ! is_a( $product, 'WC_Product' ) ) return;

		$post 			= get_post( $product->get_id() );

		if ( WCV_Vendors::is_vendor( $post->post_author ) ) {
			$vendor_data = get_userdata( $post->post_author );
			$vendor_email = $vendor_data->user_email;
			$emails .= ','.$vendor_email;
		}

		return $emails;

	}

	/**
	*	Filter hook for order actions meta box
	*
	*/
	public function order_actions( $order_actions ){
		$order_actions[ 'send_vvendor_new_order' ] = sprintf( __( 'Resend %s new order notification', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) );
		return $order_actions;
	}

	/**
	* 	Action hook : trigger the notify vendor email
	*
	*/
	public function order_actions_save( $order ){

		WC()->mailer()->emails[ 'WC_Email_Notify_Vendor' ]->trigger( $order->get_id(), $order );
		WC()->mailer()->emails[ 'WCVendors_Vendor_Notify_Order' ]->trigger( $order->get_id(), $order );
	}

	/**
	* Trigger the notify vendor shipped emails
	*
	* @since 2.0.0
	*/
	public function vendor_shipped( $order_id, $user_id, $order ){
		// Notify the admin
		WC()->mailer()->emails[ 'WCVendors_Admin_Notify_Shipped' ]->trigger( $order->get_id(), $user_id, $order );
		// Notify the customer
		WC()->mailer()->emails[ 'WCVendors_Customer_Notify_Shipped' ]->trigger( $order->get_id(), $user_id, $order );
	}


	/**
	* Trigger the notify admin new vendor product
	*
	* @since 2.0.0
	*/
	public function new_vendor_product( $from, $to, $post ){

		if ( $from != $to && $post->post_status == 'pending' && WCV_Vendors::is_vendor( $post->post_author ) && $post->post_type == 'product' ) {

			WC()->mailer()->emails[ 'WCVendors_Admin_Notify_Product' ]->trigger( $post->post_id, $post );
		}
	}

	/**
	* Trigger the vendor application emails
	*
	* @since 2.0.0
	*/
	public function vendor_application( $user_id, $role ){

		if ( !empty( $_POST[ 'apply_for_vendor' ] ) || ( !empty( $_GET[ 'action' ] ) && ( $_GET[ 'action' ] == 'approve_vendor' || $_GET[ 'action' ] == 'deny_vendor' ) ) ) {

			if ( $role == 'pending_vendor' ) {
				WC()->mailer()->emails[ 'WCVendors_Vendor_Notify_Application' ]->trigger( $user_id, __( 'pending', 'wc-vendors' ) );
			} else if ( $role == 'vendor' ) {
				WC()->mailer()->emails[ 'WCVendors_Vendor_Notify_Approved' ]->trigger( $user_id );
			} else if ( !empty( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'deny_vendor' ) {
				$reason = isset( $_GET[ 'reason' ] ) ? $_GET[ 'reason' ] : '';
				WC()->mailer()->emails[ 'WCVendors_Vendor_Notify_Denied' ]->trigger( $user_id, $reason );
			}

			WC()->mailer()->emails[ 'WCVendors_Admin_Notify_Application' ]->trigger( $user_id, $role );

		}
	}


	/*
	* Show the order details table filtered for each vendor
	*/
	public function vendor_order_details( $order, $vendor_items, $totals_display, $vendor_id, $sent_to_vendor = false, $sent_to_admin = false, $plain_text = false, $email = '' ) {


		if ( $plain_text ) {

			wc_get_template( 'emails/plain/vendor-order-details.php', array(
				'order' 			=> $order,
				'vendor_id'			=> $vendor_id,
				'vendor_items' 		=> $vendor_items,
				'sent_to_admin'		=> $sent_to_admin,
				'sent_to_vendor' 	=> $sent_to_vendor,
				'totals_display'	=> $totals_display,
				'plain_text' 		=> $plain_text,
				'email' 			=> $email ),
			'woocommerce', WCV_TEMPLATE_BASE );

		} else {

			wc_get_template( 'emails/vendor-order-details.php', array(
				'order' 			=> $order,
				'vendor_id'			=> $vendor_id,
				'vendor_items' 		=> $vendor_items,
				'sent_to_admin'		=> $sent_to_admin,
				'sent_to_vendor' 	=> $sent_to_vendor,
				'totals_display'	=> $totals_display,
				'plain_text' 		=> $plain_text,
				'email' 			=> $email ),
			'woocommerce', WCV_TEMPLATE_BASE );
		}
	}
}
