<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCVendors_Admin_Notify_Shipped' ) ) :

	/**
	 * Notify Admin Shipped
	 *
	 * An email sent to the admin when the vendor marks the order shipped.
	 *
	 * @class       WCVendors_Admin_Notify_Shipped
	 * @version     2.0.0
	 * @package     Classes/Admin/Emails
	 * @author      WC Vendors
	 * @extends     WC_Email
	 */
	class WCVendors_Admin_Notify_Shipped extends WC_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id             = 'admin_notify_shipped';
			$this->title          = sprintf( __( 'Admin notify %s shipped', 'wc-vendors' ), wcv_get_vendor_name( true, false ) );
			$this->description    = sprintf( __( 'Notification is sent to chosen recipient(s) when a %s marks an order shipped.', 'wc-vendors' ), wcv_get_vendor_name( true, false ) );
			$this->template_html  = 'emails/admin-notify-shipped.php';
			$this->template_plain = 'emails/plain/admin-notify-shipped.php';
			$this->template_base  = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/templates/';
			$this->placeholders   = array(
				'{site_title}'   => $this->get_blogname(),
				'{order_date}'   => '',
				'{order_number}' => '',
			);

			// Call parent constructor
			parent::__construct();

			// Other settings
			$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
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
				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}'] = $this->object->get_order_number();
			}

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
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
				'recipient'  => array(
					'title'       => __( 'Recipient(s)', 'wc-vendors' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'wc-vendors' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
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
	}

endif;
