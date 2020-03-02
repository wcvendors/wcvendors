<?php
/**
 * WC Vendors Vendor Functions
 *
 * Functions for customers.
 *
 * @package WCVendors/Functions
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wcv_create_new_vendor' ) ) {

	/**
	 * Create a new vendor.
	 *
	 * @param  string $email    Vendor email.
	 * @param  string $username Vendor username.
	 * @param  string $password Vendor password.
	 * @param  array  $args     List of arguments to pass to `wp_insert_user()`.
	 * @return int|WP_Error Returns WP_Error on failure, Int (user ID) on success.
	 */
	function wcv_create_new_vendor( $email, $username = '', $password = '', $args = array() ) {
		if ( empty( $email ) || ! is_email( $email ) ) {
			return new WP_Error( 'registration-error-invalid-email', __( 'Please provide a valid email address.', 'wc-vendors' ) );
		}

		if ( email_exists( $email ) ) {
			return new WP_Error( 'registration-error-email-exists', apply_filters( 'wcvendors_registration_error_email_exists', __( 'An account is already registered with your email address. Please log in.', 'wc-vendors' ), $email ) );
		}

		$username = sanitize_user( $username );

		if ( empty( $username ) || ! validate_username( $username ) ) {
			return new WP_Error( 'registration-error-invalid-username', __( 'Please enter a valid account username.', 'wc-vendors' ) );
		}

		if ( username_exists( $username ) ) {
			return new WP_Error( 'registration-error-username-exists', __( 'An account is already registered with that username. Please choose another.', 'wc-vendors' ) );
		}

		if ( empty( $password ) ) {
			return new WP_Error( 'registration-error-missing-password', __( 'Please enter an account password.', 'wc-vendors' ) );
		}

		// Use WP_Error to handle registration errors.
		$errors = new WP_Error();

		do_action( 'wcvendors_register_post', $username, $email, $errors );

		$errors = apply_filters( 'wcvendors_registration_errors', $errors, $username, $email );

		if ( $errors->get_error_code() ) {
			return $errors;
		}

		$approve_vendors = wc_string_to_bool( get_option( 'wcvendors_vendor_approve_registration', 'no' ) );
		$vendor_role   	 = apply_filters( 'wcvendors_pending_role', ( $approve_vendors ? 'pending_vendor' : 'vendor' ) );

		$new_vendor_data = apply_filters(
			'wcvendors_new_vendor_data',
			array_merge(
				$args,
				array(
					'user_login' => $username,
					'user_pass'  => $password,
					'user_email' => $email,
					'role'       => $role,
				)
			)
		);

		$vendor_id = wp_insert_user( $new_vendor_data );

		if ( is_wp_error( $vendor_id ) ) {
			return $vendor_id;
		}

		do_action( 'wcvendors_created_vendor', $vendor_id, $new_vendor_data );

		return $vendor_id;
	}
}


/**
 * Check to see if the user_id is a vendor. Based on capability and role.
 *
 * @param int $user_id the user id to check 
 */
function wcv_is_vendor( $user_id ) {
	$current_user = get_userdata( $user_id );

	if ( is_object( $current_user ) ) {

		if ( is_array( $current_user->roles ) ) {
			return in_array( 'vendor', $current_user->roles );
		}
	}

	return false;
}

/**
 * Check to see if the user_id is a vendor pending. Based on capability and role.
 *
 * @param int $user_id the user id to check 
 */
function wcv_is_vendor_pending( $user_id ) {
	$current_user = get_userdata( $user_id );

	if ( is_object( $current_user ) ) {

		if ( is_array( $current_user->roles ) ) {
			return in_array( 'vendor_pending', $current_user->roles );
		}
	}

	return false;
}

/**
 * Check to see if the user_id is a vendor denied. Based on capability and role.
 *
 * @param int $user_id the user id to check 
 */
function wcv_is_vendor_denied( $user_id ) {
	$current_user = get_userdata( $user_id );

	if ( is_object( $current_user ) ) {

		if ( is_array( $current_user->roles ) ) {
			return in_array( 'vendor_denied', $current_user->roles );
		}
	}

	return false;
}

/**
 * This function gets the vendor name used throughout the interface on the front and backend
 *
 * @param bool $sngluar is it a singular? 
 * @param bool $uppercase ucfirst() the label? 

 */
function wcv_get_vendor_name( $singluar = true, $upper_case = true ) {

	$vendor_singular = get_option( 'wcvendors_vendor_singular', __( 'Vendor', 'wc-vendors' ) );
	$vendor_plural   = get_option( 'wcvendors_vendor_plural', __( 'Vendors', 'wc-vendors' ) );

	$vendor_label = $singluar ? __( $vendor_singular, 'wc-vendors' ) : __( $vendor_plural, 'wc-vendors' );
	$vendor_label = $upper_case ? ucfirst( $vendor_label ) : lcfirst( $vendor_label );

	return apply_filters( 'wcv_vendor_display_name', $vendor_label, $vendor_singular, $vendor_plural, $singluar, $upper_case );

}

/**
 * Get all vendors
  *
 * @param array $args list of arguments to pass to get_users().
 */
function wcv_get_vendors( $args = array() ) {

	$args = wp_parse_args(
		$args,
		array(
			'role__in' => array( 'vendor', 'administrator' ),
			'fields'   => array( 'ID', 'display_name', 'username' ),
		)
	);

	$vendors = get_users( $args );

	return $vendors;
}

/**
 * Get the vendor display name
 *
 * @param int $vendor_id the vendor_id to get th display name for. 
 */
function wcv_get_vendor_display_name( $vendor_id ) {

	$vendor_display_name_option = apply_filters( 'wcv_vendor_display_name_option', 'user_login' );
	$vendor                     = get_userdata( $vendor_id );
	$display_name               = __( 'vendor', 'wc-vendors' );

	switch ( $vendor_display_name_option ) {

		case 'display_name':
			$display_name = $vendor->display_name;
			break;
		case 'user_email':
			$display_name = $vendor->user_email;
			break;
		case 'user_login':
			$display_name = $vendor->user_login;
			break;
		default:
			$display_name = $vendor->user_login;
			break;
	}

	return $display_name;

}

/**
 * Set the primary role of the specified user to vendor while retaining all other roles after
 *
 * @param $user WP_User the user object to 
 *
 * @since 2.1.10
 * @version 2.1.10
 */

if ( ! function_exists( 'wcv_set_primary_vendor_role' ) ){
	function wcv_set_primary_vendor_role( $user ){
		// Get existing roles
		$existing_roles = $user->roles;
		// Remove all existing roles
		foreach ( $existing_roles as $role ) {
			$user->remove_role( $role );
		}
		// Add vendor first
		$user->add_role( 'vendor' );
		// Re-add all other roles.
		foreach ( $existing_roles as $role ) {
			$user->add_role( $role );
		}
	}
}