<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * New Order Email
 *
 * An email sent to the vendor when a new order is received/paid for.
 *
 * @class    WC_Email_Notify_Vendor
 * @version  2.0.0
 * @extends  WC_Email
 * @author   WooThemes
 * @package  WooCommerce/Classes/Emails
 */
class WC_Email_Notify_Vendor extends WC_Email {

	/**
	 * Constructor
	 */
	function __construct() {

		$this->id          = 'vendor_new_order';
		$this->title       = __( 'Notify vendors - deprecated', 'wc-vendors' );
		$this->description = __( 'New order emails are sent when an order is received/paid by a customer. <strong>This email has been deprecated.</strong>', 'wc-vendors' );

		$this->heading = __( 'New customer order', 'wc-vendors' );
		$this->subject = __( '[{blogname}] New customer order ({order_number}) - {order_date}', 'wc-vendors' );

		$this->template_html  = 'vendor-new-order.php';
		$this->template_plain = 'vendor-new-order.php';
		$this->template_base  = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/templates/emails/';

		// Triggers for this email
		add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'trigger' ) );
		add_action( 'woocommerce_order_status_pending_to_completed_notification' , array( $this, 'trigger' ) );
		add_action( 'woocommerce_order_status_failed_to_processing_notification' , array( $this, 'trigger' ) );
		add_action( 'woocommerce_order_status_failed_to_completed_notification'  , array( $this, 'trigger' ) );
		add_action( 'woocommerce_order_status_on-hold_to_processing_notification', array( $this, 'trigger' ) ); // Added in 1.8.4
		add_action( 'woocommerce_order_status_on-hold_to_completed_notification' , array( $this, 'trigger' ) ); // Added in 1.8.4

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
	function trigger( $order_id ) {

		global $woocommerce;

		if ( $order_id ) {
			$this->object = wc_get_order( $order_id );

			$order_date = $this->object->get_date_created();

			$this->find[]    = '{order_date}';
			$this->replace[] = date_i18n( wc_date_format(), strtotime( $order_date ) );

			$this->find[]    = '{order_number}';
			$this->replace[] = $this->object->get_order_number();

		}

		if ( ! $this->is_enabled() ) {
			return;
		}

		$vendors = $this->get_vendors( $this->object );

		if ( empty( $vendors ) ) {
			return;
		}

		add_filter( 'woocommerce_order_get_items', array( $this, 'check_items' ), 10, 2 );
		add_filter( 'woocommerce_get_order_item_totals', array( $this, 'check_order_totals' ), 10, 2 );
		add_filter(
			'woocommerce_order_formatted_line_subtotal', array(
			$this,
			'check_order_formatted_line_subtotal',
		), 10, 3
		);
		add_filter( 'woocommerce_order_subtotal_to_display', array( $this, 'check_order_subtotal_to_display' ), 10, 3 );
		foreach ( $vendors as $user_id => $user_email ) {
			$this->current_vendor = $user_id;
			$this->send( $user_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
		remove_filter( 'woocommerce_get_order_item_totals', array( $this, 'check_order_totals' ), 10, 2 );
		remove_filter( 'woocommerce_order_get_items', array( $this, 'check_items' ), 10, 2 );
		remove_filter(
			'woocommerce_order_formatted_line_subtotal', array(
			$this,
			'check_order_formatted_line_subtotal',
		), 10, 3
		);
		remove_filter(
			'woocommerce_order_subtotal_to_display', array(
			$this,
			'check_order_subtotal_to_display',
		), 10, 3
		);
	}


	/**
	 *
	 *
	 * @param unknown $total_rows
	 * @param unknown $order
	 *
	 * @return unknown
	 */
	function check_order_totals( $total_rows, $order ) {

		$commission_label                 = apply_filters( 'wcv_notify_vendor_commission_label', __( 'Commission Subtotal:', 'wc-vendors' ) );
		$return['cart_subtotal']          = $total_rows['cart_subtotal'];
		$return['cart_subtotal']['label'] = $commission_label;

		if ( wc_string_to_bool( get_option( 'wcvendors_vendor_give_taxes', 'no' ) ) ) {
			$return['tax_subtotal']          = array(
				'label' => '',
				'value' => '',
			);
			$return['tax_subtotal']['label'] = apply_filters( 'wcv_notify_vendor_tax_label', __( 'Tax Subtotal:', 'wc-vendors' ) );
		}

		$dues = WCV_Vendors::get_vendor_dues_from_order( $order );

		foreach ( $dues as $due ) {
			if ( $this->current_vendor == $due['vendor_id'] ) {
				if ( ! empty( $return['shipping'] ) ) {
					$return['shipping'] = $total_rows['shipping'];
				}
				$return['shipping']['label'] = __( 'Shipping Subtotal:', 'wc-vendors' );
				$return['shipping']['value'] = wc_price( $due['shipping'] );
				if ( get_option( 'wcvendors_vendor_give_taxes' ) ) {
					$return['tax_subtotal']['value'] += (float) $due['tax'];
				}
				break;
			}
		}
		// Format tax price
		if ( wc_string_to_bool( get_option( 'wcvendors_vendor_give_taxes', 'no' ) ) ) {
			$return['tax_subtotal']['value'] = wc_price( $return['tax_subtotal'] ['value'] );
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
	public function get_vendors( $order ) {

		$items   = $order->get_items();
		$vendors = array();

		foreach ( $items as $key => $product ) {

			if ( empty( $product['product_id'] ) ) {
				continue;
			}
			$author = WCV_Vendors::get_vendor_from_product( $product['product_id'] );

			// Only store the vendor authors
			if ( ! WCV_Vendors::is_vendor( $author ) ) {
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
	function check_items( $items, $order ) {

		$settings = get_option( 'woocommerce_vendor_new_order_settings' );

		if ( empty( $settings ) ) {
			$settings = $this->get_default_settings();
		}

		foreach ( $items as $key => $product ) {

			// If this is a line item
			if ( $product['type'] == 'line_item' ) {

				$author = WCV_Vendors::get_vendor_from_product( $product['product_id'] );

				if ( $this->current_vendor != $author ) {
					unset( $items[ $key ] );
					continue;
				} else {

					// If display commission is ticked show this otherwise show the full price.
					if ( 'yes' == $settings['commission_display'] ) {

						$order_id = $order->get_id();

						// Get correct product_id depending on which product type
						$product_id = ! empty( $product['variation_id'] ) ? $product['variation_id'] : $product['product_id'];

						$commission_due = WCV_Commission::get_commission_due( $order_id, $product_id, $author );

						$items[ $key ]['line_subtotal'] = $commission_due;
						$items[ $key ]['line_total']    = $commission_due;

						// Don't display tax if give tax is not enabled.
						if ( ! get_option( 'wcvendors_vendor_give_taxes' ) ) {
							unset( $items[ $key ]['line_tax'] );
						}
					}
				}
			}
		}

		return $items;

	} // check_items()


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
		), 'woocommerce', $this->template_base
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
		), 'woocommerce', $this->template_base
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
			'enabled'            => array(
				'title'   => __( 'Enable/Disable', 'wc-vendors' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'wc-vendors' ),
				'default' => 'no',
			),
			'subject'            => array(
				'title'       => __( 'Subject', 'wc-vendors' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'wc-vendors' ), $this->subject ),
				'placeholder' => '',
				'default'     => '',
			),
			'heading'            => array(
				'title'       => __( 'Email Heading', 'wc-vendors' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'wc-vendors' ), $this->heading ),
				'placeholder' => '',
				'default'     => '',
			),
			'commission_display' => array(
				'title'   => __( 'Product Totals', 'wc-vendors' ),
				'type'    => 'checkbox',
				'label'   => __( 'Show the commission due/paid as the product totals instead of the product prices.', 'wc-vendors' ),
				'default' => 'yes',
			),
			'email_type'         => array(
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


	/**
	 *  check the order line item sub total to ensure that the tax is shown correctly on the vendor emails
	 */
	function check_order_formatted_line_subtotal( $subtotal, $item, $order ) {

		$order_currency = $order->get_currency();

		$subtotal = wc_price( $order->get_line_subtotal( $item ), array( 'currency' => $order_currency ) );

		return $subtotal;

	} // check_order_formatted_line_subtotal()


	function check_order_subtotal_to_display( $subtotal, $compound, $order ) {

		$new_subtotal = 0;

		foreach ( $order->get_items() as $key => $product ) {
			$new_subtotal += $product['line_subtotal'];
		}

		return wc_price( $new_subtotal );

	} // check_order_subtotal_to_display()

	/**
	 * Get the default settings for this email if not already set.
	 *
	 * @since 1.9.9
	 */
	public function get_default_settings() {

		$settings = array();

		foreach ( $this->form_fields as $key => $field ) {
			$settings[ $key ] = $field['default'];
		}

		return $settings;

	} // get_default_settings()


}
