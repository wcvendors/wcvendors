<?php
/**
 * WC Vendors Emails
 *
 * @package    WC_Vendors
 * @subpackage Emails
 */

namespace WCVendors\Emails;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class to hook to WooCommerce emails
 *
 * @since 3.0.0
 * @version 3.0.0
 */
class Emails {
	/**
	 * Add WooCommerce email hooks.
	 *
	 * @return void
	 * @version 3.0
	 * @since   3.0
	 */
	public function init_hooks() {
		add_filter( 'woocommerce_email_classes'                     , array( $this, 'email_classes' ) );
		add_filter( 'woocommerce_order_actions'                     , array( $this, 'order_actions' ) );
		add_action( 'woocommerce_order_action_send_vendor_new_order', array( $this, 'order_actions_save' ) );

		// Low stock
		// These fatal error in WC3.3.3 @todo fix !
		add_filter( 'woocommerce_email_recipient_low_stock', array( $this, 'vendor_low_stock_email' ), 10, 2 );
		add_filter( 'woocommerce_email_recipient_no_stock' , array( $this, 'vendor_no_stock_email' ), 10, 2 );
		add_filter( 'woocommerce_email_recipient_backorder', array( $this, 'vendor_backorder_stock_email' ), 10, 2 );

		// New emails
		// Triggers
		add_action( 'wcvendors_vendor_ship'           , array( $this, 'vendor_shipped' )         , 10, 3 );
		add_action( 'wcvendors_email_order_details'   , array( $this, 'vendor_order_details' )   , 10, 8 );
		add_action( 'wcvendors_email_customer_details', array( $this, 'vendor_customer_details' ), 10, 4 );

		// Trigger application emails as required.
		add_action( 'add_user_role', array( $this, 'vendor_application' ), 10, 2 );
		add_action( 'wcvendors_deny_vendor' , array( $this, 'deny_application' ) );

		// WooCommerce Product Enquiry Compatibility
		add_filter( 'product_enquiry_send_to', array( $this, 'product_enquiry_compatibility' ), 10, 2 );
	}

	/**
	 * Load WooCommerce email classes.
	 *
	 * @param   array $emails Current list of WooCommerce emails.
	 * @version 3.0.0
	 * @since   1.0.0
	 *
	 * @return array
	 */
	public function email_classes( $emails ) {

		/**
		 * New emails introduced in
		 *
		 * @version 3.0.0
		 * @since   2.0.0
		 */
		$emails['CustomerNotifyShipped']      = new CustomerNotifyShipped();
		$emails['AdminNotifyShipped']         = new AdminNotifyShipped();
		$emails['AdminNotifyProduct']         = new AdminNotifyProduct();
		$emails['AdminNotifyApplication']     = new AdminNotifyApplication();
		$emails['AdminNotifyApproved']        = new AdminNotifyApproved();
		$emails['VendorNotifyApplication']    = new VendorNotifyApplication();
		$emails['VendorNotifyApproved']       = new VendorNotifyApproved();
		$emails['VendorNotifyDenied']         = new VendorNotifyDenied();
		$emails['VendorNotifyOrder']          = new VendorNotifyOrder();
		$emails['VendorNotifyCancelledOrder'] = new VendorNotifyCancelledOrder();

		return $emails;

	} // email_classes

	/**
	 * Add the vendor email to the low stock emails.
	 *
	 * @param array $emails The currently registered email.
	 * @param WC_Product $product The WC_Product object.
	 */
	public function vendor_stock_email( $emails, $product ) {

		if ( ! is_a( $product, 'WC_Product' ) ) {
			return $emails;
		}

		$post = get_post( $product->get_id() );

		if ( wcv_is_vendor( $post->post_author ) ) {
			$vendor_data  = get_userdata( $post->post_author );
			$vendor_email = trim( $vendor_data->user_email );
			$emails       .= ',' . $vendor_email;
		}

		return $emails;

	}

	/**
	 *  Handle low stock emails for vendors
	 *
	 * @version 3.0.0
	 * @since 2.1.10
	 *
	 * @param array $emails The currently registered emails.
	 * @param WC_Product $product The product object.
	 */
	public function vendor_low_stock_email( $emails, $product ) {
		if ( 'no' === get_option( 'wcvendors_notify_low_stock', 'yes' ) ) {
			return $emails;
		}
		return $this->vendor_stock_email(  $emails, $product );
	}

	/**
	 *  Handle no stock emails for vendors
	 *
	 * @since 2.1.10
	 * @version 2.1.0
	 *
	 * @param array $emails The registered emails.
	 * @param WC_Product $product The WC_Product object.
	 */
	public function vendor_no_stock_email( $emails, $product ) {
		if ( 'no' === get_option( 'wcvendors_notify_low_stock', 'yes' ) ) {
			return $emails;
		}
		return $this->vendor_stock_email( $emails, $product );
	}

	/**
	 *  Handle backorder stock emails for vendors
	 *
	 * @since 2.1.10
	 * @version 2.1.0
	 *
	 * @param array $emails The registered emails.
	 * @param WC_Product The product object.
	 * @return void.
	 */
	public function vendor_backorder_stock_email( $emails, $product ) {
		if ( 'no' === get_option( 'wcvendors_notify_backorder_stock', 'yes' ) ) {
			return;
		}
		$this->vendor_stock_email( $emails, $product );
	}


	/**
	 * Filter hook for order actions meta box
	 *
	 * @version 3.0.0
	 * @since   2.0.0
	 *
	 * @param array $order_actions The currently registered order actions.
	 * @return array
	 */
	public function order_actions( $order_actions ) {
		$order_actions['send_vendor_new_order'] = sprintf( __( 'Resend %s new order notification', 'wc-vendors' ), wcv_get_vendor_name( true, false ) );

		return $order_actions;
	}

	/**
	 * Action hook : trigger the notify vendor email
	 *
	 * @version 3.0.0
	 * @since   2.0.0
	 *
	 * @param WC_Order $order The order object.
	 *
	 * @return void.
	 */
	public function order_actions_save( $order ) {
		if ( ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order );
		}
		WC()->mailer()->emails['Notify_Vendor']->trigger( $order->get_id(), $order );
		WC()->mailer()->emails['VendorNotifyOrder']->trigger( $order->get_id(), $order );
	}

	/**
	 * Trigger the notify vendor shipped emails
	 *
	 * @version 3.0.0
	 * @since   2.0.0
	 *
	 * @param int $order_id The order ID.
	 * @param int $user_id The user ID
	 * @param WC_Order $order The order object.
	 */
	public function vendor_shipped( $order_id, $user_id, $order ) {
		if ( ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}
		// Notify the admin
		WC()->mailer()->emails['AdminNotifyShipped']->trigger( $order->get_id(), $user_id, $order );
		// Notify the customer
		WC()->mailer()->emails['CustomerNotifyShipped']->trigger( $order->get_id(), $user_id, $order );
	}

	/**
	 * Trigger the vendor application emails
	 *
	 * @since 2.0.0
	 * @version 2.1.7
	 *
	 * @param int $user_id The user ID.
	 * @param string $role The user's role. 
	 */
	public function vendor_application( $user_id, $role = '' ) {

		/**
		 * If the role is not given, set it according to the vendor approval option in admin
		 */
		if ( $role == '' ) {
			$manual = wc_string_to_bool( get_option( 'wcvendors_vendor_approve_registration', 'no' ) );
			$role   = apply_filters( 'wcvendors_pending_role', ( $manual ? 'pending_vendor' : 'vendor' ) );
		}

		if ( $role == 'pending_vendor' ) {
			$status = __( 'pending', 'wc-vendors' );
			WC()->mailer()->emails['VendorNotifyApplication']->trigger( $user_id, $status );
			WC()->mailer()->emails['AdminNotifyApplication']->trigger( $user_id, $status );
		} elseif ( $role == 'vendor' ) {
			$status = __( 'approved', 'wc-vendors' );
			WC()->mailer()->emails['VendorNotifyApproved']->trigger( $user_id, $status );
			WC()->mailer()->emails['AdminNotifyApproved']->trigger( $user_id, $status );
		}

	}

	/**
	 * Trigger the deny application email
	 *
	 * @version 3.0.0
	 * @since   2.1.8
	 *
	 * @param WP_User $user The user object.
	 */
	public function deny_application( $user ){
		$user_id = $user->ID;
		WC()->mailer()->emails['VendorNotifyDenied']->trigger( $user_id );
	}

	/**
	 * Show the order details table filtered for each vendor.
	 *
	 * @param WC_Order $order          The WC_Order object.
	 * @param array    $vendor_items   Items ordered from the vendor.
	 * @param mixed    $totals_display Whether to display totals or not.
	 * @param int      $vendor_id      The vendor's ID
	 * @param boolean  $sent_to_vendor Whether email is sent to vendor.
	 * @param boolean  $sent_to_admin  Whether email is sent to admin.
	 * @param boolean  $plain_text     Whether to send plain text.
	 * @param string   $email          The email.
	 * @return void
	 * @version 3.0.0
	 * @since   2.0.0
	 */
	public function vendor_order_details( $order, $vendor_items, $totals_display, $vendor_id, $sent_to_vendor = false, $sent_to_admin = false, $plain_text = false, $email = '' ) {

		if ( $plain_text ) {

			wc_get_template(
				'emails/plain/vendor-order-details.php',
				array(
					'order'          => $order,
					'vendor_id'      => $vendor_id,
					'vendor_items'   => $vendor_items,
					'sent_to_admin'  => $sent_to_admin,
					'sent_to_vendor' => $sent_to_vendor,
					'totals_display' => $totals_display,
					'plain_text'     => $plain_text,
					'email'          => $email,
				),
				'woocommerce',
				WCV_TEMPLATE_BASE
			);

		} else {

			wc_get_template(
				'emails/vendor-order-details.php',
				array(
					'order'          => $order,
					'vendor_id'      => $vendor_id,
					'vendor_items'   => $vendor_items,
					'sent_to_admin'  => $sent_to_admin,
					'sent_to_vendor' => $sent_to_vendor,
					'totals_display' => $totals_display,
					'plain_text'     => $plain_text,
					'email'          => $email,
				),
				'woocommerce',
				WCV_TEMPLATE_BASE
			);
		}
	}

	/**
	 * Show the customer address details based on the capabilities for the vendor.
	 *
	 * @param WC_Order $order         The order object.
	 * @param boolean  $sent_to_admin Whether email is sent to admin or not
	 * @param boolean  $plain_text    Whether to send plain text or not.
	 * @param string   $email         The email to send.
	 * @return void
	 * @version 3.0.0
	 * @since   2.0.0
	 */
	public function vendor_customer_details( $order, $sent_to_admin, $plain_text, $email ) {

		$show_customer_billing_name  = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_name', 'no' ) );
		$show_customer_shipping_name = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_shipping_name', 'no' ) );
		$show_customer_email         = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_email', 'no' ) );
		$show_customer_phone         = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_phone', 'no' ) );
		$show_billing_address        = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_billing', 'no' ) );
		$show_shipping_address       = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_shipping', 'no' ) );
		$customer_billing_name       = $show_customer_billing_name ? $order->get_formatted_billing_full_name() : '';
		$customer_shipping_name      = $show_customer_shipping_name ? $order->get_formatted_shipping_full_name() : '';

		if ( $plain_text ) {
			wc_get_template(
				'emails/plain/vendor-order-addresses.php',
				array(
					'show_customer_email'         => $show_customer_email,
					'show_customer_phone'         => $show_customer_phone,
					'show_billing_address'        => $show_billing_address,
					'show_shipping_address'       => $show_shipping_address,
					'show_customer_billing_name'  => $show_customer_billing_name,
					'customer_billing_name'       => $customer_billing_name,
					'show_customer_shipping_name' => $show_customer_billing_name,
					'customer_shipping_name'      => $customer_shipping_name,
					'order'                       => $order,
					'sent_to_admin'               => $sent_to_admin,
				),
				'woocommerce',
				WCV_TEMPLATE_BASE
			);
		} else {
			wc_get_template(
				'emails/vendor-order-addresses.php',
				array(
					'show_customer_email'         => $show_customer_email,
					'show_customer_phone'         => $show_customer_phone,
					'show_billing_address'        => $show_billing_address,
					'show_shipping_address'       => $show_shipping_address,
					'show_customer_billing_name'  => $show_customer_billing_name,
					'customer_billing_name'       => $customer_billing_name,
					'show_customer_shipping_name' => $show_customer_billing_name,
					'customer_shipping_name'      => $customer_shipping_name,
					'order'                       => $order,
					'sent_to_admin'               => $sent_to_admin,
				),
				'woocommerce',
				WCV_TEMPLATE_BASE
			);
		}
	}

	/**
	 * WooCommerce Product Enquiry hook - Send email to vendor instead of admin
	 *
	 * @param string $send_to    The email address to send email to.
	 * @param int    $product_id The product ID.
	 * @return string
	 * @version 3.0.0
	 * @since   2.0.0
	 */
	public function product_enquiry_compatibility( $send_to, $product_id ) {
		$author_id = get_post( $product_id )->post_author;
		if ( wcv_is_vendor( $author_id ) ) {
			$send_to = get_userdata( $author_id )->user_email;
		}

		return $send_to;
	}
}
