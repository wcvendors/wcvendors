<?php
/**
 * WC Vendors Admin Functions
 *
 * @package WCVendors/Traits
 */

namespace WCVendors\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WC Vendors Admin Functions
 */
trait Admin {
	/**
	 * Get all WC Vendors screen ids.
	 *
	 * @return array
	 */
	public function get_screen_ids() {

		$wc_screen_id = sanitize_title( __( 'WC Vendors', 'wc-vendors' ) );
		$screen_ids   = array(
			'toplevel_page_' . $wc_screen_id,
			$wc_screen_id . '_page_wcv-commissions',
			$wc_screen_id . '_page_wcv-vendors',
			$wc_screen_id . '_page_wcv-settings',
			$wc_screen_id . '_page_wcv-addons',
		);

		return apply_filters( 'wcvendors_screen_ids', $screen_ids );
	}

	/**
	 * Output a single select page drop down.
	 *
	 * @param string $id Option ID.
	 * @param string $value Selected value.
	 * @param string $class CSS classes.
	 * @param string $css CSS style rule.
	 */
	public function single_select_page( $id, $value, $class = '', $css = '' ) {

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

		// phpcs:ignore
		echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'wc-vendors' ) . "' style='" . $css . "' class='" . $class . "' id=", wp_dropdown_pages( $dropdown_args ) );
	}
}
