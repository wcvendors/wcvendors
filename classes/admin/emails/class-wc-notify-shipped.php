<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * New Order Email
 *
 * An email sent to the admin when a new product is created.
 *
 * @class    WC_Email_Notify_Shipped
 * @version  2.0.0
 * @extends  WC_Email
 * @author   WooThemes
 * @package  WooCommerce/Classes/Emails
 */
class WC_Email_Notify_Shipped extends WC_Email {


	/**
	 * Constructor
	 */
	function __construct() {

		$this->id          = 'vendor_notify_shipped';
		$this->title       = sprintf( __( '%s has shipped - deprecated', 'wc-vendors' ), wcv_get_vendor_name() );
		$this->description = sprintf( __( 'An email is sent when a %s has marked one of their orders as shipped. <strong>This email has been deprecated.</strong>', 'wc-vendors' ), wcv_get_vendor_name( true, false ) );

		$this->heading = __( 'Your order has been shipped', 'wc-vendors' );
		$this->subject = __( '[{blogname}] Your order has been shipped ({order_number}) - {order_date}', 'wc-vendors' );

		$this->template_html  = 'notify-vendor-shipped.php';
		$this->template_plain = 'notify-vendor-shipped.php';
		$this->template_base  = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/templates/emails/';

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
	function trigger( $order_id, $vendor_id ) {

		$this->object         = wc_get_order( $order_id );
		$this->current_vendor = $vendor_id;
		$order_date           = $this->object->get_date_created();

		$this->find[]    = '{order_date}';
		$this->replace[] = date_i18n( wc_date_format(), strtotime( $order_date ) );

		$this->find[]    = '{order_number}';
		$this->replace[] = $this->object->get_order_number();

		$billing_email = $this->object->get_billing_email();

		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'woocommerce_order_get_items', array( $this, 'check_items' ), 10, 2 );
		add_filter( 'woocommerce_get_order_item_totals', array( $this, 'check_order_totals' ), 10, 2 );
		$this->send( $billing_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		remove_filter( 'woocommerce_get_order_item_totals', array( $this, 'check_order_totals' ), 10, 2 );
		remove_filter( 'woocommerce_order_get_items', array( $this, 'check_items' ), 10, 2 );
	}


	/**
	 *
	 *
	 * @param unknown $items
	 * @param unknown $order
	 *
	 * @return unknown
	 */
	public function check_items( $items, $order ) {

		foreach ( $items as $key => $product ) {

			if ( empty( $product['product_id'] ) ) {
				unset( $items[ $key ] );
				continue;
			}

			$author = WCV_Vendors::get_vendor_from_product( $product['product_id'] );

			if ( $this->current_vendor != $author ) {
				unset( $items[ $key ] );
				continue;
			}
		}

		return $items;
	}

	/**
	 *
	 *
	 * @param unknown $total_rows
	 * @param unknown $order
	 *
	 * @return unknown
	 */
	public function check_order_totals( $total_rows, $order ) {

		$return['cart_subtotal']          = $total_rows['cart_subtotal'];
		$return['cart_subtotal']['label'] = __( 'Subtotal:', 'wc-vendors' );

		return $return;
	}

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {

		ob_start();
		wc_get_template(
			$this->template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
		), 'woocommerce/emails', $this->template_base
		);

		return ob_get_clean();
	}


	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {

		ob_start();
		wc_get_template(
			$this->template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
		), 'woocommerce/emails', $this->template_base
		);

		return ob_get_clean();
	}


	/**
	 * Initialise Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	function init_form_fields() {

		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'wc-vendors' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'wc-vendors' ),
				'default' => 'no',
			),
			'subject'    => array(
				'title'       => __( 'Subject', 'wc-vendors' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'wc-vendors' ), $this->subject ),
				'placeholder' => '',
				'default'     => '',
			),
			'heading'    => array(
				'title'       => __( 'Email Heading', 'wc-vendors' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'wc-vendors' ), $this->heading ),
				'placeholder' => '',
				'default'     => '',
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'wc-vendors' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'wc-vendors' ),
				'default'     => 'html',
				'class'       => 'email_type',
				'options'     => array(
					'plain'     => __( 'Plain text', 'wc-vendors' ),
					'html'      => __( 'HTML', 'wc-vendors' ),
					'multipart' => __( 'Multipart', 'wc-vendors' ),
				),
			),
		);
	}


}
