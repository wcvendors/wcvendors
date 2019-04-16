<?php
/**
 * WC Vendors Admin Functions
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get all WC Vendors screen ids.
 *
 * @return array
 */
function wcv_get_screen_ids() {

	$wc_screen_id = sanitize_title( __( 'WC Vendors', 'woocommerce' ) );
	$screen_ids   = array(
		'toplevel_page_' . $wc_screen_id,
		$wc_screen_id . '_page_wcv-commissions',
		$wc_screen_id . '_page_wcv-vendors',
		$wc_screen_id . '_page_wcv-settings',
		$wc_screen_id . '_page_wcv-addons',
	);

	return apply_filters( 'wcvendors_screen_ids', $screen_ids );
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

	echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'wcvendors' ) . "' style='" . $css . "' class='" . $class . "' id=", wp_dropdown_pages( $dropdown_args ) );
}
