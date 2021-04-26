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
 * The vendor store info defaults plugins can hook into this to add more data to a vendor store. 
 *
 * @return array $vendor_store_defaults
 */
function wcv_vendor_store_info_defaults(){

	$store_defaults =  array(  
			'name' => '',
			'info' => '',
			'description' => '',
			'permalink' => '',
			'slug' => '',
			'icon' => '',
			'banner' => '',
			'phone' => '',
			'email' => '', 
			'address' => array(),
			'location' => array(), 
			'payment' => array(
				'paypal' => array(
					'email' => ''
				), 
				'bank'   => array(
					'account_name'   => '',
					'account_number' => '',
					'bank_name'      => '',
					'iban'           => '',
					'bic_swift'      => '',  
				),
			),
			'give_tax'      => 'no', // Capability? 
			'give_shipping' => 'no', // Capability? 
			'commission_rate' => '', 
	);

	return apply_filters( 'wcv_store_data_defaults', $store_defaults );
}

/**
 * Format the wp user meta removing any empty values
 *
 * @since 3.0.0
 * @param int $vendor_id Vendor ID to look up 
 * @return array $wp_user_meta cleand array 
 */
function wcv_format_user_data( $vendor_id ){ 

	// user array_filter to remove empty values
	$wp_user_meta = array_filter( 
		array_map( 
			function( $a ) {
				return $a[0];
			}, 
			get_user_meta( $vendor_id ) 
		)
	);

	return apply_filters( 'wcv_formatted_user_data', $wp_user_meta, $vendor_id );
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

/**
 * Retrieve the shop name for a specific vendor
 *
 * @param int $vendor_id
 *
 * @version 3.0.0
 * @since   3.0.0
 * @return string
 */
function wcv_get_vendor_shop_name( $vendor_id ) {

	$name      = $vendor_id ? get_user_meta( $vendor_id, 'pv_shop_name', true ) : false;
	$shop_name = ( ! $name && $vendor = get_userdata( $vendor_id ) ) ? $vendor->user_login : $name;

	return $shop_name;
}

/**
 * Get vendors from an order including all user meta and vendor items filtered and grouped
 *
 * @param object  $order
 * @param unknown $items (optional)
 *
 * @return array $vendors
 * @version 3.0.0
 * @since   3.0.0
 */
function wcv_get_vendors_from_order( $order, $items = false ) {

	$vendors      = array();
	$vendor_items = array();

	if ( is_a( $order, 'WC_Order' ) ) {

		// Only loop through order items if there isn't an error
		if ( is_array( $order->get_items() ) || is_object( $order->get_items() ) ) {

			foreach ( $order->get_items() as $item_id => $order_item ) {

				if ( 'line_item' === $order_item->get_type() ) {

					$product_id = ( $order_item->get_variation_id() ) ? $order_item->get_variation_id() : $order_item->get_product_id();
					$vendor_id  = wcv_get_vendor_from_product( $product_id );

					if ( ! wcv_is_vendor( $vendor_id ) ) {
						continue;
					}

					if ( array_key_exists( $vendor_id, $vendors ) ) {
						$vendors[ $vendor_id ]['line_items'][ $order_item->get_id() ] = $order_item;
					} else {
						$vendor_details        = array(
							'vendor'     => get_userdata( $vendor_id ),
							'line_items' => array( $order_item->get_id() => $order_item ),
						);
						$vendors[ $vendor_id ] = $vendor_details;
					}
				}
			}
		} else {
			$vendors = array();
		}
	}

	// legacy filter left in place.
	$vendors = apply_filters( 'pv_vendors_from_order', $vendors, $order );

	return apply_filters( 'wcvendors_get_vendors_from_order', $vendors, $order );
}
	
/**
 * Get a vendor from a product.
 *
 * @param int $product_id The product id.
 *
 * @version  3.0.0
 * @since    3.0.0
 * @return mixed
 */
function wcv_get_vendor_from_product( $product_id ) {

	// Make sure we are returning an author for products or product variations only
	if ( 'product' === get_post_type( $product_id ) || 'product_variation' === get_post_type( $product_id ) ) {
		$parent = get_post_ancestors( $product_id );
		if ( $parent ) {
			$product_id = $parent[0];
		}

		$post   = get_post( $product_id );
		$author = $post ? $post->post_author : 1;
		$author = apply_filters( 'pv_product_author', $author, $product_id );
	} else {
		$author = -1;
	}

	return $author;
}