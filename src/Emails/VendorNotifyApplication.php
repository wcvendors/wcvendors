<?php
/**
 * Defines the class to notify vendor about new application
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

if ( ! class_exists( 'VendorNotifyApplication' ) ) :

	/**
	 * Notify vendor application has started
	 *
	 * An email sent to the admin when the vendor marks the order shipped.
	 *
	 * @class       VendorNotifyApplication
	 * @extends     WC_Email
	 */
	class VendorNotifyApplication extends WC_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id = 'vendor_notify_application';
			/* translators: %s: name used to refer to a vendor */
			$this->title = sprintf( __( '%s notify application', 'wc-vendors' ), wcv_get_vendor_name() );
			/* translators: %s: name used to refer to a vendor */
			$this->description    = sprintf( __( 'Notification is sent to the %s that their application has been received', 'wc-vendors' ), wcv_get_vendor_name( true, false ) );
			$this->template_html  = 'emails/vendor-notify-application.php';
			$this->template_plain = 'emails/plain/vendor-notify-application.php';
			$this->template_base  = dirname( dirname( dirname( __FILE__ ) ) ) . '/templates/';
			$this->placeholders   = array(
				'{site_title}' => $this->get_blogname(),
			);

			// Call parent constructor.
			parent::__construct();

		}

		/**
		 * Get email subject.
		 *
		 * @since  2.0.0
		 * @return string
		 */
		public function get_default_subject() {
			/* translators: %s: Name used to refer to a vendor */
			return sprintf( __( '[{site_title}] Your %s application has been received', 'wc-vendors' ), wcv_get_vendor_name( true, false ) );
		}

		/**
		 * Get email heading.
		 *
		 * @since  2.0.0
		 * @return string
		 */
		public function get_default_heading() {
			/* translators: %s: name used to refer to a vendor */
			return sprintf( __( '%s application received', 'wc-vendors' ), wcv_get_vendor_name() );
		}

		/**
		 * Get default email content.
		 *
		 * @return string
		 * @version 3.0.0
		 * @since   2.0.0
		 */
		public function get_default_content() {
			/* translators: %s: Name used to refer to a vendor */
			return sprintf( __( 'Hi there. This is a notification about your %1$s application on %2$s.', 'wc-vendors' ), wcv_get_vendor_name( true, false ), get_option( 'blogname' ) );
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param int      $vendor_id The order ID.
		 * @param WC_Order $status    Status of the application.
		 */
		public function trigger( $vendor_id, $status = '' ) {

			$this->setup_locale();

			$this->user       = get_userdata( $vendor_id );
			$this->user_email = $this->user->user_email;
			$this->status     = $status;

			if ( $this->is_enabled() ) {
				$this->send( $this->user_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
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
					'user'          => $this->user,
					'status'        => $this->status,
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
					'user'          => $this->user,
					'status'        => $this->status,
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
				'subject'    => array(
					'title'       => __( 'Subject', 'wc-vendors' ),
					'type'        => 'text',
					'desc_tip'    => true,
					/* translators: %s: list of placeholders */
					'description' => sprintf( __( 'Available placeholders: %s', 'wc-vendors' ), '<code>{site_title}</code>' ),
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'    => array(
					'title'       => __( 'Email heading', 'wc-vendors' ),
					'type'        => 'text',
					'desc_tip'    => true,
					/* translators: %s: list of placeholders */
					'description' => sprintf( __( 'Available placeholders: %s', 'wc-vendors' ), '<code>{site_title}</code>' ),
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
