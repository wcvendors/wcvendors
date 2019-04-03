<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCVendors_Customer_Notify_Shipped' ) ) :

	/**
	 * Notify Admin Shipped
	 *
	 * An email sent to the admin when the vendor marks the order shipped.
	 *
	 * @class       WCVendors_Customer_Notify_Shipped
	 * @version     2.0.0
	 * @package     Classes/Admin/Emails
	 * @author      WC Vendors
	 * @extends     WC_Email
	 */
	class WCVendors_Customer_Notify_Shipped extends WC_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id             = 'customer_notify_shipped';
			$this->customer_email = true;
			$this->title          = sprintf( __( 'Customer %s shipped', 'wc-vendors' ), wcv_get_vendor_name( true, false ) );
			$this->description    = sprintf( __( 'Email is sent to the customer when a %s marks an order received/paid by a customer.', 'wc-vendors' ), wcv_get_vendor_name( true, false ) );
			$this->template_html  = 'emails/customer-notify-shipped.php';
			$this->template_plain = 'emails/plain/customer-notify-shipped.php';
			$this->template_base  = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/templates/';
			$this->placeholders   = array(
				'{site_title}'   => $this->get_blogname(),
				'{order_date}'   => '',
				'{order_number}' => '',
			);

			// Call parent constructor
			parent::__construct();

		}

		/**
		 * Get email subject.
		 *
		 * @since  2.0.0
		 * @return string
		 */
		public function get_default_subject() {

			return sprintf( __( '[{site_title}] %s has marked shipped ({order_number}) - {order_date}', 'wc-vendors' ), wcv_get_vendor_name() );
		}

		/**
		 * Get email heading.
		 *
		 * @since  2.0.0
		 * @return string
		 */
		public function get_default_heading() {

			return sprintf( __( '%s has shipped', 'wc-vendors' ), wcv_get_vendor_name() );
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param int      $order_id The order ID.
		 * @param WC_Order $order    Order object.
		 */
		public function trigger( $order_id, $user_id, $order = false ) {

			$this->setup_locale();
			$this->vendor_id = $user_id;

			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object                         = $order;
				$this->recipient                      = $this->object->get_billing_email();
				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}'] = $this->object->get_order_number();
			}

			if ( $this->is_enabled() && $this->get_recipient() ) {
				// Filter the order items to only show the products owned by the vendor that marked shipped.
				add_filter( 'woocommerce_order_get_items', array( $this, 'filter_vendor_items' ), 10, 3 );
				add_filter( 'woocommerce_get_order_item_totals', array( $this, 'udpate_order_totals' ), 10, 3 );

				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

				// Remove filters
				remove_filter( 'woocommerce_get_order_item_totals', array( $this, 'udpate_order_totals' ), 10, 3 );
				remove_filter( 'woocommerce_order_get_items', array( $this, 'filter_vendor_items' ), 10, 3 );
			}

			$this->restore_locale();
		}

		/**
		 * Get content html.
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_html() {

			return wc_get_template_html(
				$this->template_html, array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => true,
				'plain_text'    => false,
				'email'         => $this,
				'vendor_id'     => $this->vendor_id,
			), 'woocommerce', $this->template_base
			);
		}

		/**
		 * Get content plain.
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_plain() {

			return wc_get_template_html(
				$this->template_plain, array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => true,
				'plain_text'    => true,
				'email'         => $this,
				'vendor_id'     => $this->vendor_id,
			), 'woocommerce', $this->template_base
			);
		}

		/**
		 * Initialise settings form fields.
		 */
		public function init_form_fields() {

			$this->form_fields = array(
				'enabled'    => array(
					'title'   => __( 'Enable/Disable', 'wc-vendors' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'wc-vendors' ),
					'default' => 'yes',
				),
				'subject'    => array(
					'title'       => __( 'Subject', 'wc-vendors' ),
					'type'        => 'text',
					'desc_tip'    => true,
					/* translators: %s: list of placeholders */
					'description' => sprintf( __( 'Available placeholders: %s', 'wc-vendors' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'    => array(
					'title'       => __( 'Email heading', 'wc-vendors' ),
					'type'        => 'text',
					'desc_tip'    => true,
					/* translators: %s: list of placeholders */
					'description' => sprintf( __( 'Available placeholders: %s', 'wc-vendors' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'email_type' => array(
					'title'       => __( 'Email type', 'wc-vendors' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'wc-vendors' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true,
				),
			);
		}


		/**
		 * Filter the order to only show vendor products
		 *
		 * @param array    $items
		 * @param WC_Order $order
		 * @param array    $types
		 *
		 * @return array
		 */
		public function filter_vendor_items( $items, $order, $types ) {

			foreach ( $items as $item_id => $order_item ) {

				if ( 'line_item' === $order_item->get_type() ) {

					$product_id = ( $order_item->get_variation_id() ) ? $order_item->get_variation_id() : $order_item->get_product_id();

					if ( empty( $product_id ) ) {
						unset( $items[ $item_id ] );
						continue;
					}

					$product_vendor = WCV_Vendors::get_vendor_from_product( $product_id );

					if ( $this->vendor_id != $product_vendor ) {
						unset( $items[ $item_id ] );
						continue;
					}
				}
			}

			return $items;

		} // filter_vendor_items

		/**
		 * Update the order totals to only show the items for the product(s)
		 *
		 * @param array    $total_rows
		 * @param WC_Order $order
		 * @param string   $tax_display
		 *
		 * @return array
		 */
		public function udpate_order_totals( $total_rows, $order, $tax_display ) {

			$new_total_rows                  = array();
			$new_total_rows['cart_subtotal'] = $total_rows['cart_subtotal'];

			return $new_total_rows;
		}
	}

endif;
