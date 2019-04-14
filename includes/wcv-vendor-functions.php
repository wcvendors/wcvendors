<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vendor helper functions 
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Functions
 * @package     WCVendors/Functions
 * @version     2.0.0
 */

/**
 * Check to see if the user_id is a vendor. Based on capability and role. 
 */
function wcv_is_vendor( $user_id ){ 
	$current_user = get_userdata( $user_id ); 
	
	if ( is_object( $current_user ) ) { 

		if ( is_array( $current_user->roles ) ){ 
			return in_array( 'vendor', $current_user->roles ); 
		}
	}

	return false;
}
/**
 * Check to see if the user_id is a vendor pending. Based on capability and role. 
 */
function wcv_is_vendor_pending( $user_id ){ 
	$current_user = get_userdata( $user_id ); 
	
	if ( is_object( $current_user ) ) { 

		if ( is_array( $current_user->roles ) ){ 
			return in_array( 'vendor_pending', $current_user->roles ); 
		}
	}

	return false;
}

/**
 * Check to see if the user_id is a vendor denied. Based on capability and role. 
 */
function wcv_is_vendor_denied( $user_id ){ 
	$current_user = get_userdata( $user_id ); 
	
	if ( is_object( $current_user ) ) { 

		if ( is_array( $current_user->roles ) ){ 
			return in_array( 'vendor_denied', $current_user->roles ); 
		}
	}

	return false;
}

/**
 * This function gets the vendor name used throughout the interface on the front and backend 
 */
function wcv_get_vendor_name( $singluar = true ){ 
	if ( $singluar ){  
		return apply_filters( 'wcv_vendor_display_name_singluar', __( 'Vendor', 'wcvendors' ) ); 
	} else { 
		return apply_filters( 'wcv_vendor_display_name_plural', __( 'Vendors', 'wcvendors' ) ); 
	}
}

/**
 * Get all vendors 
 */
function wcv_get_vendors( $args = array() ){ 

	$args = wp_parse_args( $args, array(
		'role__in'	=> array( 'vendor', 'administrator' ), 
		'fields' 	=> array( 'ID', 'display_name', 'username' )
	) );

	$vendors = get_users( $args ); 
	return $vendors; 
}

/**
 * Get the vendor display name  
 */
function wcv_get_vendor_display_name( $vendor_id ){ 

	$vendor_display_name_option 	= apply_filters( 'wcv_vendor_display_name_option', 'user_login' );
	$vendor 						= get_userdata( $vendor_id ); 
	$display_name 					= __( 'vendor', 'wcvendors' ); 

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