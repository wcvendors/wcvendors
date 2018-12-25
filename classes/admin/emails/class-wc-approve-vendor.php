<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * New Order Email
 *
 * An email sent to the admin when a new order is received/paid for.
 *
 * @class    WC_Email_Approve_Vendor
 * @version  2.0.0
 * @extends  WC_Email
 * @author   WooThemes
 * @package  WooCommerce/Classes/Emails
 */
class WC_Email_Approve_Vendor extends WC_Email {


	/**
	 * Constructor
	 */
	function __construct() {

		$this->id          = 'vendor_application';
		$this->title       = sprintf( __( '%s Application - deprecated', 'wc-vendors' ), wcv_get_vendor_name() );
		$this->description = sprintf( __( '%s application will either be approved, denied, or pending. <strong>This email has been deprecated.</strong>', 'wc-vendors' ), wcv_get_vendor_name() );

		$this->heading = __( 'Application {status}', 'wc-vendors' );
		$this->subject = sprintf( __( '[{blogname}] Your %s application has been {status}', 'wc-vendors' ), wcv_get_vendor_name( true, false ) );

		$this->template_base  = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/templates/emails/';
		$this->template_html  = 'application-status.php';
		$this->template_plain = 'application-status.php';

		// Call parent constuctor
		parent::__construct();

		// Other settings
		$this->recipient = $this->get_option( 'recipient' );

		if ( ! $this->recipient ) {
			$this->recipient = get_option( 'admin_email' );
		}
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 *
	 * @param unknown $order_id
	 */
	function trigger( $user_id, $status ) {

		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->find[]    = '{status}';
		$this->replace[] = $status;

		$this->status = $status;

		$this->user = get_userdata( $user_id );
		$user_email = $this->user->user_email;

		$this->send( $user_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

		if ( $status == __( 'pending', 'wc-vendors' ) ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
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
			'status'        => $this->status,
			'user'          => $this->user,
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
			'status'        => $this->status,
			'user'          => $this->user,
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
			'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'wc-vendors' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'wc-vendors' ),
				'default' => 'no',
			),
			'recipient'  => array(
				'title'       => __( 'Recipient(s)', 'woocommerce' ),
				'type'        => 'text',
				'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'wc-vendors' ), esc_attr( get_option( 'admin_email' ) ) ),
				'placeholder' => '',
				'default'     => '',
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
