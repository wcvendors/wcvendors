<?php
/**
 * Vendor helper functions
 *
 * @package     WCVendors/Functions
 */

/**
 * Check to see if the user_id is a vendor. Based on capability and role.
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
 */
function wcv_get_vendor_name( $singluar = true ) {
	if ( $singluar ) {
		return apply_filters( 'wcv_vendor_display_name_singluar', __( 'Vendor', 'wc-vendors' ) );
	} else {
		return apply_filters( 'wcv_vendor_display_name_plural', __( 'Vendors', 'wc-vendors' ) );
	}
}

/**
 * Get all vendors
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
		$author = - 1;
	}

	return $author;
}