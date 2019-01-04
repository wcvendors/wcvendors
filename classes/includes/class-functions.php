<?php

if ( ! class_exists( 'WCV_Dependencies' ) ) {
	require_once 'class-dependencies.php';
}

/**
 * WC Detection
 * */
if ( ! function_exists( 'wcv_is_woocommerce_activated' ) ) {
	function wcv_is_woocommerce_activated() {

		return WCV_Dependencies::woocommerce_active_check();
	}
}

/*
*
*  Get User Role
*/
if ( ! function_exists( 'wcv_get_user_role' ) ) {
	function wcv_get_user_role( $user_id ) {

		global $wp_roles;
		$user  = new WP_User( $user_id );
		$roles = $user->roles;
		$role  = array_shift( $roles );

		return isset( $wp_roles->role_names[ $role ] ) ? translate_user_role( $wp_roles->role_names[ $role ] ) : false;
	}
}


/**
 * This function gets the vendor name used throughout the interface on the front and backend
 */
function wcv_get_vendor_name( $singluar = true, $upper_case = true ) {

	$vendor_singular = get_option( 'wcvendors_vendor_singular', __( 'Vendor', 'wc-vendors' ) );
	$vendor_plural   = get_option( 'wcvendors_vendor_plural', __( 'Vendors', 'wc-vendors' ) );

	$vendor_label = $singluar ? $vendor_singular : $vendor_plural;
	$vendor_label = $upper_case ? ucfirst( $vendor_label ) : lcfirst( $vendor_label );

	return apply_filters( 'wcv_vendor_display_name', $vendor_label, $vendor_singular, $vendor_plural, $singluar, $upper_case );

}

// Output a single select page drop down
function wcv_single_select_page( $id, $value, $class = '', $css = '' ) {

	$dropdown_args = array(
		'name'             => $id,
		'id'               => $id,
		'sort_column'      => 'menu_order',
		'sort_order'       => 'ASC',
		'show_option_none' => ' ',
		'class'            => $class,
		'echo'             => false,
		'selected'         => $value,
	);

	echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'wc-vendors' ) . "' style='" . $css . "' class='" . $class . "' id=", wp_dropdown_pages( $dropdown_args ) );
}

// Get the WC Vendors Screen ids
function wcv_get_screen_ids() {

	return apply_filters(
		'wcv_get_screen_ids', array(
			'wc-vendors_page_wcv-settings',
			'wc-vendors_page_wcv-commissions',
			'wc-vendors_page_wcv-extensions',
		)
	);
}


