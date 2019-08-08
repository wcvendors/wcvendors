<?php
/**
 * Defines the class to send admin notification for approved vendors.
 *
 * @version     2.0.13
 * @package     Classes/Admin/Emails
 * @author      WC Vendors
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCVendors_Admin_Notify_Approved' ) ) :

	/**
	 * Notify Admin Approved
	 *
	 * An email sent to the admin when admin approves a user to be a vendor
	 *
	 * @since   2.0.
	 * @version 2.0.
	 * @class   WCVendors_Admin_Notify_Approved
	 * @extends WC_Email
	 */
	class WCVendors_Admin_Notify_Approved extends WC_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'admin_notify_application';
			$this->title          = sprintf( __( 'Admin notify %s approved', 'wc-vendors' ), wcv_get_vendor_name( true, false ) );
			$this->description    = sprintf( __( 'Notification is sent to chosen recipient(s) when admin approves a user to be a %s', 'wc-vendors' ), wcv_get_vendor_name( true, false ) );
			$this->template_html  = 'emails/admin-notify-approved.php';
			$this->template_plain = 'emails/plain/admin-notify-approved.php';
			$this->template_base  = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/templates/';
			$this->placeholders   = array(
				'{site_title}' => $this->get_blogname(),
				'{user_name}'  => '',
			);

			// Call parent constructor.
			parent::__construct();

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
		}

		/**
		 * Get email subject.
		 *
		 * @since  2.0.0
		 * @return string
		 */
		public function get_default_subject() {
			return sprintf( __( '[{site_title}] {user_name} has been approved to be a %s', 'wc-vendors' ), wcv_get_vendor_name( true, false ) );
		}

		/**
		 * Get email heading.
		 *
		 * @since  2.0.0
		 * @return string
		 */
		public function get_default_heading() {
			return sprintf( __( '%s application approved', 'wc-vendors' ), wcv_get_vendor_name() );
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param int    $vendor_id The order ID.
		 * @param string $status    vendor role.
		 */
		public function trigger( $vendor_id, $status ) {

			$this->setup_locale();

			$this->user                        = get_userdata( $vendor_id );
			$this->status                      = $status;
			$this->placeholders['{user_name}'] = $this->user->user_login;

			$send_if     = $this->get_option( 'notification' );
			$should_send = $send_if == 'vendor' ? true : ( $send_if == 'vendor' && $status == 'approved' ? true : false );

			if ( $this->is_enabled() && $this->get_recipient() && $should_send ) {
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
		 * @access public
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
				'enabled'      => array(
					'title'   => __( 'Enable/Disable', 'wc-vendors' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'wc-vendors' ),
					'default' => 'yes',
				),
				'recipient'    => array(
					'title'       => __( 'Recipient(s)', 'wc-vendors' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'wc-vendors' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'subject'      => array(
					'title'       => __( 'Subject', 'wc-vendors' ),
					'type'        => 'text',
					'desc_tip'    => true,
					/* translators: %s: list of placeholders */
					'description' => sprintf( __( 'Available placeholders: %s', 'wc-vendors' ), '<code>{user_name}</code>' ),
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'      => array(
					'title'       => __( 'Email heading', 'wc-vendors' ),
					'type'        => 'text',
					'desc_tip'    => true,
					/* translators: %s: list of placeholders */
					'description' => sprintf( __( 'Available placeholders: %s', 'wc-vendors' ), '<code>{user_name}</code>' ),
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'notification' => array(
					'title'       => __( 'Notification', 'wc-vendors' ),
					'type'        => 'select',
					'description' => __( 'Choose when to be notified of an application.', 'wc-vendors' ),
					'default'     => 'vendor',
					'class'       => 'wc-enhanced-select',
					'options'     => array(
						'vendor'         => __( 'All Applications', 'wc-vendors' ),
						'pending_vendor' => __( 'Pending Applications', 'wc-vendors' ),
					),
					'desc_tip'    => true,
				),
				'email_type'   => array(
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
