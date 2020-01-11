<?php
/**
 * Manage the Vendor Orders table.
 *
 * @package     WCVendors/Admin
 */

namespace WCVendors\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The vendor admin class handles all vendor related functions in admin view
 */
class VendorOrdersTable {

	/**
	 * Initialize WP hooks.
	 */
	public function init_hooks() {
		add_filter( 'request', array( $this, 'request' ) );
	}

	/**
	 * Filter request when query vendor order from admin.
	 *
	 * @param array $query_args Query arguments.
	 */
	public function request( $query_args ) {
		if ( isset( $query_args['post_status'] ) && empty( $query_args['post_status'] ) ) {
			$query_args['post_status'] = array_keys( wc_get_order_statuses() );
		}
		return $query_args;
	}
}
