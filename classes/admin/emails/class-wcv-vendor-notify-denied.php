<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCV_Vendor_Notify_Denied' ) ) :

/**
 * Notify vendor application has been denied
 *
 * An email sent to the vendor when the admin denies their application
 *
 * @class       WCV_Vendor_Notify_Denied
 * @version     2.0.0
 * @package     Classes/Admin/Emails
 * @author      WC Vendors
 * @extends     WC_Email
 */
class WCV_Vendor_Notify_Denied extends WC_Email {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = 'vendor_notify_denied';
		$this->title          = __( 'Vendor notify denied', 'wc-vendors' );
		$this->description    = __( 'Notification is sent to the vendor that their application has been denied', 'wc-vendors' );
		$this->template_html  = 'emails/vendor-notify-denied.php';
		$this->template_plain = 'emails/plain/vendor-notify-denied.php';
		$this->template_base  = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/templates/';
		$this->placeholders   = array(
			'{site_title}'   => $this->get_blogname(),
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
		return __( '[{site_title}] Your vendor application has been denied', 'wc-vendors' );
	}

	/**
	 * Get email heading.
	 *
	 * @since  2.0.0
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Vendor Application Denied', 'wc-vendors' );
	}

	/**
	* Get email content
	*
	* @since  2.0.0
	* @return string
	*/
	public function get_default_content(){
		return __( 'Your application to become a vendor has been denied.', 'wc-vendors' );
	}

	/**
	* Get email reason
	*
	* @since  2.0.0
	* @return string
	*/
	public function get_default_reason(){
		return __( 'We are not taking any new applications at this time.', 'wc-vendors' );
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int $order_id The order ID.
	 * @param WC_Order $order Order object.
	 */
	public function trigger( $vendor_id, $reason = '' ) {

		$this->setup_locale();

		$this->user 		= get_userdata( $vendor_id );
		$this->user_email 	= $this->user->user_email;
		$this->content 		= $this->get_option( 'content', $this->get_default_content() );
		$this->reason 		= ( $reason ) ? $reason : $this->get_option( 'reason', $this->get_default_reason() );

		if ( $this->is_enabled() ) {
			$this->send( $this->user_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
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

		return wc_get_template_html( $this->template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => false,
			'email'			=> $this,
			'user'			=> $this->user,
			'reason' 		=> $this->reason,
			'content'		=> $this->content,
		), 'woocommerce', $this->template_base );
	}

	/**
	 * Get content plain.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => true,
			'email'			=> $this,
			'reason' 		=> $this->reason,
			'content'		=> $this->content,
			'user'			=> $this->user,
		) );
	}

	/**
	 * Initialise settings form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'         => __( 'Enable/Disable', 'wc-vendors' ),
				'type'          => 'checkbox',
				'label'         => __( 'Enable this email notification', 'wc-vendors' ),
				'default'       => 'no',
			),
			'subject' => array(
				'title'         => __( 'Subject', 'wc-vendors' ),
				'type'          => 'text',
				'desc_tip'      => true,
				/* translators: %s: list of placeholders */
				'description'   => sprintf( __( 'Available placeholders: %s', 'wc-vendors' ), '<code>{site_title}</code>' ),
				'placeholder'   => $this->get_default_subject(),
				'default'       => '',
			),
			'content' => array(
				'title'         => __( 'Content', 'wc-vendors' ),
				'type'          => 'textarea',
				'desc_tip'      => true,
				/* translators: %s: list of placeholders */
				'description'   => sprintf( __( 'Available placeholders: %s', 'wc-vendors' ), '<code>{site_title}</code>' ),
				'placeholder'   => $this->get_default_content(),
				'default'       => $this->get_default_content(),
			),
			'reason' => array(
				'title'         => __( 'Reason', 'wc-vendors' ),
				'type'          => 'textarea',
				'desc_tip'      => true,
				'description'   => __( 'Provide a reason for denying the vendor application', 'wc-vendors'),
				'placeholder'   => $this->get_default_reason(),
				'default'       => $this->get_default_reason(),
			),
			'heading' => array(
				'title'         => __( 'Email heading', 'wc-vendors' ),
				'type'          => 'text',
				'desc_tip'      => true,
				/* translators: %s: list of placeholders */
				'description'   => sprintf( __( 'Available placeholders: %s', 'wc-vendors' ), '<code>{site_title}</code>' ),
				'placeholder'   => $this->get_default_heading(),
				'default'       => '',
			),
			'email_type' => array(
				'title'         => __( 'Email type', 'wc-vendors' ),
				'type'          => 'select',
				'description'   => __( 'Choose which format of email to send.', 'wc-vendors' ),
				'default'       => 'html',
				'class'         => 'email_type wc-enhanced-select',
				'options'       => $this->get_email_type_options(),
				'desc_tip'      => true,
			),
		);
	}
}

endif;

return new WCV_Vendor_Notify_Denied();
