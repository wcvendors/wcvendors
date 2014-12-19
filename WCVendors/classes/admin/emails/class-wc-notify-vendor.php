<?php

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * New Order Email
 *
 * An email sent to the admin when a new order is received/paid for.
 *
 * @class    WC_Email_Notify_Vendor
 * @version  2.0.0
 * @extends  WC_Email
 * @author   WooThemes
 * @package  WooCommerce/Classes/Emails
 */


class WC_Email_Notify_Vendor extends WC_Email
{

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->id          = 'vendor_new_order';
		$this->title       = __( 'Notify vendors', 'wcvendors' );
		$this->description = __( 'New order emails are sent when an order is received/paid by a customer.', 'wcvendors' );

		$this->heading = __( 'New customer order', 'wcvendors' );
		$this->subject = __( '[{blogname}] New customer order ({order_number}) - {order_date}', 'wcvendors' );

		$this->template_html  = 'admin-new-order.php';
		$this->template_plain = 'plain/admin-new-order.php';
		$this->template_base  = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/views/emails/';

		// Triggers for this email
		add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'trigger' ) );
		add_action( 'woocommerce_order_status_pending_to_completed_notification', array( $this, 'trigger' ) );
		add_action( 'woocommerce_order_status_failed_to_processing_notification', array( $this, 'trigger' ) );
		add_action( 'woocommerce_order_status_failed_to_completed_notification', array( $this, 'trigger' ) );

		$this->recipient = get_option( 'admin_email' );

		// Call parent constuctor
		parent::__construct();

	}


	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 *
	 * @param unknown $order_id
	 */
	function trigger( $order_id )
	{
		global $woocommerce;

		if ( $order_id ) {
			$this->object = new WC_Order( $order_id ); 

			$this->find[ ]    = '{order_date}';
			$this->replace[ ] = date_i18n( woocommerce_date_format(), strtotime( $this->object->order_date ) );

			$this->find[ ]    = '{order_number}';
			$this->replace[ ] = $this->object->get_order_number();

		}

		if ( !$this->is_enabled() ) return;

		$vendors = $this->get_vendors( $this->object );

		if ( empty( $vendors ) ) return;

		add_filter( 'woocommerce_order_get_items', array( $this, 'check_items' ), 10, 2 );
		add_filter( 'woocommerce_get_order_item_totals', array( $this, 'check_order_totals' ), 10, 2 );
		foreach ( $vendors as $user_id => $user_email ) {
			$this->current_vendor = $user_id;
			$this->send( $user_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
		remove_filter( 'woocommerce_get_order_item_totals', array( $this, 'check_order_totals' ), 10, 2 );
		remove_filter( 'woocommerce_order_get_items', array( $this, 'check_items' ), 10, 2 );

	}


	/**
	 *
	 *
	 * @param unknown $total_rows
	 * @param unknown $order
	 *
	 * @return unknown
	 */
	function check_order_totals( $total_rows, $order )
	{
		$return[ 'cart_subtotal' ]            = $total_rows[ 'cart_subtotal' ];
		$return[ 'cart_subtotal' ][ 'label' ] = __( 'Commission Subtotal:', 'wcvendors' );

		$dues = WCV_Vendors::get_vendor_dues_from_order( $order );

		foreach ( $dues as $due ) {
			if ( $this->current_vendor == $due['vendor_id'] ) {
				if (!empty($return[ 'shipping' ]))	$return[ 'shipping' ]          = $total_rows[ 'shipping' ];
				$return[ 'shipping' ]['label']   = __( 'Shipping Subtotal:', 'wcvendors' );
				$return[ 'shipping' ][ 'value' ] = woocommerce_price( $due['shipping'] );
				break;
			}
		}

		return $return;
	}


	/**
	 *
	 *
	 * @param unknown $order
	 *
	 * @return unknown
	 */
	public function get_vendors( $order )
	{
		$items = $order->get_items();

		foreach ( $items as $key => $product ) {

			if ( empty( $product[ 'product_id' ] ) ) continue;
			$author = WCV_Vendors::get_vendor_from_product( $product[ 'product_id' ] );

			// Only store the vendor authors
			if ( !WCV_Vendors::is_vendor( $author ) ) {
				unset( $items[ $key ] );
				continue;
			}

			$vendors[ $author ] = get_userdata( $author )->user_email;
		}

		return $vendors;
	}

	/**
	 *
	 *
	 * @param unknown $items
	 * @param unknown $order
	 *
	 * @return unknown
	 */
	function check_items( $items, $order )
	{	
		foreach ( $items as $key => $product ) {

			//  If this is a line item 
			if ($product['type'] == 'line_item') { 

				$author = WCV_Vendors::get_vendor_from_product( $product[ 'product_id' ] );

				if ( $this->current_vendor != $author) {
					unset( $items[ $key ] );
					continue;
				} else {
					$commission_due = WCV_Commission::calculate_commission( $product[ 'line_subtotal' ], $product[ 'product_id' ], $order );

					$items[ $key ][ 'line_subtotal' ] = $commission_due;
					$items[ $key ][ 'line_total' ]    = $commission_due;
					unset( $items[ $key ][ 'line_tax' ] );
				}
			}

		}


		return $items;
	}


	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html()
	{
		ob_start();
		wc_get_template( $this->template_html, array(
															 'order'         => $this->object,
															 'email_heading' => $this->get_heading()
														), 'woocommerce/', $this->template_base );

		return ob_get_clean();
	}


	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain()
	{
		ob_start();
		wc_get_template( $this->template_plain, array(
															  'order'         => $this->object,
															  'email_heading' => $this->get_heading()
														 ), 'woocommerce/', $this->template_base );

		return ob_get_clean();
	}


	/**
	 * Initialise Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	function init_form_fields()
	{
		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'wcvendors' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'wcvendors' ),
				'default' => 'yes'
			),
			'subject'    => array(
				'title'       => __( 'Subject', 'wcvendors' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'wcvendors' ), $this->subject ),
				'placeholder' => '',
				'default'     => ''
			),
			'heading'    => array(
				'title'       => __( 'Email Heading', 'wcvendors' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'wcvendors' ), $this->heading ),
				'placeholder' => '',
				'default'     => ''
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'wcvendors' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'wcvendors' ),
				'default'     => 'html',
				'class'       => 'email_type',
				'options'     => array(
					'plain'     => __( 'Plain text', 'wcvendors' ),
					'html'      => __( 'HTML', 'wcvendors' ),
					'multipart' => __( 'Multipart', 'wcvendors' ),
				)
			)
		);
	}


}
