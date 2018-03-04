<?php

if ( !class_exists( 'WCV_Dependencies' ) ) require_once 'class-dependencies.php';

/**
 * WC Detection
 * */
if ( !function_exists( 'wcv_is_woocommerce_activated' ) ) {
	function wcv_is_woocommerce_activated()
	{
		return WCV_Dependencies::woocommerce_active_check();
	}
}

/**
 * JS Detection
 * */
if ( !function_exists( 'wcv_is_jigoshop_activated' ) ) {
	function wcv_is_jigoshop_activated()
	{
		return WCV_Dependencies::jigoshop_active_check();
	}
}

/**
 * EDD Detection
 * */
if ( !function_exists( 'wcv_is_edd_activated' ) ) {
	function wcv_is_edd_activated()
	{
		return WCV_Dependencies::edd_active_check();
	}
}

/*
*
*  Get User Role
*/
if (!function_exists('wcv_get_user_role')) {
	function wcv_get_user_role($user_id) {
		global $wp_roles;
		$user = new WP_User($user_id);
		$roles = $user->roles;
		$role = array_shift($roles);
		return isset($wp_roles->role_names[$role]) ? translate_user_role($wp_roles->role_names[$role] ) : false;
	}
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

// Output a single select page drop down
function wcv_single_select_page( $id, $value, $class = '', $css = '' ){

	$dropdown_args = array(
			'name'             => $id,
			'id'               => $id,
			'sort_column'      => 'menu_order',
			'sort_order'       => 'ASC',
			'show_option_none' => ' ',
			'class'            => $class,
			'echo'             => false,
			'selected'         => $value
		);

	echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'wcvendors' ) . "' style='" . $css . "' class='" . $class . "' id=", wp_dropdown_pages( $dropdown_args ) );
}

?>
