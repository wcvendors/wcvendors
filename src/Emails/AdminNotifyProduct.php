<?php
/**
 * Defines the class to notify admin about new application
 *
 * @version     3.0.0
 * @since       3.0.0
 * @package     WC_Vendors
 * @subpackage  Emails
 */

namespace WCVendors\Emails;

use WC_Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AdminNotifyProduct' ) ) :

	/**
	 * Notify Admin of new vendor product
	 *
	 * An email sent to the admin when a vendor adds a new product for approval
	 *
	 * @class       AdminNotifyProduct
	 * @extends     WC_Email
	 */
	class AdminNotifyProduct extends WC_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'admin_notify_product';
			$this->title = sprintf(
				/* translators: %s: The name used to refer to vendors. */
				__( 'Admin new %s product', 'wc-vendors' ),
				wcv_get_vendor_name( true, false )
			);
			$this->description = sprintf(
				/* translators: %s: The name used to refer to a single vendor. */
				__( 'Notification is sent to chosen recipient(s) when a %s submits a product for approval.', 'wc-vendors' ),
				wcv_get_vendor_name()
			);
			$this->template_html  = 'emails/admin-notify-product.php';
			$this->template_plain = 'emails/plain/admin-notify-product.php';
			$this->template_base  = dirname( dirname( dirname( __FILE__ ) ) ) . '/templates/';
			$this->placeholders   = array(
				'{site_title}'   => $this->get_blogname(),
				'{product_name}' => '',
				'{vendor_name}'  => '',
			);

			// Triggers for this email.
			$this->init_hooks();

			// Call parent constructor.
			parent::__construct();

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
		}

		/**
		 * Add hooks to trigger this email
		 *
		 * @return void
		 * @version 3.0.0
		 * @since   3.0.0
		 */
		public function init_hooks() {
			add_action( 'pending_product', array( $this, 'trigger' ), 10, 2 );
			add_action( 'pending_product_variation', array( $this, 'trigger' ), 10, 2 );
		}

		/**
		 * Get email subject.
		 *
		 * @since  2.0.0
		 * @return string
		 */
		public function get_default_subject() {

			return sprintf(
				/* translators: %s: The name used to refer to as ingle vendor. */
				__( '[{site_title}] New %s product submitted by {vendor_name} - {product_name}', 'wc-vendors' ),
				wcv_get_vendor_name( true, false )
			);
		}

		/**
		 * Get email heading.
		 *
		 * @since  2.0.0
		 * @return string
		 */
		public function get_default_heading() {
			/* translators: %s: Name used to refer to a vendor */
			return sprintf( __( 'New %s product submitted: {product_name}', 'wc-vendors' ), wcv_get_vendor_name( true, false ) );
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param int      $post_id The order ID.
		 * @param WC_Order $post    Order object.
		 */
		public function trigger( $post_id, $post ) {

			$this->setup_locale();

			if ( ! wcv_is_vendor( $post->post_author ) ) {
				return;
			}

			$this->post_id     = $post_id;
			$this->vendor_id   = $post->post_author;
			$this->product     = wc_get_product( $post_id );
			$this->vendor_name = wcv_get_vendor_shop_name( $post->post_author );

			if ( is_object( $this->product ) ) {
				$this->placeholders['{product_name}'] = $this->product->get_title();
				$this->placeholders['{vendor_name}']  = $this->vendor_name;

				if ( $this->is_enabled() && $this->get_recipient() ) {
					$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
				}

				$this->restore_locale();
			}
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {

			return wc_get_template_html(
				$this->template_html,
				array(
					'order'         => $this->object,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => false,
					'email'         => $this,
					'post_id'       => $this->post_id,
					'vendor_id'     => $this->vendor_id,
					'vendor_name'   => $this->vendor_name,
					'product'       => $this->product,
				),
				'woocommerce',
				$this->template_base
			);
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {

			return wc_get_template_html(
				$this->template_plain,
				array(
					'order'         => $this->object,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => true,
					'email'         => $this,
					'post_id'       => $this->post_id,
					'vendor_id'     => $this->vendor_id,
					'vendor_name'   => $this->vendor_name,
					'product'       => $this->product,
				),
				'woocommerce',
				$this->template_base
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
					/* translators: %s: The default vendor name. */
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
