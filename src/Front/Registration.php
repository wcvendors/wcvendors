<?php
/**
 * Registration clas file 
 *
 * @package WCVendors
 */

namespace WCVendors\Front;
use WP_user; 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The vendor registration class
 * 
 * @since 3.0.0 
 */
class Registration { 


	/**
	 * Init hooks for registration 
	 */
	public function init_hooks(){ 

		if ( ! wc_string_to_bool( get_option( 'wcvendors_vendor_allow_registration', 'no' ) ) ) {
			return;
		}

		add_action( 'woocommerce_register_form', 	array( $this, 'add_vendor_registration' ) );
		add_action( 'woocommerce_register_post', 	array( $this, 'validate_registration' ), 10, 3 );
		add_action( 'woocommerce_created_customer', array( $this, 'save_registration' ), 10, 2 );
		
	}

	/**
	 * Add the vendor registration form to the WooCommerce register form 
	 */
	public function add_vendor_registration(){ 

		$become_a_vendor_label = strtolower( __( get_option( 'wcvendors_label_become_a_vendor', __( 'Become a ', 'wc-vendors' ) ), 'wc-vendors' ) );
		$terms_page = get_option( 'wcvendors_vendor_terms_page_id', '' );

		wc_get_template( 
			'vendor-registration.php', array( 
			'terms_page'			=> $terms_page,
			'become_a_vendor_label'	=> $become_a_vendor_label, 
			), 'wc-vendors/front/', WCV_PLUGIN_PATH . 'templates/front/' 
		); 
	}


	/**
	 * Validate the terms page 
	 * 
	 * @param string $username the username 
	 * @param string $email the email address 
	 * @param WP_Error $errors the error object 
	 * @return WP_Error $errors the error object 
	 */
	public function validate_registration( $username, $email, $errors ) {

		$terms_page = get_option( 'wcvendors_vendor_terms_page_id', '' );

		if ( isset( $_POST['apply_for_vendor'] ) ) {
			if ( $terms_page && ! isset( $_POST['agree_to_terms'] ) ) {
				$errors->add( 'agree_to_terms_error', apply_filters( 'wcvendors_agree_to_terms_error', __( 'You must accept the terms and conditions to become a vendor.', 'wc-vendors' ) ) );
			}
		}

		return $errors;
	}

	/**
	 * Save the vendor registrations 
	 *
	 * @param int|WP_Error $user_id user_id returned when created via registration form. 
	 */
	public function save_registration( $user_id ) {

		if ( isset( $_POST['apply_for_vendor'] ) ) {

			wc_clear_notices();

			if ( user_can( $user_id, 'manage_options' ) ) {
				wc_add_notice( apply_filters( 'wcvendors_application_denied_msg', __( 'Administrators should not apply to be a vendor. Create a separate vendor account.', 'wc-vendors' ) ), 'error' );
			} else {
				wc_add_notice( apply_filters( 'wcvendors_application_submitted_msg', __( 'Your application has been submitted.', 'wc-vendors' ) ), 'notice' );

				$manual = wc_string_to_bool( get_option( 'wcvendors_vendor_approve_registration', 'no' ) );
				$role   = apply_filters( 'wcvendors_pending_role', ( $manual ? 'pending_vendor' : 'vendor' ) );

				$wp_user_object = new WP_User( $user_id );
				$wp_user_object->add_role( $role );

				do_action( 'wcvendors_application_submited', $user_id );
				add_filter( 'woocommerce_registration_redirect', array( $this, 'redirect_to_vendor_dash' ) );
			}
		}
	}


	/**
	 * Redirect to the vendor dashboard after registration 
	 * 
	 * @param string $redirect - the redirect URL. 
	 */
	public function redirect_to_vendor_dash( $redirect ) {

		$vendor_dashboard_page = get_option( 'wcvendors_vendor_dashboard_page_id' );
		return apply_filters( 'wcvendors_signup_redirect', get_permalink( $vendor_dashboard_page ) );
	}

}