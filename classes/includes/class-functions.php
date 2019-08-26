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

		return isset( $wp_roles->role_names[ $role ] ) ? $role : false;
	}
}


/**
 * This function gets the vendor name used throughout the interface on the front and backend
 */
function wcv_get_vendor_name( $singluar = true, $upper_case = true ) {

	$vendor_singular = get_option( 'wcvendors_vendor_singular', __( 'Vendor', 'wc-vendors' ) );
	$vendor_plural   = get_option( 'wcvendors_vendor_plural', __( 'Vendors', 'wc-vendors' ) );

	$vendor_label = $singluar ? __( $vendor_singular, 'wc-vendors' ) : __( $vendor_plural, 'wc-vendors' );
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

/**
 * Filterable navigation items classes for Vendor Dashboard.
 *
 * @param string $item_id Navigation item ID.
 *
 * @return string
 */
function wcv_get_dashboard_nav_item_classes( $item_id ) {

	$classes = array( 'button' );

	$classes = apply_filters( 'wcv_dashboard_nav_item_classes', $classes, $item_id );

	return implode( ' ', array_map( 'sanitize_html_class', $classes ) );
}


/**
 * Generate a drop down with the vendor name based on the Dsiplay name setting used in the admin
 *
 * @since 2.1.10
 * @return string
 */
if ( !function_exists( 'wcv_vendor_drop_down_options' ) ){
	function wcv_vendor_drop_down_options( $users, $vendor_id ){
		$output = '';
		foreach ( (array) $users as $user ) {
			$shop_name = WCV_Vendors::get_vendor_sold_by( $user->ID );
			$display_name = empty( $shop_name ) ? $user->display_name : $shop_name;
			$select = selected( $user->ID, $vendor_id, false );
			$output .= "<option value='$user->ID' $select>$display_name</option>";
		}
		return apply_filters('wcv_vendor_drop_down_options', $output );
	}
}


/**
 * Set the primary role of the specified user to vendor while retaining all other roles after
 *
 * @param $user WP_User
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
