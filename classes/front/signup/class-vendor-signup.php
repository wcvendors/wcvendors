<?php

/**
 * Signup form for applying as a vendor
 *
 * @author  Matt Gates <http://mgates.me>, WC Vendors <http://wcvendors.com>
 * @package WCVendors
 */


class WCV_Vendor_Signup {


	/**
	 * __construct()
	 */
	function __construct() {

		if ( ! wc_string_to_bool( get_option( 'wcvendors_vendor_allow_registration', 'no' ) ) ) {
			return;
		}

		$this->terms_page = get_option( 'wcvendors_vendor_terms_page_id' );

		add_action( 'woocommerce_register_form', array( $this, 'vendor_option' ) );

		if ( ! class_exists( 'WCVendors_Pro' ) ) {
			add_action( 'woocommerce_created_customer', array( $this, 'save_pending' ), 10, 2 );
		}

		add_action( 'template_redirect', array( $this, 'apply_form_dashboard' ), 10 );
		add_action( 'woocommerce_register_post', array( $this, 'validate_vendor_registration' ), 10, 3 );

		if ( $this->terms_page ) {
			add_action( 'login_enqueue_scripts', array( $this, 'load_scripts' ), 1 );
			add_filter( 'registration_errors', array( $this, 'vendor_registration_errors' ), 10, 3 );
		}
	}

	/**
	 *
	 */
	public function vendor_option() {

		$become_a_vendor_label = strtolower( __( get_option( 'wcvendors_label_become_a_vendor', __( 'Become a ', 'wc-vendors' ) ), 'wc-vendors' ) );

		apply_filters( 'wcvendors_vendor_signup_path', include_once 'views/html-vendor-signup.php' );
	}


	/**
	 * WILL BE COMPLETELY REMOVED
	 *
	 * Show apply to be vendor on the wp-login screen
	 *
	 * @since   1.9.0
	 * @version 1.0.0
	 */
	public function login_apply_vendor_option() {

		include_once 'views/html-vendor-signup.php';

	} // login_apply_vendor_option


	/**
	 * Load the javascript for the terms page
	 *
	 * @since   1.9.0
	 * @version 1.0.0
	 */
	public function load_scripts() {

		wp_enqueue_script( 'wcv-admin-login', wcv_assets_url . 'js/wcv-admin-login.js', array( 'jquery' ), WCV_VERSION, true );

	} // load_scripts()


	public function vendor_registration_errors( $errors, $sanitized_user_login, $user_email ) {

		if ( empty( $_POST['agree_to_terms'] ) || ! empty( $_POST['agree_to_terms'] ) && trim( $_POST['agree_to_terms'] ) == '' ) {
			$errors->add( 'terms_errors', sprintf( '<strong>%s</strong>: %s', __( 'ERROR', 'wc-vendors' ), __( 'Please agree to the terms and conditions', 'wc-vendors' ) ) );
		}

		return $errors;
	}

	/**
	 *
	 *
	 * @param unknown $user_id
	 */
	public function save_pending( $user_id ) {

		if ( isset( $_POST['apply_for_vendor'] ) ) {

			wc_clear_notices();

			if ( user_can( $user_id, 'manage_options' ) ) {
				wc_add_notice( apply_filters( 'wcvendors_application_denied_msg', __( 'Application denied. You are an administrator.', 'wc-vendors' ) ), 'error' );
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
	 * Save the pending vendor from the login screen
	 *
	 * @since   1.9.0
	 * @version 1.0.0
	 */
	public function login_save_pending( $user_id ) {

		if ( isset( $_POST['apply_for_vendor'] ) ) {

			$manual = wc_string_to_bool( get_option( 'wcvendors_vendor_approve_registration', 'no' ) );
			$role   = apply_filters( 'wcvendors_pending_role', ( $manual ? 'pending_vendor' : 'vendor' ) );

			$wp_user_object = new WP_User( $user_id );
			$wp_user_object->add_role( $role );

			do_action( 'wcvendors_application_submited', $user_id );

		}

	} // login_save_pending()

	/**
	 *  Login authentication check code for vendors
	 */
	public function login_vendor_check( $user, $password ) {

		if ( isset( $_POST['apply_for_vendor'] ) ) {

			if ( $this->terms_page && ! isset( $_POST['agree_to_terms'] ) ) {
				$error = new WP_Error();
				$error->add( 'no_terms', apply_filters( 'wcvendors_agree_to_terms_error', __( 'You must accept the terms and conditions to become a vendor.', 'wc-vendors' ) ) );

				return $error;
			} else {
				return $user;
			}
		}

		return $user;

	}


	public function redirect_to_vendor_dash( $redirect ) {

		$vendor_dashboard_page = get_option( 'wcvendors_vendor_dashboard_page_id' );

		return apply_filters( 'wcvendors_signup_redirect', get_permalink( $vendor_dashboard_page ) );
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function apply_form_dashboard() {

		global $wp_query;

		if ( ! isset( $_POST['apply_for_vendor'] ) ) {
			return false;
		}

		$vendor_dashboard_page = get_option( 'wcvendors_vendor_dashboard_page_id' );
		$page_id               = get_queried_object_id();

		if ( $page_id == $vendor_dashboard_page || isset( $wp_query->query['become-a-vendor'] ) ) {
			if ( $this->terms_page ) {
				if ( isset( $_POST['agree_to_terms'] ) ) {
					self::save_pending( get_current_user_id() );
				} else {
					wc_add_notice( apply_filters( 'wcvendors_agree_to_terms_error', __( 'You must accept the terms and conditions to become a vendor.', 'wc-vendors' ), 'error' ) );
				}
			} else {
				self::save_pending( get_current_user_id() );
			}
		}
	}

	public function validate_vendor_registration( $username, $email, $validation_errors ) {

		if ( isset( $_POST['apply_for_vendor'] ) ) {
			if ( $this->terms_page && ! isset( $_POST['agree_to_terms'] ) ) {
				$validation_errors->add( 'agree_to_terms_error', apply_filters( 'wcvendors_agree_to_terms_error', __( 'You must accept the terms and conditions to become a vendor.', 'wc-vendors' ) ) );
			}
		}
	}


}
